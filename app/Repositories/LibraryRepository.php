<?php

namespace App\Repositories;

use App\Http\Resources\NovelLibraryResource;
use App\Http\Utils\ElasticSetUp;
use App\Models\Novel;

class LibraryRepository
{

    protected $elastic;

    public function __construct()
    {
        $this->elastic = (new ElasticSetUp())->setUp();
    }

    public function searchNovelFromDB($q, $genre, $progress, $limit)
    {
        $novels = Novel::query()
            ->whereHas('chapters', function ($query) {
                $query->where('chapters.status', 'published')
                    ->whereNull('chapters.deleted_at');
            })
            ->where('novels.status', 'published')
            ->whereNull('novels.deleted_at');

        if ($q) {
            $novels->where(function ($query) use ($q) {
                $query->where('novels.title', 'like', "%{$q}%")
                    ->orWhere('novels.description', 'like', "%{$q}%")
                    ->orWhere('novels.synopsis', 'like', "%{$q}%")
                    ->orWhere('novels.tags', 'like', "%{$q}%")
                    ->orWhere('novels.unique_name', 'like', "%{$q}%")
                    ->orWhereHas('user', function ($query) use ($q) {
                        $query->where('users.full_name', 'like', "%{$q}%");
                    });
            });
        }
        if ($genre) {
            $novels->where('novels.genre.name', $genre);
        }
        if ($progress) {
            $novels->where('novels.progress', $progress);
        }

        $novels = $novels->groupBy('novels.id')
            ->orderBy('novels.created_at', 'desc')
            ->paginate($limit);

        return NovelLibraryResource::collection($novels);
    }

    public function searchNovelFromElastic($q, $genre, $progress, $limit, $page)
    {
        $must = [];
        $filter = [];
        $sort = [];
        

        if ($q) {

            $type = 'best_fields';
            $operator = 'and';

            if(count(array_filter(explode(" ", $q))) == 1){
                $type = 'phrase_prefix';  
                $operator = 'or';
            }

            $must[] = [
                'multi_match' => [
                    'query' => $q,
                    'fields' => ['title^3', 'unique_name^3', 'description^1', 'synopsis^1', 'tags^1', 'author.full_name^2'],
                    'type' => $type,
                    'fuzziness' => 'AUTO',
                    'operator' => $operator
                ],
            ];

            
            
        } else {
            $sort = [
                'created_at' => ['order' => 'desc'],
            ];
        }

        if ($genre) {
            $filter[] = [
                'term' => ['genre.keyword' => $genre],
            ];
        }

        if ($progress) {
            $filter[] = [
                'term' => ['progress.keyword' => $progress],
            ];
        }

        $searchNovels = $this->elastic->search([
            'index' => 'novels',
            'body' => [
                'query' => [
                    'bool' => [
                        'must' => $must,
                        'filter' => $filter,
                    ],
                ],
                'size' => $limit,
                'from' => ($page - 1) * $limit,
                'sort' => $sort, 
            ],
        ]);

       
        return $this->formatNovelData($searchNovels,$limit,$page);
    }

    public function formatNovelData($searchNovels,$limit,$page)
    {
        $hits = $searchNovels->hits->hits;

        $formattedNovels = collect($hits)->map(function ($hit) {
            return [
                'id' => $hit->_source->id,
                'title' => $hit->_source->title,
                'description' => $hit->_source->description,
                'synopsis' => $hit->_source->synopsis,
                'tags' => $hit->_source->tags,
                'image' => $hit->_source->image,
                'genre' => $hit->_source->genre,
                'progress' => $hit->_source->progress,
                'status' => $hit->_source->status,
                'author' => $hit->_source->author,
                'chapters_count' => $hit->_source->chapters_count,
                'views_count' => $hit->_source->views_count,
                'loved_count' => $hit->_source->loved_count,
                'created_at' => $hit->_source->created_at,
            ];
        });

        return response()->json([
            'data' => $formattedNovels,
            'meta' => [
                'last_page' => round($searchNovels->hits->total->value / $limit),
                'current_page' => (int)$page,
            ],
        ]);

    }
}
