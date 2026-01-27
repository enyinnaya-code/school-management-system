<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ExamQuestion extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'section_id',
        'session_id',
        'term_id',
        'class_id',
        'subject_id',
        'created_by',
        'exam_title',
        'exam_type',
        'exam_date',
        'duration_minutes',
        'total_marks',
        'instructions',
        'sections',
        'school_name',
        'school_address',
        'school_logo',
        'status',
        'show_marking_scheme',
        'marking_scheme',
        'notes',
    ];

    protected $casts = [
        'sections' => 'array',
        'exam_date' => 'date',
        'show_marking_scheme' => 'boolean',
    ];

    // Relationships
    public function section()
    {
        return $this->belongsTo(Section::class);
    }

    public function session()
    {
        return $this->belongsTo(Session::class, 'session_id');
    }

    public function term()
    {
        return $this->belongsTo(Term::class);
    }

    public function schoolClass()
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function subject()
    {
        return $this->belongsTo(Course::class, 'subject_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Accessors
    public function getFormattedExamDateAttribute()
    {
        return $this->exam_date ? $this->exam_date->format('F j, Y') : 'Not Set';
    }

    public function getDurationFormattedAttribute()
    {
        if (!$this->duration_minutes) {
            return 'Not Set';
        }

        $hours = floor($this->duration_minutes / 60);
        $minutes = $this->duration_minutes % 60;

        if ($hours > 0 && $minutes > 0) {
            return "{$hours} hour(s) {$minutes} minute(s)";
        } elseif ($hours > 0) {
            return "{$hours} hour(s)";
        } else {
            return "{$minutes} minute(s)";
        }
    }

    // Scopes
    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopeByTeacher($query, $teacherId)
    {
        return $query->where('created_by', $teacherId);
    }

    public function scopeForCurrentTerm($query)
    {
        $currentTerm = Term::where('is_current', 1)->first();
        if ($currentTerm) {
            return $query->where('term_id', $currentTerm->id);
        }
        return $query;
    }
}
