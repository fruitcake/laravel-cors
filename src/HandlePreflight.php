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

        // Find route aliases
        $aliases = array_keys($router->getMiddleware(), HandleCors::class, true);

        // Check for aliases and the actual class
        return !empty(array_intersect($aliases + [HandleCors::class], $route->middleware()));
    }

    protected function isLumen()
    {
        return str_contains(app()->version(), 'Lumen');
    }

}
