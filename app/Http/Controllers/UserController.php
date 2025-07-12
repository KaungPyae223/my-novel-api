<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserRequest;
use App\Http\Resources\ProfileResource;
use App\Repositories\UserRepository;
use Illuminate\Http\Request;
use App\Http\Utils\ImageUtils;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{

    protected $userRepository;

    public function __construct(UserRepository $userRepository) {
        $this->userRepository = $userRepository;
    }

    public function show($id)
    {

    }

    public function viewProfile()
    {
        $id = Auth::user()->id;

        $user = $this->userRepository->findUser($id);

        if (!$user) {
            return response()->json([
                'message' => 'User not found',
            ], 404);
        }

        return response()->json([
            'user' => new ProfileResource($user),
        ]);
    }

    public function update(UserRequest $request)
    {

        $id = Auth::user()->id;

        $user = $this->userRepository->findUser($id);


        if (!$user) {
            return response()->json([
                'message' => 'User not found',
            ], 404);
        }


        $this->userRepository->updateUser($id, $request->all());

        return response()->json([
            'message' => 'User updated successfully',
            'user' => $user,
        ]);
    }

    public function uploadCoverImage(Request $request)
    {
        $user = $this->userRepository->findUser(Auth::user()->id);

        if (!$user) {
            return response()->json([
                'message' => 'User not found',
            ], 404);
        }

        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg|max:5120',
        ]);

        if ($user->cover_image_public_id) {
            ImageUtils::deleteImage($user->cover_image_public_id);
        }

        $uploadImage = $request->file('image');

        $uploaded = ImageUtils::uploadImage($uploadImage);


        $this->userRepository->updateUser($user->id, [
            'cover_image' => $uploaded["imageUrl"],
            'cover_image_public_id' => $uploaded["publicId"],
        ]);

        return response()->json([
            'message' => 'Cover image uploaded successfully',
        ]);
    }


    public function uploadProfileImage(Request $request)
    {

        $user = $this->userRepository->findUser(Auth::user()->id);

        if (!$user) {
            return response()->json([
                'message' => 'User not found',
            ], 404);
        }

        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:5120',
        ]);

        if ($user->profile_image_public_id) {
            ImageUtils::deleteImage($user->profile_image_public_id);
        }

        $uploadImage = $request->file('image');

        $uploaded = ImageUtils::uploadImage($uploadImage);


        $this->userRepository->updateUser($user->id, [
            'profile_image' => $uploaded['imageUrl'],
            'profile_image_public_id' => $uploaded['publicId'],
        ]);

        return response()->json([
            'message' => 'Profile image uploaded successfully',
        ]);
    }

    public function checkUser(){
        return response()->json([
            'message' => 'User checked successfully',
        ]);
    }
}
