<?php

namespace Barryvdh\Cors;

use Asm89\Stack\CorsService;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use Illuminate\Support\Str;

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
            $config = $app['config']->get('cors');

            // Convert case to supported options
            $options = [
                'supportsCredentials' => $config['supports_credentials'],
                'allowedOrigins' => $config['allowed_origins'],
                'allowedOriginsPatterns' => $config['allowed_origins_patterns'],
                'allowedHeaders' => $config['allowed_headers'],
                'allowedMethods' => $config['allowed_methods'],
                'exposedHeaders' => $config['exposed_headers'],
                'maxAge' => $config['max_age'],
            ];

            // Transform wildcard pattern
            foreach ($options['allowedOrigins'] as $origin) {
                if (strpos($origin, '*') !== false) {
                    $options['allowedOriginsPatterns'][] = $this->convertWildcardToPattern($origin);
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

            return;
        }

        $this->publishes([$this->configPath() => config_path('cors.php')]);

        /** @var \Illuminate\Foundation\Http\Kernel $kernel */
        $kernel = $this->app->make(Kernel::class);

        // When the HandleCors middleware is not attached globally, add the PreflightCheck
        if (! $kernel->hasMiddleware(HandleCors::class)) {
            $kernel->prependMiddleware(HandlePreflight::class);
        }
    }

    protected function configPath()
    {
        return __DIR__ . '/../config/cors.php';
    }

    protected function isLumen()
    {
        return Str::contains($this->app->version(), 'Lumen');
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

        return '#^' . $pattern . '\z#u';
    }
}
