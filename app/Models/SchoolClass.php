<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Section;
use App\Models\User;

class SchoolClass extends Model
{
    use HasFactory;

    protected $table = 'school_classes';

    protected $fillable = [
        'name',
        'section_id',
        'added_by',
    ];

    // Relationship with Section
    public function section()
    {
        return $this->belongsTo(Section::class);
    }

    // Relationship with User (added_by)
    public function user()
    {
        return $this->belongsTo(User::class, 'added_by');
    }

    public function studentAttendances()
    {
        return $this->hasMany(StudentAttendance::class, 'class_id');
    }

    public function courses()
    {
        return $this->hasMany(Course::class, 'class_id');
    }

    // Teachers directly assigned to this class via class_user table
    public function directlyAssignedTeachers()
    {
        return $this->belongsToMany(User::class, 'class_user', 'school_class_id', 'user_id');
    }

    // Teachers assigned through course assignments (course_user table)
    public function assignedTeachers()
    {
        return $this->belongsToMany(User::class, 'course_user', 'class_id', 'user_id');
    }

    // Courses taught in this class via course_user table
    public function taughtCourses()
    {
        return $this->belongsToMany(Course::class, 'course_user', 'class_id', 'course_id')
            ->withPivot('user_id', 'section_id');
    }

    // Get ALL teachers assigned to this class (both directly and through courses)
    public function allAssignedTeachers()
    {
        $directTeachers = $this->directlyAssignedTeachers()->pluck('users.id');
        $courseTeachers = $this->assignedTeachers()->pluck('users.id');

        $allTeacherIds = $directTeachers->merge($courseTeachers)->unique();

        return User::whereIn('id', $allTeacherIds)->get();
    }

    // NEW: Students in this class
    public function students()
    {
        return $this->hasMany(User::class, 'class_id');
    }

    // In App\Models\SchoolClass.php

    public function offeredCourses()
    {
        return $this->belongsToMany(Course::class, 'class_course', 'school_class_id', 'course_id')
            ->withTimestamps();
    }


    public function tests()
    {
        return $this->belongsToMany(Test::class, 'test_class', 'school_class_id', 'test_id');
    }
}
