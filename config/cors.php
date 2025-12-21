<?php

return [
    'paths' => ['api/*'],

    'allowed_methods' => ['POST', 'GET', 'OPTIONS'],

    'allowed_origins' => [
        'https://biscuits-ia.com',
        'http://localhost:4321',
        'http://127.0.0.1:4321',
    ],

    'allowed_origins_patterns' => [],

    'allowed_headers' => [
        'Content-Type',
        'Accept',
    ],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => false,
];
