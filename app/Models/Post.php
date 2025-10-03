<?php

namespace App\Models;

use App\Traits\CreateLog;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    /** @use HasFactory<\Database\Factories\PostFactory> */
    use HasFactory;
    use CreateLog;

    protected $fillable = [
        'content',
        'image',
        'image_public_id',
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

    public function novel()
    {
        return $this->belongsTo(Novel::class,'postable_id','id');
    }
}
