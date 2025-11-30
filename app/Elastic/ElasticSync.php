<?php

namespace App\Elastic;

use App\Models\Novel;
use Illuminate\Support\Facades\Log;

class ElasticSync
{
    protected $elastic;

    public function __construct()
    {
        $this->elastic = app('elasticsearch');
    }

    public  function sync()
    {
        if (!$this->elastic) {
            Log::error('Elasticsearch client is not initialized.');
            return;
        }

        $this->novelSync();

    }

    public function novelSync()
    {
        $novels = Novel::query()
            ->where('status', 'published')
            ->whereNull('deleted_at')
            ->whereHas('chapters', function ($query) {
                $query->where('chapters.status', 'published')
                    ->whereNull('chapters.deleted_at');
            })
            ->with(['genre', 'user'])
            ->get();

        foreach ($novels as $novel) {
            try {
                $this->elastic->index([
                    'index' => 'novels',
                    'id'    => $novel->id,
                    'body'  => [
                        'id'             => $novel->id,
                        'unique_name'    => $novel->unique_name,
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
    }
}
