<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Log extends Model
{
    /** @use HasFactory<\Database\Factories\LogFactory> */
    use HasFactory;

    protected $fillable = [
        'title',
        'user_id',
        'action',
        'ip_address',
        'user_agent',
        'description',
        'logable_id',
        'logable_type',
    ];

    public function logable()
    {
        return $this->morphTo();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
