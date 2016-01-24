<?php namespace Barryvdh\Cors;

use Closure;
use Asm89\Stack\CorsService;

class HandlePreflight
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
		if ($this->cors->isPreflightRequest($request)) {
			return $this->cors->handlePreflightRequest($request);
		}

		return $next($request);
	}
}
