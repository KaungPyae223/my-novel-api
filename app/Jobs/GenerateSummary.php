<?php

namespace App\Jobs;

use App\Repositories\ChapterRepository;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Http;

class GenerateSummary implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    protected $chapterId;
    protected $content;
    public function __construct($chapterId,$content)
    {
        $this->chapterId = $chapterId;
        $this->content = $content;

    }

    /**
     * Execute the job.
     */
    public function handle(ChapterRepository $chapterRepository): void
    {


        $apiKey = config('ai.api_key');

        $response = Http::withOptions([
            'proxy' => ''
        ])->withHeaders([
            'Authorization' => 'Bearer ' . $apiKey,
            'Content-Type' => 'application/json',
        ])->post('https://openrouter.ai/api/v1/chat/completions', [
            'model' => config('ai.main_model'),
            'messages' => [
                [
                    'role' => 'system',
                    'content' => "Generate a concise summary of the chapter, focusing only on the main events, key character developments, and important plot points. The summary must be clear, objective, and no longer than 300 words. Return only the summary text without any additional commentary or explanation."
                ],
                [
                    'role' => 'user',
                    'content' => $this->content,
                ],
            ],
        ]);


        $summary = $response['choices'][0]['message']['content'] ?? null;


        $chapterRepository->updateChapter($this->chapterId, [
            'summary' => $summary,
        ]);

    }
}
