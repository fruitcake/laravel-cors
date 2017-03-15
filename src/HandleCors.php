<?php namespace Barryvdh\Cors;

use Closure;
use Exception;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Foundation\Http\Events\RequestHandled;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
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

		// Add the headers on the Request Handled event as fallback in case of exceptions
		if (class_exists(RequestHandled::class)) {
            $this->events->listen(RequestHandled::class, function(RequestHandled $event) use ($cors) {
                $this->addHeaders($cors, $event->request, $event->response);
            });
        } else {
            $this->events->listen('kernel.handled', function(Request $request, Response $response) use ($cors) {
                $this->addHeaders($cors, $request, $response);
            });
        }

        $response = $next($request);

        return $this->addHeaders($cors, $request, $response);
    }

    /**
     * @param CorsService $cors
     * @param Request $request
     * @param Response $response
     * @return Response
     */
	protected function addHeaders(CorsService $cors, Request $request, Response $response)
    {
        // Prevent double checking
        if ( ! $response->headers->has('Access-Control-Allow-Origin')) {
            $response = $cors->addActualRequestHeaders($response, $request);
        }

        return $response;
    }
}
