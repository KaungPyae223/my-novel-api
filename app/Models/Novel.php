<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Novel extends Model
{
    /** @use HasFactory<\Database\Factories\NovelFactory> */
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'synopsis',
        'tags',
        'image',
        'status',
        'progress',
        'genre_id',
        'views',
        'image_public_id',
        'user_id',
        'unique_name',
    ];

    public function genre()
    {
        return $this->belongsTo(Genre::class, 'genre_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function chapters()
    {
        return $this->hasMany(Chapter::class, 'novel_id', 'id');
    }

    public function posts()
    {
        return $this->morphMany(post::class, 'postable');
    }

    public function love()
    {
        return $this->morphMany(Love::class, 'loveable');
    }

    public function view()
    {
        return $this->morphMany(View::class, 'viewable');
    }

    public function history()
    {
        return $this->morphMany(History::class, 'historyable');
    }

    public function review()
    {
        return $this->hasMany(Review::class, 'novel_id', 'id');
    }

    public function favorite()
    {
        return $this->hasMany(Favorite::class, 'novel_id', 'id');
    }
}
