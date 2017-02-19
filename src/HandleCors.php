<?php namespace Barryvdh\Cors;

use Closure;
use Exception;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Foundation\Http\Events\RequestHandled;
use Illuminate\Http\Response;
use Throwable;

class HandleCors
{
    /** @var Dispatcher $events */
    protected $events;

    public function __construct(Dispatcher $events)
    {
        $this->events = $events;
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

		// Add the headers on the Request Handled event
		if (class_exists(RequestHandled::class)) {
            $this->events->listen(RequestHandled::class, function(RequestHandled $event) use ($cors) {
                $cors->addActualRequestHeaders($event->response, $event->request);
            });
        } else {
            $this->events->listen('kernel.handled', function($request, $response) use ($cors) {
                $cors->addActualRequestHeaders($response, $request);
            });
        }

        return $next($request);
	}
}
