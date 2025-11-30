<?php

namespace App\Providers;

use Elastic\Elasticsearch\ClientBuilder;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;

class ElasticsearchServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton('elasticsearch', function () {

            if (!config('elastic.hosts')) {
                Log::warning('Elasticsearch hosts config missing');
                return null;
            }

            try {
                return ClientBuilder::create()
                    ->setHosts([config('elastic.hosts')])
                    ->setSSLVerification(false)
                    ->setBasicAuthentication(
                        config('elastic.user'),
                        config('elastic.password')
                    )
                    ->build();
            } catch (\Exception $e) {
                Log::error('Elasticsearch initialization failed: ' . $e->getMessage());
                return null;
            }
        });
    }
}
