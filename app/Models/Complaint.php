<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Complaint extends Model {
    public const STATUSES = ['Open','In progress','Resolved'];
    public const PRIORITIES = ['Low','Normal','Urgent'];
    protected $fillable = ['user_id','category_id','assigned_to','title','description','location','priority','status','resolved_at'];
    protected function casts(): array { return ['resolved_at'=>'datetime']; }
    public function user() { return $this->belongsTo(User::class); }
    public function category() { return $this->belongsTo(Category::class); }
    public function assignee() { return $this->belongsTo(User::class,'assigned_to'); }
    public function comments() { return $this->hasMany(Comment::class); }
}
