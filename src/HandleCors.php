<?php

namespace Fruitcake\Cors;

use Closure;
use Asm89\Stack\CorsService;
use Illuminate\Http\Request;
use Illuminate\Contracts\Container\Container;
use Symfony\Component\HttpFoundation\Response;

class HandleCors
{
    /** @var CorsService $cors */
    protected $cors;

    /** @var \Illuminate\Contracts\Container\Container $container */
    protected $container;
    
    public function __construct(CorsService $cors, Container $container)
    {
        $this->cors = $cors;
        $this->container = $container;
    }

    /**
     * Handle an incoming request. Based on Asm89\Stack\Cors by asm89
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return Response
     */
    public function handle($request, Closure $next)
    {
        // Check if we're dealing with CORS and if we should handle it
        if (! $this->shouldRun($request)) {
            return $next($request);
        }

        if ($request->getMethod() === 'OPTIONS') {
            // For Preflight, return the Preflight response
            if ($this->cors->isPreflightRequest($request)) {
                $response = $this->cors->handlePreflightRequest($request);
            } else {
                // Otherwise only Vary the response for Access-Control-Request-Method
                $response = $next($request);
            }

            $this->cors->varyHeader($response, 'Access-Control-Request-Method');

            return $response;
        }

        // Handle the request
        $response = $next($request);

        // Add the CORS headers to the Response
        return $this->cors->addActualRequestHeaders($response, $request);
    }

    /**
     * Determine if the request has a URI that should pass through the CORS flow.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    protected function shouldRun(Request $request): bool
    {
        return $this->isMatchingPath($request);
    }

    /**
     * The the path from the config, to see if the CORS Service should run
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    protected function isMatchingPath(Request $request): bool
    {
        // Get the paths from the config or the middleware
        $paths = $this->container['config']->get('cors.paths', []);

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
}
