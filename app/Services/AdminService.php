<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\AdminRepository;

final class AdminService
{
    public function __construct(
        private readonly AdminRepository $repository = new AdminRepository(),
        private readonly IpLocationService $ipLocation = new IpLocationService(),
        private readonly UserAgentParserService $uaParser = new UserAgentParserService(),
    ) {
    }

    public function indicators(): array
    {
        return $this->repository->dashboardIndicators();
    }

    public function logs(int $limit = 50): array
    {
        return $this->repository->recentLogs($limit);
    }

    public function visits(int $limit = 100): array
    {
        $visits = $this->repository->recentVisits($limit);
        foreach ($visits as &$visit) {
            $ip = (string) ($visit['ip'] ?? '');
            $visit['location'] = $this->ipLocation->resolve($ip);
            $visit['user_agent'] = $this->uaParser->parse((string) ($visit['user_agent'] ?? ''));

            $rawDate = (string) ($visit['data_visita'] ?? '');
            $visit['data_visita_formatada'] = $this->formatDate($rawDate);
        }

        return $visits;
    }

    private function formatDate(string $date): string
    {
        if ($date === '') {
            return '-';
        }

        $dt = \DateTime::createFromFormat('Y-m-d', $date);
        if ($dt instanceof \DateTime) {
            return $dt->format('d/m/Y');
        }

        return $date;
    }
}
