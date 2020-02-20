<?php

namespace Fruitcake\Cors\Tests;

use Fruitcake\Cors\CorsServiceProvider;

abstract class TestCase extends \Orchestra\Testbench\TestCase
{
    protected function resolveApplicationConfiguration($app)
    {
        parent::resolveApplicationConfiguration($app);
    }

    protected function getPackageProviders($app)
    {
        return [CorsServiceProvider::class];
    }
}
