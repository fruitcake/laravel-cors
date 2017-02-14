<?php namespace Barryvdh\Cors;

use Closure;
use Barryvdh\Cors\Stack\CorsService;
use Illuminate\Http\Response;

class HandleCors
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

        if ($request->isMethod('OPTIONS') && $cors->isPreflightRequest($request)) {
            return $cors->handlePreflightRequest($request);
        }

		if (! $cors->isCorsRequest($request)) {
			return $next($request);
		}

		if ( ! $cors->isActualRequestAllowed($request)) {
			return new Response('Not allowed.', 403);
		}

		/** @var \Illuminate\Http\Response $response */
		$response = $next($request);

		return $cors->addActualRequestHeaders($response, $request);
	}

}
