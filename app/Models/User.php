<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'user_type',
        'phone',
        'added_by',
        'is_active',
        'login_attempts',
        'class_id',
        'gender',
        'admission_no',
        'dob',
        'guardian_name',
        'guardian_phone',
        'guardian_email',
        'guardian_address',
        'address',
        'is_form_teacher',
        'is_librarian',
        'hostel_id',
        'form_class_id'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function class()
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function announcements()
    {
        return $this->belongsToMany(Announcement::class, 'announcement_user')
            ->withPivot('read_at')
            ->withTimestamps();
    }

    public function unreadAnnouncements()
    {
        return $this->announcements()
            ->wherePivot('read_at', null);
    }

    public function unreadAnnouncementsCount()
    {
        return $this->unreadAnnouncements()->count();
    }

    public function markAnnouncementAsRead($announcementId)
    {
        $this->announcements()->updateExistingPivot($announcementId, [
            'read_at' => Carbon::now()
        ]);
    }

    public function markAllAnnouncementsAsRead()
    {
        $this->announcements()
            ->whereNull('announcement_user.read_at')
            ->update(['announcement_user.read_at' => Carbon::now()]);
    }

    public function sections()
    {
        return $this->belongsToMany(Section::class, 'section_user', 'user_id', 'section_id');
    }

    public function classes()
    {
        return $this->belongsToMany(SchoolClass::class, 'class_user', 'user_id', 'school_class_id');
    }

    // FIXED: Removed the extra 'section_id' parameter and added withPivot for additional columns
    public function courses()
    {
        return $this->belongsToMany(Course::class, 'course_user', 'user_id', 'course_id')
            ->withPivot('section_id', 'class_id')
            ->withTimestamps();
    }

    public function students()
    {
        return $this->belongsToMany(User::class, 'parent_student', 'parent_id', 'student_id');
    }

    public function parents()
    {
        return $this->belongsToMany(User::class, 'parent_student', 'student_id', 'parent_id');
    }

    public function section()
    {
        return $this->belongsTo(Section::class, 'section');
    }

    public function studentAttendances()
    {
        return $this->hasMany(StudentAttendance::class, 'student_id');
    }

    public function schoolClass()
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function taughtCourses()
    {
        return $this->belongsToMany(Course::class, 'course_user', 'user_id', 'course_id');
    }

    public function sectionRelation()
    {
        return $this->belongsTo(Section::class, 'section', 'id');
    }


    public function formClass()
    {
        return $this->belongsTo(SchoolClass::class, 'form_class_id');
    }


    /**
     * The hostel this student belongs to
     */
    public function hostel()
    {
        return $this->belongsTo(Hostel::class, 'hostel_id');
    }

    /**
     * The hostels this user (warden/teacher) is assigned to
     */
    public function hostelsAsWarden()
    {
        return $this->belongsToMany(Hostel::class, 'hostel_warden', 'user_id', 'hostel_id');
    }
}
