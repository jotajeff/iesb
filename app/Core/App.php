<?php

declare(strict_types=1);

namespace App\Core;

use App\Services\VisitTrackerService;

final class App
{
    public function __construct(
        private readonly Router $router,
        private readonly VisitTrackerService $visitTracker = new VisitTrackerService(),
    )
    {
    }

    public function run(): void
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $uri = $_SERVER['REQUEST_URI'] ?? '/';

        if ($method === 'OPTIONS' && str_starts_with($uri, '/api/')) {
            header('Access-Control-Allow-Origin: https://www.magdabrazilcursos.com.br');
            header('Access-Control-Allow-Methods: GET, OPTIONS');
            header('Access-Control-Allow-Headers: Content-Type');
            header('Access-Control-Max-Age: 86400');
            http_response_code(204);
            return;
        }

        $this->visitTracker->track($method, $uri);
        $this->router->dispatch($method, $uri);
    }
}
