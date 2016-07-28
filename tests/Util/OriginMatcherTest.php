<?php

use Barryvdh\Cors\Util\OriginMatcher;

class OriginMatcherTest extends PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider parseOriginPatternDataProvider
     */
    public function testParseOriginPattern($origin, $result)
    {
        $this->assertSame(
            $result,
            OriginMatcher::parseOriginPattern($origin)
        );
        $this->assertSame(
            $result['scheme'],
            OriginMatcher::parseOriginPattern($origin, PHP_URL_SCHEME)
        );
        $this->assertSame(
            $result['host'],
            OriginMatcher::parseOriginPattern($origin, PHP_URL_HOST)
        );
        $this->assertSame(
            $result['port'],
            OriginMatcher::parseOriginPattern($origin, PHP_URL_PORT)
        );
    }

    public function parseOriginPatternDataProvider()
    {
        return [
            [
                'google.com',
                ['scheme' => null, 'host' => 'google.com', 'port' => null],
            ],
            [
                '*.google.com',
                ['scheme' => null, 'host' => '*.google.com', 'port' => null],
            ],
            [
                'http://google.com',
                ['scheme' => 'http', 'host' => 'google.com', 'port' => null],
            ],
            [
                'http://*.google.com',
                ['scheme' => 'http', 'host' => '*.google.com', 'port' => null],
            ],
            [
                'google.com:8080',
                ['scheme' => null, 'host' => 'google.com', 'port' => 8080],
            ],
            [
                '*.google.com:8080',
                ['scheme' => null, 'host' => '*.google.com', 'port' => 8080],
            ],
            [
                'https://google.com:8080',
                ['scheme' => 'https', 'host' => 'google.com', 'port' => 8080],
            ],
            [
                'https://*.google.com:8080',
                ['scheme' => 'https', 'host' => '*.google.com', 'port' => 8080],
            ],
            [
                'https://*.g-o_o1gLe.com:8080',
                ['scheme' => 'https', 'host' => '*.g-o_o1gLe.com', 'port' => 8080],
            ],
            [
                '*',
                ['scheme' => null, 'host' => '*', 'port' => null],
            ],
            [
                'https://*',
                ['scheme' => 'https', 'host' => '*', 'port' => null],
            ],
            [
                '*:8000',
                ['scheme' => null, 'host' => '*', 'port' => 8000],
            ],
            [
                '192.168.0.1',
                ['scheme' => null, 'host' => '192.168.0.1', 'port' => null],
            ],
            [
                'localhost',
                ['scheme' => null, 'host' => 'localhost', 'port' => null],
            ],
        ];
    }

    /**
     * @dataProvider parseOriginPatternInvalidPatternDataProvider
     * @expectedException Exception
     */
    public function testParseOriginPatternInvalidPattern($pattern)
    {
        OriginMatcher::parseOriginPattern($pattern);
    }

    public function parseOriginPatternInvalidPatternDataProvider()
    {
        return [
            ['foo.*'],
            ['foo..bar'],
            ['ftp://google.com'],
            ['http:/google.com'],
            ['//google.com'],
            ['google:com'],
        ];
    }

    /**
     * @dataProvider parseOriginPatternInvalidComponentDataProvider
     * @expectedException Exception
     */
    public function testParseOriginPatternInvalidComponent($pattern, $component)
    {
        OriginMatcher::parseOriginPattern($pattern, $component);
    }

    public function parseOriginPatternInvalidComponentDataProvider()
    {
        return [
            ['google.com', PHP_URL_USER],
            ['google.com', PHP_URL_PASS],
            ['google.com', PHP_URL_QUERY],
            ['google.com', PHP_URL_FRAGMENT],
        ];
    }

    /**
     * @dataProvider schemeMatchesDataProvider
     */
    public function testSchemeMatches($pattern, $scheme, $matches)
    {
        $this->assertSame(
            $matches,
            OriginMatcher::schemeMatches($pattern, $scheme)
        );
    }

    public function schemeMatchesDataProvider()
    {
        return [
            ['http', 'http' , true ],
            [null  , 'http' , true ],
            ['http', 'https', false],
            ['ftp' , 'http' , false],
        ];
    }

    /**
     * @dataProvider hostMatchesDataProvider
     */
    public function testHostMatches($pattern, $host, $matches)
    {
        $this->assertSame(
            $matches,
            OriginMatcher::hostMatches($pattern, $host)
        );
    }

    public function hostMatchesDataProvider()
    {
        return [
            ['google.com'     , 'google.com'     , true ],
            ['*.google.com'   , 'maps.google.com', true ],
            ['maps.google.com', 'google.com'     , false],
            ['maps.google.com', '*.google.com'   , false],
            ['google.com'     , 'maps.google.com', false],
            ['google.com'     , null             , false],
        ];
    }

    /**
     * @dataProvider portMatchesDataProvider
     */
    public function testPortMatches($pattern, $port, $matches)
    {
        $this->assertSame(
            $matches,
            OriginMatcher::portMatches($pattern,$port)
        );
    }

    public function portMatchesDataProvider()
    {
        return [
            [8080, 8080, true],
            [null, 8080, true],
            [8080, 8090, false],
            [8000, null, false],
        ];
    }

    /**
     * @dataProvider matchesDataProvider
     */
    public function testMatches($pattern, $origin, $matches)
    {
        $this->assertSame(
            $matches,
            OriginMatcher::matches($pattern, $origin)
        );
    }

    public function matchesDataProvider()
    {
        return [
            ['google.com'            , 'google.com'                 , true ],
            ['http://google.com'     , 'http://google.com'          , true ],
            ['http://google.com:8000', 'http://google.com:8000'     , true ],
            ['*.google.com'          , 'http://google.com:8000'     , true ],
            ['*.google.com'          , 'http://maps.google.com:8000', true ],
            ['http://*.google.com'   , 'https://maps.google.com'    , false],
        ];
    }
}
