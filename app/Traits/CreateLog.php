<?php

namespace App\Traits;

use App\Models\Log;
use App\Models\Novel;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log as FacadesLog;

trait CreateLog
{
    public static function bootCreateLog()
    {
        static::created(function ($model) {

            $parent = null;
            if ($model instanceof Novel) {
                $parent = $model;
            } elseif (isset($model->novel)) {
                $parent = $model->novel;
            } else {
                $parent = null;
            }

            Log::create([
                'title' => $model->title ?? null,
                'logable_id' => $model->id,
                'logable_type' => get_class($model),
                'parentable_id' => $parent->id ?? null,
                'parentable_type' => get_class($parent) ?? null,
                'ip_address' => request()->ip() ?? "system",
                'user_agent' => request()->userAgent() ?? "system",
                'user_id' => Auth::user()->id ?? null,
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

            $parent = null;
            if ($model instanceof Novel) {
                $parent = $model;
            } elseif (isset($model->novel)) {
                $parent = $model->novel;
            } else {
                $parent = null;
            }

            if (!empty($changes)) {
                Log::create([
                    'title' => $model->title ?? null,
                    'logable_id' => $model->id,
                    'logable_type' => get_class($model),
                    'parentable_id' => $parent->id ?? null,
                    'parentable_type' => get_class($parent) ?? null,
                    'ip_address' => request()->ip() ?? "system",
                    'user_agent' => request()->userAgent() ?? "system",
                    'user_id' => Auth::user()->id ?? null,
                    'description' => json_encode($changes, JSON_UNESCAPED_UNICODE),
                    'action' => isset($changes['deleted_at']) ? 'restored' : 'updated',
                ]);
            }
        });
        static::deleted(function ($model) {

            $parent = null;
            if ($model instanceof Novel) {
                $parent = $model;
            } elseif (isset($model->novel)) {
                $parent = $model->novel;
            } else {
                $parent = null;
            }

            Log::create([
                'title' => $model->title ?? null,
                'logable_id' => $model->id,
                'logable_type' => get_class($model),
                'parentable_id' => $parent->id ?? null,
                'parentable_type' => get_class($parent) ?? null,
                'ip_address' => request()->ip() ?? "system",
                'user_agent' => request()->userAgent() ?? "system",
                'user_id' => Auth::user()->id ?? null,
                'description' => json_encode($model->getAttributes()),
                'action' => $model->trashed() ? 'trashed' : 'deleted',
            ]);
        });
    }
}

