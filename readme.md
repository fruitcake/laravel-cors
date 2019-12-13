# CORS Middleware for Laravel

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE.md)
[![Build Status][ico-travis]][link-travis]
[![Total Downloads][ico-downloads]][link-downloads]

Based on https://github.com/asm89/stack-cors

## About

The `laravel-cors` package allows you to send [Cross-Origin Resource Sharing](http://enable-cors.org/)
headers with Laravel middleware configuration.

If you want to have have a global overview of CORS workflow, you can  browse
this [image](http://www.html5rocks.com/static/images/cors_server_flowchart.png).

## Features

* Handles CORS pre-flight OPTIONS requests
* Adds CORS headers to your responses

## Installation

Require the `barryvdh/laravel-cors` package in your `composer.json` and update your dependencies:
```sh
composer require barryvdh/laravel-cors
```

## Global usage

To allow CORS for all your routes, add the `HandleCors` middleware in the `$middleware` property of  `app/Http/Kernel.php` class:

```php
protected $middleware = [
    // ...
    \Barryvdh\Cors\HandleCors::class,
];
```

> Note: Adding this to a group will make it harder to add CORS headers to all requests (eg. 404/500 errors). Make sure you add a `fallback` route to your group.

## Configuration

The defaults are set in `config/cors.php`. Copy this file to your own config directory to modify the values. You can publish the config using this command:
```sh
$ php artisan vendor:publish --provider="Barryvdh\Cors\ServiceProvider"
```
> **Note:** When using custom headers, like `X-Auth-Token` or `X-Requested-With`, you must set the `allowedHeaders` to include those headers. You can also set it to `array('*')` to allow all custom headers.

> **Note:** If you are explicitly whitelisting headers, you must include `Origin` or requests will fail to be recognized as CORS.

    
```php
return [
     /*
     |--------------------------------------------------------------------------
     | Laravel CORS
     |--------------------------------------------------------------------------
     |
     | allowedOrigins, allowedHeaders and allowedMethods can be set to array('*')
     | to accept any value.
     |
     */
    'supportsCredentials' => false,
    'allowedOrigins' => ['*'],
    'allowedHeaders' => ['Content-Type', 'X-Requested-With'],
    'allowedMethods' => ['*'], // ex: ['GET', 'POST', 'PUT',  'DELETE']
    'exposedHeaders' => [],
    'maxAge' => 0,
];
```

`allowedOrigins`, `allowedHeaders` and `allowedMethods` can be set to `array('*')` to accept any value.

> **Note:** Try to be a specific as possible. You can start developing with loose constraints, but it's better to be as strict as possible!

> **Note:** Because of [http method overriding](http://symfony.com/doc/current/reference/configuration/framework.html#http-method-override) in Laravel, allowing POST methods will also enable the API users to perform PUT and DELETE requests as well.

### Lumen

On Laravel Lumen, load your configuration file manually in `bootstrap/app.php`:
```php
$app->configure('cors');
```

And register the ServiceProvider:

```php
$app->register(Barryvdh\Cors\ServiceProvider::class);
```

## Global usage for Lumen
To allow CORS for all your routes, add the `HandleCors` middleware to the global middleware:
```php
$app->middleware([
    // ...
    \Barryvdh\Cors\HandleCors::class,
]);
```

### Disabling CSRF protection for your API

If possible, use a different route group with CSRF protection enabled. 
Otherwise you can disable CSRF for certain requests in `App\Http\Middleware\VerifyCsrfToken`:

```php
protected $except = [
    'api/*'
];
```
    
## License

Released under the MIT License, see [LICENSE](LICENSE).

[ico-version]: https://img.shields.io/packagist/v/barryvdh/laravel-cors.svg?style=flat-square
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[ico-travis]: https://img.shields.io/travis/barryvdh/laravel-cors/master.svg?style=flat-square
[ico-scrutinizer]: https://img.shields.io/scrutinizer/coverage/g/barryvdh/laravel-cors.svg?style=flat-square
[ico-code-quality]: https://img.shields.io/scrutinizer/g/barryvdh/laravel-cors.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/barryvdh/laravel-cors.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/barryvdh/laravel-cors
[link-travis]: https://travis-ci.org/barryvdh/laravel-cors
[link-scrutinizer]: https://scrutinizer-ci.com/g/barryvdh/laravel-cors/code-structure
[link-code-quality]: https://scrutinizer-ci.com/g/barryvdh/laravel-cors
[link-downloads]: https://packagist.org/packages/barryvdh/laravel-cors
[link-author]: https://github.com/barryvdh
[link-contributors]: ../../contributors
