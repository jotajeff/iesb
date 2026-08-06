<?php

declare(strict_types=1);

namespace App\Helpers;

final class WhatsAppHelper
{
    private const ICON_COLOR = '#128C7E';

    public static function onlyDigits(string $value): string
    {
        return preg_replace('/\D/', '', $value);
    }

    /**
     * Formata o número no padrão (xx) xxxx.xxxx ou (xx) xxxxx.xxxx.
     */
    public static function format(string $value): string
    {
        $digits = self::onlyDigits($value);

        if (strlen($digits) === 10) {
            return sprintf(
                '(%s)%s.%s',
                substr($digits, 0, 2),
                substr($digits, 2, 4),
                substr($digits, 6, 4)
            );
        }

        if (strlen($digits) === 11) {
            return sprintf(
                '(%s)%s.%s',
                substr($digits, 0, 2),
                substr($digits, 2, 5),
                substr($digits, 7, 4)
            );
        }

        if (strlen($digits) === 8) {
            return sprintf('%s.%s', substr($digits, 0, 4), substr($digits, 4, 4));
        }

        if (strlen($digits) === 9) {
            return sprintf('%s.%s', substr($digits, 0, 5), substr($digits, 5, 4));
        }

        return $value;
    }

    public static function icon(string $extraClass = ''): string
    {
        $class = trim('bi bi-whatsapp ' . $extraClass);
        return '<i class="' . trim($class) . '" style="color:' . self::ICON_COLOR . ';"></i>';
    }
}
