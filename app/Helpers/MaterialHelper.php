<?php

declare(strict_types=1);

namespace App\Helpers;

final class MaterialHelper
{
    public static function icon(string $tipo): string
    {
        return match ($tipo) {
            'video' => 'bi-camera-reels',
            'drive' => 'bi-google',
            'pdf' => 'bi-file-earmark-pdf',
            'arquivo' => 'bi-file-earmark',
            'link' => 'bi-link-45deg',
            'imagem' => 'bi-image',
            default => 'bi-file-earmark-text',
        };
    }
}
