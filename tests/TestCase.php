<?php

namespace StatamicRadPack\Mellisearch\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use StatamicRadPack\Mellisearch\ServiceProvider;

class TestCase extends Orchestra
{
    public function setUp(): void
    {
        parent::setUp();
    }

    protected function getPackageProviders($app)
    {
        return [
            ServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        // ...
    }
}
