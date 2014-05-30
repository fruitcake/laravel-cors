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
        $request = $this->app['request'];
        if(!$request->headers->has('Origin')){
            return;
        }

        $this->package('barryvdh/laravel-cors');
        $defaults = $this->normalizeOptions($this->app['config']->get('laravel-cors::config.defaults', array()));
        $paths = $this->app['config']->get('laravel-cors::config.paths', array());

        $currentPath = $request->getPathInfo() ?: '/';
        foreach ($paths as $path => $options) {
            if (preg_match('{' . $path . '}i', $currentPath)) {
                $options = array_merge($defaults, $this->normalizeOptions($options));
                $this->app->middleware('Asm89\Stack\Cors', array($options));
                return;
            }
        }
	}

    protected function normalizeOptions($options){
        $replaces = array(
            'allow_credentials' => 'supportsCredentials',
            'allow_origin'=> 'allowedOrigins',
            'allow_headers' => 'allowedHeaders',
            'allow_methods'=> 'allowedMethods',
            'expose_headers'=> 'exposedHeaders',
            'max_age' => 'maxAge',
        );
        foreach($options as $k=>$v){
            if(isset($replaces[$k])){
                $options[$replaces[$k]] = $v;
                unset($options[$k]);
            }
        }
        return $options;

    }


}