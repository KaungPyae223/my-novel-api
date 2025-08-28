<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePostRequest;
use App\Http\Requests\UpdatePostRequest;
use App\Http\Utils\ImageUtils;
use App\Jobs\DeleteImage;
use App\Models\Post;
use App\Repositories\PostRepository;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;

class PostController extends Controller
{

    use AuthorizesRequests;

    protected $postRepository;


    public function __construct(PostRepository $postRepository) {
        $this->postRepository = $postRepository;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePostRequest $request)
    {

    }

    /**
     * Display the specified resource.
     */
    public function show(post $post)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePostRequest $request, $id)
    {

        $post = $this->postRepository->findPost($id);

        if (!$post) {
            return response()->json([
                'message' => 'Post not found',
            ], 404);
        }

        $this->authorize('update', $post);


        if ($request->hasFile('post_image')) {
            if ($post->image_public_id) {
                dispatch(new DeleteImage($post->image_public_id));
            }
            $uploadImage = $request->file('post_image');
            $uploaded = ImageUtils::uploadImage($uploadImage);
            $request->merge([
                'image' => $uploaded["imageUrl"],
                'image_public_id' => $uploaded["publicId"],
            ]);
        }

        $post = $this->postRepository->update($id, $request->all());


        return response()->json([
            'message' => 'Post updated successfully',
            'post' => $post,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */

    public function destroy($id)
    {

        $post = $this->postRepository->findPost($id);

        if (!$post) {
            return response()->json([
                'message' => 'Post not found',
            ], 404);
        }

        $this->authorize('delete', $post);


        if ($post->image_public_id) {
            dispatch(new DeleteImage($post->image_public_id));
        }

        $this->postRepository->deletePost($id);

        return response()->json([
            'message' => 'Post deleted successfully',
        ]);
    }

    public function postLove($id)
    {

        $post = $this->postRepository->findPost($id);

        if (!$post) {
            return response()->json([
                'message' => 'Chapter not found',
            ], 404);
        }

        $userID = Auth::user()->id;


        $already_loved = $post->love()->where('user_id', $userID)->exists();

        if ($already_loved) {
            $post->love()->where('user_id', $userID)->delete();
            $message = 'Post unloved successfully';
        }else{
            $post->love()->create([
                'user_id' => $userID,
            ]);
            $message = 'Post loved successfully';
        }

        return response()->json([
            'message' => $message,
        ], 200);

    }
}
