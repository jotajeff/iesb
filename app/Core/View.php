<?php

declare(strict_types=1);

namespace App\Core;

final class View
{
    public static function render(string $view, array $data = [], string $layout = 'base'): void
    {
        extract($data, EXTR_SKIP);

        $viewPath = dirname(__DIR__) . "/Views/{$view}.php";
        $layoutPath = dirname(__DIR__) . "/Views/layouts/{$layout}.php";

        if (!is_file($viewPath)) {
            http_response_code(500);
            echo 'View não encontrada.';
            return;
        }

        if (!is_file($layoutPath)) {
            http_response_code(500);
            echo 'Layout não encontrado.';
            return;
        }

        require $layoutPath;
    }
}
