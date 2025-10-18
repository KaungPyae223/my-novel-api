<?php

use App\Http\Controllers\AuthenticationController;
use App\Http\Controllers\ChapterController;
use App\Http\Controllers\GenreController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LibraryController;
use App\Http\Controllers\NovelController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\QuerySuggestionController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::prefix("v1")->group(function () {

    Route::controller(AuthenticationController::class)->group(function () {
        Route::post('/login', 'login');
        Route::post('/register', 'register');
        Route::get('/check-username', 'check_username');
        Route::get('/email/verify/{id}/{hash}', 'verifyEmail')->middleware(['signed'])->name('verification.verify');
    });



    Route::middleware('auth:sanctum')->group(function () {


        Route::controller(LibraryController::class)->group(function () {
            Route::get('library/novels', 'Novels');
        });

        Route::controller(AuthenticationController::class)->group(function () {
            Route::post('/send-verification-mail', 'SendVerificationEmail');
            Route::post('/logout', 'logout');
        });

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

        Route::middleware(['verified'])->group(function () {
            Route::apiResource('novels', NovelController::class);
            Route::controller(NovelController::class)->group(function () {
                Route::post('novels/upload-image/{id}', 'novelImageUpload');
                Route::get('my-novels', 'getMyNovels');
                Route::get('novel-chapters/{id}', 'getNovelChapters');
                Route::post('novels/create-post/{id}', 'createNovelPost');
                Route::get('my-novels/kpi', 'getMyNovelsKPI');
                Route::get('novel-logs/{id}', 'getNovelLogs');
                Route::get('novel-trashed-chapters/{id}', 'getTrashedChapters');
            });
        });
        Route::controller(NovelController::class)->group(function () {
            Route::post('novels/loved/{id}', 'novelLove');
            Route::post('novels/favorite/{id}', 'novelFavorite');
        });

        Route::middleware(['verified'])->group(function () {
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
                Route::post('chapters/restore/{id}', 'restoreChapter');
            });
            Route::post('reviews', [ReviewController::class, 'store']);
        });
        Route::post('chapters/loved/{id}', [ChapterController::class, 'chapterLove']);

        Route::apiResource('posts',PostController::class)->only(["destroy"]);
        Route::controller(PostController::class)->group(function () {
            Route::post('posts/{id}', 'update');
            Route::post('posts/loved/{id}', 'postLove');
        });

        Route::controller(HomeController::class)->prefix('home')->group(function () {
            Route::get('novels', 'recommendNovels');
            Route::get('chapters', 'recommendChapters');
            Route::get('posts', 'recommendPosts');
        });

        Route::controller(QuerySuggestionController::class)->prefix('suggestions')->group(function () {
            Route::get('novels', 'novelSuggestion');
        });

    });


    Route::controller(NovelController::class)->group(function () {
        Route::get('user/novels/{id}', 'showUserNovel');
        Route::get('user/novel-chapters/{id}', 'showUserNovelChapter');
        Route::get('novels/posts/{id}', 'getNovelPosts');
        Route::post('novels/share/{id}', 'novelShare');
        Route::get('novels/reviews/{id}', 'novelReviews');
        Route::get('novels/last-read-chapter/{id}', 'getUserLastReadChapter');
    });

    Route::controller(ChapterController::class)->group(function () {
        Route::post('chapters/share/{id}', 'chapterShare');
        Route::get('chapters/{id}', 'show');
    });

});


