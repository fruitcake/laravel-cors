<?php namespace Barryvdh\Cors;

use Asm89\Stack\CorsService;
use Closure;
use Illuminate\Http\Response;
use Illuminate\Routing\Route;
use Illuminate\Routing\Router;
use Illuminate\Support\Arr;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class HandlePreflight
{
    /** @var CorsService $cors */
    protected $cors;

    public function __construct(CorsService $cors)
    {
        $this->cors = $cors;
    }

    /**
     * Handle an incoming Preflight request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if ($this->cors->isPreflightRequest($request)) {
            return $this->cors->handlePreflightRequest($request);
        }

        return $next($request);
    }
}
