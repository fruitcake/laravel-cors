# CORS in Laravel 5
Based on https://github.com/asm89/stack-cors

### For Laravel 4, please use the [0.2 branch](https://github.com/barryvdh/laravel-cors/tree/0.2)!

## About

The `laravel-cors` package allows you to send [Cross-Origin Resource Sharing](http://enable-cors.org/)
headers with ACL-style per-url configuration.

If you want to have have a global overview of CORS workflow, you can  browse
this [image](http://www.html5rocks.com/static/images/cors_server_flowchart.png).

## Features

* Handles CORS pre-flight OPTIONS requests
* Adds CORS headers to your responses

## Configuration

The defaults are set in `config/cors.php'. Copy this file to your own config directory to modify the values. You can publish the config using this command:

    php artisan vendor:publish --provider="Barryvdh\Cors\ServiceProvider"

> **Note:** When using custom headers, like `X-Auth-Token` or `X-Requested-With`, you must set the allowedHeaders to include those headers. You can also set it to `array('*')` to allow all custom headers.

> **Note:** If you are explicitly whitelisting headers, you must include `Origin` or requests will fail to be recognized as CORS.

    return [
        'supportsCredentials' => false,
        'allowedOrigins' => ['*'],
        'allowedHeaders' => ['Content-Type', 'Accept'],
        'allowedMethods' => ['GET', 'POST', 'PUT',  'DELETE'],
        'exposedHeaders' => [],
        'maxAge' => 0,
        'hosts' => [],
    ]

`allowedOrigins`, `allowedHeaders` and `allowedMethods` can be set to `array('*')` to accept any value, the
allowed methods however have to be explicitly listed.

> **Note:** Because of [http method overriding](http://symfony.com/doc/current/reference/configuration/framework.html#http-method-override) in Laravel, allowing POST methods will also enable the API users to perform PUT and DELETE requests as well.

## Installation

Require the `barryvdh/laravel-cors` package in your composer.json and update your dependencies.

    $ composer require barryvdh/laravel-cors 0.7.x

Add the Cors\ServiceProvider to your config/app.php providers array:

     'Barryvdh\Cors\ServiceProvider',
     
## Usage

The ServiceProvider adds a route middleware you can use, called `cors`. You can apply this to a route or group to add CORS support.

    Route::group(['middleware' => 'cors'], function(Router $router){
        $router->get('api', 'ApiController@index');
    });

## Common problems and errors

When an error occurs, the middleware isn't run completely. So when this happens, you won't see the actual result, but will get a CORS error.

This could be a CSRF token error or just a simple problem.

### Disabling CSRF protection for your API

In `App\Http\Middleware\VerifyCsrfToken`, add your routes to the exceptions:

    protected $except = [
      'api/*'
    ];
    
### Debugging errors

A simple but hacky method is to just always send the CORS headers. This isn't recommended for production, but it will show you the actual errors.

Add this to the top of `public/index.php`:

    header("Access-Control-Allow-Origin: *");
    
Don't forget to remove that in production, so you can specify what routes/headers/origins are allowed.
    
You can add the CORS headers to the Errors also, in your Exception Handler:

    public function render($request, Exception $e)
    {
        $response = parent::render($request, $e);

        if ($request->is('api/*')) {
            app('Asm89\Stack\CorsService')->addActualRequestHeaders($response, $request);
        }

        return $response;
    }
    
## License

Released under the MIT License, see LICENSE.
