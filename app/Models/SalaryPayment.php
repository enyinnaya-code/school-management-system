<?php
// app/Models/SalaryPayment.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalaryPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'payroll_id',
        'employee_id',
        'section_id',
        'session_id',
        'term_id',
        'month',
        'year',
        'basic_salary',
        'allowances',
        'deductions',
        'total',
        'net_pay',
        'bank_name',
        'account_number',
        'description',
        'status',
        'processed_by',
        'processed_at',
    ];

    protected $casts = [
        'basic_salary' => 'decimal:2',
        'allowances' => 'decimal:2',
        'deductions' => 'decimal:2',
        'total' => 'decimal:2',
        'net_pay' => 'decimal:2',
        'processed_at' => 'datetime',
    ];

    // Relationships
    public function payroll()
    {
        return $this->belongsTo(Payroll::class);
    }

    public function employee()
    {
        return $this->belongsTo(User::class, 'employee_id');
    }

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

    public function processedBy()
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    public function getEmployeeNameAttribute()
    {
        if ($this->employee) {
            return $this->employee->name;
        }

        // Fallback: get from payroll
        return $this->payroll?->staff_name ?? 'N/A';
    }
}
