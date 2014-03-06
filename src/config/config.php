<?php

return array(

    /*
     |--------------------------------------------------------------------------
     | Laravel CORS Defaults
     |--------------------------------------------------------------------------
     |
     | The defaults are the default values applied to all the paths that match,
     | unless overridden in a specific URL configuration.
     | If you want them to apply to everything, you must define a path with ^/.
     |
     | allow_origin and allow_headers can be set to * to accept any value,
     | the allowed methods however have to be explicitly listed.
     |
     */
    'defaults' => array(
        'allow_credentials' => false,
        'allow_origin' => array(),
        'allow_headers' => array(),
        'allow_methods' => array(),
        'expose_headers' => array(),
        'max_age' => 0,
    ),

    'paths' => array(
        '^/api/' => array(
            'allow_origin' => array('*'),
            'allow_headers' => array('Content-Type'),
            'allow_methods' => array('POST', 'PUT', 'GET', 'DELETE'),
            'max_age' => 3600,
        ),
    ),

);
