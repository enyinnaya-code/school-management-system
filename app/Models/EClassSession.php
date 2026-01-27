<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class EClassSession extends Model
{
    use HasFactory;

    protected $table = 'e_class_sessions';

    protected $fillable = [
        'teacher_id',
        'class_id',
        'course_id',
        'title',
        'description',
        'room_name',
        'password',
        'start_time',
        'duration_minutes',
        'is_active',
    ];

    protected $casts = [
        'start_time' => 'datetime',
    ];

    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function schoolClass()
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function course()
    {
        return $this->belongsTo(Course::class, 'course_id');
    }

    public function participants()
    {
        return $this->hasMany(EClassParticipant::class, 'session_id');
    }

    public function getJitsiUrlAttribute()
    {
        $base = "https://meet.jit.si";
        $room = $this->room_name;

        $config = [
            'roomName' => $room,
            'parentNode' => '#jitsi-container',
            'width' => '100%',
            'height' => 600,
        ];

        if ($this->password) {
            $config['password'] = $this->password;
        }

        return "$base/$room#" . http_build_query(['config' => $config]);
    }

    public function isOngoing()
    {
        $now = Carbon::now();
        $end = $this->start_time->copy()->addMinutes($this->duration_minutes);

        return $now->between($this->start_time, $end) && $this->is_active;
    }
}