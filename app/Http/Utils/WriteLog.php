<?php

namespace App\Http\Utils;

use App\Models\Log;
use App\Models\Novel;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log as FacadesLog;

class WriteLog
{

    public static function write($model, string $action, $data)
    {

        $parent = $model instanceof Novel ? $model : ($model->novel ? $model->novel : $model->postable);



        Log::create([
            'title' => $model->title ?? null,
            'logable_id' => $model->id,
            'logable_type' => get_class($model),
            'parentable_id' => $parent->id ?? null,
            'parentable_type' => $parent ? get_class($parent) : null,
            'ip_address' => request()->ip() ?? "system",
            'user_agent' => request()->user_agent ?? "system",
            'user_id' => Auth::user()->id ?? null,
            'description' => json_encode($data, JSON_UNESCAPED_UNICODE),
            'action' => $action,
        ]);
    }

    public static function writeUserLog($title, $model, string $action, $data)
    {

        Log::create([
            'title' => $title,
            'logable_id' => $model->id,
            'logable_type' => get_class($model),
            'parentable_id' => null,
            'parentable_type' => null,
            'ip_address' => request()->ip() ?? "system",
            'user_agent' => request()->userAgent() ?? 'system',
            'user_id' => Auth::user()->id ?? null,
            'description' => json_encode($data, JSON_UNESCAPED_UNICODE),
            'action' => $action,
        ]);
    }
}
