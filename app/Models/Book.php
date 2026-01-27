<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Book extends Model
{
    protected $fillable = ['title', 'author', 'isbn', 'quantity', 'available_quantity', 'description', 'publisher', 'publication_year'];

    public function borrowings()
    {
        return $this->hasMany(Borrowing::class);
    }
}