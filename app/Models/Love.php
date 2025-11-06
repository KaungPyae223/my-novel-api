<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;

class Love extends Model
{
    /** @use HasFactory<\Database\Factories\LoveFactory> */
    use HasFactory;
    
    protected $connection = 'mongodb';
    protected $collection = 'loves';

    protected $fillable = [
        'user_id',
    ];

    public function loveable()
    {
        return $this->morphTo();
    }

    public function getUserAttribute()
    {
        return User::find($this->user_id);
    }
}
