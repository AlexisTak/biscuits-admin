<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    */

    'paths' => [
        'api/*',
        'sanctum/csrf-cookie',
    ],

    'allowed_methods' => ['*'], // ✅ Autoriser toutes les méthodes

    'allowed_origins' => [
        'https://biscuits-ia.com',
        'https://www.biscuits-ia.com',
        'http://localhost:4321',
        'http://127.0.0.1:4321',
        'http://localhost:3000', // Pour les tests locaux
    ],

    'allowed_origins_patterns' => [
        // Si vous avez des sous-domaines
        // '/^https:\/\/.*\.biscuits-ia\.com$/',
    ],

    'allowed_headers' => ['*'], // ✅ Autoriser tous les headers

    'exposed_headers' => [
        'X-RateLimit-Limit',
        'X-RateLimit-Remaining',
    ],

    'max_age' => 86400, // Cache preflight pendant 24h

    'supports_credentials' => false, // Pas besoin de cookies pour l'API publique
];