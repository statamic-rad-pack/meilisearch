<?php

namespace StatamicRadPack\Mellisearch;

use MeiliSearch\Client;
use Statamic\Facades\Search;
use Statamic\Providers\AddonServiceProvider;

class ServiceProvider extends AddonServiceProvider
{
    public function bootAddon()
    {
        Search::extend('meilisearch', function ($app, array $config, $name) {
            $credentials = $config['credentials'];
            $url = $credentials['url'];
            $masterKey = $credentials['secret'];

            $client = new Client($url, $masterKey);

            return new MeiliSearch\Index($client, $name, $config);
        });
    }
}
