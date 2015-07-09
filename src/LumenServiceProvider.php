<?php namespace Barryvdh\Cors;

use Asm89\Stack\CorsService;
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

        $this->app->bind('Asm89\Stack\CorsService', function($app){
            return new CorsService(config('cors'));
        });
    }

    /**
     * Add the Cors middleware to the router.
     *
     */
    public function boot()
    {
        $this->app->routeMiddleware(['cors' => 'Barryvdh\Cors\HandleCors']);

        /** @var  \Illuminate\Http\Request $request */
        $request = app('Illuminate\Http\Request');

        if ($request->isMethod('OPTIONS')) {

            $this->app->options($request->path(), function()
            {
                return response('OK', 200);
            });

            $this->app->middleware(['Barryvdh\Cors\HandlePreflight']);
        }
    }

    protected function configPath()
    {
        return __DIR__ . '/../config/cors.php';
    }
}
