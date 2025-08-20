<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreNovelRequest;
use App\Http\Requests\StorePostRequest;
use App\Http\Requests\UpdateNovelRequest;
use App\Http\Resources\NovelChapterResource;
use App\Http\Resources\NovelResource;
use App\Http\Resources\PostResource;
use App\Http\Resources\UserChapterWithAuth;
use App\Http\Resources\UserChapterWithoutAuth;
use App\Http\Utils\GenerateUniqueName;
use App\Http\Utils\ImageUtils;
use App\Jobs\DeleteImage;
use App\Models\Novel;
use App\Repositories\NovelRepository;
use App\Repositories\PostRepository;
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

        $user_id = null;

        if (Auth::guard('sanctum')->check()) {
            $user_id = Auth::guard('sanctum')->user()->id;
        }


        if ($novel->status != 'published' && !Auth::guard('sanctum')->check() && $novel->user_id != $user_id) {
            return response()->json([
                'message' => 'Novel not found',
            ], 404);
        }

        $already_loved = false;

        if($user_id){
            $already_loved = $novel->love()->where('user_id', $user_id)->exists();
        }

        $novel->already_loved = $already_loved;


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

    public function getNovelChapters($id)
    {


        $novel = $this->novelRepository->findNovel($id);


        if (!$novel) {
            return response()->json([
                'message' => 'Novel not found',
            ], 404);
        }

        $this->authorize('view', $novel);


        return response()->json([
            'data' => NovelChapterResource::collection($novel->chapters),
        ]);
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

        return response()->json([
            'data' => $data,
        ]);
    }

    public function getMyNovels(Request $request)
    {

        $q = $request->input('q');


        $novels = $this->novelRepository->getMyNovels(Auth::user()->id,$q);

        return response()->json([
            'data' => NovelResource::collection($novels),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {

        $novel = $this->novelRepository->findNovel($id);

        if (!$novel) {
            return response()->json([
                'message' => 'Novel not found',
            ], 404);
        }

        $this->authorize('delete', $novel);

        if ($novel->image_public_id) {
            dispatch(new DeleteImage($novel->image_public_id));
        }

        $this->novelRepository->delete($novel->id);

        return response()->json([
            'message' => 'Novel deleted successfully',
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

        $novelPosts = $this->novelRepository->getNovelPost($id);

        return response()->json([
            'data' => PostResource::collection($novelPosts),
        ]);
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
            $novel->love()->where('user_id', $userID)->delete();
            $message = 'Novel unloved successfully';
        }else{
            $novel->love()->create([
                'user_id' => $userID,
            ]);
            $message = 'Novel loved successfully';
        }

        return response()->json([
            'message' => $message,
        ], 200);
    }

}
