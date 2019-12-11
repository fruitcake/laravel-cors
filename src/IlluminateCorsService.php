<?php namespace Barryvdh\Cors;

use Asm89\Stack\CorsService;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\Response as IlluminateResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class IlluminateCorsService extends CorsService
{
    /** @var ResponseFactory $responseFactory */
    protected $responseFactory;

    public function __construct(ResponseFactory $responseFactory, array $options = array())
    {
        parent::__construct($options);
        $this->responseFactory = $responseFactory;
    }

    public function handlePreflightRequest(Request $request)
    {
        $result = parent::handlePreflightRequest($request);
        if ($result instanceof SymfonyResponse) {
            $result = $this->convertResponse($result);
        }
        return $result;
    }

    /**
     * Converts symfony response to illuminate response.
     * @param SymfonyResponse $response Response returned by extended CORS service.
     * @return IlluminateResponse Illuminate response.
     */
    private function convertResponse(SymfonyResponse $response)
    {
        return $this->responseFactory->make(
            $response->getContent(),
            $response->getStatusCode(),
            $response->headers->all()
        );
    }
}
