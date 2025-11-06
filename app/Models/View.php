<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;

class View extends Model
{
    /** @use HasFactory<\Database\Factories\ViewFactory> */
    use HasFactory;

    protected $connection = 'mongodb';
    protected $collection = 'views';

    protected $fillable = [
        'user_id',
    ];

    public function getNovelAttribute()
    {
        return Novel::find($this->viewable_id);
    }


}
