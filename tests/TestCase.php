<?php

namespace Barryvdh\Cors\Tests;

use Illuminate\Routing\Router;

abstract class TestCase extends \Orchestra\Testbench\TestCase
{
    use \Illuminate\Foundation\Validation\ValidatesRequests;

    protected function resolveApplicationConfiguration($app)
    {
        parent::resolveApplicationConfiguration($app);

        $app['config']['cors'] = [
            'supports_credentials' => false,
            'allowed_origins' => ['localhost'],
            'allowed_headers' => ['X-Custom-1', 'X-Custom-2'],
            'allowed_methods' => ['GET', 'POST'],
            'exposed_headers' => [],
            'max_age' => 0,
        ];
    }

    protected function getPackageProviders($app)
    {
        return [\Barryvdh\Cors\CorsServiceProvider::class];
    }

    /**
     * Define environment setup.
     *
     * @param  Illuminate\Foundation\Application $app
     *
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        $router = $app['router'];
        $this->addWebRoutes($router);
        $this->addApiRoutes($router);
    }

    /**
     * @param Router $router
     */
    protected function addWebRoutes(Router $router)
    {
        $router->get('web/ping', [
            'as' => 'web.ping',
            'uses' => function () {
                return 'pong';
            }
        ]);

        $router->post('web/ping', [
            'uses' => function () {
                return 'PONG';
            }
        ]);

        $router->post('web/error', [
            'uses' => function () {
                abort(500);
            }
        ]);

        $router->post('web/validation', [
            'uses' => function (\Illuminate\Http\Request $request) {
                $this->validate($request, [
                    'name' => 'required',
                ]);

                return 'ok';
            }
        ]);
    }

    /**
     * @param Router $router
     */
    protected function addApiRoutes($router)
    {
        $router->group(['middleware' => \Barryvdh\Cors\HandleCors::class], function () use ($router) {

            $router->get('api/ping', [
                'as' => 'api.ping',
                'uses' => function () {
                    return 'pong';
                }
            ]);

            $router->post('api/ping', [
                'uses' => function () {
                    return 'PONG';
                }
            ]);

            $router->put('api/ping', [
                'uses' => function () {
                    return 'PONG';
                }
            ]);

            $router->post('api/error', [
                'uses' => function () {
                    abort(500);
                }
            ]);

            $router->post('api/validation', [
                'uses' => function (\Illuminate\Http\Request $request) {
                    $this->validate($request, [
                        'name' => 'required',
                    ]);

                    return 'ok';
                }
            ]);
        });
    }
}
