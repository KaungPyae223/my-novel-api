<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class FreshAllDB extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'migrate:fresh-all-db';

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
        Artisan::call('migrate:fresh --seed', [], $this->getOutput());

        $mongo = DB::connection('mongodb'); 

        $collections = $mongo->listCollections();

        foreach ($collections as $collection) {
            $name = $collection->getName();
            if (!in_array($name, ['system.indexes'])) {
                $mongo->getCollection($name)->drop();
                $this->line("Dropped MongoDB collection: {$name}");
            }
        }

        $this->info('All done!');
    }
}
