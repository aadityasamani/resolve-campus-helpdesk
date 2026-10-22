<?php
namespace App\Http\Controllers;
use App\Models\{Complaint,Category,Comment,User};
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
class ResolveController extends Controller {
    private function visible(Request $r) {
        return Complaint::query()->when($r->user()->role!=='staff',fn($q)=>$q->where('user_id',$r->user()->id));
    }
    private function access(Request $r, Complaint $c): void {
        abort_unless($r->user()->role==='staff' || $c->user_id===$r->user()->id,403);
    }
    private function staff(Request $r): void { abort_unless($r->user()->role==='staff',403); }
    private function filtered(Request $r) {
        $r->validate(['q'=>'nullable|string|max:150','status'=>['nullable',Rule::in(Complaint::STATUSES)],'category'=>'nullable|exists:categories,id']);
        return $this->visible($r)->when($r->filled('q'),fn($q)=>$q->where(function($q)use($r){$q->where('title','like','%'.$r->q.'%')->orWhere('location','like','%'.$r->q.'%');}))
            ->when($r->filled('status'),fn($q)=>$q->where('status',$r->status))->when($r->filled('category'),fn($q)=>$q->where('category_id',$r->category));
    }
    public function dashboard(Request $r) {
        $query=$this->visible($r);
        $counts=collect(Complaint::STATUSES)->mapWithKeys(fn($s)=>[$s=>(clone $query)->where('status',$s)->count()]);
        $recent=(clone $query)->with('category','user')->latest()->limit(5)->get();
        return view('dashboard',compact('counts','recent'));
    }
    public function index(Request $r) {
        $complaints=$this->filtered($r)->with('category','user','assignee')->latest()->paginate(10)->withQueryString();
        $categories=Category::orderBy('name')->get(); return view('complaints.index',compact('complaints','categories'));
    }
    public function create() { return $this->form(new Complaint); }
    private function form(Complaint $complaint) {
        return view('complaints.form',['complaint'=>$complaint,'categories'=>Category::orderBy('name')->get(),'staff'=>User::where('role','staff')->get()]);
    }
    private function validated(Request $r): array {
        $data=$r->validate(['title'=>'required|string|max:150','description'=>'required|string|min:10|max:10000',
            'location'=>'required|string|max:150','category_id'=>'required|exists:categories,id','priority'=>['required',Rule::in(Complaint::PRIORITIES)],
            'status'=>[$r->user()->role==='staff'?'required':'nullable',Rule::in(Complaint::STATUSES)],'assigned_to'=>['nullable',Rule::exists('users','id')->where('role','staff')]]);
        if ($r->user()->role!=='staff') { unset($data['status'],$data['assigned_to']); }
        return $data;
    }
    public function store(Request $r) {
        $data=$this->validated($r); $data['user_id']=$r->user()->id;
        if (($data['status']??'Open')==='Resolved') $data['resolved_at']=now();
        $c=Complaint::create($data); return redirect()->route('complaints.show',$c)->with('success','Complaint submitted. Your voice is on the record.');
    }
    public function show(Request $r, Complaint $complaint) {
        $this->access($r,$complaint); $complaint->load('category','user','assignee','comments.user');
        return view('complaints.show',compact('complaint'));
    }
    public function edit(Request $r, Complaint $complaint) { $this->access($r,$complaint); return $this->form($complaint); }
    public function update(Request $r, Complaint $complaint) {
        $this->access($r,$complaint); $data=$this->validated($r);
        if ($r->user()->role==='staff') $data['resolved_at']=($data['status']??$complaint->status)==='Resolved' ? ($complaint->resolved_at??now()) : null;
        $complaint->update($data); return redirect()->route('complaints.show',$complaint)->with('success','Complaint updated successfully.');
    }
    public function destroy(Request $r, Complaint $complaint) {
        $this->access($r,$complaint); $complaint->delete(); return redirect()->route('complaints.index')->with('success','Complaint and its follow-ups deleted.');
    }
    public function comment(Request $r, Complaint $complaint) {
        $this->access($r,$complaint); $data=$r->validate(['body'=>'required|string|max:3000']);
        $complaint->comments()->create($data+['user_id'=>$r->user()->id]); return back()->with('success','Follow-up added to the timeline.');
    }
    public function categories(Request $r) {
        $this->staff($r); return view('categories',['categories'=>Category::withCount('complaints')->orderBy('name')->get(),'editing'=>null]);
    }
    public function categoryEdit(Request $r, Category $category) {
        $this->staff($r); return view('categories',['categories'=>Category::withCount('complaints')->orderBy('name')->get(),'editing'=>$category]);
    }
    public function categorySave(Request $r, ?Category $category=null) {
        $this->staff($r); $data=$r->validate(['name'=>['required','string','max:60',Rule::unique('categories')->ignore($category?->id)],'description'=>'nullable|string|max:2000']);
        if ($category) $category->update($data); else Category::create($data);
        return redirect()->route('categories.index')->with('success','Category saved.');
    }
    public function categoryDelete(Request $r, Category $category) {
        $this->staff($r);
        if ($category->complaints()->exists()) return back()->with('error','This category is used by complaints. Reassign them before deleting it.');
        $category->delete(); return redirect()->route('categories.index')->with('success','Category deleted.');
    }
    public function reports(Request $r) {
        $rows=$this->filtered($r)->with('user','category','assignee')->latest()->get();
        $summary=$rows->groupBy('status')->map->count(); $categories=Category::orderBy('name')->get();
        return view('reports',compact('rows','summary','categories'));
    }
}
