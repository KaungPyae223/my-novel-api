<?php

use App\Console\Commands\ChapterSchedule;
use App\Console\Commands\ElasticSyncSchedule;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command(ChapterSchedule::class)->everyFiveMinutes();
Schedule::command(ElasticSyncSchedule::class)->twiceDaily(0, 12);
