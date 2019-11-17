<?php namespace Barryvdh\Cors;

use Asm89\Stack\CorsService;
use Closure;
use Exception;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Foundation\Http\Events\RequestHandled;
use Illuminate\Http\Request;
use Illuminate\Http\Response as LaravelResponse;
use Symfony\Component\Debug\Exception\FatalThrowableError;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class HandleCors
{
    /** @var CorsService $cors */
    protected $cors;

    /** @var Dispatcher $events */
    protected $events;

    /** @var ExceptionHandler $exceptionHandler */
    protected $exceptionHandler;

    public function __construct(CorsService $cors, Dispatcher $events, ExceptionHandler $exceptionHandler)
    {
        $this->cors = $cors;
        $this->events = $events;
        $this->exceptionHandler = $exceptionHandler;
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
        if (! $this->cors->isCorsRequest($request)) {
            return $next($request);
        }

        if ($this->cors->isPreflightRequest($request)) {
            return $this->cors->handlePreflightRequest($request);
        }

        if (! $this->cors->isActualRequestAllowed($request)) {
            return new LaravelResponse('Not allowed in CORS policy.', 403);
        }

        // Add the headers on the Request Handled event as fallback in case of exceptions
        if (class_exists(RequestHandled::class)) {
            $this->events->listen(RequestHandled::class, function (RequestHandled $event) {
                $this->addHeaders($event->request, $event->response);
            });
        } else {
            $this->events->listen('kernel.handled', function (Request $request, Response $response) {
                $this->addHeaders($request, $response);
            });
        }

        try {
            $response = $next($request);
        } catch (Throwable $e) {
            if (! ($e instanceof Exception)) {
                $e = new FatalThrowableError($e);
            }

            $this->exceptionHandler->report($e);
            $response = $this->exceptionHandler->render($request, $e);
        }

        return $this->addHeaders($request, $response instanceof Responsable
            ? $response->toResponse($request)
            : $response);
    }

    /**
     * @param Request $request
     * @param Response $response
     * @return Response
     */
    protected function addHeaders(Request $request, Response $response)
    {
        // Prevent double checking
        if (! $response->headers->has('Access-Control-Allow-Origin')) {
            $response = $this->cors->addActualRequestHeaders($response, $request);
        }

        return $response;
    }
}
