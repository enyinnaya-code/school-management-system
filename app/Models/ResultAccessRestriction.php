<?php
// ── app/Models/ResultAccessRestriction.php ────────────────────────────────

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ResultAccessRestriction extends Model
{
    protected $fillable = [
        'student_id',
        'session_id',
        'term_id',
        'reason',
        'blocked_by',
    ];

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function session()
    {
        return $this->belongsTo(Session::class, 'session_id');
    }

    public function term()
    {
        return $this->belongsTo(Term::class, 'term_id');
    }

    public function blockedBy()
    {
        return $this->belongsTo(User::class, 'blocked_by');
    }
}


