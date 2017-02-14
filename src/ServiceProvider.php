<?php namespace Barryvdh\Cors;

use Barryvdh\Cors\Stack\CorsService;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;
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
    }

    /**
     * Add the cors config
     *
     */
    public function boot()
    {
        $this->publishes([$this->configPath() => config_path('cors.php')]);
    }

    protected function configPath()
    {
        return __DIR__ . '/../config/cors.php';
    }
}
