<?php

namespace App\Http\Utils;

use Elastic\Elasticsearch\ClientBuilder;
use Illuminate\Support\Facades\Log;


class ElasticSetUp
{
    public function setUp()
    {
        $elasticsearch = null;

        if (config('elastic.hosts')) {
            try {
                $elasticsearch = ClientBuilder::create()
                    ->setHosts([config('elastic.hosts')])
                    ->setSSLVerification(false)
                    ->setBasicAuthentication(config('elastic.user'), config('elastic.password'))
                    ->build();
            } catch (\Exception $e) {
                Log::error('Elasticsearch initialization failed: ' . $e->getMessage());
                $elasticsearch = null;
            }
        }

        return $elasticsearch;
    }
}