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

        $this->app->singleton(CorsService::class, function($app){
            return new CorsService($app['config']->get('cors'));
        });
    }

    /**
     * Add the Cors middleware to the router.
     *
     * @param Kernel $kernel
     */
    public function boot(Request $request, Kernel $kernel)
    {
        $this->publishes([$this->configPath() => config_path('cors.php')]);

        $this->app['router']->aliasMiddleware('cors', HandleCors::class);

        if ($request->isMethod('OPTIONS')) {
            $kernel->prependMiddleware(HandlePreflight::class);
        }
    }

    protected function configPath()
    {
        return __DIR__ . '/../config/cors.php';
    }
}
