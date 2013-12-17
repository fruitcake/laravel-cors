<?php

/*
 * This file is based on the NelmioCorsBundle.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Barryvdh\Cors;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Foundation\Application;

/**
 * Adds CORS headers and handles pre-flight requests
 *
 * @author Jordi Boggiano <j.boggiano@seld.be>
 */
class CorsListener
{
    /**
     * Simple headers as defined in the spec should always be accepted
     */
    protected static $simpleHeaders = array(
        'accept',
        'accept-language',
        'content-language',
        'origin',
    );

    protected $app;
    protected $paths;
    protected $defaults;
    protected $runAfter = false;

    public function __construct(Application $app, array $paths, array $defaults = array())
    {
        $this->app = $app;
        $this->paths = $paths;
        $this->defaults = $defaults;
    }

    public function checkRequest(Request $request)
    {
        // skip if not a CORS request
        if (!$request->headers->has('Origin')) {
            return;
        }

        $currentPath = $request->getPathInfo() ?: '/';

        foreach ($this->paths as $path => $options) {
            if (preg_match('{'.$path.'}i', $currentPath)) {
                $options = array_merge($this->defaults, $options);

                // perform preflight checks
                if ('OPTIONS' === $request->getMethod()) {
                    return $this->getPreflightResponse($request, $options);
                }

                if (!$this->checkOrigin($request, $options)) {
                    return new Response('', 403, array('Access-Control-Allow-Origin' => 'null'));
                }

                $this->runAfter = true;

                // Save response headers
                $headers = array();
                $headers['Access-Control-Allow-Origin'] =  $request->headers->get('Origin');
                if ($options['allow_credentials']) {
                    $headers['Access-Control-Allow-Credentials'] = 'true';
                }
                if ($options['expose_headers']) {
                    $headers['Access-Control-Expose-Headers'] = strtolower(implode(', ', $options['expose_headers']));
                }
                $this->app['laravel-cors.send'] = true;
                $this->app['laravel-cors.headers'] = $headers;

                return;
            }
        }
    }

    public function modifyResponse(Request $request, Response $response)
    {
        if(!$this->runAfter){
            return $response;
        }
        // add CORS response headers
        $response->headers->add($this->app['laravel-cors.headers']);
        return $response;
    }

    protected function getPreflightResponse(Request $request, array $options)
    {
        $response = new Response();

        if ($options['allow_credentials']) {
            $response->headers->set('Access-Control-Allow-Credentials', 'true');
        }
        if ($options['allow_methods']) {
            $response->headers->set('Access-Control-Allow-Methods', strtoupper(implode(', ', $options['allow_methods'])));
        }
        if ($options['allow_headers']) {
            $headers = $options['allow_headers'] === true
                ? $request->headers->get('Access-Control-Request-Headers')
                : implode(', ', $options['allow_headers']);
            $response->headers->set('Access-Control-Allow-Headers', $headers);
        }
        if ($options['max_age']) {
            $response->headers->set('Access-Control-Max-Age', $options['max_age']);
        }

        if (!$this->checkOrigin($request, $options)) {
            $response->headers->set('Access-Control-Allow-Origin', 'null');
            return $response;
        }

        $response->headers->set('Access-Control-Allow-Origin', $request->headers->get('Origin'));

        // check request method
        if (!in_array($request->headers->get('Access-Control-Request-Method'), $options['allow_methods'], true)) {
            $response->setStatusCode(405);
            return $response;
        }

        // check request headers
        $headers = $request->headers->get('Access-Control-Request-Headers');
        if ($options['allow_headers'] !== true && $headers) {
            $headers = trim(strtolower($headers));
            foreach (preg_split('{, *}', $headers) as $header) {
                if (in_array($header, self::$simpleHeaders, true)) {
                    continue;
                }
                if (!in_array($header, $options['allow_headers'], true)) {
                    $response->setStatusCode(400);
                    $response->setContent('Unauthorized header '.$header);
                    break;
                }
            }
        }

        return $response;
    }

    protected function checkOrigin(Request $request, array $options)
    {
        // check origin
        $origin = $request->headers->get('Origin');
        if ($options['allow_origin'] === true || in_array($origin, $options['allow_origin'])) {
            return true;
        }

        return false;
    }
}
