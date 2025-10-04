<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class View extends Model
{
    /** @use HasFactory<\Database\Factories\ViewFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
    ];

    public function viewable()
    {
        return $this->morphTo();
    }

    public function novel()
    {
        return $this->belongsTo(Novel::class);
    }
}
