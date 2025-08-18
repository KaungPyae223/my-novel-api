<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePostRequest;
use App\Http\Requests\UpdatePostRequest;
use App\Jobs\DeleteImage;
use App\Models\Post;
use App\Repositories\PostRepository;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;


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
    public function update(UpdatePostRequest $request, post $post)
    {
        //
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
}
