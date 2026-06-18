<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\DashboardRepository;

final class DashboardService
{
    public function __construct(
        private readonly DashboardRepository $repository = new DashboardRepository(),
    ) {
    }

    public function indicators(?int $userId = null, bool $isAdmin = true): array
    {
        return $this->repository->indicators($userId, $isAdmin);
    }

    public function taskIndicators(int $userId, bool $isAdmin): array
    {
        return $this->repository->taskIndicators($userId, $isAdmin);
    }
}
