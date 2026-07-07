<?php

return [
    'paths' => ['api/*', 'sanctum/csrf-cookie'],
    'allowed_methods' => ['*'],
    'allowed_origins' => array_filter([
        env('FRONTEND_URL'),
        'http://localhost:5173',
        'http://127.0.0.1:5173',
        'https://cits-frontend-mu.vercel.app',
    ], fn($value) => !empty($value)),
    'allowed_origins_patterns' => [
        // Allow all Render, Vercel, and Netlify deployments
        '#^https://.*\.onrender\.com$#',
        '#^https://.*\.vercel\.app$#',
        '#^https://.*\.netlify\.app$#',
    ],
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => true,
];
