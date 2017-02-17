<?php namespace Barryvdh\Cors;

use Closure;
use Exception;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Foundation\Http\Events\RequestHandled;
use Illuminate\Http\Response;
use Throwable;

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

        if (! $cors->isCorsRequest($request)) {
            return $next($request);
        }

        if ($cors->isPreflightRequest($request)) {
            return $cors->handlePreflightRequest($request);
        }

		if ( ! $cors->isActualRequestAllowed($request)) {
			return new Response('Not allowed.', 403);
		}

		try {
            /** @var \Illuminate\Http\Response $response */
            $response = $next($request);
        } catch (Exception $e) {
            $this->addKernelHandledEvent($cors);

            throw $e;
        } catch (Throwable $e) {
            $this->addKernelHandledEvent($cors);

            throw $e;
        }

        return $cors->addActualRequestHeaders($response, $request);
	}

	protected function addKernelHandledEvent(CorsService $cors)
    {
        $event = version_compare(app()->version(), '5.4', '<') ? 'kernel.handled' : RequestHandled::class;
        app(Dispatcher::class)->listen($event, function($request, $response) use ($cors) {
            $cors->addActualRequestHeaders($response, $request);
        });
    }

}
