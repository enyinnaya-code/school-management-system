<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OtherExpense extends Model
{
    use HasFactory;

    protected $fillable = ['amount', 'description', 'section_id', 'session_id', 'term_id', 'created_by'];


    protected $casts = [
        'amount' => 'decimal:2',
    ];

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

    /**
     * Get the section that owns the other expense.
     */
    public function section()
    {
        return $this->belongsTo(Section::class);
    }
}
