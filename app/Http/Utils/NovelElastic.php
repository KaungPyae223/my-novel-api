<?php

namespace App\Http\Utils;

use App\Http\Utils\ElasticSetUp;
use App\Models\Novel;
use Illuminate\Support\Facades\Log;

class NovelElastic
{


    public static function updateNovelElastic($id)
    {

        $elastic = (new ElasticSetUp())->setUp();

        $novel = Novel::find($id);

        if ($novel->chapters()->where('chapters.status', 'published')->whereNull('chapters.deleted_at')->count() == 0) {
            self::deleteNovelElastic($id);
            return;
        }

        try {
            $elastic->index([
                'index' => 'novels',
                'id'    => $novel->id,
                'body'  => [
                    'id'             => $novel->id,
                    'title'          => $novel->title,
                    'description'    => $novel->description,
                    'synopsis'       => $novel->synopsis,
                    'tags'           => $novel->tags,
                    'image'          => $novel->image,
                    'genre'          => optional($novel->genre)->genre,
                    'progress'       => $novel->progress,
                    'status'         => $novel->status,
                    'author'         => optional($novel->user)->full_name,
                    'chapters_count' => $novel->chapters()
                        ->where('chapters.status', 'published')
                        ->whereNull('chapters.deleted_at')
                        ->count(),
                    'views_count'    => $novel->view()->count(),
                    'loved_count'    => $novel->love()->count(),
                    'created_at'     => $novel->created_at,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to index novel ID {$novel->id}: " . $e->getMessage());
        }
    }

    public static function deleteNovelElastic($id)
    {

        $elastic = (new ElasticSetUp())->setUp();

        try {
            $elastic->delete([
                'index' => 'novels',
                'id'    => $id,
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to delete novel ID {$id}: " . $e->getMessage());
        }
    }
}
