<?php namespace Barryvdh\Cors;

use Illuminate\Contracts\Http\Kernel;
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

        $this->app->singleton(CorsService::class, function ($app) {
            return new CorsService($app['config']->get('cors'));
        });
    }

    /**
     * Add the Cors middleware to the router.
     *
     */
    public function boot()
    {
        // Lumen is limited, so always add the preflight.
        if ($this->isLumen()) {
            $this->app->middleware([HandlePreflight::class]);
        } else {
            $this->publishes([$this->configPath() => config_path('cors.php')]);

            /** @var \Illuminate\Foundation\Http\Kernel $kernel */
            $kernel = $this->app->make(Kernel::class);

            // When the HandleCors middleware is not attached globally, add the PreflightCheck
            if (! $kernel->hasMiddleware(HandleCors::class)) {
                $kernel->prependMiddleware(HandlePreflight::class);
            }
        }
    }

    protected function configPath()
    {
        return __DIR__ . '/../config/cors.php';
    }

    protected function isLumen()
    {
        return str_contains($this->app->version(), 'Lumen');
    }
}
