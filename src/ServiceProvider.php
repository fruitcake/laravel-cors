<?php namespace Barryvdh\Cors;

use Asm89\Stack\CorsService;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use Illuminate\Support\Str;

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
        /** @var \Illuminate\Http\Request $request */
        $request = $this->app['request'];

        $this->mergeConfigFrom($this->configPath(), 'cors');

        $this->app->bind('Asm89\Stack\CorsService', function($app) use ($request) {
            return new CorsService($this->getOptions($request));
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

        $this->app['router']->middleware('cors', 'Barryvdh\Cors\HandleCors');

        if ($request->isMethod('OPTIONS')) {
            $kernel->prependMiddleware('Barryvdh\Cors\HandlePreflight');
        }
    }

    protected function configPath()
    {
        return __DIR__ . '/../config/cors.php';
    }


    /**
     * Find the options for the current request, based on the paths/hosts settings.
     *
     * @param Request $request
     * @return array
     */
    protected function getOptions(Request $request)
    {
        $defaults = $this->normalizeOptions($this->app['config']->get('cors'));
        $paths = $this->app['config']->get('cors.paths');
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
                unset($options['paths']);
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
}
