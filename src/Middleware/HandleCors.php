<?php namespace Barryvdh\Cors\Middleware;

use Closure;
use Asm89\Stack\CorsService;
use Illuminate\Contracts\Routing\Middleware;
use Symfony\Component\HttpFoundation\Response;

class HandleCors implements Middleware {

	/**
	 * @param CorsService $cors
	 */
	public function __construct(CorsService $cors)
	{
		$this->cors = $cors;
	}

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
		if (
			! $this->cors->isCorsRequest($request)
			|| $request->headers->get('Origin') == $request->getSchemeAndHttpHost()
		) {
			return $next($request);
		}

		if ($this->cors->isPreflightRequest($request)) {
			return $this->cors->handlePreflightRequest($request);
		}

		if ( ! $this->cors->isActualRequestAllowed($request)) {
			return new Response('Not allowed.', 403);
		}

		$response = $next($request);

		return $this->cors->addActualRequestHeaders($response, $request);
	}
}
