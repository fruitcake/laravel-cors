# CORS in Laravel 5
Based on https://github.com/asm89/stack-cors
For Laravel 4, please use the [0.2 branch](https://github.com/barryvdh/laravel-cors/tree/0.2)!

## About

The `laravel-cors` package allows you to send [Cross-Origin Resource Sharing](http://enable-cors.org/)
headers with Laravel middleware configuration.

If you want to have have a global overview of CORS workflow, you can  browse
this [image](http://www.html5rocks.com/static/images/cors_server_flowchart.png).

## Features

* Handles CORS pre-flight OPTIONS requests
* Adds CORS headers to your responses

## Configuration

The defaults are set in `config/cors.php`. Copy this file to your own config directory to modify the values. You can publish the config using this command:

    php artisan vendor:publish --provider="Barryvdh\Cors\ServiceProvider"

> **Note:** When using custom headers, like `X-Auth-Token` or `X-Requested-With`, you must set the allowedHeaders to include those headers. You can also set it to `array('*')` to allow all custom headers.

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
    'allowedHeaders' => ['*'], // ex : ['Content-Type', 'Accept']
    'allowedMethods' => ['*'], // ex: ['GET', 'POST', 'PUT',  'DELETE']
    'exposedHeaders' => [],
    'maxAge' => 0,
]
```

`allowedOrigins`, `allowedHeaders` and `allowedMethods` can be set to `array('*')` to accept any value, the
allowed methods however have to be explicitly listed.

> **Note:** Because of [http method overriding](http://symfony.com/doc/current/reference/configuration/framework.html#http-method-override) in Laravel, allowing POST methods will also enable the API users to perform PUT and DELETE requests as well.

## Installation

Require the `barryvdh/laravel-cors` package in your composer.json and update your dependencies.

    $ composer require barryvdh/laravel-cors

Add the Cors\ServiceProvider to your config/app.php providers array:

```php
Barryvdh\Cors\ServiceProvider::class,
```

Add the Preflight Middleware to your global middleware array:

```php
Barryvdh\Cors\HandlePreflight::class,
```

## Usage

Add the CORS middleware to the group you want to allow CORS access:

```php
Barryvdh\Cors\HandleCors::class,
```

If you want to add CORS to all your routes, just add HandleCors to the global middleware.

## Lumen

On Laravel Lumen, use `HandlePreflightSimple` as global middleware:

    Barryvdh\Cors\HandlePreflightSimple::class,

And load your configuration file manually:

    $app->configure('cors');

## Common problems and errors
In order for the package to work, the request has to be a valid CORS request and needs to include an "Origin" header.

When an error occurs, the middleware isn't run completely. So when this happens, you won't see the actual result, but will get a CORS error instead.

This could be a CSRF token error or just a simple problem.

### Disabling CSRF protection for your API

In `App\Http\Middleware\VerifyCsrfToken`, add your routes to the exceptions:

```php
protected $except = [
  'api/*'
];
```
    
### Debugging errors

A simple but hacky method is to just always send the CORS headers. This isn't recommended for production, but it will show you the actual errors.

Add this to the top of `public/index.php`:

```php
header("Access-Control-Allow-Origin: *");
```

Don't forget to remove that in production, so you can specify what routes/headers/origins are allowed.
    
You can add the CORS headers to the Errors also, in your Exception Handler:

```php
public function render($request, Exception $e)
{
    $response = parent::render($request, $e);

    if ($request->is('api/*')) {
        app('Barryvdh\Cors\Stack\CorsService')->addActualRequestHeaders($response, $request);
    }

    return $response;
}
```
## License

Released under the MIT License, see LICENSE.
