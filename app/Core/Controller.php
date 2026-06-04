<?php

declare(strict_types=1);

namespace App\Core;

use App\Services\AdminService;
use App\Support\Session;

abstract class Controller
{
    protected function render(string $view, array $data = [], string $layout = 'base'): void
    {
        $shared = [
            'authUser' => Session::get('user'),
            'flash' => Session::getFlash('flash'),
            'niveisMenu' => (new AdminService())->niveis(),
            'nivelSelecionado' => Session::get('nivel_selecionado'),
        ];

        View::render($view, array_merge($shared, $data), $layout);
    }

    protected function redirect(string $path): void
    {
        header('Location: ' . $path);
        exit;
    }

    protected function input(string $key, mixed $default = null): mixed
    {
        return $_POST[$key] ?? $default;
    }
}
