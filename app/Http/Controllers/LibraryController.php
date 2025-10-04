<?php

namespace App\Http\Controllers;

use App\Http\Resources\NovelLibraryResource;
use App\Models\Novel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LibraryController extends Controller
{
    public function Novels(Request $request)
    {

        $q = $request->query('q');
        $genre = $request->query('genre');
        $progress = $request->query('progress');

        $novels = Novel::query()
            ->whereHas('chapters', function ($query) {
                $query->where('chapters.status', 'published')
                ->where('chapters.deleted_at', null);
            })
            ->where('novels.status', 'published')
            ->where('novels.deleted_at', null);

        if ($q) {
            $novels->where(function ($query) use ($q) {
                $query->where('novels.title', 'like', "%{$q}%")
                    ->orWhere('novels.description', 'like', "%{$q}%")
                    ->orWhere('novels.synopsis', 'like', "%{$q}%")
                    ->orWhere('novels.tags', 'like', "%{$q}%")
                    ->orWhereHas('user', function ($query) use ($q) {
                        $query->where('users.full_name', 'like', "%{$q}%");
                    });
            });
        }
        if ($genre) {
            $novels->where('novels.genre_id', $genre);
        }
        if ($progress) {
            $novels->where('novels.progress', $progress);
        }

        $novels = $novels->groupBy('novels.id')
            ->orderBy('novels.created_at', 'desc')
            ->paginate(8);


        return NovelLibraryResource::collection($novels);
    }
}
