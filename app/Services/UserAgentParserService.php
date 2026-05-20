<?php

declare(strict_types=1);

namespace App\Services;

final class UserAgentParserService
{
    public function parse(string $ua): array
    {
        $device = $this->detectDevice($ua);
        $os = $this->detectOs($ua);
        $browser = $this->detectBrowser($ua);

        return [
            'device' => $device,
            'os' => $os,
            'browser' => $browser,
            'summary' => sprintf('%s - %s - %s', $device, $os, $browser),
        ];
    }

    private function detectDevice(string $ua): string
    {
        $lower = mb_strtolower($ua);
        if (str_contains($lower, 'mobile') || str_contains($lower, 'iphone') || str_contains($lower, 'android')) {
            return 'Mobile';
        }

        if (str_contains($lower, 'ipad') || str_contains($lower, 'tablet')) {
            return 'Tablet';
        }

        return 'Desktop';
    }

    private function detectOs(string $ua): string
    {
        $map = [
            'Windows' => 'windows',
            'macOS' => 'mac os',
            'Linux' => 'linux',
            'Android' => 'android',
            'iOS' => 'iphone',
        ];

        $lower = mb_strtolower($ua);
        foreach ($map as $name => $pattern) {
            if (str_contains($lower, $pattern)) {
                return $name;
            }
        }

        return 'Sistema desconhecido';
    }

    private function detectBrowser(string $ua): string
    {
        $lower = mb_strtolower($ua);

        if (str_contains($lower, 'edg/')) {
            return 'Edge';
        }

        if (str_contains($lower, 'opr/') || str_contains($lower, 'opera')) {
            return 'Opera';
        }

        if (str_contains($lower, 'firefox/')) {
            return 'Firefox';
        }

        if (str_contains($lower, 'chrome/') && !str_contains($lower, 'edg/')) {
            return 'Chrome';
        }

        if (str_contains($lower, 'safari/') && !str_contains($lower, 'chrome/')) {
            return 'Safari';
        }

        return 'Navegador desconhecido';
    }
}
