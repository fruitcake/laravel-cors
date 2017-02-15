<?php

namespace Barryvdh\Cors;

/**
 * Fork of asm89/stack-cors
 */
class OriginMatcher
{

    public static function matches($pattern, $origin)
    {
        if ($pattern === $origin) {
            return true;
        }
        $scheme = parse_url($origin, PHP_URL_SCHEME);
        $host = parse_url($origin, PHP_URL_HOST);
        $port = parse_url($origin, PHP_URL_PORT);

        $schemePattern = static::parseOriginPattern($pattern, PHP_URL_SCHEME);
        $hostPattern = static::parseOriginPattern($pattern, PHP_URL_HOST);
        $portPattern = static::parseOriginPattern($pattern, PHP_URL_PORT);

        $schemeMatches = static::schemeMatches($schemePattern, $scheme);
        $hostMatches = static::hostMatches($hostPattern, $host);
        $portMatches = static::portMatches($portPattern, $port);
        return $schemeMatches && $hostMatches && $portMatches;
    }

    public static function schemeMatches($pattern, $scheme)
    {
        return is_null($pattern) || $pattern === $scheme;
    }

    public static function hostMatches($pattern, $host)
    {
        $patternComponents = array_reverse(explode('.', $pattern));
        $hostComponents = array_reverse(explode('.', $host));
        foreach ($patternComponents as $index => $patternComponent) {
            if ($patternComponent === '*') {
                return true;
            }
            if (!isset($hostComponents[$index])) {
                return false;
            }
            if ($hostComponents[$index] !== $patternComponent) {
                return false;
            }
        }
        return count($patternComponents) === count($hostComponents);
    }

    public static function portMatches($pattern, $port)
    {
        if ($pattern === "*") {
            return true;
        }
        if ((string)$pattern === "") {
            return (string)$port === "";
        }
        if (preg_match('/\A\d+\z/', $pattern)) {
            return (string)$pattern === (string)$port;
        }
        if (preg_match('/\A(?P<from>\d+)-(?P<to>\d+)\z/', $pattern, $captured)) {
            return $captured['from'] <= $port && $port <= $captured['to'];
        }
        throw new \InvalidArgumentException("Invalid port pattern: ${pattern}");
    }

    public static function parseOriginPattern($originPattern, $component = -1)
    {
        $matched = preg_match(
            '!\A
                (?: (?P<scheme> ([a-z][a-z0-9+\-.]*) ):// )?
                (?P<host> (?:\*|[\w-]+)(?:\.[\w-]+)* )
                (?: :(?P<port> (?: \*|\d+(?:-\d+)? ) ) )?
            \z!x',
            $originPattern,
            $captured
        );
        if (!$matched) {
            throw new \InvalidArgumentException("Invalid origin pattern ${originPattern}");
        }
        $components = [
            'scheme' => $captured['scheme'] ?: null,
            'host' => $captured['host'],
            'port' => array_key_exists('port', $captured) ? $captured['port'] : null,
        ];
        switch ($component) {
            case -1:
                return $components;
            case PHP_URL_SCHEME:
                return $components['scheme'];
            case PHP_URL_HOST:
                return $components['host'];
            case PHP_URL_PORT:
                return $components['port'];
        }
        throw new \InvalidArgumentException("Invalid component: ${component}");
    }
}
