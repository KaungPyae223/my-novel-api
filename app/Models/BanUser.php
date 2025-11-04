<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BanUser extends Model
{
    /** @use HasFactory<\Database\Factories\BanUserFactory> */
    use HasFactory;

    protected $table = 'ban_users';

    protected $fillable = [
        'user_id',
        'novel_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

}
