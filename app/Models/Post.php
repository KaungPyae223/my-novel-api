<?php

namespace App\Models;

use App\Traits\CreateLog;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Post extends Model implements HasMedia
{
    /** @use HasFactory<\Database\Factories\PostFactory> */
    use HasFactory;
    use CreateLog;
    use InteractsWithMedia;

    protected $fillable = [
        'content',
        'user_id',
    ];

    

    public function postable()
    {
        return $this->morphTo();
    }

    public function love()
    {
        return $this->morphMany(Love::class, 'loveable');
    }

    public function comment()
    {
        return $this->hasMany(Comment::class, 'post_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class,'user_id');
    }

}
