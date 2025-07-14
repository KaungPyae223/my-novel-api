<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreNovelRequest;
use App\Http\Requests\UpdateNovelRequest;
use App\Http\Utils\GenerateUniqueName;
use App\Http\Utils\ImageUtils;
use App\Models\Novel;
use App\Repositories\NovelRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;

class NovelController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    protected $novelRepository;

    public function __construct(NovelRepository $novelRepository) {
        $this->novelRepository = $novelRepository;
    }

    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreNovelRequest $request)
    {

        $unique_name = GenerateUniqueName::generate($request->title);

        $count = Novel::where('unique_name', $unique_name)->count();

        $unique_name = $unique_name . '-' . ($count+1);

        $request->merge([
            'unique_name' => $unique_name,
            'user_id' => Auth::user()->id,
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

    public function updateNovel($id, UpdateNovelRequest $request)
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

    /**
     * Display the specified resource.
     */
    public function show(Novel $novel)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateNovelRequest $request, Novel $novel)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Novel $novel)
    {
        //
    }
}
