<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

class MiscFeePayment extends Model
{
    use HasFactory;

    protected $table = 'misc_fee_payments';
    protected $fillable = [
        'misc_fee_type_id',
        'student_id',
        'amount_paid',
        'payment_date',
        'receipt_number',
        'status',
        'paid_by',
        'notes',
    ];

    protected $casts = [
        'amount_paid' => 'decimal:2',
        'payment_date' => 'date',
    ];

    public function miscFeeType()
    {
        return $this->belongsTo(MiscFee::class, 'misc_fee_type_id');
    }

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function paidBy()
    {
        return $this->belongsTo(User::class, 'paid_by');
    }

    public function section(): HasOneThrough
    {
        return $this->hasOneThrough(
            Section::class,
            User::class,
            'id', // Foreign key on the intermediate model (User)
            'id', // Local key on the target model (Section)
            'student_id', // Local key on the current model (MiscFeePayment)
            'section' // Foreign key on the intermediate model (User -> section)
        );
    }
}