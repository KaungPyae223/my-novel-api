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
                'logable_id' => $model->id,
                'logable_type' => get_class($model),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'user_id' => Auth::user()->id,
                'description' => json_encode($model->getAttributes()),
                'action' => 'created',
            ]);
        });
        static::updated(function ($model) {
            Log::create([
                'logable_id' => $model->id,
                'logable_type' => get_class($model),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'user_id' => Auth::user()->id,
                'description' => json_encode($model->getAttributes()),
                'action' => 'updated',
            ]);
        });
        static::deleted(function ($model) {
            Log::create([
                'logable_id' => $model->id,
                'logable_type' => get_class($model),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'user_id' => Auth::user()->id,
                'description' => json_encode($model->getAttributes()),
                'action' => 'deleted',
            ]);
        });
    }
}

