<?php

use App\Http\Controllers\AuthenticationController;
use App\Http\Controllers\ChapterController;
use App\Http\Controllers\GenreController;
use App\Http\Controllers\NovelController;
use App\Http\Controllers\PostController;
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

        Route::apiResource('genres', GenreController::class)->only([
            'index',
            'store',
        ]);

        Route::apiResource('novels', NovelController::class);
        Route::controller(NovelController::class)->group(function () {
            Route::post('novels/upload-image/{id}', 'novelImageUpload');
            Route::get('my-novels', 'getMyNovels');
            Route::get('novel-chapters/{id}', 'getNovelChapters');
            Route::post('novels/create-post/{id}', 'createNovelPost');
            Route::get('novels/posts/{id}', 'getNovelPosts');
        });

        Route::apiResource('chapters', ChapterController::class)->except([
            'index', 'show'
        ]);
        Route::controller(ChapterController::class)->group(function () {
            Route::get('chapters/generate-suggestion/{id}', 'generateSuggestion');
            Route::get('chapters/draft-count/{id}', 'draftCount');
            Route::post('grammar-check', 'grammarCheck');
            Route::post('chapter-assessment', 'assessment');
            Route::get('chapter-status-check', 'chapterStatusCheck');
            Route::get('chapters/update-chapter-show/{id}', 'updateChapterShow');
        });

        Route::apiResource('posts',PostController::class)->only(["destroy","update"]);

    });


    Route::controller(NovelController::class)->group(function () {
        Route::get('user/novels/{id}', 'showUserNovel');
        Route::get('user/novel-chapters/{id}', 'showUserNovelChapter');
    });

    Route::apiResource('chapters', ChapterController::class)->only([
        'show',
    ]);



});
