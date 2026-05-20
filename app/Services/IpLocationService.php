<?php

declare(strict_types=1);

namespace App\Services;

final class IpLocationService
{
    private const CACHE_TTL = 86400;

    public function resolve(string $ip): array
    {
        if (!$this->isPublicIp($ip)) {
            return [
                'country' => 'Local/Privado',
                'city' => '-',
                'country_code' => '',
                'flag' => '🏠',
            ];
        }

        $cached = $this->readCache($ip);
        if ($cached !== null) {
            return $cached;
        }

        $location = $this->fetchLocation($ip);
        $this->writeCache($ip, $location);

        return $location;
    }

    private function fetchLocation(string $ip): array
    {
        $url = 'http://ip-api.com/json/' . rawurlencode($ip) . '?fields=status,country,countryCode,city';
        $context = stream_context_create([
            'http' => [
                'method' => 'GET',
                'timeout' => 2,
            ],
        ]);

        $raw = @file_get_contents($url, false, $context);
        if (!is_string($raw) || $raw === '') {
            return $this->unknown();
        }

        $data = json_decode($raw, true);
        if (!is_array($data) || ($data['status'] ?? '') !== 'success') {
            return $this->unknown();
        }

        $countryCode = strtoupper((string) ($data['countryCode'] ?? ''));
        return [
            'country' => (string) ($data['country'] ?? '-'),
            'city' => (string) ($data['city'] ?? '-'),
            'country_code' => $countryCode,
            'flag' => $this->flagFromCountryCode($countryCode),
        ];
    }

    private function flagFromCountryCode(string $countryCode): string
    {
        if (strlen($countryCode) !== 2) {
            return '🏳️';
        }

        $first = mb_ord($countryCode[0]) - 65 + 127462;
        $second = mb_ord($countryCode[1]) - 65 + 127462;

        if ($first < 127462 || $second < 127462) {
            return '🏳️';
        }

        return mb_chr($first) . mb_chr($second);
    }

    private function unknown(): array
    {
        return [
            'country' => 'Não identificado',
            'city' => '-',
            'country_code' => '',
            'flag' => '🏳️',
        ];
    }

    private function isPublicIp(string $ip): bool
    {
        return filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false;
    }

    private function cacheFile(string $ip): string
    {
        return dirname(__DIR__, 2) . '/storage/cache/ip_geo_' . md5($ip) . '.json';
    }

    private function readCache(string $ip): ?array
    {
        $file = $this->cacheFile($ip);
        if (!is_file($file)) {
            return null;
        }

        $raw = @file_get_contents($file);
        if (!is_string($raw) || $raw === '') {
            return null;
        }

        $payload = json_decode($raw, true);
        if (!is_array($payload)) {
            return null;
        }

        $expiresAt = (int) ($payload['expires_at'] ?? 0);
        if ($expiresAt < time()) {
            return null;
        }

        $location = $payload['location'] ?? null;
        return is_array($location) ? $location : null;
    }

    private function writeCache(string $ip, array $location): void
    {
        $file = $this->cacheFile($ip);
        $payload = [
            'expires_at' => time() + self::CACHE_TTL,
            'location' => $location,
        ];

        @file_put_contents($file, json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    }
}
