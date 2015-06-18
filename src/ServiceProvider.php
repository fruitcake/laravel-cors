<?php namespace Barryvdh\Cors;

use Asm89\Stack\CorsService;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;

class ServiceProvider extends BaseServiceProvider
{

    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom($this->configPath(), 'cors');

        $this->app->bind('Asm89\Stack\CorsService', function($app){
            return new CorsService($app['config']->get('cors'));
        });
    }

    /**
     * Add the Cors middleware to the router
     */
    public function boot()
    {
        $this->publishes([$this->configPath() => config_path('cors.php')]);

        $this->app['router']->middleware('cors', 'Barryvdh\Cors\HandleCors');
    }

    protected function configPath()
    {
        return __DIR__ . '/../config/cors.php';
    }
}
