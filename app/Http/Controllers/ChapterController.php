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
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class ChapterController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    use AuthorizesRequests;

    protected $NovelRepository;
    protected $ChapterRepository;

    public function __construct(NovelRepository $NovelRepository,ChapterRepository $ChapterRepository) {
        $this->NovelRepository = $NovelRepository;
        $this->ChapterRepository = $ChapterRepository;
    }

    public function index()
    {
        return Chapter::all();
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



        $cacheKey = 'chapter_suggestion_' . $id.'_'.$last_chapter->id;



        $response = Cache::remember($cacheKey, now()->addHours(12), function () use ($novel, $last_chapter) {

            $apiKey = config('ai.api_key');

            $response = Http::withOptions([
                'proxy' => ''
            ])->withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json',
            ])->post('https://openrouter.ai/api/v1/chat/completions', [
                'model' => 'deepseek/deepseek-r1-0528:free',
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => "You are a professional writing assistant helping fiction writers improve their chapters. Respond only in valid JSON format with the following structure:
                            {
                            \"chapter_direction\": \"Your suggestion here\",
                            \"character_development\": \"Your suggestion here\",
                            \"plot_enhancement\": \"Your suggestion here\",
                            \"writing_tips\": \"Your suggestion here\"
                            }
                            Each field should contain one helpful, concise suggestion. Do not include any extra commentary outside the JSON.
                            In the content please separate me with // if the suggestion is more than one and you can give me more than one suggestion in each field.
                            "
                    ],
                    [
                        'role' => 'user',
                        'content' => "Here's a summary of novel: ".$novel->synopsis."\n\n Here's the content of last chapter: ".$last_chapter->content."\n\n"
                    ]
                ],
            ]);


            $content = $response['choices'][0]['message']['content'] ?? null;


            if ($content) {
                //Mark Down to JSON
                preg_match('/```json\s*(.*?)\s*```/s', $content, $matches);

                if (isset($matches[1])) {
                    $json = $matches[1];
                    $data = json_decode($json, true);

                    if (json_last_error() === JSON_ERROR_NONE) {
                        return [
                            'chapter_direction' => $data['chapter_direction'] ?? null,
                            'character_development' => $data['character_development'] ?? null,
                            'plot_enhancement' => $data['plot_enhancement'] ?? null,
                            'writing_tips' => $data['writing_tips'] ?? null,
                        ];
                    } else {
                        return ['error' => 'Failed to parse suggestion JSON.'];
                    }
                } else {
                    return ['error' => 'No JSON block found in response.'];
                }
            } else {
                return ['error' => 'Invalid response content.'];
            }

        });


        if (isset($response['error'])) {
            Cache::forget($cacheKey);
            return response()->json($response, 422);
        }

        return response()->json($response);
    }

    public function assessment(Request $request){
        $request->validate([
            'content' => 'required|string',
        ]);

        $apiKey = config('ai.api_key');

        $response = Http::withOptions([
            'proxy' => ''
        ])->withHeaders([
            'Authorization' => 'Bearer ' . $apiKey,
            'Content-Type' => 'application/json',
        ])->post('https://openrouter.ai/api/v1/chat/completions', [
            'model' => 'deepseek/deepseek-r1-0528:free',
            'messages' => [
                [
                    'role' => 'system',
                    'content' => "You are a fiction writing assistant. Analyze the following chapter and provide enhancement suggestions across the following four key categories:

                        1. **Story Pacing** — Evaluate how well the chapter balances dialogue, action, and narration. Suggest any improvements to rhythm, tempo, or structural pauses.

                        2. **Character Development** — Analyze the emotional and psychological growth of the characters. Suggest ways to deepen their emotional arc or show internal conflict more vividly.

                        3. **World Building** — Assess the immersive quality of the setting. Suggest improvements in descriptive details or the uniqueness of the environment.

                        4. **Chapter Ending** — Evaluate how effective the ending is in leaving an impact or encouraging the reader to continue. Suggest how it can be made more satisfying or suspenseful.

                        For each category:
                        - Give a short but insightful paragraph.
                        - Include a score out of 10.

                        Then, give an **Overall Assessment**:
                        - A summary of how the chapter contributes to the story.
                        - Mention strengths and opportunities for improvement.
                        - Provide an overall score out of 10 and a one-word rating (e.g., \"Excellent\", \"Good\", \"Needs Improvement\").

                        Respond only in valid JSON format with the following structure:
                        {
                            \"story_pacing\": {
                                \"suggestion\": \"Your suggestion here\",
                                \"score\": \"Your score here (0-10) please give only the score number\"
                            },
                            \"character_development\": {
                                \"suggestion\": \"Your suggestion here\",
                                \"score\": \"Your score here (0-10) please give only the score number\"
                            },
                            \"world_building\": {
                                \"suggestion\": \"Your suggestion here\",
                                \"score\": \"Your score here (0-10) please give only the score number\"
                            },
                            \"chapter_ending\": {
                                \"suggestion\": \"Your suggestion here\",
                                \"score\": \"Your score here (0-10) please give only the score number\"
                            },
                            \"overall_assessment\": {
                                \"suggestion\": \"Your suggestion here\",
                                \"score\": \"Your score here (0-10) please give only the score number\"
                            }
                        }
                        Do not include any extra commentary outside the JSON.
                    "
                ],
                [
                    'role' => 'user',
                    'content' => "Here's the content of chapter: ".$request->content."\n\n"
                ]
            ],
        ]);

        $content = $response['choices'][0]['message']['content'] ?? null;


        if ($content) {
            //Mark Down to JSON
            preg_match('/```json\s*(.*?)\s*```/s', $content, $matches);

            if (isset($matches[1])) {
                $json = $matches[1];
                $data = json_decode($json, true);

                if (json_last_error() === JSON_ERROR_NONE) {
                    return response()->json($data);
                } else {
                    return response()->json(['error' => 'Failed to parse suggestion JSON.']);
                }
            } else {
                return response()->json(['error' => 'No JSON block found in response.']);
            }

        }

        return response()->json(['error' => 'Invalid response content.']);

    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreChapterRequest $request)
    {

        $novel = $this->NovelRepository->findNovel($request->novel_id);

        $this->authorize('storeChapter',$novel);

        if($request->status === 'published'){
            $draftCount = $this->draftCount($request->novel_id);

            if ($draftCount >= 1) {
                $request->merge([
                    'status' => 'draft',
                ]);
                $message = 'Chapter created successfully but it is in draft status because you do not have a published previous chapter';
            }
            else{
                $message = 'Chapter created successfully';
            }
        }else{
            $message = 'Chapter created successfully';
        }

        if($request->status != 'scheduled'){
            $request->merge([
                'scheduled_date' => null,
            ]);
        }


        $chapter = $this->ChapterRepository->createChapter($request->all());

        if(!$request->summary){
            GenerateSummary::dispatch($chapter->id,$request->content);
        }

        return response()->json([
            'message' => $message,
            'data' => new ChapterResource($chapter)
        ], 201);

    }

    public function draftCount($id)
    {
        $novel = $this->NovelRepository->findNovel($id);

        if (!$novel) {
            return response()->json([
                'message' => 'Novel not found',
            ], 404);
        }

        $draftCount = $novel->chapters()->where('status', '!=', 'published')->count();

        return $draftCount;

    }

    public function chapterStatusCheck(Request $request)
    {
        $chapter_id = $request->input('chapter_id');

        $novel_id = $request->input('novel_id');

        $chapterStatusCheck = $this->updateChapterStatusCheck($novel_id,$chapter_id);

        return response()->json($chapterStatusCheck);
    }

    public function updateChapterStatusCheck($novel_id,$chapter_id)
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

        $draft_count = $novel->chapters()->where('id','<', $chapter_id)->where('status' ,'!=', 'published')->count();

        $published_count = $novel->chapters()->where('id','>', $chapter_id)->where('status' ,'==', 'published')->count();

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

    public function show($id)
    {
        $chapter = $this->ChapterRepository->findChapter($id);

        if (!$chapter) {
            return response()->json([
                'message' => 'Chapter not found',
            ], 404);
        }

        if ($chapter->status != 'published') {
            return response()->json([
                'message' => 'Chapter is not found',
            ], 404);
        }

        return response()->json([
            'data' => new ChapterResource($chapter)
        ]);
    }

    public function grammarCheck(Request $request){

        $request->validate([
            'content' => 'required|string',
        ]);

        $content = $request->content;

        $apiKey = config('ai.grammar_key');


        $response = Http::withOptions([
                'proxy' => ''
            ])->withHeaders([
                'Content-Type' => 'application/json',
            ])->post('https://api.textgears.com/analyze', [
                'text' => $content,
                'language' => 'en-US',
                'key' => $apiKey,
            ]);

        $data = $response["response"];

        $fleschKincaid = $data['stats']['fleschKincaid'];
        $errors = $data['grammar']['errors'];

        return response()->json([
            'fleschKincaid' => $fleschKincaid,
            'errors' => $errors,
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateChapterRequest $request, $id)
    {
        $chapter = $this->ChapterRepository->findChapter($id);

        if (!$chapter) {
            return response()->json([
                'message' => 'Chapter not found',
            ], 404);
        }

        $this->authorize('update', $chapter);

        $chapterStatusCheck = $this->updateChapterStatusCheck($chapter->novel_id, $id);

        if ($request->status === 'published' && $chapterStatusCheck['canPublish'] == false) {
            $request->merge([
                'status' => 'draft',
            ]);
            $message = 'Chapter updated successfully but it is in draft status because you do not have a published previous chapter';
        }

        elseif ($request->status === 'draft' && $chapterStatusCheck['canDraft'] == false) {
            $request->merge([
                'status' => 'published',
            ]);
            $message = 'Chapter updated successfully but it is in published status because there are published chapters after this chapter';
        }

        else{
            $message = 'Chapter updated successfully';
        }

        if($request->status != 'scheduled'){
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
        $chapter = $this->ChapterRepository->findChapter($id);

        if(!$chapter){
            return response()->json([
                'message' => 'Chapter not found',
            ], 404);
        }

        $this->authorize('delete', $chapter);

        if($chapter->status != 'draft'){
            return response()->json([
                'message' => 'Chapter is not in draft status',
            ], 400);
        }

        $this->ChapterRepository->deleteChapter($id);

        return response()->json([
            'message' => 'Chapter deleted successfully',
        ], 200);
    }
}
