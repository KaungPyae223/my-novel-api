<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use MongoDB\Laravel\Eloquent\HybridRelations;



class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;
    use HybridRelations;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'full_name',
        'email',
        'username',
        'password',
        'profile_image',
        'cover_image',
        'profile_image_public_id',
        'cover_image_public_id',
        'about',
        'location',
        'phone',
        'facebook',
        'twitter',
        'instagram',
        'youtube',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function novels()
    {
        return $this->hasMany(Novel::class, 'user_id', 'id');
    }

    public function novelHistories()
    {
        return $this->hasMany(History::class, 'user_id', 'id')->where('historyable_type', Novel::class);
    }

    public function chapterHistories()
    {
        return $this->hasMany(History::class, 'user_id', 'id')->where('historyable_type', Chapter::class);
    }

    public function viewedNovels()
    {
        return $this->hasMany(View::class, 'user_id', 'id')->where('viewable_type', Novel::class);
    }

    public function viewedChapters()
    {
        return $this->hasMany(View::class, 'user_id', 'id')->where('viewable_type', Chapter::class);
    }

    public function favorites()
    {
        return $this->hasMany(Favorite::class, 'user_id', 'id');
    }
}
