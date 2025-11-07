<?php

namespace App\Traits;

use App\Http\Utils\WriteLog;

trait CreateUserLog
{
    public static function bootCreateUserLog()
    {

        static::updated(function ($model) {

            $changes = [];
            foreach ($model->getChanges() as $attribute => $newValue) {
                $original = $model->getOriginal($attribute);
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

                WriteLog::writeUserLog($title, $model, 'updated', $changes);
            }
        });
    }
}
