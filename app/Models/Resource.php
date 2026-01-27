<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Resource extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'author',
        'description',
        'resource_type',
        'file_path',
        'url',
        'publisher',
        'publication_year',
    ];
}