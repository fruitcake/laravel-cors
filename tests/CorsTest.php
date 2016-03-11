<?php

use PHPUnit_Framework_TestCase;
use Barryvdh\Cors\Stack\Cors;
use Barryvdh\Cors\Stack\CorsService;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CorsTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_does_not_modify_on_a_request_without_origin()
    {
        $app                = $this->createStackedApp();
        $unmodifiedResponse = new Response();

        $response = $app->handle(new Request());

        $this->assertEquals($unmodifiedResponse->headers, $response->headers);
    }

    /**
     * @test
     */
    public function it_does_not_modify_on_a_request_with_same_origin()
    {
        $app = $this->createStackedApp(array('allowedOrigins' => array('*')));
        $unmodifiedResponse = new Response();

        $request  = new Request();
        $request->headers->set('Host', 'foo.com');
        $request->headers->set('Origin', 'http://foo.com');
        $response = $app->handle($request);
        $unmodifiedResponse->headers->date = '';
        $response->headers->date = '';

        $this->assertEquals($unmodifiedResponse->headers, $response->headers);
    }

    /**
     * @test
     */
    public function it_returns_403_on_valid_actual_request_with_origin_not_allowed()
    {
        $app      = $this->createStackedApp(array('allowedOrigins' => array('notlocalhost')));
        $request  = $this->createValidActualRequest();

        $response = $app->handle($request);

        $this->assertEquals(403, $response->getStatusCode());
    }

    /**
     * @test
     */
    public function it_returns_allow_origin_header_on_valid_actual_request()
    {
        $app      = $this->createStackedApp();
        $request  = $this->createValidActualRequest();

        $response = $app->handle($request);

        $this->assertTrue($response->headers->has('Access-Control-Allow-Origin'));
        $this->assertEquals('localhost', $response->headers->get('Access-Control-Allow-Origin'));
    }

    /**
     * @test
     */
    public function it_returns_403_on_valid_actual_request_with_origin_regExp_not_allowed()
    {
        $app      = $this->createStackedApp(array('allowedOrigins' => array(''), 'allowedOriginsRegExp' => array('/notlocal.ost/i')));
        $request  = $this->createValidActualRequest();

        $response = $app->handle($request);

        $this->assertEquals(403, $response->getStatusCode());
    }

    /**
     * @test
     */
    public function it_returns_allow_origin_header_on_valid_regExp_actual_request()
    {
        $app      = $this->createStackedApp(array('allowedOrigins' => array(''), 'allowedOriginsRegExp' => array('/local.ost/i')));
        $request  = $this->createValidActualRequest();

        $response = $app->handle($request);

        $this->assertTrue($response->headers->has('Access-Control-Allow-Origin'));
        $this->assertEquals('localhost', $response->headers->get('Access-Control-Allow-Origin'));
    }

    /**
     * @test
     */
    public function it_returns_allow_origin_header_on_allow_all_origin_request()
    {
        $app      = $this->createStackedApp(array('allowedOrigins' => array('*')));
        $request  = new Request();
        $request->headers->set('Origin', 'http://localhost');

        $response = $app->handle($request);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertTrue($response->headers->has('Access-Control-Allow-Origin'));
        $this->assertEquals('http://localhost', $response->headers->get('Access-Control-Allow-Origin'));
    }

    /**
     * @test
     */
    public function it_returns_allow_headers_header_on_allow_all_headers_request()
    {
        $app     = $this->createStackedApp(array('allowedHeaders' => array('*')));
        $request = $this->createValidPreflightRequest();
        $request->headers->set('Access-Control-Request-Headers', 'Foo, BAR');

        $response = $app->handle($request);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('FOO, BAR', $response->headers->get('Access-Control-Allow-Headers'));
    }

    /**
     * @test
     */
    public function it_does_not_return_allow_origin_header_on_valid_actual_request_with_origin_not_allowed()
    {
        $app      = $this->createStackedApp(array('allowedOrigins' => array('notlocalhost')));
        $request  = $this->createValidActualRequest();

        $response = $app->handle($request);

        $this->assertFalse($response->headers->has('Access-Control-Allow-Origin'));
    }

    /**
     * @test
     */
    public function it_sets_allow_credentials_header_when_flag_is_set_on_valid_actual_request()
    {
        $app     = $this->createStackedApp(array('supportsCredentials' => true));
        $request = $this->createValidActualRequest();

        $response = $app->handle($request);

        $this->assertTrue($response->headers->has('Access-Control-Allow-Credentials'));
        $this->assertEquals('true', $response->headers->get('Access-Control-Allow-Credentials'));
    }

    /**
     * @test
     */
    public function it_does_not_set_allow_credentials_header_when_flag_is_not_set_on_valid_actual_request()
    {
        $app     = $this->createStackedApp();
        $request = $this->createValidActualRequest();

        $response = $app->handle($request);

        $this->assertFalse($response->headers->has('Access-Control-Allow-Credentials'));
    }

    /**
     * @test
     */
    public function it_sets_exposed_headers_when_configured_on_actual_request()
    {
        $app     = $this->createStackedApp(array('exposedHeaders' => array('x-exposed-header', 'x-another-exposed-header')));
        $request = $this->createValidActualRequest();

        $response = $app->handle($request);

        $this->assertTrue($response->headers->has('Access-Control-Expose-Headers'));
        $this->assertEquals('x-exposed-header, x-another-exposed-header', $response->headers->get('Access-Control-Expose-Headers'));
    }

    /**
     * @test
     * @see http://www.w3.org/TR/cors/index.html#resource-implementation
     */
    public function it_adds_a_vary_header()
    {
        $app      = $this->createStackedApp();
        $request  = $this->createValidActualRequest();

        $response = $app->handle($request);

        $this->assertTrue($response->headers->has('Vary'));
        $this->assertEquals('Origin', $response->headers->get('Vary'));
    }

    /**
     * @test
     * @see http://www.w3.org/TR/cors/index.html#resource-implementation
     */
    public function it_appends_an_existing_vary_header()
    {
        $app      = $this->createStackedApp(array(), array('Vary' => 'Content-Type'));
        $request  = $this->createValidActualRequest();

        $response = $app->handle($request);

        $this->assertTrue($response->headers->has('Vary'));
        $this->assertEquals('Content-Type, Origin', $response->headers->get('Vary'));
    }

    /**
     * @test
     */
    public function it_returns_access_control_headers_on_cors_request()
    {
        $app      = $this->createStackedApp();
        $request  = new Request();
        $request->headers->set('Origin', 'localhost');

        $response = $app->handle($request);

        $this->assertTrue($response->headers->has('Access-Control-Allow-Origin'));
        $this->assertEquals('localhost', $response->headers->get('Access-Control-Allow-Origin'));
    }

    /**
     * @test
     */
    public function it_returns_access_control_headers_on_valid_preflight_request()
    {
        $app     = $this->createStackedApp();
        $request = $this->createValidPreflightRequest();

        $response = $app->handle($request);

        $this->assertTrue($response->headers->has('Access-Control-Allow-Origin'));
        $this->assertEquals('localhost', $response->headers->get('Access-Control-Allow-Origin'));
    }

    /**
     * @test
     */
    public function it_returns_403_on_valid_preflight_request_with_origin_not_allowed()
    {
        $app     = $this->createStackedApp(array('allowedOrigins' => array('notlocalhost')));
        $request = $this->createValidPreflightRequest();

        $response = $app->handle($request);

        $this->assertEquals(403, $response->getStatusCode());
    }

    /**
     * @test
     */
    public function it_does_not_modify_request_with_origin_not_allowed()
    {
        $passedOptions = array(
          'allowedOrigins' => array('notlocalhost'),
        );

        $service  = new CorsService($passedOptions);
        $request  = $this->createValidActualRequest();
        $response = new Response();
        $service->addActualRequestHeaders($response, $request);

        $this->assertEquals($response, new Response());
    }

    /**
     * @test
     */
    public function it_returns_405_on_valid_preflight_request_with_method_not_allowed()
    {
        $app     = $this->createStackedApp(array('allowedMethods' => array('put')));
        $request = $this->createValidPreflightRequest();

        $response = $app->handle($request);

        $this->assertEquals(405, $response->getStatusCode());
    }

    /**
     * @test
     */
    public function it_allow_methods_on_valid_preflight_request()
    {
        $app     = $this->createStackedApp(array('allowedMethods' => array('get', 'put')));
        $request = $this->createValidPreflightRequest();

        $response = $app->handle($request);

        $this->assertTrue($response->headers->has('Access-Control-Allow-Methods'));
        // it will uppercase the methods
        $this->assertEquals('GET, PUT', $response->headers->get('Access-Control-Allow-Methods'));
    }

    /**
     * @test
     */
    public function it_returns_valid_preflight_request_with_allow_methods_all()
    {
        $app     = $this->createStackedApp(array('allowedMethods' => array('*')));
        $request = $this->createValidPreflightRequest();

        $response = $app->handle($request);

        $this->assertTrue($response->headers->has('Access-Control-Allow-Methods'));
        // it will return the Access-Control-Request-Method pass in the request
        $this->assertEquals('GET', $response->headers->get('Access-Control-Allow-Methods'));
    }

    /**
     * @test
     */
    public function it_returns_403_on_valid_preflight_request_with_one_of_the_requested_headers_not_allowed()
    {
        $app     = $this->createStackedApp();
        $request = $this->createValidPreflightRequest();
        $request->headers->set('Access-Control-Request-Headers', 'x-not-allowed-header');

        $response = $app->handle($request);

        $this->assertEquals(403, $response->getStatusCode());
    }

    /**
     * @test
     */
    public function it_returns_ok_on_valid_preflight_request_with_requested_headers_allowed()
    {
        $app            = $this->createStackedApp();
        $requestHeaders = 'X-Allowed-Header, x-other-allowed-header';
        $request        = $this->createValidPreflightRequest();
        $request->headers->set('Access-Control-Request-Headers', $requestHeaders);

        $response = $app->handle($request);

        $this->assertEquals(200, $response->getStatusCode());

        $this->assertTrue($response->headers->has('Access-Control-Allow-Headers'));
        // the response will have the "allowedHeaders" value passed to Cors rather than the request one
        $this->assertEquals('x-allowed-header, x-other-allowed-header', $response->headers->get('Access-Control-Allow-Headers'));
    }

    /**
     * @test
     */
    public function it_sets_allow_credentials_header_when_flag_is_set_on_valid_preflight_request()
    {
        $app     = $this->createStackedApp(array('supportsCredentials' => true));
        $request = $this->createValidPreflightRequest();

        $response = $app->handle($request);

        $this->assertTrue($response->headers->has('Access-Control-Allow-Credentials'));
        $this->assertEquals('true', $response->headers->get('Access-Control-Allow-Credentials'));
    }

    /**
     * @test
     */
    public function it_does_not_set_allow_credentials_header_when_flag_is_not_set_on_valid_preflight_request()
    {
        $app     = $this->createStackedApp();
        $request = $this->createValidPreflightRequest();

        $response = $app->handle($request);

        $this->assertFalse($response->headers->has('Access-Control-Allow-Credentials'));
    }

    /**
     * @test
     */
    public function it_sets_max_age_when_set()
    {
        $app     = $this->createStackedApp(array('maxAge' => 42));
        $request = $this->createValidPreflightRequest();

        $response = $app->handle($request);

        $this->assertTrue($response->headers->has('Access-Control-Max-Age'));
        $this->assertEquals(42, $response->headers->get('Access-Control-Max-Age'));
    }

    private function createValidActualRequest()
    {
        $request  = new Request();
        $request->headers->set('Origin', 'localhost');

        return $request;
    }

    private function createValidPreflightRequest()
    {
        $request  = new Request();
        $request->headers->set('Origin', 'localhost');
        $request->headers->set('Access-Control-Request-Method', 'get');
        $request->setMethod('OPTIONS');

        return $request;
    }

    private function createStackedApp(array $options = array(), array $responseHeaders = array())
    {
        $passedOptions = array_merge(array(
                'allowedHeaders'      => array('x-allowed-header', 'x-other-allowed-header'),
                'allowedMethods'      => array('delete', 'get', 'post', 'put'),
                'allowedOrigins'      => array('localhost'),
                'exposedHeaders'      => false,
                'maxAge'              => false,
                'supportsCredentials' => false,
            ),
            $options
        );

        return new Cors(new MockApp($responseHeaders), $passedOptions);
    }
}

class MockApp implements HttpKernelInterface
{
    private $responseHeaders;

    public function __construct(array $responseHeaders)
    {
        $this->responseHeaders = $responseHeaders;
    }

    public function handle(Request $request, $type = HttpKernelInterface::MASTER_REQUEST, $catch = true)
    {
        $response = new Response();

        $response->headers->add($this->responseHeaders);

        return $response;
    }
}
