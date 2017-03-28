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
            if (! $this->isLumen()) {

                $route = $this->findRouteForMethod($request);

                if (! $route) {
                    return new Response('Not found.', 404);
                }

                if (! $this->hasMatchingCorsRoute($route)) {
                    return new Response('Not allowed.', 403);
                }
            }

            return $this->cors->handlePreflightRequest($request);
        }

        return $next($request);
    }

    /**
     * Find the current route for the requested method. Only possible on Laravel (not Lumen)
     *
     * @param  \Illuminate\Http\Request $request
     * @return Route|null
     */
    protected function findRouteForMethod($request)
    {
        $method = $request->header('Access-Control-Request-Method');

        /** @var Router $router */
        $router = app(Router::class);

        $routes = $router->getRoutes()->get($method);

        return $this->matchAgainstRoutes($routes, $request);
    }

    /**
     * Verify the matching ROUTE is CORS-enabled.
     *
     * @param  Route $route
     * @return boolean
     */
    protected function hasMatchingCorsRoute($route)
    {
        return in_array(HandleCors::class, $route->middleware());
    }

    /**
     * @param array $routes
     * @param $request
     * @return Route|null
     */
    protected function matchAgainstRoutes(array $routes, $request)
    {
        return Arr::first($routes, function ($value) use ($request) {
            return $value->matches($request, false);
        });
    }

    /**
     * @return bool
     */
    protected function isLumen()
    {
        return str_contains(app()->version(), 'Lumen');
    }
}
