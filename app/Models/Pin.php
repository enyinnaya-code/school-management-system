<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pin extends Model
{
    use HasFactory;

    protected $fillable = ['section_id', 'session_id', 'term_id', 'pin_code', 'is_used', 'usage_count', 'created_by'];

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

     public function issuedPin()
    {
        return $this->hasOne(IssuedPin::class);
    }
}