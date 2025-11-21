<?php

namespace App\Models;
use MongoDB\Laravel\Eloquent\Model;

class Subscribe extends Model
{
    protected $table = 'subscriptions';
    protected $connection = 'mongodb';

    protected $fillable = [
        'endpoint',
        'p256dh',
        'auth',
    ];
}
