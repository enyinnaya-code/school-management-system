<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentRemark extends Model
{
    protected $fillable = [
        'student_id',
        'class_id',
        'session_id',
        'term_id',
        'affective_ratings',
        'psychomotor_ratings',
        'teacher_remark',
        'principal_remark',
        'updated_by',
    ];

    protected $casts = [
        'affective_ratings' => 'array',
        'psychomotor_ratings' => 'array',
    ];

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function class()
    {
        return $this->belongsTo(SchoolClass::class);
    }

    public function session()
    {
        return $this->belongsTo(Session::class);
    }

    public function term()
    {
        return $this->belongsTo(Term::class);
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
