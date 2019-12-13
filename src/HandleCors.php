<?php

namespace Barryvdh\Cors;

use Asm89\Stack\CorsService;
use Closure;
use Illuminate\Config\Repository;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Foundation\Http\Events\RequestHandled;
use Illuminate\Http\Request;
use Illuminate\Http\Response as LaravelResponse;
use Symfony\Component\HttpFoundation\Response;

class HandleCors
{
    /**
     * The paths to add CORS headers to.
     *
     * @var array
     */
    protected $paths;

    /** @var CorsService $cors */
    protected $cors;

    /** @var Dispatcher $events */
    protected $events;

    /** @var \Illuminate\Contracts\Config\Repository */
    protected $config;

    public function __construct(CorsService $cors, Dispatcher $events, Repository $config)
    {
        $this->cors = $cors;
        $this->events = $events;
        $this->config = $config;
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
        $paths = $this->paths ?: $this->config->get('cors.paths', []);

        // When it doesn't match, skip the CORS flow
        if (! $this->isMatchingPath((array) $paths, $request)) {
            return $next($request);
        }

        // Check if we're dealing with CORS
        if (! $this->cors->isCorsRequest($request)) {
            return $next($request);
        }

        // For Preflight, return Preflight response
        if ($this->cors->isPreflightRequest($request)) {
            return $this->cors->handlePreflightRequest($request);
        }

        if (! $this->cors->isActualRequestAllowed($request)) {
            return new LaravelResponse('Not allowed in CORS policy.', 403);
        }

        $response = $next($request);

        return $this->addHeaders($request, $response instanceof Responsable
            ? $response->toResponse($request)
            : $response);
    }

    /**
     * Determine if the request has a URI that should pass through the CORS flow.
     *
     * @param  array $paths
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    protected function isMatchingPath(array $paths, $request)
    {
        foreach ($paths as $path) {
            if ($path !== '/') {
                $path = trim($path, '/');
            }

            if ($request->fullUrlIs($path) || $request->is($path)) {
                return true;
            }
        }

        return false;
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
