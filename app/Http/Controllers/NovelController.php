<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreNovelRequest;
use App\Http\Requests\UpdateNovelRequest;
use App\Http\Resources\NovelResource;
use App\Http\Utils\GenerateUniqueName;
use App\Http\Utils\ImageUtils;
use App\Models\Novel;
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

    public function __construct(NovelRepository $novelRepository) {
        $this->novelRepository = $novelRepository;
    }

    public function index()
    {
        $novels = $this->novelRepository->all();

        return response()->json([
            'message' => 'Novels retrieved successfully',
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

        $this->authorize('update', $this->novelRepository->findNovel($id),Novel::class);

        $novel = $this->novelRepository->findNovel($id);

        if (!$novel) {
            return response()->json([
                'message' => 'Novel not found',
            ], 404);
        }

        if ($novel->image_public_id) {
            ImageUtils::deleteImage($novel->image_public_id);
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

        $this->authorize('update', $novel,Novel::class);

        if (!$novel) {
            return response()->json([
                'message' => 'Novel not found',
            ], 404);
        }

        $this->novelRepository->update($novel->id, $request->all());

        return response()->json([
            'message' => 'Novel updated successfully',
            'novel' => $novel,
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
        $this->authorize('delete', $this->novelRepository->findNovel($id),Novel::class);

        $novel = $this->novelRepository->findNovel($id);

        if (!$novel) {
            return response()->json([
                'message' => 'Novel not found',
            ], 404);
        }

        if ($novel->image_public_id) {
            ImageUtils::deleteImage($novel->image_public_id);
        }

        $this->novelRepository->delete($novel->id);

        return response()->json([
            'message' => 'Novel deleted successfully',
        ]);
    }
}
