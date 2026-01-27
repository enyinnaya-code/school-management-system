<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payroll extends Model
{
    use HasFactory;

    protected $table = 'payrolls';

    protected $fillable = [
        'employee_id',
        'section_id',
        'staff_name',
        'basic_salary',
        'allowances',
        'deductions',
        'net_salary',
        'payroll_date',
        'bank_name',           // Add this
        'account_number',      // Add this
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'payroll_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relationship to the Employee (User)
    public function employee()
    {
        return $this->belongsTo(User::class, 'employee_id');
    }

    // Relationship to the Section
    public function section()
    {
        return $this->belongsTo(Section::class, 'section_id');
    }

    // Relationship to the User who created the payroll
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Relationship to the User who last updated the payroll
    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }


    public function getSectionNameAttribute()
    {
        return $this->section_id == 0
            ? 'Administrative / Not Applicable'
            : ($this->section?->section_name ?? 'N/A');
    }

    // Helper to get display name
    public function getDisplayNameAttribute()
    {
        return $this->employee ? $this->employee->name : $this->staff_name;
    }

    public function getDisplayEmailAttribute()
    {
        return $this->employee ? $this->employee->email : null;
    }


    
}
