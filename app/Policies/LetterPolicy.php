<?php

namespace App\Policies;

use App\Models\Letter;
use App\Models\User;

class LetterPolicy
{
    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
        //
    }

    public function replyLetter(User $user, Letter $letter): bool
    {
        return $user->id === $letter->novel->user_id;
    }

    public function deleteLetter(User $user, Letter $letter): bool
    {
        return $user->id === $letter->user_id;
    }
}
