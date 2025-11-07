<?php

namespace App\Traits;

use App\Http\Utils\WriteLog;
use Illuminate\Database\Eloquent\SoftDeletes;

trait CreateLog
{
    public static function bootCreateLog()
    {
        static::created(function ($model) {
            WriteLog::write($model, 'created', $model->getAttributes());
        });

        static::updated(function ($model) {

            $changes = [];
            foreach ($model->getChanges() as $attribute => $newValue) {
                $original = $model->getOriginal($attribute);
                if ($original != $newValue) {
                    $changes[$attribute] = [
                        'old' => $original,
                        'new' => $newValue,
                    ];
                }
            }

            unset($changes['created_at'], $changes['updated_at'], $changes['deleted_at']);

            if (!empty($changes) && !isset($changes['deleted_at'])) {
                WriteLog::write($model, 'updated', $changes);
            }
        });

        if (in_array(SoftDeletes::class, class_uses_recursive(static::class))) {

            static::restored(function ($model) {
                WriteLog::write($model, 'restored', $model->getAttributes());
            });

            static::forceDeleted(function ($model) {
                WriteLog::write($model, 'deleted', $model->getAttributes());
            });

        } else {
            static::deleted(function ($model) {
                WriteLog::write($model, 'deleted', $model->getAttributes());
            });
        }
    }
}
