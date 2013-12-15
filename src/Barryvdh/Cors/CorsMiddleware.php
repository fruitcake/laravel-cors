<?php namespace Barryvdh\Cors;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class CorsMiddleware implements HttpKernelInterface {

    /** @var \Symfony\Component\HttpKernel\HttpKernelInterface $app */
    protected $app;
    /** @var \Barryvdh\Cors\CorsListener $listener */
    protected $listener;

    /**
     * Create a new CORS middleware
     *
     * @param \Symfony\Component\HttpKernel\HttpKernelInterface $app
     * @param CorsListener $listener
     */
    public function __construct(HttpKernelInterface $app, CorsListener $listener)
    {
        $this->app = $app;
        $this->listener = $listener;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(Request $request, $type = self::MASTER_REQUEST, $catch = true)
    {
        $this->listener->checkRequest($request);
        $response = $this->app->handle($request, $type, $catch);
        return $this->listener->modifyResponse($request, $response);
    }
}
