<?php

namespace StatamicRadPack\Mellisearch;

use Illuminate\Foundation\Application;
use MeiliSearch\Client;
use Statamic\Facades\Search;
use Statamic\Providers\AddonServiceProvider;

class ServiceProvider extends AddonServiceProvider
{
    public function bootAddon()
    {
        Search::extend('meilisearch', function (Application $app, array $config, $name) {
            $client = $app->makeWith(Client::class, [
                'url' => $config['credentials']['url'],
                'apiKey' => $config['credentials']['secret'],
            ]);

            return $app->makeWith(Mellisearch\Index::class, [
                'client' => $client,
                'name' => $name,
                'config' => $config,
            ]);
        });
    }
}
