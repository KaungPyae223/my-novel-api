<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;


class History extends Model
{
    /** @use HasFactory<\Database\Factories\HistoryFactory> */
    use HasFactory;
    protected $connection = 'mongodb';
    protected $collection = 'histories';

    protected $fillable = [
        'user_id',
    ];

    public function historyable()
    {
        return $this->morphTo();
    }
  
}
