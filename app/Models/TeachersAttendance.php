<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TeachersAttendance extends Model
{
    use HasFactory;

    protected $table = 'teachers_attendance';

    protected $fillable = [
        'teacher_id',
        'attendance',
        'date',
        'time',
        'session_id',
        'session_term',
    ];

    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id')->where('user_type', 3);
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