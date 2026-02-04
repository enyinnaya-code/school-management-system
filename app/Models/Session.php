<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Session extends Model
{
    protected $table = 'school_sessions';
    protected $fillable = ['name', 'is_current', 'section_id'];

    protected $casts = [
        'is_current' => 'boolean',
    ];

    // Relationship with Section model
    public function section()
    {
        return $this->belongsTo(Section::class);
    }

    public function terms()
    {
        return $this->hasMany(Term::class);
    }

    public function issuedPins()
    {
        return $this->hasMany(IssuedPin::class);
    }

     // Add this relationship
    public function results()
    {
        return $this->hasMany(Result::class);
    }
    
}
