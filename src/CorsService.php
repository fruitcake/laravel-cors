<?php

namespace Barryvdh\Cors;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Fork of asm89/stack-cors
 */
class CorsService
{
    private $options;

    public function __construct(array $options = array())
    {
        $this->options = $this->normalizeOptions($options);
    }

    private function normalizeOptions(array $options = array())
    {
        // Set defaults from the config file
        $options += require __DIR__ .'/../config/cors.php';

        // normalize array('*') to true
        if ($options['allowedOrigins'] === true || in_array('*', $options['allowedOrigins'])) {
            $options['allowedOrigins'] = true;
        }

        if ($options['allowedHeaders'] === true || in_array('*', $options['allowedHeaders'])) {
            $options['allowedHeaders'] = true;
        }

        if ($options['allowedMethods'] === true || in_array('*', $options['allowedMethods'])) {
            $options['allowedMethods'] = true;
        } else {
            $options['allowedMethods'] = array_map('strtoupper', $options['allowedMethods']);
        }

        return $options;
    }

    public function isActualRequestAllowed(Request $request)
    {
        return $this->checkOrigin($request);
    }

    public function isCorsRequest(Request $request)
    {
        return $request->headers->has('Origin') && !$this->isSameHost($request);
    }

    public function isPreflightRequest(Request $request)
    {
        return $this->isCorsRequest($request)
            && $request->getMethod() === 'OPTIONS'
            && $request->headers->has('Access-Control-Request-Method');
    }

    public function addActualRequestHeaders(Response $response, Request $request)
    {
        if (!$this->checkOrigin($request)) {
            return $response;
        }

        $allowOrigin = $this->options['allowedOrigins'] === true && !$this->options['supportsCredentials']
            ? '*'
            : $request->headers->get('Origin');
        $response->headers->set('Access-Control-Allow-Origin', $allowOrigin);

        if (!$response->headers->has('Vary')) {
            $response->headers->set('Vary', 'Origin');
        } else {
            $response->headers->set('Vary', $response->headers->get('Vary') . ', Origin');
        }

        if ($this->options['supportsCredentials']) {
            $response->headers->set('Access-Control-Allow-Credentials', 'true');
        }

        if ($this->options['exposedHeaders']) {
            $response->headers->set('Access-Control-Expose-Headers', implode(', ', $this->options['exposedHeaders']));
        }

        return $response;
    }

    public function handlePreflightRequest(Request $request)
    {
        if (true !== $check = $this->checkPreflightRequestConditions($request)) {
            return $check;
        }

        return $this->buildPreflightCheckResponse($request);
    }

    private function buildPreflightCheckResponse(Request $request)
    {
        $response = new Response();

        if ($this->options['supportsCredentials']) {
            $response->headers->set('Access-Control-Allow-Credentials', 'true');
        }

        $allowOrigin = $this->options['allowedOrigins'] === true && !$this->options['supportsCredentials']
            ? '*'
            : $request->headers->get('Origin');
        $response->headers->set('Access-Control-Allow-Origin', $allowOrigin);

        if ($this->options['maxAge']) {
            $response->headers->set('Access-Control-Max-Age', $this->options['maxAge']);
        }

        $allowMethods = $this->options['allowedMethods'] === true
            ? strtoupper($request->headers->get('Access-Control-Request-Method'))
            : implode(', ', $this->options['allowedMethods']);
        $response->headers->set('Access-Control-Allow-Methods', $allowMethods);

        $allowHeaders = $this->options['allowedHeaders'] === true
            ? $request->headers->get('Access-Control-Request-Headers')
            : implode(', ', $this->options['allowedHeaders']);
        $response->headers->set('Access-Control-Allow-Headers', $allowHeaders);

        return $response;
    }

    private function checkPreflightRequestConditions(Request $request)
    {
        if (!$this->checkOrigin($request)) {
            return $this->createBadRequestResponse(403, 'Origin not allowed');
        }

        if (!$this->checkMethod($request)) {
            return $this->createBadRequestResponse(405, 'Method not allowed');
        }

        // if allowedHeaders has been set to true ('*' allow all flag) just skip this check
        if ($this->options['allowedHeaders'] !== true && $request->headers->has('Access-Control-Request-Headers')) {
            $allowedHeaders = array_map('strtolower', $this->options['allowedHeaders']);
            $headers = strtolower($request->headers->get('Access-Control-Request-Headers'));
            $requestHeaders = explode(',', $headers);

            foreach ($requestHeaders as $header) {
                if (!in_array(trim($header), $allowedHeaders)) {
                    return $this->createBadRequestResponse(403, 'Header not allowed');
                }
            }
        }

        return true;
    }

    private function createBadRequestResponse($code, $reason = '')
    {
        return new Response($reason, $code);
    }

    private function isSameHost(Request $request)
    {
        return $request->headers->get('Origin') === $request->getSchemeAndHttpHost();
    }

    private function checkOrigin(Request $request)
    {
        if ($this->options['allowedOrigins'] === true) {
            // allow all '*' flag
            return true;
        }
        $origin = $request->headers->get('Origin');

        foreach ($this->options['allowedOrigins'] as $allowedOrigin) {
            if (OriginMatcher::matches($allowedOrigin, $origin)) {
                return true;
            }
        }
        return false;
    }

    private function checkMethod(Request $request)
    {
        if ($this->options['allowedMethods'] === true) {
            // allow all '*' flag
            return true;
        }

        $requestMethod = strtoupper($request->headers->get('Access-Control-Request-Method'));
        return in_array($requestMethod, $this->options['allowedMethods']);
    }

}
