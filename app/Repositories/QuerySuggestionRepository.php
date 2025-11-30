<?php

namespace App\Repositories;

use App\Models\Novel;

class QuerySuggestionRepository
{

    protected $elastic;

    public function __construct()
    {
        $this->elastic = app('elasticsearch');
    }

    public function suggestNovelFromDB($q)
    {

        $suggestion = Novel::query()
            ->where('novels.status', 'published')
            ->whereNull('novels.deleted_at')
            ->where(function ($query) use ($q) {
                $query->where('novels.title', 'like', "%{$q}%")
                    ->orWhere('novels.description', 'like', "%{$q}%");
            })
            ->groupBy('novels.id')
            ->orderBy('novels.created_at', 'desc')
            ->take(6)
            ->get();

        $formattedNovels = $suggestion->map(function ($data) {
            return [
                'id' => $data->id,
                'title' => $data->title,
                'description' => $data->description,
                'image' => $data->image,
            ];
        });

        return response()->json($formattedNovels);
    }

    public function suggestNovelFromElastic($q)
    {

       

        $option = [];

        if (count(array_filter(explode(" ", $q))) == 1) {
            $option['type'] = 'phrase_prefix';
            $option['operator'] = 'or';
        } else {
            $option['type'] = 'best_fields';
            $option['operator'] = 'and';
            $option['fuzziness'] = 'AUTO';
        }

        $suggestion = $this->elastic->search([
            'index' => 'novels',
            'body'  => [
                '_source' => ['title', 'id', 'description', 'image'],
                'query' => [
                    'multi_match' => [
                        'query' => $q,
                        'fields' => ['title^2', 'description'],
                        ...$option
                    ],
                ],
                'highlight' => [
                    'pre_tags' => ['<span class="highlight">'],
                    'post_tags' => ['</span>'],
                    'fields' => [
                        'title' => ['number_of_fragments' => 0],
                        'description' => ['number_of_fragments' => 1, 'fragment_size' => 600],
                    ],
                ],
                'sort' => [
                    ['_score' => 'desc'],
                ],
                'size' => 6,
            ],
        ]);

        return $this->formatElasticNovelSuggestionData($suggestion);
    }

    public function formatElasticNovelSuggestionData($suggestion)
    {

        $hits = $suggestion->hits->hits;

        $formattedNovels = collect($hits)->map(function ($hit) {
            return [
                'id' => $hit->_source->id,
                'title' => $hit->highlight->title ?? $hit->_source->title,
                'description' => $hit->highlight->description ?? $hit->_source->description,
                'image' => $hit->_source->image,
            ];
        });

        return response()->json($formattedNovels);
    }
}
