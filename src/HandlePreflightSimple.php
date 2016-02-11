<?php namespace Barryvdh\Cors;

use Closure;
use Barryvdh\Cors\Stack\CorsService;

class HandlePreflightSimple
{
	/**
	 * @param CorsService $cors
	 */
	public function __construct(CorsService $cors)
	{
		$this->cors = $cors;
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

		if ($this->cors->isPreflightRequest($request)) {
			$preflight = $this->cors->handlePreflightRequest($request);
			$response->headers->add($preflight->headers->all());
		}

		return $response;
	}
}
