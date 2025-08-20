<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Chapter extends Model
{
    /** @use HasFactory<\Database\Factories\ChapterFactory> */
    use HasFactory;

    protected $fillable = [
        'title',
        'status',
        'novel_id',
        'content',
        'summary',
        'scheduled_date',
    ];

    public function novel()
    {
        return $this->belongsTo(Novel::class, 'novel_id', 'id');
    }

    public function love()
    {
        return $this->morphMany(Love::class, 'loveable');
    }
}
