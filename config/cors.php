<?php


return [
    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    'allowed_origins' => ['*'], // Temporarily allow all (or list your frontend IPs for more security)

    'allowed_headers' => ['*'],

    'supports_credentials' => true,
];
