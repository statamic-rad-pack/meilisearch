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

        $app['config']->set('statamic.search.drivers.meilisearch', [
            'credentials' => [
                'url' => 'http://localhost:7700',
                'secret' => 'masterKey',
            ],
        ]);

        $app['config']->set('statamic.search.indexes.cp', [
            'driver' => 'null',
        ]);

        $app['config']->set('statamic.search.indexes.meilisearch_index', [
            'driver' => 'meilisearch',
            'searchables' => ['collection:pages'],
        ]);
    }
}
