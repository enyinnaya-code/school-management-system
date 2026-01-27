<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CounsellingSession extends Model
{
    protected $fillable = [
        'student_id',
        'counsellor_id',
        'session_date',
        'session_time',
        'reason',
        'notes',
        'follow_up_date',
        'follow_up_notes',
        'status',
    ];

    protected $casts = [
        'session_date' => 'date',
        'follow_up_date' => 'date',
        'session_time' => 'datetime:H:i',
    ];

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function counsellor()
    {
        return $this->belongsTo(User::class, 'counsellor_id');
    }
}