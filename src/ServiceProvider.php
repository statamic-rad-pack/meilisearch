<?php

namespace StatamicRadPack\Meilisearch;

use Illuminate\Foundation\Application;
use Meilisearch\Client;
use Statamic\Facades\Search;
use Statamic\Providers\AddonServiceProvider;

class ServiceProvider extends AddonServiceProvider
{
    public function bootAddon()
    {
        Search::extend('meilisearch', function (Application $app, array $config, $name, $locale = null) {
            $client = $app->makeWith(Client::class, [
                'url' => $config['credentials']['url'],
                'apiKey' => $config['credentials']['secret'],
            ]);

            return $app->makeWith(Meilisearch\Index::class, [
                'client' => $client,
                'name' => $name,
                'config' => $config,
                'locale' => $locale,
            ]);
        });
    }
}
