<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_name',
        'section_id',
        'added_by',
    ];

    public function section()
    {
        return $this->belongsTo(Section::class);
    }

    public function addedBy()
    {
        return $this->belongsTo(User::class, 'added_by');
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'course_user');
    }

    // public function teachers()
    // {
    //     return $this->belongsToMany(User::class, 'course_user', 'course_id', 'user_id');
    // }

    // If needed, reverse:
    public function classes()
    {
        return $this->belongsTo(SchoolClass::class, 'class_id'); // Adjust if needed
    }

    public function teachers()
    {
        return $this->belongsToMany(User::class, 'course_user', 'course_id', 'user_id')
            ->withPivot('section_id', 'class_id');
    }

    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    // In App\Models\Course.php

    public function schoolClasses()
    {
        return $this->belongsToMany(SchoolClass::class, 'class_course', 'course_id', 'school_class_id')
            ->withTimestamps();
    }

    public function results()
    {
        return $this->hasMany(Result::class);
    }
    
}
