<?php

namespace Fruitcake\Cors\Tests;

use Fruitcake\Cors\CorsServiceProvider;
use Fruitcake\Cors\HandleCors;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\File;
use Illuminate\Validation\UnauthorizedException;

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
            'allowed_headers' => ['X-Requested-With', 'Authorization'],
            'allowed_methods' => ['GET', 'POST', 'PATCH', 'PUT', 'DELETE', 'OPTIONS'],
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

        \Orchestra\Testbench\Dusk\Options::withoutUI();
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

        $router->any('auth', function (Request $request) {
            $auth = $request->header('Authorization');
            list ($type, $token) = explode (' ', $auth, 2);

            return $token;
        });

        $router->any('invalid', function () {
            File::put(__DIR__ .'/Browser/invalid.flag', '1');
            throw new \Exception('Should not reach this');
        });

        $router->get('error', function () {
            $foo++;
        });

        $router->get('abort', function () {
            abort('400', 'Aborted');
        });

        $router->get('exception', function () {
            throw new \RuntimeException('Exception!');
        });

    }

    public function testFetch()
    {
        File::delete(__DIR__ .'/Browser/invalid.flag');

        $this->browse(function ($browser) {
            $browser->visit('js/fetch.html')
                ->waitForText('passes: 12')
                ->assertSee('passes: 12');
        });

        $this->assertFalse(File::exists(__DIR__ .'/Browser/invalid.flag'));
    }

    public function testFetchWildcard()
    {
        $this->tweakApplication(function ($app) {
            $app['config']->set('cors.allowed_origins', ['*']);
            $app['config']->set('cors.allowed_methods', ['*']);
        });

        File::delete(__DIR__ .'/Browser/invalid.flag');

        $this->browse(function ($browser) {
            $browser->visit('js/fetch.html')
                ->waitForText('passes: 12')
                ->assertSee('passes: 12');
        });

        $this->assertFalse(File::exists(__DIR__ .'/Browser/invalid.flag'));
    }

    public function testPushMiddleware()
    {
        $this->tweakApplication(function ($app) {
            // Add the middleware
            /** @var Kernel $kernel */
            $kernel = $app->make(Kernel::class);
            $kernel->pushMiddleware(ProtectedMiddleware::class);
        });

        $this->browse(function ($browser) {
            $browser->visit('js/middleware.html')
                ->waitForText('passes: 2')
                ->assertSee('passes: 2');
        });
    }


    public function testPrependMiddleware()
    {
        $this->tweakApplication(function ($app) {
            // Add the middleware
            /** @var Kernel $kernel */
            $kernel = $app->make(Kernel::class);
            $kernel->prependMiddleware(ProtectedMiddleware::class);
        });

        $this->browse(function ($browser) {
            $browser->visit('js/middleware.html')
                ->waitForText('passes: 2')
                ->assertSee('passes: 2');
        });
    }

    public function testFetchInvalid()
    {
        $this->tweakApplication(function ($app) {
            $app['config']->set('cors.allowed_origins', ['http://example.org']);
        });

        File::delete(__DIR__ .'/Browser/invalid.flag');

        $this->browse(function ($browser) {
            $browser->visit('js/invalid.html')
                ->waitForText('passes: 4')
                ->assertSee('passes: 4');
        });

        $this->assertFalse(File::exists(__DIR__ .'/Browser/invalid.flag'));
    }

    public function testFetchCredentials()
    {
        $this->tweakApplication(function ($app) {
            $app['config']->set('cors.supports_credentials', true);
            $app['config']->set('cors.allowed_headers', ['X-Requested-With', 'Authorization']);
            $app['config']->set('cors.allowed_methods', ['GET', 'POST', 'PUT']);
        });

        File::delete(__DIR__ .'/Browser/invalid.flag');

        $this->browse(function ($browser) {
            $browser->visit('js/credentials.html')
                ->waitForText('passes: 6')
                ->assertSee('passes: 6');
        });

        $this->assertFalse(File::exists(__DIR__ .'/Browser/invalid.flag'));
    }


    public function testFetchAxios()
    {
        $this->tweakApplication(function ($app) {
            $app['config']->set('cors.supports_credentials', false);
            $app['config']->set('cors.allowed_origins', ['*']);
            $app['config']->set('cors.allowed_headers', ['*']);
            $app['config']->set('cors.allowed_methods', ['*']);
        });

        $this->browse(function ($browser) {
            $browser->visit('js/axios.html')
                ->waitForText('passes: 3', 30)
                ->assertSee('passes: 3');
        });
    }
}

class ProtectedMiddleware {
    public function handle($request, \Closure $next)
    {
        if ($request->is('protected')) {
            return response()->json(['message'=> 'Unauthorized'], 401);
        }
        return $next($request);
    }
}