<?php

namespace Fruitcake\Cors\Tests;

use Fruitcake\Cors\CorsServiceProvider;
use Fruitcake\Cors\HandleCors;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Routing\Router;

class BrowserTest extends \Orchestra\Testbench\Dusk\TestCase
{
    protected static $baseServeHost = '127.0.0.1';
    protected static $baseServePort = 9292;


    protected function resolveApplicationConfiguration($app)
    {
        parent::resolveApplicationConfiguration($app);

        $app['config']['cors'] = [
            'paths' => ['*'],
            'supports_credentials' => false,
            'allowed_origins' => ['http://127.0.0.1:9292'],
            'allowed_headers' => ['X-Requested-With'],
            'allowed_methods' => ['*'],
            'exposed_headers' => [],
            'max_age' => 0,
        ];

    }

    protected function getPackageProviders($app)
    {
        return [CorsServiceProvider::class];
    }

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
        $kernel = $app->make(Kernel::class);
        $kernel->prependMiddleware(HandleCors::class);

        /** @var Router $router */
        $router = $app['router'];

        $this->addRunnerRoutes($router);
        $this->addWebRoutes($router);

//        \Orchestra\Testbench\Dusk\Options::withoutUI();

    }

    /**
     * @param Router $router
     */
    protected function addRunnerRoutes(Router $router)
    {
        foreach (scandir(__DIR__ .'/js') as $file) {
            if (strlen($file) > 3) {
                $router->get('js/' . $file, function ()  use($file) {
                    return file_get_contents(__DIR__ . '/js/' . $file);
                });
            }
        }
    }


    /**
     * @param Router $router
     */
    protected function addWebRoutes(Router $router)
    {
        $router->any('/', function () {
            return 'Hello world';
        });

        $router->any('cors', function () {
            return 'OK!';
        });
    }

    public function testFetch()
    {
        $this->browse(function ($browser) {
            $browser->visit('js/fetch.html')
                ->waitUntil('completed', 10)
                ->pause(100)
                ->assertSee('passes: 8');
        });
    }
}