<?php

namespace Elvenstar\StatamicMeiliSearch\Tests;

use Elvenstar\StatamicMeiliSearch\StatamicMeiliSearchServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    public function setUp(): void
    {
        parent::setUp();
    }

    protected function getPackageProviders($app)
    {
        return [
            StatamicMeiliSearchServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        // ...
    }
}
