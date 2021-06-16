<?php

namespace Elvenstar\StatamicMeiliSearch;

use Statamic\Facades\Search;
use Statamic\Providers\AddonServiceProvider;
use MeiliSearch\Client;

class ServiceProvider extends AddonServiceProvider
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
        Search::extend('meilisearch', function ($app, array $config, $name) {
            $credentials = $config['credentials'];
            $url = $credentials['url'];
            $masterKey = $credentials['secret'];

            $client = new Client($url, $masterKey);

            return new MeiliSearch\Index($client, $name, $config);
        });
    }
}
