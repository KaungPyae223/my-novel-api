<?php

use App\Http\Controllers\AuthenticationController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix("v1")->group(function () {

    Route::controller(AuthenticationController::class)->group(function () {
        Route::post('/login', 'login');
        Route::post('/register', 'register');
        Route::get('/check-username', 'check_username');
    });

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthenticationController::class, 'logout']);


        Route::controller(UserController::class)->group(function () {
            Route::get('/author/{id}', 'show');
            Route::get('/profile', 'viewProfile');
            Route::put('/profile', 'update');
            Route::post('/profile/upload-cover-image', 'uploadCoverImage');
            Route::post('/profile/upload-profile-image', 'uploadProfileImage');
            Route::get('/check-user', 'checkUser');
        });

    });



});
