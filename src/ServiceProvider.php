<?php namespace Barryvdh\Cors;

use Asm89\Stack\CorsService;
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
            $options = $app['config']->get('cors');

            if (isset($options['allowedOrigins'])) {
                foreach ($options['allowedOrigins'] as $origin) {
                    if (strpos($origin, '*') !== false) {
                        $options['allowedOriginsPatterns'][] = $this->convertWildcardToPattern($origin);
                    }
                }
            }

            return new CorsService($options);
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

    /**
     * Create a pattern for a wildcard, based on Str::is() from Laravel
     *
     * @see https://github.com/laravel/framework/blob/5.5/src/Illuminate/Support/Str.php
     * @param $pattern
     * @return string
     */
    protected function convertWildcardToPattern($pattern)
    {
        $pattern = preg_quote($pattern, '#');

        // Asterisks are translated into zero-or-more regular expression wildcards
        // to make it convenient to check if the strings starts with the given
        // pattern such as "library/*", making any string check convenient.
        $pattern = str_replace('\*', '.*', $pattern);

        return '#^'.$pattern.'\z#u';
    }
}
