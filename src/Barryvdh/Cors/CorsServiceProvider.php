<?php namespace Barryvdh\Cors;

use Illuminate\Support\ServiceProvider;
use Symfony\Component\HttpFoundation\Request;
/*
 * This file is based on the NelmioCorsBundle.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class CorsServiceProvider extends ServiceProvider {

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
        $this->package('barryvdh/laravel-cors');
        $this->app['laravel-cors.send'] = false;
        $this->app['laravel-cors.headers'] = array();
        $this->load($this->app['config']->get('laravel-cors::config'));


	}

    public function load(array $config){
        if (!$config['paths']) {
            return;
        }

        $defaults = array_merge(
            array(
                'allow_origin' => array(),
                'allow_credentials' => false,
                'allow_headers' => array(),
                'expose_headers' => array(),
                'allow_methods' => array(),
                'max_age' => 0,
            ),
            $config['defaults']
        );

        // normalize array('*') to true
        if (in_array('*', $defaults['allow_origin'])) {
            $defaults['allow_origin'] = true;
        }
        if (in_array('*', $defaults['allow_headers'])) {
            $defaults['allow_headers'] = true;
        } else {
            $defaults['allow_headers'] = array_map('strtolower', $defaults['allow_headers']);
        }
        $defaults['allow_methods'] = array_map('strtoupper', $defaults['allow_methods']);
        foreach ($config['paths'] as $path => $opts) {
            $opts = array_filter($opts);
            if (isset($opts['allow_origin']) && in_array('*', $opts['allow_origin'])) {
                $opts['allow_origin'] = true;
            }
            if (isset($opts['allow_headers']) && in_array('*', $opts['allow_headers'])) {
                $opts['allow_headers'] = true;
            } elseif (isset($opts['allow_headers'])) {
                $opts['allow_headers'] = array_map('strtolower', $opts['allow_headers']);
            }
            $opts['allow_methods'] = array_map('strtoupper', $opts['allow_methods']);

            $config['paths'][$path] = $opts;
        }


        $listener = new CorsListener($this->app, $config['paths'], $defaults);

        $this->app->before(function(Request $request) use ($listener){
               return $listener->checkRequest($request);
            });
        $this->app->after(function(Request $request, $response) use ($listener){
            $listener->modifyResponse($request, $response);
        });

    }

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return array('laravel-cors.send', 'laravel-cors.headers');
	}

}