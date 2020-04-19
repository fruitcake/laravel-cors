<?php

namespace Fruitcake\Cors\Tests;

use Fruitcake\Cors\HandleCors;
use Illuminate\Contracts\Http\Kernel;

class GlobalMiddlewareTest extends TestCase
{
    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application $app
     *
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        // Add the middleware
        $kernel = $app->make(Kernel::class);
        $kernel->prependMiddleware(HandleCors::class);

        parent::getEnvironmentSetUp($app);
    }

    public function testShouldReturnHeaderAssessControlAllowOriginWhenDontHaveHttpOriginOnRequest()
    {
        $crawler = $this->call('OPTIONS', 'api/ping', [], [], [], [
            'HTTP_ACCESS_CONTROL_REQUEST_METHOD' => 'POST',
        ]);

        $this->assertEquals('localhost', $crawler->headers->get('Access-Control-Allow-Origin'));
        $this->assertEquals(204, $crawler->getStatusCode());
    }

    public function testOptionsAllowOriginAllowed()
    {
        $crawler = $this->call('OPTIONS', 'api/ping', [], [], [], [
            'HTTP_ORIGIN' => 'localhost',
            'HTTP_ACCESS_CONTROL_REQUEST_METHOD' => 'POST',
        ]);

        $this->assertEquals('localhost', $crawler->headers->get('Access-Control-Allow-Origin'));
        $this->assertEquals(204, $crawler->getStatusCode());
    }

    public function testAllowAllOrigins()
    {
        $this->app['config']->set('cors.allowed_origins', ['*']);

        $crawler = $this->call('OPTIONS', 'api/ping', [], [], [], [
            'HTTP_ORIGIN' => 'laravel.com',
            'HTTP_ACCESS_CONTROL_REQUEST_METHOD' => 'POST',
        ]);

        $this->assertEquals('*', $crawler->headers->get('Access-Control-Allow-Origin'));
        $this->assertEquals(204, $crawler->getStatusCode());
    }

    public function testAllowAllOriginsWildcard()
    {
        $this->app['config']->set('cors.allowed_origins', ['*.laravel.com']);

        $crawler = $this->call('OPTIONS', 'api/ping', [], [], [], [
            'HTTP_ORIGIN' => 'test.laravel.com',
            'HTTP_ACCESS_CONTROL_REQUEST_METHOD' => 'POST',
        ]);

        $this->assertEquals('test.laravel.com', $crawler->headers->get('Access-Control-Allow-Origin'));
        $this->assertEquals(204, $crawler->getStatusCode());
    }

    public function testAllowAllOriginsWildcardNoMatch()
    {
        $this->app['config']->set('cors.allowed_origins', ['*.laravel.com']);

        $crawler = $this->call('OPTIONS', 'api/ping', [], [], [], [
            'HTTP_ORIGIN' => 'test.symfony.com',
            'HTTP_ACCESS_CONTROL_REQUEST_METHOD' => 'POST',
        ]);

        $this->assertEquals(null, $crawler->headers->get('Access-Control-Allow-Origin'));
    }

    public function testOptionsAllowOriginAllowedNonExistingRoute()
    {
        $crawler = $this->call('OPTIONS', 'api/pang', [], [], [], [
            'HTTP_ORIGIN' => 'localhost',
            'HTTP_ACCESS_CONTROL_REQUEST_METHOD' => 'POST',
        ]);

        $this->assertEquals('localhost', $crawler->headers->get('Access-Control-Allow-Origin'));
        $this->assertEquals(204, $crawler->getStatusCode());
    }

    public function testOptionsAllowOriginNotAllowed()
    {
        $crawler = $this->call('OPTIONS', 'api/ping', [], [], [], [
            'HTTP_ORIGIN' => 'otherhost',
            'HTTP_ACCESS_CONTROL_REQUEST_METHOD' => 'POST',
        ]);

        $this->assertEquals('localhost', $crawler->headers->get('Access-Control-Allow-Origin'));
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

    public function testAllowHeaderAllowedOptions()
    {
        $crawler = $this->call('OPTIONS', 'api/ping', [], [], [], [
            'HTTP_ORIGIN' => 'localhost',
            'HTTP_ACCESS_CONTROL_REQUEST_METHOD' => 'POST',
            'HTTP_ACCESS_CONTROL_REQUEST_HEADERS' => 'x-custom-1, x-custom-2',
        ]);
        $this->assertEquals('x-custom-1, x-custom-2', $crawler->headers->get('Access-Control-Allow-Headers'));
        $this->assertEquals(204, $crawler->getStatusCode());

        $this->assertEquals('', $crawler->getContent());
    }

    public function testAllowHeaderAllowedWildcardOptions()
    {
        $this->app['config']->set('cors.allowed_headers', ['*']);

        $crawler = $this->call('OPTIONS', 'api/ping', [], [], [], [
            'HTTP_ORIGIN' => 'localhost',
            'HTTP_ACCESS_CONTROL_REQUEST_METHOD' => 'POST',
            'HTTP_ACCESS_CONTROL_REQUEST_HEADERS' => 'x-custom-3',
        ]);
        $this->assertEquals('*', $crawler->headers->get('Access-Control-Allow-Headers'));
        $this->assertEquals(204, $crawler->getStatusCode());

        $this->assertEquals('', $crawler->getContent());
    }

    public function testAllowHeaderNotAllowedOptions()
    {
        $crawler = $this->call('OPTIONS', 'api/ping', [], [], [], [
            'HTTP_ORIGIN' => 'localhost',
            'HTTP_ACCESS_CONTROL_REQUEST_METHOD' => 'POST',
            'HTTP_ACCESS_CONTROL_REQUEST_HEADERS' => 'x-custom-3',
        ]);
        $this->assertEquals('x-custom-1, x-custom-2', $crawler->headers->get('Access-Control-Allow-Headers'));
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

    public function testAllowHeaderAllowedWildcard()
    {
        $this->app['config']->set('cors.allowed_headers', ['*']);

        $crawler = $this->call('POST', 'web/ping', [], [], [], [
            'HTTP_ORIGIN' => 'localhost',
            'HTTP_ACCESS_CONTROL_REQUEST_HEADERS' => 'x-custom-3',
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

    public function testInvalidExposedHeadersException()
    {
        $this->expectException(\RuntimeException::class);

        $this->app['config']->set('cors.exposed_headers', true);

        $this->call('POST', 'api/validation', [], [], [], [
            'HTTP_ORIGIN' => 'localhost',
        ]);
    }

    public function testInvalidOriginsException()
    {
        $this->expectException(\RuntimeException::class);

        $this->app['config']->set('cors.allowed_origins', true);

        $this->call('POST', 'api/validation', [], [], [], [
            'HTTP_ORIGIN' => 'localhost',
        ]);
    }
}
