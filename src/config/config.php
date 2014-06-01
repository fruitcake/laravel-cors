<?php

return array(

    /*
     |--------------------------------------------------------------------------
     | Laravel CORS Defaults
     |--------------------------------------------------------------------------
     |
     | The defaults are the default values applied to all the paths that match,
     | unless overridden in a specific URL configuration.
     | If you want them to apply to everything, you must define a path with *.
     |
     | allowedOrigins, allowedHeaders and allowedMethods can be set to array('*') 
     | to accept any value, the allowed methods however have to be explicitly listed.
     |
     */
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
            'allowedHeaders' => array('Content-Type'),
            'allowedMethods' => array('POST', 'PUT', 'GET', 'DELETE'),
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

);
