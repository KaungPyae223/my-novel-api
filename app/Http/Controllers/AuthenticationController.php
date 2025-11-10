<?php

namespace App\Http\Controllers;

use App\Events\LogInEvent;
use App\Http\Resources\UserResource;
use App\Mail\VerificationMail;
use App\Models\User;
use App\Notifications\UserLoginNotification;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;

class AuthenticationController extends Controller
{
    public function register(Request $request)
    {

        $request->merge([
            'username' => '@' . $request->username,
        ]);

        $request->validate([
            'full_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'username' => 'required|string|max:255|unique:users,username|regex:/^\S+$/',
        ]);

        $user = User::create([
            'full_name' => $request->full_name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'username' => $request->username,
        ]);

        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => new UserResource($user),
        ]);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email|max:255',
            'password' => 'required|string|min:8',
        ]);

        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        $user = Auth::user();

        $token = $user->createToken('api-token')->plainTextToken;

       
        event(new LogInEvent($user));

        return response()->json(['token' => $token, 'user' => new UserResource($user)]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out successfully']);
    }

    public function SendVerificationEmail(Request $request)
    {

        $user = Auth::user();

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(15),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        Mail::to($user->email)->send(new VerificationMail($verificationUrl, $user));

        return response()->json(['message' => 'Send Verification Email']);
    }

    public function verifyEmail($id, $hash)
    {
        $user = User::find($id);

        if (! $user) {
            return response()->json(['message' => 'User not found.'], 404);
        }

        if (! hash_equals((string) $hash, sha1($user->email))) {
            return response()->json(['message' => 'Invalid verification link.'], 400);
        }

        if (! $user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
            event(new Verified($user));
        }

        return redirect()->away(config('ai.app_url') . '/verified-email');
    }
}
