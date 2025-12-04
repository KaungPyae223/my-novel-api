<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreChapterRequest;
use App\Http\Requests\UpdateChapterRequest;
use App\Http\Resources\ChapterResource;
use App\Http\Resources\ChapterUpdateResource;
use App\Jobs\GenerateSummary;
use App\Models\Chapter;
use App\Repositories\ChapterRepository;
use App\Repositories\NovelRepository;
use App\Services\ChapterServices;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class ChapterController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    use AuthorizesRequests;

    protected $NovelRepository;
    protected $ChapterRepository;
    protected $chapterServices;

    public function __construct(NovelRepository $NovelRepository, ChapterServices $chapterServices, ChapterRepository $ChapterRepository)
    {
        $this->NovelRepository = $NovelRepository;
        $this->ChapterRepository = $ChapterRepository;
        $this->chapterServices = $chapterServices;
    }

    public function index()
    {
        return Chapter::all();
    }

    public function notFound()
    {
        return response()->json([
            'message' => 'Chapter not found',
        ], 404);
    }

    public function generateSuggestion($id)
    {


        $novel = $this->NovelRepository->findNovel($id);


        if (!$novel) {
            return response()->json([
                'message' => 'Novel not found',
            ], 404);
        }

        $last_chapter = $novel->chapters()->orderBy('id', 'desc')->first();

        $cacheKey = 'chapter_suggestion_' . $id . '_' . ($last_chapter->id ?? 0);

        $response = Cache::remember($cacheKey, now()->addHours(12), function () use ($novel, $last_chapter) {

            $content = $this->chapterServices->generateSuggestion($novel, $last_chapter);

            return $this->chapterServices->formatContent($content);
        });


        if (isset($response['error'])) {
            Cache::forget($cacheKey);
            return response()->json($response, 422);
        }

        return response()->json($response);
    }

    public function assessment(Request $request)
    {
        $request->validate([
            'content' => 'required|string',
        ]);

       $content = $this->chapterServices->generateAssessment($request->content);

        if ($content) {

            $formatContent = $this->chapterServices->formatContent($content);

            return response()->json($formatContent);
        }

        return response()->json(['error' => 'Invalid response content.']);
    }

   

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreChapterRequest $request)
    {

        $novel = $this->NovelRepository->findNovel($request->novel_id);

        if (!$novel) {
            return response()->json([
                'message' => 'Novel not found',
            ], 404);
        }

        $this->authorize('storeChapter', $novel);

        if ($request->status === 'published') {
            $draftCount = $this->chapterServices->draftCount($novel);

            if ($draftCount >= 1) {
                $request->merge([
                    'status' => 'draft',
                ]);
                $message = 'Chapter created successfully but it is in draft status because you do not have a published previous chapter';
            } else {
                $message = 'Chapter created successfully';
            }
        } else {
            $message = 'Chapter created successfully';
        }


        $chapter = $this->ChapterRepository->createChapter($request->all());

        if (!$request->summary) {
            GenerateSummary::dispatch($chapter->id, $request->content);
        }

        return response()->json([
            'message' => $message,
            'data' => new ChapterResource($chapter)
        ], 201);
    }

    

    public function chapterStatusCheck(Request $request)
    {
        $chapter_id = $request->input('chapter_id');

        $novel_id = $request->input('novel_id');

        $chapterStatusCheck = $this->updateChapterStatusCheck($novel_id, $chapter_id);

        return response()->json($chapterStatusCheck);
    }

    public function updateChapterStatusCheck($novel_id, $chapter_id)
    {
        $novel = $this->NovelRepository->findNovel($novel_id);

        if (!$novel) {
            return response()->json([
                'message' => 'Novel not found',
            ], 404);
        }

        $chapter = $this->ChapterRepository->findChapter($chapter_id);

        if (!$chapter) {
            return response()->json([
                'message' => 'Chapter not found',
            ], 404);
        }

        $draft_count = $novel->chapters()->where('id', '<', $chapter_id)->where('status', '!=', 'published')->count();

        $published_count = $novel->chapters()->where('id', '>', $chapter_id)->where('status', '==', 'published')->count();

        return [
            'canDraft' => $published_count == 0,
            'canPublish' => $draft_count == 0,
        ];
    }



    /**
     * Display the specified resource.
     */

    public function updateChapterShow($id)
    {
        $chapter = $this->ChapterRepository->findChapter($id);

        if (!$chapter) {
            return response()->json([
                'message' => 'Chapter not found',
            ], 404);
        }

        $this->authorize('update', $chapter);

        return response()->json([
            'data' => new ChapterUpdateResource($chapter)
        ]);
    }

    public function show($id, Request $request)
    {


        $chapter = $this->ChapterRepository->findChapterWithTrash($id);
        $language = $request->input('language');
        $readType = $request->input('read_type');

        if (!$chapter) {
            return $this->notFound();
        }

        $user_id = $this->chapterServices->checkUser();

        $isAuthenticated = Auth::guard('sanctum')->check();
        $isPublished = $chapter->status == 'published';
        $isTrashed = $chapter->trashed(); 

        if (!$isPublished && !$isAuthenticated && $chapter->novel->user_id != $user_id) {
            return $this->notFound();
        }

        if ($isAuthenticated && $isPublished && !$isTrashed) {
            $this->chapterServices->addView($chapter, $user_id);
            $this->chapterServices->addHistory($chapter, $user_id);
        }

        $already_loved = $user_id ? $chapter->love()->where('user_id', $user_id)->exists() : false;


        if ($language) {
            if ($readType == 'summary' && !empty($chapter->summary)) {
                $chapter->summary = $this->chapterServices->translate($chapter->summary, $chapter->novel->genre->genre, $language);
            } else if (!empty($chapter->content)) {
                $chapter->content = $this->chapterServices->translate($chapter->content, $chapter->novel->genre->genre, $language);
            }
        }

        $chapter->already_loved = $already_loved;

        return response()->json([
            'data' => new ChapterResource($chapter),

        ]);
    }

    public function grammarCheck(Request $request)
    {

        $request->validate([
            'content' => 'required|string',
        ]);

        $grammarCheck = $this->chapterServices->grammarCheck($request->content);

        return response()->json([
            'fleschKincaid' => $grammarCheck['fleschKincaid'],
            'errors' => $grammarCheck['errors'],
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateChapterRequest $request, $id)
    {
        $chapter = $this->ChapterRepository->findChapter($id);

        if (!$chapter) {
            return $this->notFound();
        }

        $this->authorize('update', $chapter);

        $chapterStatusCheck = $this->updateChapterStatusCheck($chapter->novel_id, $id);

        if ($request->status === 'published' && $chapterStatusCheck['canPublish'] == false) {
            $request->merge([
                'status' => 'draft',
            ]);
            $message = 'Chapter updated successfully but it is in draft status because you do not have a published previous chapter';
        } elseif ($request->status === 'draft' && $chapterStatusCheck['canDraft'] == false) {
            $request->merge([
                'status' => 'published',
            ]);
            $message = 'Chapter updated successfully but it is in published status because there are published chapters after this chapter';
        } else {
            $message = 'Chapter updated successfully';
        }

        if ($request->status != 'scheduled') {
            $request->merge([
                'scheduled_date' => null,
            ]);
        }

        $chapter = $this->ChapterRepository->updateChapter($id, $request->all());

        return response()->json([
            'message' => $message,
            'data' => new ChapterUpdateResource($chapter)
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */


    public function destroy($id)
    {
        $chapter = $this->ChapterRepository->findChapterWithTrash($id);

        if (!$chapter) {
            return $this->notFound();
        }

        $this->authorize('delete', $chapter);

        if ($chapter->status != 'draft') {
            return response()->json([
                'message' => 'Chapter is not in draft status',
            ], 400);
        }

        $this->ChapterRepository->deleteChapter($id);

        return response()->json([
            'message' => 'Chapter deleted successfully',
        ], 200);
    }

    public function restoreChapter($id)
    {
        $chapter = $this->ChapterRepository->findChapterWithTrash($id);

        if (!$chapter) {
            return $this->notFound();
        }

        $this->authorize('delete', $chapter);

        $this->ChapterRepository->restoreChapter($id);

        return response()->json([
            'message' => 'Chapter restored successfully',
        ], 200);
    }


    public function chapterLove($id)
    {

        $chapter = $this->ChapterRepository->findChapter($id);

        if (!$chapter) {
            return $this->notFound();
        }

        $message = $this->chapterServices->toggleLove($chapter);

        return response()->json([
            'message' => $message,
        ], 200);
    }

    public function chapterShare($id)
    {
        $chapter = $this->ChapterRepository->findChapter($id);

        if (!$chapter) {
            return $this->notFound();
        }
       
        $this->chapterServices->share($chapter);

        return response()->json([
            'message' => 'Chapter shared successfully',
        ]);
    }
}
