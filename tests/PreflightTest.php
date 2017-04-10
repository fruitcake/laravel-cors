<?php

class PreflightTest extends TestCase
{
    public function testAllowOriginAllowed()
    {
        $crawler = $this->call('OPTIONS', 'api/ping', [], [], [], [
            'HTTP_ORIGIN' => 'localhost',
            'HTTP_ACCESS_CONTROL_REQUEST_METHOD' => 'POST',
        ]);

        $this->assertEquals('localhost', $crawler->headers->get('Access-Control-Allow-Origin'));
        $this->assertEquals(200, $crawler->getStatusCode());
    }

    public function testAllowOriginNotAllowed()
    {
        $crawler = $this->call('OPTIONS', 'api/ping', [], [], [], [
            'HTTP_ORIGIN' => 'otherhost',
            'HTTP_ACCESS_CONTROL_REQUEST_METHOD' => 'POST',
        ]);

        $this->assertEquals(null, $crawler->headers->get('Access-Control-Allow-Origin'));
        $this->assertEquals(403, $crawler->getStatusCode());
    }

    public function testAllowNotFoundUsesConfig()
    {
        $crawler = $this->call('OPTIONS', 'api/pang', [], [], [], [
            'HTTP_ORIGIN' => 'localhost',
            'HTTP_ACCESS_CONTROL_REQUEST_METHOD' => 'POST',
        ]);
        $this->assertEquals('GET, POST', $crawler->headers->get('Access-Control-Allow-Methods'));
        $this->assertEquals(200, $crawler->getStatusCode());
    }

    public function testAllowMethodAllowed()
    {
        $crawler = $this->call('OPTIONS', 'api/ping', [], [], [], [
            'HTTP_ORIGIN' => 'localhost',
            'HTTP_ACCESS_CONTROL_REQUEST_METHOD' => 'POST',
        ]);
        $this->assertEquals('GET, POST', $crawler->headers->get('Access-Control-Allow-Methods'));
        $this->assertEquals(200, $crawler->getStatusCode());
    }

    public function testAllowMethodNotAllowed()
    {
        $crawler = $this->call('OPTIONS', 'api/ping', [], [], [], [
            'HTTP_ORIGIN' => 'localhost',
            'HTTP_ACCESS_CONTROL_REQUEST_METHOD' => 'PUT',
        ]);
        $this->assertEquals(null, $crawler->headers->get('Access-Control-Allow-Methods'));
        $this->assertEquals(405, $crawler->getStatusCode());
    }

    public function testAllowMethodsForWeb()
    {
        $crawler = $this->call('OPTIONS', 'web/ping', [], [], [], [
            'HTTP_ORIGIN' => 'localhost',
            'HTTP_ACCESS_CONTROL_REQUEST_METHOD' => 'POST',
        ]);
        $this->assertEquals('GET, POST', $crawler->headers->get('Access-Control-Allow-Methods'));
        $this->assertEquals(200, $crawler->getStatusCode());
    }

    public function testAllowHeaderAllowed()
    {
        $crawler = $this->call('OPTIONS', 'api/ping', [], [], [], [
            'HTTP_ORIGIN' => 'localhost',
            'HTTP_ACCESS_CONTROL_REQUEST_METHOD' => 'POST',
            'HTTP_ACCESS_CONTROL_REQUEST_HEADERS' => 'X-Custom-1, X-Custom-2',
        ]);
        $this->assertEquals('x-custom-1, x-custom-2', $crawler->headers->get('Access-Control-Allow-Headers'));
        $this->assertEquals(200, $crawler->getStatusCode());
    }

    public function testAllowAllHeaderAllowed()
    {
        config(['cors.allowedHeaders' => ['*']]);

        $crawler = $this->call('OPTIONS', 'api/ping', [], [], [], [
            'HTTP_ORIGIN' => 'localhost',
            'HTTP_ACCESS_CONTROL_REQUEST_METHOD' => 'POST',
            'HTTP_ACCESS_CONTROL_REQUEST_HEADERS' => 'X-Custom-3',
        ]);
        $this->assertEquals('X-CUSTOM-3', $crawler->headers->get('Access-Control-Allow-Headers'));
        $this->assertEquals(200, $crawler->getStatusCode());
    }

    public function testAllowHeaderNotAllowed()
    {
        $crawler = $this->call('OPTIONS', 'api/ping', [], [], [], [
            'HTTP_ORIGIN' => 'localhost',
            'HTTP_ACCESS_CONTROL_REQUEST_METHOD' => 'POST',
            'HTTP_ACCESS_CONTROL_REQUEST_HEADERS' => 'x-custom-3',
        ]);
        $this->assertEquals(null, $crawler->headers->get('Access-Control-Allow-Headers'));
        $this->assertEquals(403, $crawler->getStatusCode());
    }
}
