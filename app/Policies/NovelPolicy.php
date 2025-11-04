<?php

namespace App\Policies;

use App\Models\Letter;
use App\Models\Novel;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class NovelPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Novel $novel): bool
    {
        return $user->id === $novel->user_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return false;
    }

    public function storeChapter(User $user,Novel $novel): bool
    {
        return $user->id === $novel->user_id;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Novel $novel): bool
    {
        return $user->id === $novel->user_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Novel $novel): bool
    {
        return $user->id === $novel->user_id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Novel $novel): bool
    {
        return false;
    }

    public function writeLetter(User $user, Novel $novel): bool
    {
        return $novel->open_letter === 'open' && $novel->ban()->where('user_id', $user->id)->exists() === false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Novel $novel): bool
    {
        return false;
    }

   
}
