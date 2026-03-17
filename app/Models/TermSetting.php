<?php
// ── app/Models/TermSetting.php ────────────────────────────────────────────

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TermSetting extends Model
{
    protected $fillable = [
        'session_id',
        'term_id',
        'resumption_date',
        'school_fees',
        'fees_payable_by',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'resumption_date' => 'date',
        'fees_payable_by' => 'date',
        'school_fees'     => 'decimal:2',
    ];

    public function session()
    {
        return $this->belongsTo(Session::class, 'session_id');
    }

    public function term()
    {
        return $this->belongsTo(Term::class, 'term_id');
    }
}