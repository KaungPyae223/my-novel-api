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
    /**
     * Get recommended novels for the user
     */
    public function recommendNovels()
    {
        $user = Auth::user();

        $history = $user->viewedNovels()
            ->pluck('novels.id')
            ->unique()
            ->toArray();

        $favoriteGenres = $user->viewedNovels()
            ->pluck('novels.genre_id')
            ->unique()
            ->toArray();

        $favoriteAuthors = $user->viewedNovels()
            ->pluck('novels.user_id')
            ->unique()
            ->toArray();

        $favoriteNovels = $user->favorites->pluck('novel_id')->toArray();

        $history = $history ?: [0];
        $favoriteGenres = $favoriteGenres ?: [0];
        $favoriteAuthors = $favoriteAuthors ?: [0];
        $favoriteNovels = $favoriteNovels ?: [0];

        // Recommend unread novels
        $novels = Novel::query()
            ->withCount('view')
            ->where('status', 'published')
            ->whereNull('deleted_at')
            ->whereHas('chapters', function ($query) {
                $query->where('status', 'published')
                    ->whereNull('deleted_at');
            })
            ->groupBy('novels.id')
            ->orderByRaw("
                (CASE WHEN genre_id IN (" . implode(',', $favoriteGenres) . ") THEN 2 ELSE 0 END) +
                (CASE WHEN user_id IN (" . implode(',', $favoriteAuthors) . ") THEN 2 ELSE 0 END) +
                (CASE WHEN id IN (" . implode(',', $history) . ") THEN -1 ELSE 0 END) +
                (CASE WHEN id IN (" . implode(',', $favoriteNovels) . ") THEN -2 ELSE 0 END) DESC
            ")
            ->orderByDesc("view_count")
            ->paginate(10);


        return HomeNovelResource::collection($novels);
    }

    /**
     * Get recommended chapters for the user
     */
    public function recommendChapters()
    {
        $user = Auth::user();

        $history = $user->viewedChapters()
            ->pluck('chapters.id')
            ->unique()
            ->toArray();

        $favoriteNovels = $user->favorites->pluck('novel_id')->toArray();

        $history = $history ?: [0];
        $favoriteNovels = $favoriteNovels ?: [0];

        $chapters = Chapter::query()
            ->whereHas('novel', function ($query) {
                $query->where('status', 'published')
                    ->whereNull('deleted_at');
            })
            ->where('status', 'published')
            ->whereNull('deleted_at')
            ->orderByRaw("
                (CASE WHEN novel_id IN (" . implode(',', $favoriteNovels) . ") THEN 1 ELSE 0 END) +
                (CASE WHEN novel_id IN (" . implode(',', $history) . ") THEN 1 ELSE 0 END) +
                (CASE WHEN id NOT IN (" . implode(',', $history) . ") THEN 2 ELSE 0 END) +
                (DATEDIFF(NOW(), created_at) / 10) DESC
            ")
            ->limit(10)
            ->get();

        return HomeChapterResource::collection($chapters);
    }

    /**
     * Get recommended posts for the user
     */
    public function recommendPosts()
    {
        $user = Auth::user();

        $history = $user->viewedNovels()
            ->pluck('novels.id')
            ->unique()
            ->toArray();

        $favoriteNovels = $user->favorites->pluck('novel_id')->toArray();

        $history = $history ?: [0];
        $favoriteNovels = $favoriteNovels ?: [0];

        $posts = Post::query()
            ->whereHas('novel', function ($query) {
                $query->where('status', 'published')
                    ->whereNull('deleted_at');
            })
            ->where('postable_type', Novel::class)
            ->orderByRaw("
                ((CASE WHEN postable_id IN (" . implode(',', $favoriteNovels) . ") THEN 2 ELSE 0 END) +
                 (CASE WHEN postable_id IN (" . implode(',', $history) . ") THEN 1 ELSE 0 END)) -
                (DATEDIFF(NOW(), created_at) / 10) DESC
            ")
            ->limit(10)
            ->get();

        return HomePostResource::collection($posts);
    }
}
