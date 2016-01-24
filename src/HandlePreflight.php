<?php namespace Barryvdh\Cors;

use Closure;
use Asm89\Stack\CorsService;
use Illuminate\Routing\Router;
use Illuminate\Contracts\Http\Kernel;

class HandlePreflight
{
	/**
	 * @param CorsService $cors
	 */
	public function __construct(CorsService $cors, Router $router, Kernel $kernel)
	{
		$this->cors = $cors;
		$this->router = $router;
		$this->kernel = $kernel;
	}

	/**
	 * Handle an incoming OPTIONS request.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \Closure  $next
	 * @return mixed
	 */
	public function handle($request, Closure $next)
	{
		$response = $next($request);

		if ($this->cors->isPreflightRequest($request) && $this->hasMatchingCorsRoute($request)) {
			$preflight = $this->cors->handlePreflightRequest($request);
			$response->headers->add($preflight->headers->all());
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
		// Check if CORS is added in a global middleware
		if ($this->kernel->hasMiddleware(HandleCors::class)) {
			return true;
		}

		// Check if CORS is added as a route middleware
		$request = clone $request;
		$request->setMethod($request->header('Access-Control-Request-Method'));

		try {
			$middleware = $this->router->getRoutes()->match($request)->middleware();
			return in_array('cors', $middleware);
		} catch (\Exception $e){
			return false;
		}
	}
}
