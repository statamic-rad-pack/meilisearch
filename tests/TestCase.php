<?php

namespace StatamicRadPack\Meilisearch\Tests;

use Statamic\Testing\AddonTestCase;
use StatamicRadPack\Meilisearch\ServiceProvider;

class TestCase extends AddonTestCase
{
    protected string $addonServiceProvider = ServiceProvider::class;

    protected $shouldFakeVersion = true;

    protected function resolveApplicationConfiguration($app)
    {
        parent::resolveApplicationConfiguration($app);

        // add driver
        $app['config']->set('statamic.search.drivers.meilisearch', [
            'credentials' => [
                'url' => 'http://localhost:7700',
                'secret' => 'LARAVEL-HERD',
            ],
        ]);

        // add index
        $app['config']->set('statamic.search.indexes.meilisearch_index', [
            'driver' => 'meilisearch',
            'searchables' => ['collection:pages'],
        ]);
    }
}
