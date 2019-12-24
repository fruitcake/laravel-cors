<?php

namespace Fruitcake\Cors;

use Closure;
use Asm89\Stack\CorsService;
use Illuminate\Http\Request;
use Illuminate\Config\Repository;
use Symfony\Component\HttpFoundation\Response;

class HandleCors
{
    /**
     * You can enable CORS for 1 or multiple paths.
     * Example: ['api/*']
     *
     * @var array
     */
    protected $paths;

    /** @var CorsService $cors */
    protected $cors;

    /** @var \Illuminate\Contracts\Config\Repository */
    protected $config;

    public function __construct(CorsService $cors, Repository $config)
    {
        $this->cors = $cors;
        $this->config = $config;
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

        // For Preflight, return the Preflight response
        if ($this->cors->isPreflightRequest($request)) {
            return $this->cors->handlePreflightRequest($request);
        }

        // If the request is not allowed, return 403
        if (! $this->cors->isActualRequestAllowed($request)) {
            return new Response('Not allowed in CORS policy.', 403);
        }

        // Handle the request
        $response = $next($request);

        // Add the CORS headers to the Response
        return $this->addHeaders($request, $response);
    }

    /**
     * Determine if the request has a URI that should pass through the CORS flow.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    protected function shouldRun($request)
    {
        // Check if this is an actual CORS request
        if (! $this->cors->isCorsRequest($request)) {
            return false;
        }

        // Get the paths from the config or the middleware
        $paths = $this->paths ?: $this->config->get('cors.paths', []);

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
     * Add the headers to the Response, if they don't exist yet.
     *
     * @param Request $request
     * @param Response $response
     * @return Response
     */
    protected function addHeaders(Request $request, Response $response)
    {
        if (! $response->headers->has('Access-Control-Allow-Origin')) {
            $response = $this->cors->addActualRequestHeaders($response, $request);
        }

        return $response;
    }
}
