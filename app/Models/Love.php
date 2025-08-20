<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Love extends Model
{
    /** @use HasFactory<\Database\Factories\LoveFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
    ];

    public function loveable()
    {
        return $this->morphTo();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
