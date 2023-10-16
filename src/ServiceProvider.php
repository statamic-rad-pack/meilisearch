<?php

namespace StatamicRadPack\meilisearch;

use Illuminate\Foundation\Application;
use meilisearch\Client;
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

            return $app->makeWith(meilisearch\Index::class, [
                'client' => $client,
                'name' => $name,
                'config' => $config,
            ]);
        });
    }
}
