<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreChapterRequest;
use App\Http\Requests\UpdateChapterRequest;
use App\Http\Resources\ChapterResource;
use App\Jobs\GenerateSummary;
use App\Models\Chapter;
use App\Repositories\ChapterRepository;
use App\Repositories\NovelRepository;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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

    public function generateSuggestion($id,Request $request)
    {


        $chapter = $request->input('chapter');

        $cacheKey = 'chapter_suggestion_' . $id.'_'.$chapter;

        $response = Cache::remember($cacheKey, now()->addMinutes(5), function () use ($id, $chapter,$cacheKey) {

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
                        'content' => "Here's a summary of my fantasy novel chapter: The villagers see a strange light in the sky, and later a dragon crashes into the forest near the town. The protagonist, a young hunter, is the first to find the dragon. He’s afraid, but feels strangely drawn to it."
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

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreChapterRequest $request)
    {


        $novel = $this->NovelRepository->findNovel($request->novel_id);

        $this->authorize('storeChapter',$novel);

        $chapter = $this->ChapterRepository->createChapter($request->all());

        if(!$request->summary){
            GenerateSummary::dispatch($chapter->id,$request->content);
        }

        return response()->json([
            'message' => 'Chapter created successfully',
            'data' => new ChapterResource($chapter)
        ], 201);

    }

    /**
     * Display the specified resource.
     */
    public function show(Chapter $chapter)
    {
        //
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

        $chapter = $this->ChapterRepository->updateChapter($id, $request->all());

        return response()->json([
            'message' => 'Chapter updated successfully',
            'data' => new ChapterResource($chapter)
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Chapter $chapter)
    {
        //
    }
}
