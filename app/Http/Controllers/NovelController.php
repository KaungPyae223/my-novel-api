<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreLetterRequest;
use App\Http\Requests\StoreNovelRequest;
use App\Http\Requests\StorePostRequest;
use App\Http\Requests\UpdateNovelRequest;
use App\Http\Resources\BannedUsersResource;
use App\Http\Resources\LetterResource;
use App\Http\Resources\LogResource;
use App\Http\Resources\NovelChapterResource;
use App\Http\Resources\NovelResource;
use App\Http\Resources\PostResource;
use App\Http\Resources\ReviewResource;
use App\Http\Resources\UserChapterResource;
use App\Http\Utils\GenerateUniqueName;
use App\Http\Utils\ShortNumber;
use App\Models\Novel;
use App\Models\User;
use App\Repositories\NovelRepository;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NovelController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display a listing of the resource.
     */

    protected $novelRepository;

    public function __construct(NovelRepository $novelRepository)
    {
        $this->novelRepository = $novelRepository;
    }

    public function index()
    {
        $novels = $this->novelRepository->all();

        return response()->json([
            'novels' => $novels,
        ]);
    }


    public function store(StoreNovelRequest $request)
    {
        $unique_name = GenerateUniqueName::generate($request->title);
        $count = Novel::where('unique_name', 'like', '%' . $unique_name . '%')->count();
        $unique_name = $unique_name . '-' . ($count + 1);

        $request->merge([
            'unique_name' => $unique_name,
            'user_id' => Auth::user()->id,
        ]);

        $novel = $this->novelRepository->create($request->all());

        $novel
        ->addMedia($request->file('cover_image'))
        ->toMediaCollection('cover_images');

        return response()->json([
            'message' => 'Novel created successfully',
            'novel' => $novel,
        ]);
    }

    public function novelImageUpload($id, Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg|max:5120',
        ]);

        $novel = $this->novelRepository->findNovel($id);

        if (!$novel) {
            return response()->json([
                'message' => 'Novel not found',
            ], 404);
        }

        $this->authorize('update', $novel);

        if ($novel->hasMedia('cover_images')) {
            $novel->getFirstMedia('cover_images')->delete();
        }

        $novel->addMedia($request->file('image'))
        ->toMediaCollection('cover_images');

        return response()->json([
            'message' => 'Image uploaded successfully',
        ]);
    }

    public function show($id)
    {
        $novel = $this->novelRepository->findNovel($id);

        if (!$novel) {
            return response()->json([
                'message' => 'Novel not found',
            ], 404);
        }

        $this->authorize('view', $novel);
        $novel->already_loved = false;

        return response()->json([
            'data' => new NovelResource($novel),
        ]);
    }

    public function showUserNovel($id)
    {
        $novel = $this->novelRepository->findNovel($id);

        if (!$novel) {
            return response()->json([
                'message' => 'Novel not found',
            ], 404);
        }

        $user_id = Auth::guard('sanctum')->check() ? Auth::guard('sanctum')->user()->id : null;

        if ($novel->status != 'published' && !$user_id && $novel->user_id != $user_id) {
            return response()->json([
                'message' => 'Novel not found',
            ], 404);
        }

        if ($user_id) {
            $this->novelRepository->addHistory($id, $user_id);
            $this->novelRepository->addView($id, $user_id);
        }

        $already_loved = $user_id ? $novel->love()->where('user_id', $user_id)->exists() : false;
        $already_favorited = $user_id ? $novel->favorite()->where('user_id', $user_id)->exists() : false;

        $novel->already_loved = $already_loved;
        $novel->already_favorited = $already_favorited;

        return response()->json([
            'data' => new NovelResource($novel),
        ]);
    }

    public function update(UpdateNovelRequest $request, $id)
    {
        $novel = $this->novelRepository->findNovel($id);

        if (!$novel) {
            return response()->json([
                'message' => 'Novel not found',
            ], 404);
        }

        $this->authorize('update', $novel);
        $this->novelRepository->update($novel->id, $request->all());

        return response()->json([
            'message' => 'Novel updated successfully',
            'novel' => $novel,
        ]);
    }

    public function getNovelLogs($id, Request $request)
    {
        $novel = $this->novelRepository->findNovel($id);

        if (!$novel) {
            return response()->json([
                'message' => 'Novel not found',
            ], 404);
        }

        $this->authorize('view', $novel);
        $logs = $this->novelRepository->getNovelLogs($id, $request);

        return LogResource::collection($logs);
    }

    public function getNovelChapters($id, Request $request)
    {
        $novel = $this->novelRepository->findNovel($id);
        $q = $request->input('q');
        $filter = $request->input('filter');
        $sort = $request->input('sort', 'newest');

        if (!$novel) {
            return response()->json([
                'message' => 'Novel not found',
            ], 404);
        }

        $this->authorize('view', $novel);
        $chapters = $novel->chapters();

        if ($q) {
            $chapters->where('title', 'like', '%' . $q . '%');
        }

        if ($filter && $filter != 'all') {
            $chapters->where('status', $filter);
        }

        switch ($sort) {
            case 'newest':
                $chapters->orderByDesc('created_at');
                break;
            case 'oldest':
                $chapters->orderBy('created_at');
                break;
            case 'az':
                $chapters->orderBy('title');
                break;
            case 'za':
                $chapters->orderByDesc('title');
                break;
        }

        $chapters = $chapters->paginate(15);
        return NovelChapterResource::collection($chapters);
    }

    public function getTrashedChapters($id)
    {
        $novel = $this->novelRepository->findNovel($id);

        if (!$novel) {
            return response()->json([
                'message' => 'Novel not found',
            ], 404);
        }

        $this->authorize('view', $novel);
        $chapters = $this->novelRepository->getTrashedChapters($novel->id);

        return NovelChapterResource::collection($chapters);
    }

    public function getUserLastReadChapter($id)
    {
        $novel = $this->novelRepository->findNovel($id);

        if (!$novel) {
            return response()->json([
                'message' => 'Novel not found',
            ], 404);
        }

        if (Auth::guard('sanctum')->check()) {
            $userId = Auth::guard('sanctum')->user()->id;

            $chapters = $novel->chapters()
                ->where('status', 'published')
                ->orderBy('created_at');

            $allChapters = $chapters->pluck('id')->toArray();
            $readChapters = $chapters->whereHas('histories', function ($query) use ($userId) {
                $query->where('user_id', $userId);
            })
                ->pluck('id')
                ->toArray();

            $firstUnreadChapter = null;
            $firstUnreadChapterIndex = null;
            foreach ($allChapters as $index => $chapterId) {
                if (!in_array($chapterId, $readChapters)) {
                    $firstUnreadChapter = $chapterId;
                    $firstUnreadChapterIndex = $index;
                    break;
                }
            }

            $page = $firstUnreadChapterIndex ? floor($firstUnreadChapterIndex / 15) + 1 : 1;

            return response()->json([
                'last_read_chapter' => $firstUnreadChapter,
                'last_read_page' => $page
            ], 200);
        }

        return response()->json([
            'last_read_chapter' => null,
            'last_read_page' => 1
        ], 200);
    }

    public function showUserNovelChapter($id, Request $request)
    {
        $novel = $this->novelRepository->findNovel($id);
        $q = $request->input('q');

        if (!$novel) {
            return response()->json([
                'message' => 'Novel not found',
            ], 404);
        }

        $chapters = $novel->chapters()
            ->where('status', 'published');

        if ($q) {
            $chapters->where('title', 'like', '%' . $q . '%');
        }

        $chapters = $chapters->orderBy('created_at')
            ->paginate(15);

        return UserChapterResource::collection($chapters);
    }

    public function getMyNovels(Request $request)
    {
        $q = $request->input('q');
        $novels = $this->novelRepository->getMyNovels(Auth::user()->id, $q);

        return NovelResource::collection($novels);
    }

    public function getMyNovelsKPI()
    {
        $user = Auth::user();

        $totalNovels = $user->novels->count();
        $totalViews = $user->novels->flatMap->view->count();
        $totalLoves = $user->novels->flatMap->love->count();
        $totalShares = $user->novels->sum('share_count');

        return response()->json([
            'totalNovels' => $totalNovels,
            'totalViews' => ShortNumber::number_shorten($totalViews),
            'totalLoves' => ShortNumber::number_shorten($totalLoves),
            'totalShares' => ShortNumber::number_shorten($totalShares),
        ]);
    }

    public function destroy($id)
    {
        $novel = $this->novelRepository->findNovelWithTrash($id);

        if (!$novel) {
            return response()->json([
                'message' => 'Novel not found',
            ], 404);
        }

        $this->authorize('delete', $novel);
        $this->novelRepository->delete($novel->id);

        return response()->json([
            'message' => 'Novel deleted successfully',
        ]);
    }

    public function novelFavorite($id)
    {
        $novel = $this->novelRepository->findNovel($id);

        if (!$novel) {
            return response()->json([
                'message' => 'Novel not found',
            ], 404);
        }

        $already_favorited = $novel->favorite()->where('user_id', Auth::user()->id)->exists();

        if ($already_favorited) {
            $this->novelRepository->removeFavorite($id);
            $message = 'Novel unfavorited successfully';
        } else {
            $this->novelRepository->addFavorite($id);
            $message = 'Novel favorited successfully';
        }

        return response()->json([
            'message' => $message,
        ]);
    }

    public function novelShare($id)
    {
        $novel = $this->novelRepository->findNovel($id);

        if (!$novel) {
            return response()->json([
                'message' => 'Novel not found',
            ], 404);
        }

       
        $this->novelRepository->share($id);

        return response()->json([
            'message' => 'Novel shared successfully',
        ]);
    }

    // Post

    public function createNovelPost($id, StorePostRequest $request)
    {
        $novel = $this->novelRepository->findNovel($id);

        if (!$novel) {
            return response()->json([
                'message' => 'Novel not found',
            ], 404);
        }

        $this->authorize('update', $novel);
        $uploadImage = $request->file('post_image');

        if ($uploadImage) {
            $novel->addMedia($uploadImage)
            ->toMediaCollection('post_images');
        }

        $request->merge([
            'user_id' => Auth::user()->id,
        ]);

        $post = $this->novelRepository->createNovelPost($id, $request->all());

        return response()->json([
            'message' => 'Post created successfully',
            'post' => $post,
        ]);
    }

    public function getNovelPosts($id)
    {
        $novel = $this->novelRepository->findNovel($id);

        if (!$novel) {
            return response()->json([
                'message' => 'Novel not found',
            ], 404);
        }

        $novelPosts = $this->novelRepository->getNovelPost($id);

        return PostResource::collection($novelPosts);
    }

    public function novelLove($id)
    {
        $novel = $this->novelRepository->findNovel($id);

        if (!$novel) {
            return response()->json([
                'message' => 'Chapter not found',
            ], 404);
        }

        $userID = Auth::user()->id;
        $already_loved = $novel->love()->where('user_id', $userID)->exists();

        if ($already_loved) {
            $this->novelRepository->removeLove($id);
            $message = 'Novel unloved successfully';
        } else {
            $this->novelRepository->addLove($id);
            $message = 'Novel loved successfully';
        }

        return response()->json([
            'message' => $message,
        ], 200);
    }

    public function novelReviews($id)
    {
        $novel = $this->novelRepository->findNovel($id);

        if (!$novel) {
            return response()->json([
                'message' => 'Chapter not found',
            ], 404);
        }

        $reviews = $this->novelRepository->getNovelReviews($id);
        return ReviewResource::collection($reviews);
    }

    public function writeLetter($id, StoreLetterRequest $request)
    {
        $novel = $this->novelRepository->findNovel($id);

        if (!$novel) {
            return response()->json([
                'message' => 'Novel not found',
            ], 404);
        }

        $this->authorize('writeLetter', $novel);

        $checkLetter = $novel->letter()->where('user_id', Auth::user()->id)->where('created_at', '>=', now()->startOfDay())->exists();

        if ($checkLetter) {
            return response()->json([
                'message' => 'You have already written a letter',
            ], 400);
        }

        $request->merge([
            'user_id' => Auth::user()->id,
        ]);

        $letter = $this->novelRepository->createLetter($id, $request->all());

        return response()->json([
            'message' => 'Letter created successfully',
            'letter' => $letter,
        ]);
    }

    public function getLetters($id)
    {
        $novel = $this->novelRepository->findNovel($id);

        if (!$novel) {
            return response()->json([
                'message' => 'Novel not found',
            ], 404);
        }

        $this->authorize('view', $novel);
        $letters = $this->novelRepository->getLetters($id);


        return LetterResource::collection($letters);
    }

    public function getUserLetter($id)
    {
        $novel = $this->novelRepository->findNovel($id);

        if (!$novel) {
            return response()->json([
                'message' => 'Novel not found',
            ], 404);
        }

        if (Auth::guard('sanctum')->check()) {
            $user_id = Auth::guard('sanctum')->user()->id;
            $letters = $this->novelRepository->getUserLetter($id, $user_id);

            return LetterResource::collection($letters);
        } else {
            return response()->json('unauthorized', 200);
        }
    }

    public function banUser($novelID, $userID)
    {
        $novel = $this->novelRepository->findNovel($novelID);

        if (!$novel) {
            return response()->json('Novel not found', 404);
        }
      
        $user = User::find($userID);
        if (!$user) {
            return response()->json('User not found', 404);
        }

        $this->authorize('view', $novel);

        $this->novelRepository->banUser($novelID, $userID);

        return response()->json([
            'message' => 'User banned successfully'
        ], 200);
    }

    public function unbanUser($novelID, $userID)
    {
        $novel = $this->novelRepository->findNovel($novelID);

        if (!$novel) {
            return response()->json('Novel not found', 404);
        }

        $user = User::find($userID);
        if (!$user) {
            return response()->json('User not found', 404);
        }

        $this->authorize('view', $novel);
        $this->novelRepository->unbanUser($novelID, $userID);

        return response()->json([
            'message' => 'User unban successfully'
        ], 200);
    }

    public function getBannedUsers($id, Request $request)
    {
        $novel = $this->novelRepository->findNovel($id);

        if (!$novel) {
            return response()->json('Novel not found', 404);
        }

        $q = $request->input('q');

        $this->authorize('view', $novel);
        $bannedUsers = $this->novelRepository->getBannedUsers($id, $q);
        $totalBannedUsers = $this->novelRepository->getTotalBannedUsers($id);

        return  BannedUsersResource::collection($bannedUsers)->additional([
            'total' => $totalBannedUsers,
        ]);
    }

    public function toggleFanLetter($id)
    {
        $novel = $this->novelRepository->findNovel($id);

        if (!$novel) {
            return response()->json('Novel not found', 404);
        }

        $this->authorize('view', $novel);

        $value = $this->novelRepository->toggleFanLetter($id);

        return response()->json([
            'message' => "Fan letter $value successfully"
        ], 200);
    }

    public function getFanLetterStatus($id)
    {
        $novel = $this->novelRepository->findNovel($id);

        if (!$novel) {
            return response()->json('Novel not found', 404);
        }

        $user_id = Auth::guard('sanctum')->user()->id ?? null;

        $isBanned = $user_id ? $novel->ban()->where('user_id', $user_id)->exists() : false;

        $openLetter = $novel->open_letter == 'open' && $user_id && !$isBanned;

        $message = ($openLetter === 'close')
            ? 'The author has closed the letter writing feature for this novel.'
            : ((!$user_id)
                ? 'You must be logged in to write a letter.'
                : ($isBanned
                    ? 'You have been banned from writing a letter.'
                    : '')
            );

        return response()->json([
            'open_letter' => $openLetter,
            'message' => $message,
        ], 200);
    }
}
