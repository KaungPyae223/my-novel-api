<?php

namespace App\Http\Controllers;

use App\Http\Resources\HomeChapterResource;
use App\Http\Resources\HomeNovelResource;
use App\Http\Resources\HomePostResource;
use App\Models\Chapter;
use App\Models\Novel;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    public function recommendNovels()
    {
        $user = Auth::user();
        // Get novels user already read
        $history = $user->histories->pluck('novel_id')->toArray() ;

        $favoriteGenres = $user->histories->pluck('novel.genre_id')->toArray();

        // need add change with follow
        $favoriteAuthors = $user->histories->pluck('novel.user_id')->toArray();

        $favoriteNovels = $user->favorites->pluck('novel_id')->toArray();


        // Get favorite genres & authors from history
        $history = !empty($history) ? $history : [0];
        $favoriteGenres = !empty($favoriteGenres) ? $favoriteGenres : [0];
        $favoriteAuthors = !empty($favoriteAuthors) ? $favoriteAuthors : [0];
        $favoriteNovels = !empty($favoriteNovels) ? $favoriteNovels : [0];

        // Recommend unread novels
        $novels = Novel::query()
        ->leftJoin('views', 'novels.id', '=', 'views.viewable_id')
        ->leftJoin('chapters', 'novels.id', '=', 'chapters.novel_id')
        ->select('novels.*', DB::raw('COUNT(views.id) as views_count'),DB::raw('COUNT(DISTINCT chapters.id) as chapters_count'))
        ->where(
            'novels.status', 'published'
        )
        ->groupBy('novels.id') 
        ->havingRaw('COUNT(DISTINCT chapters.id) > 0')
        ->orderByRaw("
            (CASE WHEN novels.genre_id IN (".implode(',', $favoriteGenres).") THEN 2 ELSE 0 END) +
            (CASE WHEN novels.user_id IN (".implode(',', $favoriteAuthors).") THEN 1 ELSE 0 END) +
            (CASE WHEN novels.id IN (".implode(',', $history).") THEN -1 ELSE 0 END) +
            (CASE WHEN novels.id IN (".implode(',', $favoriteNovels).") THEN -2 ELSE 0 END) DESC
            ")
        ->orderBy('views_count', 'desc')
        ->limit(10)
        ->get();


        return HomeNovelResource::collection($novels);
    }

    public function recommendChapters()
    {
        $user = Auth::user();
        // Get novels user already read
        $history = $user->histories->pluck('novel_id')->toArray();

        $favoriteNovels = $user->favorites->pluck('novel_id')->toArray();

        $history = !empty($history) ? $history : [0];
        $favoriteNovels = !empty($favoriteNovels) ? $favoriteNovels : [0];

        // Recommend unread novels
        $chapters = Chapter::query()
        ->leftJoin('views', 'chapters.id', '=', 'views.viewable_id')
        ->leftJoin('novels', 'chapters.novel_id', '=', 'novels.id')
        ->select('chapters.*', 'views.viewable_id')
        ->where(
            'chapters.status', 'published'
        )->where(
            'novels.status', 'published'
        )
        ->groupBy('chapters.id')
        ->orderByRaw("
            (CASE WHEN chapters.novel_id IN (".implode(',', $favoriteNovels).") THEN 1 ELSE 0 END) +
            (CASE WHEN chapters.novel_id IN (".implode(',', $history).") THEN 1 ELSE 0 END) +
            (CASE WHEN views.viewable_id NOT IN (".implode(',', $history).") THEN 1 ELSE 0 END) +
            (CASE WHEN views.viewable_id NOT IN (".implode(',', $favoriteNovels).") THEN 2 ELSE 0 END) -
            (DateDiff(now(), chapters.created_at) / 10) DESC

        ")
        ->limit(10)
        ->get();


        return HomeChapterResource::collection($chapters);
    }

    public function recommendPosts()
    {
        $user = Auth::user();
        // Get novels user already read
        $history = $user->histories->pluck('novel_id')->toArray();

        $favoriteNovels = $user->favorites->pluck('novel_id')->toArray();

        $history = !empty($history) ? $history : [0];
        $favoriteNovels = !empty($favoriteNovels) ? $favoriteNovels : [0];

        $posts = Post::query()
        ->select('posts.*')
        ->leftJoin('novels', 'posts.postable_id', '=', 'novels.id')
        ->where(
            'novels.status', 'published'
        )
        ->orderByRaw("((CASE WHEN posts.postable_id IN (".implode(',', $favoriteNovels).") THEN 2 ELSE 0 END) +
            (CASE WHEN posts.postable_id IN (".implode(',', $history).") THEN 1 ELSE 0 END) ) -
            (DateDiff(now(), posts.created_at) / 10) DESC")
        ->limit(10)
        ->get();

        return HomePostResource::collection($posts);
    }
}

