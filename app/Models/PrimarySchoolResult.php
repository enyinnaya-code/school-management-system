<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PrimarySchoolResult extends Model
{
    use HasFactory;

    protected $table = 'primary_school_results';

    protected $fillable = [
        'student_id',
        'course_id',
        'session_id',
        'term_id',
        'first_half_obtainable',
        'first_half_obtained',
        'second_half_obtainable',
        'second_half_obtained',
        'final_obtainable',
        'final_obtained',
        'class_average',
        'teacher_remark',
        'uploaded_by',
    ];

    public function student()   { return $this->belongsTo(User::class, 'student_id'); }
    public function course()    { return $this->belongsTo(Course::class); }
    public function session()   { return $this->belongsTo(Session::class); }
    public function term()      { return $this->belongsTo(Term::class); }
    public function uploadedBy(){ return $this->belongsTo(User::class, 'uploaded_by'); }
}