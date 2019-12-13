<?php

return [
   /*
   |--------------------------------------------------------------------------
   | Laravel CORS Paths
   |--------------------------------------------------------------------------
   |
   | You can allow 1 or multiple paths to add CORS headers.
   | Example: 'api/*'
   |
   */
    'paths' => [],

    /*
    |--------------------------------------------------------------------------
    | Laravel CORS Options
    |--------------------------------------------------------------------------
    |
    | allowed_origins, allowed_headers and allowed_methods can be set to array('*')
    | to accept any value.
    |
    */
    'supports_credentials' => false,
    'allowed_origins' => ['*'],
    'allowed_origins_patterns' => [],
    'allowed_headers' => ['*'],
    'allowed_methods' => ['*'],
    'exposed_headers' => [],
    'max_age' => 0,

];
