<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MiscFee extends Model
{
    use HasFactory;

    protected $table = 'misc_fee_types';
    protected $fillable = [
        'name',
        'description',
        'amount',
        'section_id',
        'created_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function section()
    {
        return $this->belongsTo(Section::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function payments()
    {
        return $this->hasMany(MiscFeePayment::class, 'misc_fee_type_id');
    }
}