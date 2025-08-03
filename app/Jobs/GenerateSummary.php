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
            'model' => 'deepseek/deepseek-r1-0528:free',
            'messages' => [
                [
                    'role' => 'system',
                    'content' => "Please generate a summary for the chapter. The summary should be concise and include the main events, character development, and any important plot points. The summary should not be longer than 150 words. Please return only the summary. Do not include any extra commentary outside the summary."
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
