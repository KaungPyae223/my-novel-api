<?php

namespace App\Console\Commands;

use App\Elastic\ElasticSync;
use Illuminate\Console\Command;

class ElasticSyncSchedule extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:elastic-sync-schedule';

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
        (new ElasticSync())->sync();
    }
}
