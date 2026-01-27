<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EClassParticipant extends Model
{
    use HasFactory;

    protected $table = 'e_class_participants';

    protected $fillable = [
        'session_id', 'user_id', 'joined_at', 'left_at', 'role'
    ];

    protected $casts = [
        'joined_at' => 'datetime',
        'left_at' => 'datetime',
    ];

    public function session()
    {
        return $this->belongsTo(EClassSession::class, 'session_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}