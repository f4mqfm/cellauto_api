<?php

$fromEnv = array_values(array_filter(array_map(
    'trim',
    explode(',', (string) env('CORS_ALLOWED_ORIGINS', ''))
)));

return [

    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    /*
     * Cross-origin kérés az admin (pl. admin.cellauto.ro) és az API (api.cellauto.ro) között.
     *
     * Ha a CORS_ALLOWED_ORIGINS üres, a viselkedés: ['*'] (fejlesztés / gyors indulás).
     * Élesben állítsd be vesszővel: https://admin.cellauto.ro,https://www.cellauto.ro
     *
     * Ha cookie-s (credentials) Sanctum SPA-t használsz, CORS_SUPPORTS_CREDENTIALS=true
     * és pontos origin lista kell (* nem lehet).
     */
    'allowed_origins' => $fromEnv !== [] ? $fromEnv : ['*'],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => filter_var(env('CORS_SUPPORTS_CREDENTIALS', false), FILTER_VALIDATE_BOOLEAN),

];
