<?php

namespace Barryvdh\Cors\Tests;

class GlobalMiddlewareTest extends TestCase
{
    /**
     * Define environment setup.
     *
     * @param  Illuminate\Foundation\Application $app
     *
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        // Add the middleware
        $kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);
        $kernel->prependMiddleware(\Barryvdh\Cors\HandleCors::class);

        parent::getEnvironmentSetUp($app);
    }

    public function testShouldReturnNullOnHeaderAssessControlAllowOriginBecauseDontHaveHttpOriginOnRequest()
    {
        $crawler = $this->call('OPTIONS', 'api/ping', [], [], [], [
            'HTTP_ACCESS_CONTROL_REQUEST_METHOD' => 'POST',
        ]);

        $this->assertNull($crawler->headers->get('Access-Control-Allow-Origin'));
        $this->assertEquals(200, $crawler->getStatusCode());
    }

    public function testOptionsAllowOriginAllowed()
    {
        $crawler = $this->call('OPTIONS', 'api/ping', [], [], [], [
            'HTTP_ORIGIN' => 'localhost',
            'HTTP_ACCESS_CONTROL_REQUEST_METHOD' => 'POST',
        ]);

        $this->assertEquals('localhost', $crawler->headers->get('Access-Control-Allow-Origin'));
        $this->assertEquals(200, $crawler->getStatusCode());
    }

    public function testOptionsAllowOriginAllowedNonExistingRoute()
    {
        $crawler = $this->call('OPTIONS', 'api/pang', [], [], [], [
            'HTTP_ORIGIN' => 'localhost',
            'HTTP_ACCESS_CONTROL_REQUEST_METHOD' => 'POST',
        ]);

        $this->assertEquals('localhost', $crawler->headers->get('Access-Control-Allow-Origin'));
        $this->assertEquals(200, $crawler->getStatusCode());
    }

    public function testOptionsAllowOriginNotAllowed()
    {
        $crawler = $this->call('OPTIONS', 'api/ping', [], [], [], [
            'HTTP_ORIGIN' => 'otherhost',
            'HTTP_ACCESS_CONTROL_REQUEST_METHOD' => 'POST',
        ]);

        $this->assertEquals(null, $crawler->headers->get('Access-Control-Allow-Origin'));
        $this->assertEquals(403, $crawler->getStatusCode());
    }

    public function testAllowMethodAllowed()
    {
        $crawler = $this->call('POST', 'web/ping', [], [], [], [
            'HTTP_ORIGIN' => 'localhost',
            'HTTP_ACCESS_CONTROL_REQUEST_METHOD' => 'POST',
        ]);
        $this->assertEquals(null, $crawler->headers->get('Access-Control-Allow-Methods'));
        $this->assertEquals(200, $crawler->getStatusCode());

        $this->assertEquals('PONG', $crawler->getContent());
    }

    public function testAllowMethodNotAllowed()
    {
        $crawler = $this->call('POST', 'web/ping', [], [], [], [
            'HTTP_ORIGIN' => 'localhost',
            'HTTP_ACCESS_CONTROL_REQUEST_METHOD' => 'PUT',
        ]);
        $this->assertEquals(null, $crawler->headers->get('Access-Control-Allow-Methods'));
        $this->assertEquals(200, $crawler->getStatusCode());
    }

    public function testAllowHeaderAllowed()
    {
        $crawler = $this->call('POST', 'web/ping', [], [], [], [
            'HTTP_ORIGIN' => 'localhost',
            'HTTP_ACCESS_CONTROL_REQUEST_HEADERS' => 'x-custom-1, x-custom-2',
        ]);
        $this->assertEquals(null, $crawler->headers->get('Access-Control-Allow-Headers'));
        $this->assertEquals(200, $crawler->getStatusCode());

        $this->assertEquals('PONG', $crawler->getContent());
    }

    public function testAllowHeaderNotAllowed()
    {
        $crawler = $this->call('POST', 'web/ping', [], [], [], [
            'HTTP_ORIGIN' => 'localhost',
            'HTTP_ACCESS_CONTROL_REQUEST_HEADERS' => 'x-custom-3',
        ]);
        $this->assertEquals(null, $crawler->headers->get('Access-Control-Allow-Headers'));
        $this->assertEquals(200, $crawler->getStatusCode());
    }

    public function testError()
    {
        $crawler = $this->call('POST', 'api/error', [], [], [], [
            'HTTP_ORIGIN' => 'localhost',
            'HTTP_ACCESS_CONTROL_REQUEST_METHOD' => 'POST',
        ]);

        $this->assertEquals('localhost', $crawler->headers->get('Access-Control-Allow-Origin'));
        $this->assertEquals(500, $crawler->getStatusCode());
    }

    public function testValidationException()
    {
        $crawler = $this->call('POST', 'api/validation', [], [], [], [
            'HTTP_ORIGIN' => 'localhost',
            'HTTP_ACCESS_CONTROL_REQUEST_METHOD' => 'POST',
        ]);
        $this->assertEquals('localhost', $crawler->headers->get('Access-Control-Allow-Origin'));
        $this->assertEquals(302, $crawler->getStatusCode());
    }
}
