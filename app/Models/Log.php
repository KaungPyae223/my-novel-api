<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;

class Log extends Model
{
    /** @use HasFactory<\Database\Factories\LogFactory> */
    use HasFactory;
    
    protected $connection = 'mongodb';
    protected $collection = 'logs';

    protected $fillable = [
        'title',
        'user_id',
        'action',
        'ip_address',
        'user_agent',
        'description',
        'logable_id',
        'logable_type',
        'parentable_id',
        'parentable_type',
    ];

    public function logable()
    {
        return $this->morphTo();
    }

    public function parentable()
    {
        return $this->morphTo();
    }

    public function getUserAttribute()
    {
        return User::find($this->user_id);
    }
}
