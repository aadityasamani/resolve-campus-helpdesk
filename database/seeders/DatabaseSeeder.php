<?php
namespace Database\Seeders;
use App\Models\{User,Category,Complaint};
use Illuminate\Database\Seeder;
class DatabaseSeeder extends Seeder {
    public function run(): void {
        foreach(['Facilities'=>'Rooms, furniture, lighting and campus infrastructure.','Connectivity'=>'Wi-Fi, network and lab access.','Cleanliness'=>'Sanitation and shared spaces.','Other'=>'A concern that deserves a closer look.'] as $name=>$description)
            Category::firstOrCreate(['name'=>$name],['description'=>$description]);
        if (app()->environment('local') && env('DEMO_PASSWORD')) {
            $staff=User::firstOrCreate(['email'=>'staff@resolve.test'],['name'=>'Campus Service Desk','password'=>env('DEMO_PASSWORD')]);
            $staff->forceFill(['role'=>'staff'])->save();
            $student=User::firstOrCreate(['email'=>'student@resolve.test'],['name'=>'Aaditya','password'=>env('DEMO_PASSWORD')]);
            if (!Complaint::exists()) {
                foreach([['A flickering light in the reading room','Facilities','Library, reading room','Open'],['Wi-Fi drops during practical sessions','Connectivity','Computer Lab B','In progress'],['Water station needs cleaning','Cleanliness','Block A, first floor','Resolved']] as [$title,$category,$location,$status])
                    Complaint::create(['title'=>$title,'description'=>'DEMO: This is a sample campus concern for exploring the helpdesk. Edit or delete it freely.','category_id'=>Category::where('name',$category)->first()->id,'user_id'=>$student->id,'assigned_to'=>$staff->id,'location'=>$location,'priority'=>'Normal','status'=>$status,'resolved_at'=>$status==='Resolved'?now():null]);
            }
        }
    }
}
