<?php namespace Barryvdh\Cors;

use Closure;
use Illuminate\Http\Response;
use Illuminate\Routing\Route;
use Illuminate\Routing\Router;
use Illuminate\Support\Arr;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class HandlePreflight
{
    /** @var CorsService $cors */
    protected $cors;

    public function __construct(CorsService $cors)
    {
        $this->cors = $cors;
    }

    /**
     * Handle an incoming Preflight request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if ($this->cors->isPreflightRequest($request)) {
            if (! $this->isLumen() && ! $this->hasMatchingCorsRoute($request)) {
                return new Response('Not allowed.', 403);
            }

            return $this->cors->handlePreflightRequest($request);
        }

        return $next($request);
    }

    /**
     * Verify the current OPTIONS request matches a CORS-enabled route. Only possible on Laravel (not Lumen)
     *
     * @param  \Illuminate\Http\Request $request
     * @return boolean
     */
    private function hasMatchingCorsRoute($request)
    {
        // Check if CORS is added as a route middleware
        $request = clone $request;
        $request->setMethod($request->header('Access-Control-Request-Method'));

        /** @var Router $router */
        $router = app(Router::class);
        try {
            $route = $router->getRoutes()->match($request);
        } catch (HttpException $e) {
            return false;
        }

        // change of method name in laravel 5.3
        if (method_exists($router, 'gatherRouteMiddleware')) {
            $middleware = $router->gatherRouteMiddleware($route);
        } else {
            $middleware = $router->gatherRouteMiddlewares($route);
        }

        return in_array(HandleCors::class, $middleware);
    }

    protected function isLumen()
    {
        return str_contains(app()->version(), 'Lumen');
    }

}
