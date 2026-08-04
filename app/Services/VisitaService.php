<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\VisitaRepository;

final class VisitaService
{
    public function __construct(
        private readonly VisitaRepository $repository = new VisitaRepository(),
        private readonly IpLocationService $ipLocation = new IpLocationService(),
        private readonly UserAgentParserService $uaParser = new UserAgentParserService(),
    ) {
    }

    public function visits(int $limit = 100): array
    {
        $visits = $this->repository->byDate(date('Y-m-d'), $limit);
        foreach ($visits as &$visit) {
            $ip = (string) ($visit['ip'] ?? '');
            $visit['location'] = $this->ipLocation->resolve($ip);
            $visit['user_agent'] = $this->uaParser->parse((string) ($visit['user_agent'] ?? ''));

            $rawDate = (string) ($visit['data_visita'] ?? '');
            $visit['data_visita_formatada'] = $this->formatDate($rawDate);
        }

        return $visits;
    }

    public function visitsByMonthDaily(?int $month = null, ?int $year = null): array
    {
        $month = $this->sanitizeMonth($month);
        $year = $this->sanitizeYear($year);

        $rows = $this->repository->byDayInMonth($month, $year);
        $totalMonth = 0;
        $days = [];
        foreach ($rows as $row) {
            $total = (int) ($row['total'] ?? 0);
            $day = substr((string) ($row['data_visita'] ?? ''), -2);
            $dayInt = (int) $day;
            $totalMonth += $total;
            $days[] = [
                'day' => $dayInt,
                'total' => $total,
            ];
        }

        return [
            'month' => $month,
            'year' => $year,
            'month_label' => $this->monthNamePtBr($month),
            'total_month' => $totalMonth,
            'days' => $days,
        ];
    }

    public function visitsAnalytics(?int $month = null, ?int $year = null): array
    {
        $month = $this->sanitizeMonth($month);
        $year = $this->sanitizeYear($year);
        $visits = $this->repository->inMonthWithPage($month, $year);

        $countries = [];
        $cities = [];
        $devices = [];
        $systems = [];
        $browsers = [];

        foreach ($visits as $visit) {
            $location = $this->ipLocation->resolve((string) ($visit['ip'] ?? ''));
            $ua = $this->uaParser->parse((string) ($visit['user_agent'] ?? ''));

            $country = trim((string) ($location['country'] ?? '-'));
            $city = trim((string) ($location['city'] ?? '-'));
            $device = trim((string) ($ua['device'] ?? '-'));
            $os = trim((string) ($ua['os'] ?? '-'));
            $browser = trim((string) ($ua['browser'] ?? '-'));

            $this->incrementBucket($countries, $country !== '' ? $country : '-');
            $this->incrementBucket($cities, $city !== '' ? $city : '-');
            $this->incrementBucket($devices, $device !== '' ? $device : '-');
            $this->incrementBucket($systems, $os !== '' ? $os : '-');
            $this->incrementBucket($browsers, $browser !== '' ? $browser : '-');
        }

        return [
            'month' => $month,
            'year' => $year,
            'month_label' => $this->monthNamePtBr($month),
            'total' => count($visits),
            'countries' => $this->toDistribution($countries),
            'cities' => $this->toDistribution($cities),
            'devices' => $this->toDistribution($devices),
            'systems' => $this->toDistribution($systems),
            'browsers' => $this->toDistribution($browsers),
        ];
    }

    public function visitsByPage(?int $month = null, ?int $year = null): array
    {
        $month = $this->sanitizeMonth($month);
        $year = $this->sanitizeYear($year);
        $rows = $this->repository->pageTotalsInMonth($month, $year);
        $total = 0;
        foreach ($rows as $row) {
            $total += (int) ($row['total'] ?? 0);
        }

        $pages = [];
        foreach ($rows as $row) {
            $count = (int) ($row['total'] ?? 0);
            $pages[] = [
                'name' => (string) ($row['pagina_nome'] ?? '-'),
                'slug' => (string) ($row['pagina_slug'] ?? '-'),
                'total' => $count,
                'percent' => $total > 0 ? round(($count / $total) * 100, 1) : 0.0,
            ];
        }

        return [
            'month' => $month,
            'year' => $year,
            'month_label' => $this->monthNamePtBr($month),
            'total' => $total,
            'pages' => $pages,
        ];
    }

    public function visitsByCoursePages(): array
    {
        $rows = $this->repository->pageTotalsBySlugPrefix('curso/');
        $total = 0;
        foreach ($rows as $row) {
            $total += (int) ($row['total'] ?? 0);
        }

        $pages = [];
        foreach ($rows as $row) {
            $count = (int) ($row['total'] ?? 0);
            $pages[] = [
                'name' => (string) ($row['pagina_nome'] ?? '-'),
                'slug' => (string) ($row['pagina_slug'] ?? '-'),
                'total' => $count,
                'percent' => $total > 0 ? round(($count / $total) * 100, 1) : 0.0,
            ];
        }

        return [
            'total' => $total,
            'pages' => $pages,
        ];
    }

    public function refererStats(?int $month = null, ?int $year = null): array
    {
        $month = $this->sanitizeMonth($month);
        $year = $this->sanitizeYear($year);

        $rows = $this->repository->refererStats($month, $year);
        $total = 0;
        foreach ($rows as $row) {
            $total += (int) ($row['total'] ?? 0);
        }

        $referers = [];
        foreach ($rows as $row) {
            $count = (int) ($row['total'] ?? 0);
            $referer = (string) ($row['referer'] ?? '-');
            $referers[] = [
                'referer' => $referer,
                'domain' => $this->extractDomain($referer),
                'total' => $count,
                'percent' => $total > 0 ? round(($count / $total) * 100, 1) : 0.0,
            ];
        }

        return [
            'month' => $month,
            'year' => $year,
            'month_label' => $this->monthNamePtBr($month),
            'total' => $total,
            'referers' => $referers,
        ];
    }

    public function utmStats(?int $month = null, ?int $year = null): array
    {
        $month = $this->sanitizeMonth($month);
        $year = $this->sanitizeYear($year);

        $rows = $this->repository->utmStats($month, $year);
        $total = 0;
        foreach ($rows as $row) {
            $total += (int) ($row['total'] ?? 0);
        }

        $utms = [];
        foreach ($rows as $row) {
            $count = (int) ($row['total'] ?? 0);
            $utms[] = [
                'source' => (string) ($row['utm_source'] ?? '-'),
                'medium' => (string) ($row['utm_medium'] ?? '-'),
                'campaign' => (string) ($row['utm_campaign'] ?? '-'),
                'total' => $count,
                'percent' => $total > 0 ? round(($count / $total) * 100, 1) : 0.0,
            ];
        }

        return [
            'month' => $month,
            'year' => $year,
            'month_label' => $this->monthNamePtBr($month),
            'total' => $total,
            'utms' => $utms,
        ];
    }

    private function extractDomain(string $url): string
    {
        if ($url === '' || $url === '-') {
            return '-';
        }

        $parsed = parse_url($url);
        if (!isset($parsed['host'])) {
            return '-';
        }

        return $parsed['host'];
    }

    private function incrementBucket(array &$bucket, string $key): void
    {
        if (!isset($bucket[$key])) {
            $bucket[$key] = 0;
        }
        $bucket[$key]++;
    }

    private function toDistribution(array $bucket): array
    {
        arsort($bucket);
        $total = array_sum($bucket);
        $result = [];
        foreach ($bucket as $label => $count) {
            $result[] = [
                'label' => $label,
                'count' => (int) $count,
                'percent' => $total > 0 ? round(((int) $count / $total) * 100, 1) : 0.0,
            ];
        }

        return $result;
    }

    private function sanitizeMonth(?int $month): int
    {
        $month = $month ?? (int) date('m');
        if ($month < 1 || $month > 12) {
            return (int) date('m');
        }

        return $month;
    }

    private function sanitizeYear(?int $year): int
    {
        $year = $year ?? (int) date('Y');
        if ($year < 2000 || $year > 2100) {
            return (int) date('Y');
        }

        return $year;
    }

    private function monthNamePtBr(int $month): string
    {
        $months = [
            1 => 'Janeiro',
            2 => 'Fevereiro',
            3 => 'Marco',
            4 => 'Abril',
            5 => 'Maio',
            6 => 'Junho',
            7 => 'Julho',
            8 => 'Agosto',
            9 => 'Setembro',
            10 => 'Outubro',
            11 => 'Novembro',
            12 => 'Dezembro',
        ];

        return $months[$month] ?? 'Mes';
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
