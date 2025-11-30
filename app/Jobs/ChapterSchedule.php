<?php

namespace App\Jobs;

use App\Models\Chapter;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ChapterSchedule implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $chapter = Chapter::where('status', 'scheduled')->where('scheduled_date', '<=', now())->get();

        if ($chapter) {
            foreach ($chapter as $chapter) {
                $chapter->update([
                    'status' => 'published',
                ]);
            }
        }
    }
}
