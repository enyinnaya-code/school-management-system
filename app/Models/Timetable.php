<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Timetable extends Model
{
    use HasFactory;

    protected $fillable = [
        'section_id',
        'session_id',
        'term_id',
        'lesson_duration',
        'break_duration',
        'num_breaks',
        'num_periods', // Added
        'has_free_periods',
        'schedule',
        'created_by',
    ];

    protected $casts = [
        'schedule' => 'array',
        'has_free_periods' => 'boolean',
    ];

    public function section()
    {
        return $this->belongsTo(Section::class);
    }

    public function session()
    {
        return $this->belongsTo(Session::class);
    }

    public function term()
    {
        return $this->belongsTo(Term::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}