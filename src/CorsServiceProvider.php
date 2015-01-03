<?php namespace Barryvdh\Cors;

use Asm89\Stack\CorsService;
use Illuminate\Support\Str;
use Illuminate\Support\ServiceProvider;
use Illuminate\Http\Request;

/*
 * This file is based on the NelmioCorsBundle.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class CorsServiceProvider extends ServiceProvider
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
        /** @var \Illuminate\Http\Request $request */
        $request = $this->app['request'];

         // Load the config
        $config = require __DIR__ . '/config/config.php';
        $this->app['config']->set('laravel-cors', $config);

        $this->app->bind('Asm89\Stack\CorsService', function() use($request){
            return new CorsService($this->getOptions($request));
        });

    }

    /**
     * Find the options for the current request, based on the paths/hosts settings.
     *
     * @param Request $request
     * @return array
     */
    protected function getOptions(Request $request)
    {
        $defaults = $this->normalizeOptions($this->app['config']->get('laravel-cors.defaults', array()));
        $paths = $this->app['config']->get('laravel-cors.paths', array());

        $uri = $request->getPathInfo() ? : '/';
        $host = $request->getHost();

        foreach ($paths as $pathPattern => $options) {
            //Check for legacy patterns
            if ($request->is($pathPattern) || (Str::startsWith($pathPattern, '^') && preg_match('{' . $pathPattern . '}i', $uri))) {
                $options = array_merge($defaults, $this->normalizeOptions($options));

                // skip if the host is not matching
                if (isset($options['hosts']) && count($options['hosts']) > 0) {
                    foreach ($options['hosts'] as $hostPattern) {
                        if (Str::is($hostPattern, $host)) {
                            return $options;
                        }
                    }
                    continue;
                }

                return $options;
            }
        }

        return $defaults;

    }

    /**
     * Normalize the options for backwards compatibility.
     *
     * @param array $options
     * @return array
     */
    protected function normalizeOptions($options)
    {
        $replaces = array(
            'allow_credentials' => 'supportsCredentials',
            'allow_origin' => 'allowedOrigins',
            'allow_headers' => 'allowedHeaders',
            'allow_methods' => 'allowedMethods',
            'expose_headers' => 'exposedHeaders',
            'max_age' => 'maxAge',
        );
        foreach ($options as $k => $v) {
            if (isset($replaces[$k])) {
                $options[$replaces[$k]] = $v;
                unset($options[$k]);
            }
        }
        return $options;
    }

    /**
     * Compare the current Laravel version
     *
     * @param $version
     * @param null $operator
     * @return mixed
     */
    protected function checkVersion($version, $operator = null)
    {
        $app = $this->app;
        return version_compare($app::VERSION, $version, $operator);
    }

}
