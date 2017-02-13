<?php

use Illuminate\Routing\Router;

abstract class TestCase extends Orchestra\Testbench\TestCase
{
    protected function getPackageProviders($app)
    {
        return [\Barryvdh\Cors\ServiceProvider::class];
    }

    /**
     * Define environment setup.
     *
     * @param  Illuminate\Foundation\Application  $app
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
        $router->options('web/ping', ['uses' => function () {
            return '';
        }]);

        $router->get('web/ping', ['as' => 'web.ping', 'uses' => function () {
            return 'pong';
        }]);

        $router->post('web/ping', ['uses' => function () {
            return 'PONG';
        }]);
    }

    /**
     * @param Router $router
     */
    protected function addApiRoutes($router)
    {
        $router->group(['middleware' => \Barryvdh\Cors\HandleCors::class], function() use($router) {

            $router->options('api/ping', ['uses' => function () {
                return '';
            }]);

            $router->get('api/ping', ['as' => 'api.ping', 'uses' => function () {
                return 'pong';
            }]);

            $router->post('api/ping', ['uses' => function () {
                return 'PONG';
            }]);

            $router->post('api/error', ['as' => 'api.ping', 'uses' => function () {
                abort(500);
            }]);

            $router->post('api/validation', ['as' => 'api.ping', 'uses' => function () {
                $validator = \Illuminate\Support\Facades\Validator::make([], []);
                throw new \Illuminate\Validation\ValidationException($validator);
            }]);
        });
    }

}