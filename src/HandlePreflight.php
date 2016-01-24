<?php namespace Barryvdh\Cors;

use Closure;
use Asm89\Stack\CorsService;
use Illuminate\Routing\Router;

class HandlePreflight
{
	/**
	 * @param CorsService $cors
	 */
	public function __construct(CorsService $cors, Router $router)
	{
		$this->cors = $cors;
		$this->router = $router;
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
	 * @param  \Illuminate\Http\Request  $request
	 * @return boolean
	 */
	private function hasMatchingCorsRoute($request)
	{
		$request = clone $request;
		$request->setMethod($request->header('Access-Control-Request-Method'));
		return in_array('cors', $this->router->getRoutes()->match($request)->middleware());
	}
}
