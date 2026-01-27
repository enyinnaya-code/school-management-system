<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\SchoolClass;
use App\Models\Session;
use App\Models\Term;

class StudentAttendance extends Model
{
    protected $table = 'students_attendance';
    protected $fillable = [
        'student_id',
        'class_id',
        'session_id',
        'session_term',
        'date',
        'time',
        'attendance',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function schoolClass()
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function session()
    {
        return $this->belongsTo(Session::class, 'session_id');
    }

    public function term()
    {
        return $this->belongsTo(Term::class, 'session_term');
    }
}