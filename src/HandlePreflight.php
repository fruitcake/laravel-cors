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

    /**
     * Handle an incoming Preflight request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $cors = new CorsService(config('cors', []));

        if ($cors->isPreflightRequest($request)) {
            if ( ! $this->hasMatchingCorsRoute($request)) {
                return new Response('Not allowed.', 403);
            }

            return $cors->handlePreflightRequest($request);
        }

        return $next($request);
    }

    /**
     * Verify the current OPTIONS request matches a CORS-enabled route
     *
     * @param  \Illuminate\Http\Request $request
     * @return boolean
     */
    private function hasMatchingCorsRoute($request)
    {
        // Check if CORS is added as a route middleware
        $request = clone $request;
        $request->setMethod($request->header('Access-Control-Request-Method'));

        try {
            /** @var Route[] $routes */
            $route = app(Router::class)->getRoutes()->match($request);
        } catch (HttpException $e) {
            return false;
        }

        return in_array(HandleCors::class, $route->middleware());
    }

}
