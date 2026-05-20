<?php

declare(strict_types=1);

namespace App\Support;

final class UiIconHelper
{
    public static function device(string $device): string
    {
        return match ($device) {
            'Mobile' => 'bi-phone',
            'Tablet' => 'bi-tablet',
            default => 'bi-laptop',
        };
    }

    public static function os(string $os): string
    {
        return match ($os) {
            'Windows' => 'bi-windows',
            'macOS' => 'bi-apple',
            'Linux' => 'bi-terminal',
            'Android' => 'bi-android2',
            'iOS' => 'bi-phone',
            default => 'bi-cpu',
        };
    }

    public static function browser(string $browser): string
    {
        return match ($browser) {
            'Chrome' => 'bi-browser-chrome',
            'Firefox' => 'bi-browser-firefox',
            'Safari' => 'bi-browser-safari',
            'Edge' => 'bi-browser-edge',
            'Opera' => 'bi-browser-chrome',
            default => 'bi-globe2',
        };
    }
}
