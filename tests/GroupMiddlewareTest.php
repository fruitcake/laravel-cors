<?php

use Illuminate\Routing\Router;

class GroupMiddlewareTest extends TestCase
{

    public function testAllowOriginAllowed()
    {
        $crawler = $this->call('POST', 'api/ping', [], [], [], [
            'HTTP_ORIGIN' => 'localhost',
            'HTTP_ACCESS_CONTROL_REQUEST_METHOD' => 'POST',
        ]);

        $this->assertEquals('localhost', $crawler->headers->get('Access-Control-Allow-Origin'));
        $this->assertEquals(200, $crawler->getStatusCode());

        $this->assertEquals('PONG', $crawler->getContent());
    }

    public function testAllowOriginNotAllowed()
    {
        $crawler = $this->call('POST', 'api/ping', [], [], [], [
            'HTTP_ORIGIN' => 'otherhost',
            'HTTP_ACCESS_CONTROL_REQUEST_METHOD' => 'POST',
        ]);

        $this->assertEquals(null, $crawler->headers->get('Access-Control-Allow-Origin'));
        $this->assertEquals(403, $crawler->getStatusCode());
    }

    public function testAllowMethodAllowed()
    {
        $crawler = $this->call('POST', 'api/ping', [], [], [], [
            'HTTP_ORIGIN' => 'localhost',
            'HTTP_ACCESS_CONTROL_REQUEST_METHOD' => 'POST',
        ]);
        $this->assertEquals(null, $crawler->headers->get('Access-Control-Allow-Methods'));
        $this->assertEquals(200, $crawler->getStatusCode());

        $this->assertEquals('PONG', $crawler->getContent());
    }

    public function testAllowMethodNotAllowed()
    {
        $crawler = $this->call('POST', 'api/ping', [], [], [], [
            'HTTP_ORIGIN' => 'localhost',
            'HTTP_ACCESS_CONTROL_REQUEST_METHOD' => 'PUT',
        ]);
        $this->assertEquals(null, $crawler->headers->get('Access-Control-Allow-Methods'));
        $this->assertEquals(200, $crawler->getStatusCode());
    }

    public function testAllowMethodsForWebNotAllowed()
    {
        $crawler = $this->call('POST', 'web/ping', [], [], [], [
            'HTTP_ORIGIN' => 'localhost',
            'HTTP_ACCESS_CONTROL_REQUEST_METHOD' => 'POST',
        ]);
        $this->assertEquals(null, $crawler->headers->get('Access-Control-Allow-Methods'));
        $this->assertEquals(200, $crawler->getStatusCode());
    }

    public function testAllowHeaderAllowed()
    {
        $crawler = $this->call('POST', 'api/ping', [], [], [], [
            'HTTP_ORIGIN' => 'localhost',
            'HTTP_ACCESS_CONTROL_REQUEST_HEADERS' => 'x-custom-1, x-custom-2',
        ]);
        $this->assertEquals('x-custom-1, x-custom-2', $crawler->headers->get('Access-Control-Allow-Headers'));
        $this->assertEquals(200, $crawler->getStatusCode());

        $this->assertEquals('PONG', $crawler->getContent());
    }

    public function testAllowHeaderNotAllowed()
    {
        $crawler = $this->call('POST', 'api/ping', [], [], [], [
            'HTTP_ORIGIN' => 'localhost',
            'HTTP_ACCESS_CONTROL_REQUEST_HEADERS' => 'x-custom-3',
        ]);
        $this->assertEquals('x-custom-1, x-custom-2', $crawler->headers->get('Access-Control-Allow-Headers'));
        $this->assertEquals(200, $crawler->getStatusCode());
    }

    public function testError()
    {
        if ($this->checkVersion('5.2', '<')) {
            $this->markTestSkipped('Catching exceptions is not possible on Laravel 5.1');
        }

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