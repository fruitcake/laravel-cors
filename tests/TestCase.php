<?php

namespace Fruitcake\Cors\Tests;

use Illuminate\Routing\Router;
use Fruitcake\Cors\HandleCors;
use Fruitcake\Cors\CorsServiceProvider;

abstract class TestCase extends \Orchestra\Testbench\TestCase
{
    protected function resolveApplicationConfiguration($app)
    {
        parent::resolveApplicationConfiguration($app);

        $app['config']['cors'] = [
            'paths' => ['api/*'],
            'supports_credentials' => false,
            'allowed_origins' => ['localhost'],
            'allowed_headers' => ['X-Custom-1', 'X-Custom-2'],
            'allowed_methods' => ['GET', 'POST'],
            'exposed_headers' => false,
            'max_age' => false,
        ];
    }

    protected function getPackageProviders($app)
    {
        return [CorsServiceProvider::class];
    }
}
