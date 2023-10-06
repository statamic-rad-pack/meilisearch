<?php

namespace Elvenstar\StatamicMeiliSearch;

use Elvenstar\StatamicMeiliSearch\MeiliSearch\Index;
use Illuminate\Foundation\Application;
use Meilisearch\Client;
use Statamic\Facades\Search;
use Statamic\Providers\AddonServiceProvider;

class StatamicMeiliSearchServiceProvider extends AddonServiceProvider
{
    public function boot()
    {
        parent::boot();

        $this->bootAddonConfig();

        $this->bootSearchClient();
    }

    protected function bootAddonConfig()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/meilisearch.php', 'meilisearch');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/meilisearch.php' => config_path('meilisearch.php'),
            ], 'statamic-meilisearch');
        }

        return $this;
    }

    protected function bootSearchClient()
    {
        Search::extend('meilisearch', function (Application $app, array $config, $name) {
            $client = $app->makeWith(Client::class, [
                'url' => $config['credentials']['url'],
                'apiKey' => $config['credentials']['secret'],
            ]);

            return $app->makeWith(Index::class, [
                'client' => $client,
                'name' => $name,
                'config' => $config,
            ]);
        });
    }
}
