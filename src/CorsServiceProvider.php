<?php

namespace Fruitcake\Cors;

use SelcukCukur\CorsExpression\CorsExpressionService;
use Illuminate\Foundation\Application as LaravelApplication;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use Laravel\Lumen\Application as LumenApplication;

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

        $this->app->singleton(CorsExpressionService::class, function ($app) {
            return new CorsExpressionService($this->corsOptions(), $app);
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

    /**
     * Get options for CorsService
     *
     * @return array
     */
    protected function corsOptions()
    {
        $config = $this->app['config']->get('cors');

        if ($config['exposed_headers'] && !is_array($config['exposed_headers'])) {
            throw new \RuntimeException('CORS config `exposed_headers` should be `false` or an array');
        }

        foreach (['allowed_origins', 'allowed_origins_patterns',  'allowed_headers', 'allowed_methods'] as $key) {
            if (!is_array($config[$key])) {
                throw new \RuntimeException('CORS config `' . $key . '` should be an array');
            }
        }

        // Convert case to supported options
        $options = [
            'SupportsCredentials' => $config['supports_credentials'],
            'AllowedOrigins' => $config['allowed_origins'],
            'AllowedOriginsPatterns' => $config['allowed_origins_patterns'],
            'AllowedHeaders' => $config['allowed_headers'],
            'AllowedMethods' => $config['allowed_methods'],
            'ExposedHeaders' => $config['exposed_headers'],
            'AccesControlMaxAge' => $config['max_age'],
        ];

        // Transform wildcard pattern
        foreach ($options['AllowedOrigins'] as $origin) {
            if (strpos($origin, '*') !== false) {
                $options['AllowedOriginsPatterns'][] = $this->convertWildcardToPattern($origin);
            }
        }

        return $options;
    }

    /**
     * Create a pattern for a wildcard, based on Str::is() from Laravel
     *
     * @see https://github.com/laravel/framework/blob/5.5/src/Illuminate/Support/Str.php
     * @param string $pattern
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
