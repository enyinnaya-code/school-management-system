<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PrimaryResultSection extends Model
{
    use HasFactory;

    protected $table = 'primary_result_section';

    protected $fillable = [
        'section_id',
        'created_by',
        'updated_by',
    ];

    public function section()
    {
        return $this->belongsTo(Section::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}