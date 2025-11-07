<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Share extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'shares';

    protected $fillable = [
        'user_id',
    ];
}
