<?php

namespace App\Observers;

use App\Http\Utils\WriteLog;
use App\Models\User;

class UserObserver
{
    /**
     * Handle the User "created" event.
     */
    public function created(User $user): void
    {
        //
    }

    /**
     * Handle the User "updated" event.
     */
    public function updated(User $user): void
    {
         $changes = [];
            foreach ($user->getChanges() as $attribute => $newValue) {
                $original = $user->getOriginal($attribute);
                if ($original !== $newValue) {
                    $changes[$attribute] = [
                        'original' => $original,
                        'new' => $newValue,
                    ];
                }
            }

            unset($changes['created_at'], $changes['updated_at']);

            if (!empty($changes)) {

                if (isset($changes['password'])) {
                    $title = "Password updated";
                } elseif (isset($changes['email_verified_at'])) {
                    $title = "Email verified";
                } elseif (isset($changes['profile_image'])) {
                    $title = "Profile image updated";
                } elseif (isset($changes['cover_image'])) {
                    $title = "Cover image updated";
                } else {
                    $title = "Profile information updated";
                }

                unset($changes['password'], $changes['email_verified_at'], $changes['profile_image_public_id'], $changes['cover_image_public_id']);

                WriteLog::writeUserLog($title, $user, 'updated', $changes);
            }
    }

    /**
     * Handle the User "deleted" event.
     */
    public function deleted(User $user): void
    {
        //
    }

    /**
     * Handle the User "restored" event.
     */
    public function restored(User $user): void
    {
        //
    }

    /**
     * Handle the User "force deleted" event.
     */
    public function forceDeleted(User $user): void
    {
        //
    }
}
