<?php
namespace Tests\Feature;
use App\Models\{User,Category,Complaint};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
class ResolveTest extends TestCase {
    use RefreshDatabase;
    private function person(bool $staff=false): User {
        $u=User::factory()->create(); if($staff)$u->forceFill(['role'=>'staff'])->save(); return $u;
    }
    private function data(): array {
        return ['title'=>'Broken lamp','description'=>'The lamp flickers during evening study.','location'=>'Library','priority'=>'Normal','category_id'=>Category::create(['name'=>'Facilities'])->id];
    }
    public function test_guests_cannot_view_or_mutate_records(): void {
        $this->get('/complaints')->assertRedirect('/login'); $this->post('/complaints',[])->assertRedirect('/login');
        $this->get('/reports')->assertRedirect('/login');
    }
    public function test_registration_login_logout_and_role_protection(): void {
        $this->post('/register',['name'=>'Aaditya','email'=>'a@example.com','password'=>'A-strong-pass123','password_confirmation'=>'A-strong-pass123','role'=>'staff'])->assertRedirect('/dashboard');
        $this->assertAuthenticated(); $this->assertDatabaseHas('users',['email'=>'a@example.com','role'=>'student']);
        $this->post('/logout')->assertRedirect('/login'); $this->assertGuest();
        $this->post('/login',['email'=>'a@example.com','password'=>'wrong'])->assertSessionHasErrors('email');
        $this->post('/login',['email'=>'a@example.com','password'=>'A-strong-pass123'])->assertRedirect('/dashboard'); $this->assertAuthenticated();
    }
    public function test_student_crud_and_staff_fields_cannot_be_forged(): void {
        $user=$this->person(); $data=$this->data();
        $this->actingAs($user)->post('/complaints',$data+['status'=>'Resolved'])->assertRedirect();
        $c=Complaint::first(); $this->assertSame('Open',$c->status);
        $this->get('/complaints/'.$c->id)->assertOk()->assertSee('Broken lamp');
        $this->put('/complaints/'.$c->id,array_merge($data,['title'=>'Lamp repair needed']))->assertRedirect();
        $this->assertDatabaseHas('complaints',['id'=>$c->id,'title'=>'Lamp repair needed']);
        $this->delete('/complaints/'.$c->id)->assertRedirect('/complaints'); $this->assertDatabaseCount('complaints',0);
    }
    public function test_cross_user_access_is_denied_and_reports_are_scoped(): void {
        $owner=$this->person(); $other=$this->person(); $c=Complaint::create($this->data()+['user_id'=>$owner->id]);
        $this->actingAs($other)->get('/complaints/'.$c->id)->assertForbidden();
        $this->put('/complaints/'.$c->id,[])->assertForbidden(); $this->delete('/complaints/'.$c->id)->assertForbidden();
        $this->get('/reports')->assertOk()->assertDontSee('Broken lamp');
    }
    public function test_staff_categories_crud_and_restrict_in_use_delete(): void {
        $this->actingAs($this->person(true))->post('/categories',['name'=>'Connectivity','description'=>'Networks'])->assertRedirect('/categories');
        $c=Category::first(); $this->get('/categories')->assertOk(); $this->get('/categories/'.$c->id.'/edit')->assertOk();
        $this->put('/categories/'.$c->id,['name'=>'Internet','description'=>'Access'])->assertRedirect();
        $this->assertDatabaseHas('categories',['id'=>$c->id,'name'=>'Internet']);
        $this->delete('/categories/'.$c->id)->assertRedirect(); $this->assertDatabaseCount('categories',0);
        $data=$this->data(); Complaint::create($data+['user_id'=>auth()->id()]);
        $this->delete('/categories/'.$data['category_id'])->assertSessionHas('error'); $this->assertDatabaseCount('categories',1);
    }
    public function test_student_cannot_manage_categories(): void {
        $this->actingAs($this->person())->get('/categories')->assertForbidden();
        $this->post('/categories',['name'=>'No'])->assertForbidden();
    }
    public function test_validation_staff_status_comments_search_and_reports(): void {
        $staff=$this->person(true); $this->actingAs($staff)->post('/complaints',[])->assertSessionHasErrors(['title','description','category_id']);
        $data=$this->data()+['status'=>'In progress','assigned_to'=>$staff->id];
        $this->post('/complaints',$data)->assertRedirect(); $c=Complaint::first();
        $this->post('/complaints/'.$c->id.'/comments',['body'=>'A technician is on the way.'])->assertRedirect();
        $this->get('/complaints/'.$c->id)->assertOk()->assertSee('A technician is on the way.');
        $this->put('/complaints/'.$c->id,array_merge($data,['status'=>'Resolved']))->assertRedirect();
        $this->assertNotNull($c->fresh()->resolved_at);
        $this->get('/complaints?q=Broken&status=Resolved')->assertOk()->assertSee('Broken lamp');
        $this->get('/reports?status=Resolved')->assertOk()->assertSee('Broken lamp');
        $this->get('/reports?status=Open')->assertOk()->assertDontSee('Broken lamp');
        $this->get('/dashboard')->assertOk();
    }
}
