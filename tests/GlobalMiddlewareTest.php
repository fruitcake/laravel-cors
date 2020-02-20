<?php

namespace Fruitcake\Cors\Tests;

use Fruitcake\Cors\HandleCors;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Route;

class GlobalMiddlewareTest extends AbstractTest
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
        /** @var Kernel $kernel */
        $kernel = $app->make(Kernel::class);
        $kernel->prependMiddleware(HandleCors::class);

        Route::group([], __DIR__ . '/routes/web.php');
        Route::group([], __DIR__ . '/routes/api.php');

        $app['config']['cors'] = [
            'paths' => ['api/*'],
            'supports_credentials' => false,
            'allowed_origins' => ['localhost'],
            'allowed_origins_patterns' => [],
            'allowed_headers' => ['X-Custom-1', 'X-Custom-2'],
            'allowed_methods' => ['GET', 'POST'],
            'exposed_headers' => false,
            'max_age' => false,
        ];

        parent::getEnvironmentSetUp($app);
    }

    /**
     * This only works on global middleware
     */
    public function testOptionsAllowOriginAllowedNonExistingRoute()
    {
        $crawler = $this->call('OPTIONS', 'api/pang', [], [], [], [
            'HTTP_ORIGIN' => 'localhost',
            'HTTP_ACCESS_CONTROL_REQUEST_METHOD' => 'POST',
        ]);

        $this->assertEquals('localhost', $crawler->headers->get('Access-Control-Allow-Origin'));
        $this->assertEquals(204, $crawler->getStatusCode());
    }
}
