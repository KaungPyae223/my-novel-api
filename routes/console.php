<?php

use App\Jobs\ChapterSchedule;
use App\Jobs\ElasticSyncSchedule;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::job(ChapterSchedule::class)->everyFiveMinutes();
Schedule::job(ElasticSyncSchedule::class)->twiceDaily(0, 12);
