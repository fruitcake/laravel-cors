<?php

namespace Barryvdh\Cors;

use Closure;
use Asm89\Stack\CorsService;
use Illuminate\Http\Request;
use Illuminate\Config\Repository;
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

    /** @var \Illuminate\Contracts\Config\Repository */
    protected $config;

    public function __construct(CorsService $cors, Repository $config)
    {
        $this->cors = $cors;
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
        if (! $this->cors->isCorsRequest($request)) {
            return false;
        }

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
