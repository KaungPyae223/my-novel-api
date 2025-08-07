<?php

namespace App\Console\Commands;

use App\Models\Chapter;
use Illuminate\Console\Command;

class ChapterSchedule extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:chapter-schedule';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
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
