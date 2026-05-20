<?php

declare(strict_types=1);

return [
    'app_name' => getenv('APP_NAME') ?: 'IESB MVC',
    'app_env' => getenv('APP_ENV') ?: 'development',
    'app_debug' => filter_var(getenv('APP_DEBUG') ?: true, FILTER_VALIDATE_BOOL),
    'app_url' => getenv('APP_URL') ?: 'http://localhost:8000',
];
