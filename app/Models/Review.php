<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    /** @use HasFactory<\Database\Factories\ReviewFactory> */
    use HasFactory;

    protected $fillable = [
        'novel_id',
        'user_id',
        'review',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
