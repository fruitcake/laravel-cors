<?php namespace Barryvdh\Cors;

use Asm89\Stack\CorsService;
use Illuminate\Http\Request;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;

class LumenServiceProvider extends BaseServiceProvider
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
     */
    public function boot()
    {
        $this->app->routeMiddleware(['cors' => HandleCors::class]);

        /** @var  \Illuminate\Http\Request $request */
        $request = app(Request::class);

        if ($request->isMethod('OPTIONS')) {

            $this->app->options($request->path(), function()
            {
                return response('OK', 200);
            });

            $this->app->middleware([HandlePreflightSimple::class]);
        }
    }

    protected function configPath()
    {
        return __DIR__ . '/../config/cors.php';
    }
}
