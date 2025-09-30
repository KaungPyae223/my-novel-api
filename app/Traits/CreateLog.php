<?php

namespace App\Traits;

use App\Models\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log as FacadesLog;

trait CreateLog
{
    public static function bootCreateLog()
    {
        static::created(function ($model) {

            Log::create([
                'title' => $model->title ?? null,
                'logable_id' => $model->id,
                'logable_type' => get_class($model),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'user_id' => Auth::user()->id ?? 1,
                'description' => json_encode($model->getAttributes()),
                'action' => 'created',
            ]);
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

            if (!empty($changes)) {
                Log::create([
                    'title' => $model->title ?? null,
                    'logable_id' => $model->id,
                    'logable_type' => get_class($model),
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                    'user_id' => Auth::id() ?? 1,
                    'description' => json_encode($changes, JSON_UNESCAPED_UNICODE),
                    'action' => 'updated',
                ]);
            }
        });
        static::deleted(function ($model) {
            Log::create([
                'title' => $model->title ?? null,
                'logable_id' => $model->id,
                'logable_type' => get_class($model),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'user_id' => Auth::user()->id ?? 1,
                'description' => json_encode($model->getAttributes()),
                'action' => 'deleted',
            ]);
        });
    }
}

