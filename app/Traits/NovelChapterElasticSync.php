<?php

namespace App\Traits;

use App\Http\Utils\NovelElastic;

trait NovelChapterElasticSync
{
    public static function bootNovelChapterElasticSync()
    {
        static::created(function ($chapter) {
            NovelElastic::updateNovelElastic($chapter->novel_id);
        });
        
        static::updated(function ($chapter) {
            NovelElastic::updateNovelElastic($chapter->novel_id);
        });

        static::deleted(function ($chapter) {
            NovelElastic::updateNovelElastic($chapter->novel_id);
        });
    }

    
}
