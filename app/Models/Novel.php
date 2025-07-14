<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Novel extends Model
{
    /** @use HasFactory<\Database\Factories\NovelFactory> */
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'synopsis',
        'tags',
        'image',
        'status',
        'genre_id',
        'image_public_id',
        'user_id',
        'unique_name',
    ];
}
