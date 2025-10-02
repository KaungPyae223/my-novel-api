<?php

namespace App\Models;

use App\Traits\CreateLog;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Novel extends Model
{
    /** @use HasFactory<\Database\Factories\NovelFactory> */
    use HasFactory,SoftDeletes;

    use CreateLog;

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

    public function softDelete()
    {
        $this->delete();
    }

    
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

    public function logs()
    {
        return $this->morphMany(Log::class, 'logable');
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

    public function log()
    {
        return $this->morphMany(Log::class, 'logable');
    }
}
