<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Term extends Model
{
    use HasFactory;

    protected $fillable = ['session_id', 'name', 'is_current'];

    public function session()
    {
        return $this->belongsTo(Session::class);
    }
}