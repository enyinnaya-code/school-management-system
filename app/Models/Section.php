<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Section extends Model
{
    use HasFactory;

    protected $table = 'sections';
    protected $fillable = ['section_name', 'created_by'];

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function sessions()
    {
        return $this->hasMany(Session::class, 'section_id');
    }

    // ADD THIS: Relationship to classes in this section
    public function classes()
    {
        return $this->hasMany(SchoolClass::class, 'section_id');
    }

    public function assignedTeachers()
    {
        return $this->belongsToMany(User::class, 'course_user', 'section_id', 'user_id')
            ->withPivot('course_id', 'class_id');
    }
}
