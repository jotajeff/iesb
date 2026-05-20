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

        $this->visitTracker->track($method, $uri);
        $this->router->dispatch($method, $uri);
    }
}
