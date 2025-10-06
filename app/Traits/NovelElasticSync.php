<?php

namespace App\Traits;

use App\Http\Utils\NovelElastic;

trait NovelElasticSync
{
    public static function bootNovelElasticSync()
    {
    
        static::updated(function ($model) {
            if($model->status == 'published') {
                NovelElastic::updateNovelElastic($model->id);
            }else{
                NovelElastic::deleteNovelElastic($model->id);
            }
        });

        static::deleted(function ($model) {
            NovelElastic::deleteNovelElastic($model->id);
        });
    }

    
}
