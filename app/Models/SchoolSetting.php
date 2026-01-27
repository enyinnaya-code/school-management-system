<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SchoolSetting extends Model
{
    protected $fillable = ['school_name', 'address', 'logo'];

    // Always load the first (and only) record
    public static function getSettings()
    {
        return self::firstOrCreate([]);
    }
}