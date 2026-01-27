<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Hostel extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
    ];

    // Wardens (teachers in charge) - many-to-many with users (teachers)
    public function wardens()
    {
        return $this->belongsToMany(User::class, 'hostel_warden', 'hostel_id', 'user_id');
    }

    // Students allocated to this hostel
    public function students()
    {
        return $this->hasMany(User::class, 'hostel_id');
    }
}