# CORS in Laravel 4.1+
Based on https://github.com/nelmio/NelmioCorsBundle and https://github.com/asm89/stack-cors

For Laravel 5, see the [0.4 branch](https://github.com/barryvdh/laravel-cors/tree/0.3)

> **Note:** If you are coming from 0.1.0 (or dev-master), the configuration for 0.2.0 has changed.
> The old config still works, but is deprecated and will probably be removed in future releases.
> Make sure you require `0.2.x` and not `dev-master` to prevent breaking changes.
> The headers are also no longer in `app('laravel-cors.send')`, as this is not needed for error handling with the Middleware.

## About

The `laravel-cors` package allows you to send [Cross-Origin Resource Sharing](http://enable-cors.org/)
headers with ACL-style per-url configuration.

If you want to have have a global overview of CORS workflow, you can  browse
this [image](http://www.html5rocks.com/static/images/cors_server_flowchart.png).

## Features

* Handles CORS pre-flight OPTIONS requests
* Adds CORS headers to your responses

## Configuration

The `defaults` are the default values applied to all the `paths` that match,
unless overridden in a specific URL configuration. This uses the same syntax as Request::is($pattern)
If you want them to apply to everything, you must define a path with `*`. Use the `hosts` key to restrict
the matches only to specific subdomains.

This example config contains all the possible config values with their default
values shown in the `defaults` key. In paths, you see that we allow CORS
requests from any origin on `/api/`. One custom header and some HTTP methods
are defined as allowed as well. Preflight requests can be cached for 3600
seconds.

> **Note:** When using custom headers, like `X-Auth-Token` or `X-Requested-With`, you must set the allowedHeaders to include those headers. You can also set it to `array('*')` to allow all custom headers.

    'defaults' => array(
        'supportsCredentials' => false,
        'allowedOrigins' => array(),
        'allowedHeaders' => array(),
        'allowedMethods' => array(),
        'exposedHeaders' => array(),
        'maxAge' => 0,
        'hosts' => array(),
    ),

    'paths' => array(
        'api/*' => array(
            'allowedOrigins' => array('*'),
            'allowedHeaders' => array('*'),
            'allowedMethods' => array('*'),
            'maxAge' => 3600,
        ),
        '*' => array(
            'allowedOrigins' => array('*'),
            'allowedHeaders' => array('Content-Type'),
            'allowedMethods' => array('POST', 'PUT', 'GET', 'DELETE'),
            'maxAge' => 3600,
            'hosts' => array('api.*'),
        ),
    ),


`allowedOrigins`, `allowedHeaders` and `allowedMethods` can be set to `array('*')` to accept any value, the
allowed methods however have to be explicitly listed.

> **Note:** Because of [http method overriding](http://symfony.com/doc/current/reference/configuration/framework.html#http-method-override) in Laravel, allowing POST methods will also enable the API users to perform PUT and DELETE requests as well.

## Installation

Require the `barryvdh/laravel-cors` package in your composer.json and update your dependencies.

    $ composer require barryvdh/laravel-cors:0.2.x

Add the CorsServiceProvider to your app/config/app.php providers array:

     'Barryvdh\Cors\CorsServiceProvider',
     
For Laravel 5.x, you need to add the Middleware to your App Kernel:

    'Barryvdh\Cors\Middleware\HandleCors',

Publish the config file to create your own configuration:

     $ php artisan config:publish barryvdh/laravel-cors

The config.php file will be published in app/config/packages/barryvdh/laravel-cors

## License

Released under the MIT License, see LICENSE.
