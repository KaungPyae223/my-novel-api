<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreNovelRequest;
use App\Http\Requests\StorePostRequest;
use App\Http\Requests\UpdateNovelRequest;
use App\Http\Resources\NovelChapterResource;
use App\Http\Resources\NovelResource;
use App\Http\Resources\PostResource;
use App\Http\Resources\ReviewResource;
use App\Http\Resources\UserChapterWithAuth;
use App\Http\Resources\UserChapterWithoutAuth;
use App\Http\Utils\GenerateUniqueName;
use App\Http\Utils\ImageUtils;
use App\Http\Utils\ShortNumber;
use App\Jobs\DeleteImage;
use App\Models\Novel;
use App\Repositories\NovelRepository;
use App\Repositories\PostRepository;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;

class NovelController extends Controller
{

    use AuthorizesRequests;

    /**
     * Display a listing of the resource.
     */

    protected $novelRepository;
    protected $postRepository;

    public function __construct(NovelRepository $novelRepository, PostRepository $postRepository) {
        $this->novelRepository = $novelRepository;
        $this->postRepository = $postRepository;
    }

    public function index()
    {
        $novels = $this->novelRepository->all();

        return response()->json([
            'novels' => $novels,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreNovelRequest $request)
    {

        $unique_name = GenerateUniqueName::generate($request->title);


        $count = Novel::where('unique_name', 'like', '%' . $unique_name . '%')->count();

        $unique_name = $unique_name . '-' . ($count+1);

        $uploadImage = $request->file('cover_image');

        $uploaded = ImageUtils::uploadImage($uploadImage);

        $request->merge([
            'unique_name' => $unique_name,
            'user_id' => Auth::user()->id,
            'image' => $uploaded["imageUrl"],
            'image_public_id' => $uploaded["publicId"],
        ]);

        $novel = $this->novelRepository->create($request->all());




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

        if ($novel->image_public_id) {
            dispatch(new DeleteImage($novel->image_public_id));
        }

        $uploadImage = $request->file('image');

        $uploaded = ImageUtils::uploadImage($uploadImage);

        $this->novelRepository->update($novel->id, [
            'image' => $uploaded["imageUrl"],
            'image_public_id' => $uploaded["publicId"],
        ]);

        return response()->json([
            'message' => 'Image uploaded successfully',
        ]);
    }



    /**
     * Display the specified resource.
     */
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

        if ($novel->status != 'published' && !Auth::guard('sanctum')->check() && $novel->user_id != $user_id) {
            return response()->json([
                'message' => 'Novel not found',
            ], 404);
        }

        $user_id = null;

        if (Auth::guard('sanctum')->check()) {
            $user_id = Auth::guard('sanctum')->user()->id;
            $this->novelRepository->addHistory($id,$user_id);
            $this->novelRepository->addView($id,$user_id);
        }

        $already_loved = false;
        $already_favorited = false;

        if($user_id){
            $already_loved = $novel->love()->where('user_id', $user_id)->exists();
            $already_favorited = $novel->favorite()->where('user_id', $user_id)->exists();
        }

        $novel->already_loved = $already_loved;
        $novel->already_favorited = $already_favorited;


        return response()->json([
            'data' => new NovelResource($novel),
        ]);
    }



    /**
     * Update the specified resource in storage.
     */
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

    public function getNovelLogs($id,Request $request)
    {


        $novel = $this->novelRepository->findNovel($id);

        if (!$novel) {
            return response()->json([
                'message' => 'Novel not found',
            ], 404);
        }

        $this->authorize('view', $novel);

        $logs = $this->novelRepository->getNovelLogs($id,$request);

        return response()->json($logs);

    }

    
    public function getNovelChapters($id)
    {


        $novel = $this->novelRepository->findNovel($id);


        if (!$novel) {
            return response()->json([
                'message' => 'Novel not found',
            ], 404);
        }

        $this->authorize('view', $novel);


        return NovelChapterResource::collection($novel->chapters);
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

    public function showUserNovelChapter($id)
    {
        $novel = $this->novelRepository->findNovel($id);

        if (!$novel) {
            return response()->json([
                'message' => 'Novel not found',
            ], 404);
        }

        $chapters = $novel->chapters()->where('status', 'published')->get();

        if (Auth::guard('sanctum')->check()) {
           $data = UserChapterWithAuth::collection($chapters);
        }else{
            $data = UserChapterWithoutAuth::collection($chapters);
        }

        return $data;
    }

    public function getMyNovels(Request $request)
    {

        $q = $request->input('q');


        $novels = $this->novelRepository->getMyNovels(Auth::user()->id,$q);

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

    /**
     * Remove the specified resource from storage.
     */
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
        }else{
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

        $userId = Auth::guard('sanctum')->user()->id ?? request()->ip();
        $key = "novel-share:{$userId}:{$id}";

        if (RateLimiter::tooManyAttempts($key, 1)) {
            return;
        }

        RateLimiter::hit($key, 60*60); // allow 5 attempts per 60 seconds

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
            $uploaded = ImageUtils::uploadImage($uploadImage);
            $request->merge([
                'image' => $uploaded["imageUrl"],
                'image_public_id' => $uploaded["publicId"],
            ]);
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

        $user_id = null;

        if (Auth::guard('sanctum')->check()) {
            $user_id = Auth::guard('sanctum')->user()->id;
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
        }else{
            $this->novelRepository->addLove($id);
            $message = 'Novel loved successfully';
        }

        return response()->json([
            'message' => $message,
        ], 200);
    }

    public function novelReviews($id){

        $novel = $this->novelRepository->findNovel($id);

        if (!$novel) {
            return response()->json([
                'message' => 'Chapter not found',
            ], 404);
        }

        $reviews = $this->novelRepository->getNovelReviews($id);

        return ReviewResource::collection($reviews);

    }


}
