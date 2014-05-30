# CORS in Laravel 4.1+
Based on https://github.com/nelmio/NelmioCorsBundle and https://github.com/asm89/stack-cors

## About

The LaravelCorsBundle allows you to send [Cross-Origin Resource Sharing](http://enable-cors.org/)
headers with ACL-style per-url configuration.

If you want to have have a global overview of CORS workflow, you can  browse
this [image](http://www.html5rocks.com/static/images/cors_server_flowchart.png).

## Features

* Handles CORS pre-flight OPTIONS requests
* Adds CORS headers to your responses

## Configuration

The `defaults` are the default values applied to all the `paths` that match,
unless overriden in a specific URL configuration. If you want them to apply
to everything, you must define a path with `^/`.

This example config contains all the possible config values with their default
values shown in the `defaults` key. In paths, you see that we allow CORS
requests from any origin on `/api/`. One custom header and some HTTP methods
are defined as allowed as well. Preflight requests can be cached for 3600
seconds.

    'defaults' => array(
        'supportsCredentials' => false,
        'allowedOrigins' => array(),
        'allowedHeaders' => array(),
        'allowedMethods' => array(),
        'exposedHeaders' => array(),
        'maxAge' => 0,
    ),

    'paths' => array(
        '^/api/' => array(
            'allowedOrigins' => array('*'),
            'allowedHeaders' => array('Content-Type'),
            'allowedMethods' => array('POST', 'PUT', 'GET', 'DELETE'),
            'maxAge' => 3600,
        ),
    ),

`allowedOrigins` and `allowedHeaders` can be set to `*` to accept any value, the
allowed methods however have to be explicitly listed.

## Installation

Require the `barryvdh/laravel-cors` package in your composer.json and update your dependencies.

    $ composer require barryvdh/laravel-cors:0.3.x

Add the CorsServiceProvider to your app/config/app.php providers array:

     'Barryvdh\Cors\CorsServiceProvider',

Publish the config file to create your own configuration:

     $ php artisan config:publish barryvdh/laravel-cors

The config.php file will be published in app/config/packages/barryvdh/laravel-cors

## License

Released under the MIT License, see LICENSE.
