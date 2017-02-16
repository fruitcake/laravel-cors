<?php namespace Barryvdh\Cors;

use Closure;
use Illuminate\Http\Response;
use Illuminate\Routing\Route;
use Illuminate\Routing\Router;

class HandlePreflight
{

	/**
	 * Handle an incoming request. Based on Asm89\Stack\Cors by asm89
	 * @see https://github.com/asm89/stack-cors/blob/master/src/Asm89/Stack/Cors.php
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \Closure  $next
	 * @return mixed
	 */
	public function handle($request, Closure $next)
	{
	    $cors = new CorsService(config('cors', []));

        /** @var \Illuminate\Http\Response $response */
        $response = $next($request);

        if ($cors->isPreflightRequest($request)) {
            if ($this->hasMatchingCorsRoute($request)) {
                return $cors->handlePreflightRequest($request);
            } else {
                return new Response('Not allowed.', 403);
            }
        }

        return $response;
	}

    /**
     * Verify the current OPTIONS request matches a CORS-enabled route
     *
     * @param  \Illuminate\Http\Request  $request
     * @return boolean
     */
    private function hasMatchingCorsRoute($request)
    {
        $method = $request->header('Access-Control-Request-Method');

        /** @var Route[] $routes */
        $routes = app(Router::class)->getRoutes()->get($method);

        foreach ($routes as $route) {
            if ($route->matches($request, false)) {
                return in_array(HandleCors::class, $route->middleware());
            }
        }

        return false;
    }

}
