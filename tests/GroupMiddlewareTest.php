<?php

namespace Fruitcake\Cors\Tests;

use Fruitcake\Cors\HandleCors;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Route;

class GroupMiddlewareTest extends AbstractTest
{
    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application $app
     *
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        // Add the middleware
        Route::group([], __DIR__ . '/routes/web.php');
        Route::middleware([HandleCors::class])->group(__DIR__ . '/routes/api.php');

        parent::getEnvironmentSetUp($app);
    }
}
