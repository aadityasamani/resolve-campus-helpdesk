<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Comment extends Model {
    protected $fillable = ['complaint_id','user_id','body'];
    public function user() { return $this->belongsTo(User::class); }
    public function complaint() { return $this->belongsTo(Complaint::class); }
}
