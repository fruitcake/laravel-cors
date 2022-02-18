<?php

namespace Fruitcake\Cors;

use Fruitcake\Cors\CorsService;
use Illuminate\Foundation\Application as LaravelApplication;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use Laravel\Lumen\Application as LumenApplication;
use Illuminate\Foundation\Http\Events\RequestHandled;

class CorsServiceProvider extends BaseServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom($this->configPath(), 'cors');

        $this->app->singleton(CorsService::class, function ($app) {
            return new CorsService($this->app['config']->get('cors'));
        });
    }

    /**
     * Register the config for publishing
     *
     */
    public function boot()
    {
        if ($this->app instanceof LaravelApplication && $this->app->runningInConsole()) {
            $this->publishes([$this->configPath() => config_path('cors.php')], 'cors');
        } elseif ($this->app instanceof LumenApplication) {
            $this->app->configure('cors');
        }

        // Add the headers on the Request Handled event as fallback in case of exceptions
        if (class_exists(RequestHandled::class) && $this->app->bound('events')) {
            $this->app->make('events')->listen(RequestHandled::class, function (RequestHandled $event) {
                $this->app->make(HandleCors::class)->onRequestHandled($event);
            });
        }
    }

    /**
     * Set the config path
     *
     * @return string
     */
    protected function configPath()
    {
        return __DIR__ . '/../config/cors.php';
    }
}
