<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Letter extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $connection = 'mongodb';
    protected $collection = 'letters';

    protected $fillable = [
        'user_id',
        'novel_id',
        'body',
        'reply',
        'status',
    ];

    public function getUserAttribute()
    {
        return User::find($this->user_id);
    }

    public function getNovelAttribute() 
    {
        return Novel::find($this->novel_id);
    }
}
