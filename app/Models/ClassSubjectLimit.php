<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ClassSubjectLimit extends Model
{
    use HasFactory;

    protected $table = 'class_subject_limits';

    protected $fillable = [
        'school_class_id',
        'min_subjects',
        'max_subjects',
        'created_by',
        'updated_by',
    ];

    public function schoolClass()
    {
        return $this->belongsTo(SchoolClass::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}