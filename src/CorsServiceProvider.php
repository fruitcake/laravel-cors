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

        //Lumen users need to copy the config file over to /config themselves
        //and it needs to be pulled in with $this->app->configure().
        if (str_contains($this->app->version(), 'Lumen')) {
            $this->app->configure('cors');
        }

        //Laravel users can run artisan config:publish and config will be
        //automatically read in with directory scanning.
        else {
            $configPath = __DIR__ . '/../config/cors.php';
            $this->publishes([$configPath => config_path('cors.php')]);
        }

        $this->app->bind('Asm89\Stack\CorsService', function() use($request){
            return new CorsService($this->getOptions($request));
        });
    }
    
    /**
     * Boot the service provider
     *
     * @return void
     */
    public function boot()
    {
        $this->app['router']->before([$this, 'onBefore']); 
    }
    
    /**
     * Check the Request before running it through the router.
     * 
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response|null
     */
    public function onBefore($request)
    {
        $cors = $this->app['Asm89\Stack\CorsService'];
        
        if (
            ! $cors->isCorsRequest($request)
            || $request->headers->get('Origin') == $request->getSchemeAndHttpHost()
        ) {
            return;
        }
        
        if ($cors->isPreflightRequest($request)) {
            return $cors->handlePreflightRequest($request);
        }
        
        if ( ! $cors->isActualRequestAllowed($request)) {
            abort(403);
        }
        
        $this->app['events']->listen('kernel.handled', [$this, 'onHandled']);
    }
    
    /**
     * Modify the Response object to add the correct headers.
     * 
     * @param  \Illuminate\Http\Request $request
     * @param  \Illuminate\Http\Response $response
     * @return void
     */
    public function onHandled($request, $response)
    {
        $this->app['Asm89\Stack\CorsService']->addActualRequestHeaders($response, $request);
    }

    /**
     * Find the options for the current request, based on the paths/hosts settings.
     *
     * @param Request $request
     * @return array
     */
    protected function getOptions(Request $request)
    {
        $defaults = $this->app['config']->get('cors.defaults', []);
        $paths = $this->app['config']->get('cors.paths', []);

        $uri = $request->getPathInfo() ? : '/';
        $host = $request->getHost();

        foreach ($paths as $pathPattern => $options) {
            //Check for legacy patterns
            if ($request->is($pathPattern) || (Str::startsWith($pathPattern, '^') && preg_match('{' . $pathPattern . '}i', $uri))) {
                $options = array_merge($defaults, $options);

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
}
