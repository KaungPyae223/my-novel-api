<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserRequest;
use App\Http\Resources\ProfileResource;
use App\Repositories\UserRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{

    protected $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function show($id) {}

    public function notFound()
    {
        return response()->json([
            'message' => 'User not found',
        ], 404);
    }

    public function viewProfile()
    {
        $id = Auth::user()->id;

        $user = $this->userRepository->findUser($id);

        if (!$user) {
            return $this->notFound();
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
            return $this->notFound();
        }

        $request->merge([
            'username' => '@' . $request->username,
        ]);

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
            return $this->notFound();
        }

        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg|max:5120',
        ]);

        if ($user->hasMedia('cover_image')) {
            $user->getFirstMediaUrl('cover_image')->delete();
        }

        $uploadImage = $request->file('image');

        $user->addMedia($uploadImage)->toMediaCollection('cover_image');


        return response()->json([
            'message' => 'Cover image uploaded successfully',
        ]);
    }


    public function uploadProfileImage(Request $request)
    {

        $user = $this->userRepository->findUser(Auth::user()->id);

        if (!$user) {
            return $this->notFound();
        }

        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:5120',
        ]);

        if ($user->hasMedia('profile_image')) {
            $user->getFirstMediaUrl('profile_image')->delete();
        }

        $uploadImage = $request->file('image');

        $user->addMedia($uploadImage)->toMediaCollection('profile_image');


        return response()->json([
            'message' => 'Profile image uploaded successfully',
        ]);

        return response()->json([
            'message' => 'Profile image uploaded successfully',
        ]);
    }

    public function checkUser()
    {

        return response()->json([
            'message' => 'User checked successfully',
        ]);
    }

    public function checkVerification()
    {

        
        $verified_at = Auth::user()->email_verified_at;

        return response()->json([
            'verfified_at' => $verified_at,
        ], 200);
    }

    public function saveSubscription(Request $request)
    {

        $request->validate([
            'endpoint' => 'required',
            'keys.p256dh' => 'required',
            'keys.auth' => 'required',
        ]);

        $user = Auth::user();

        $user->updatePushSubscription(
            $request->endpoint,
            $request->keys['p256dh'],
            $request->keys['auth']
        );

        return response()->json(['success' => true]);
    }
}
