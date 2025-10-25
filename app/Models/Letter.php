<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Letter extends Model
{
    /** @use HasFactory<\Database\Factories\LetterFactory> */
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'novel_id',
        'body',
        'reply',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function novel()
    {
        return $this->belongsTo(Novel::class);
    }
}
