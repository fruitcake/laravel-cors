<?php namespace Barryvdh\Cors;

use Closure;
use Asm89\Stack\CorsService;
use Illuminate\Contracts\Debug\ExceptionHandler;

class HandleCors
{
    /**
     * The Exception Handler
     *
     * @var ExceptionHandler
     */
    protected $exceptionHandler;
    
    /**
     * The CORS service
     *
     * @var CorsService
     */
    protected $cors;

	/**
	 * @param CorsService $cors
	 */
	public function __construct(CorsService $cors, ExceptionHandler $exceptionHandler)
	{
		$this->cors = $cors;
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
		if ($this->isSameDomain($request) || ! $this->cors->isCorsRequest($request)) {
			return $next($request);
		}

		if ( ! $this->cors->isActualRequestAllowed($request)) {
			abort(403);
		}

		try {
		    /** @var \Illuminate\Http\Response $response */
		    $response = $next($request);
		} catch (\Exception $e) {
		    $this->exceptionHandler->report($e);
		    $response = $this->exceptionHandler->render($request, $e);
		}

		return $this->cors->addActualRequestHeaders($response, $request);
	}

	/**
	 * @param  \Illuminate\Http\Request  $request
	 * @return bool
	 */
	protected function isSameDomain($request)
	{
		return $request->headers->get('Origin') == $request->getSchemeAndHttpHost();
	}
}
