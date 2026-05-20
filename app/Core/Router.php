<?php

declare(strict_types=1);

namespace App\Core;

final class Router
{
    private array $routes = [];

    public function get(string $path, callable|array $handler): void
    {
        $this->addRoute('GET', $path, $handler);
    }

    public function post(string $path, callable|array $handler): void
    {
        $this->addRoute('POST', $path, $handler);
    }

    private function addRoute(string $method, string $path, callable|array $handler): void
    {
        $this->routes[$method][$this->normalizePath($path)] = $handler;
    }

    public function dispatch(string $method, string $uri): void
    {
        $path = $this->normalizePath(parse_url($uri, PHP_URL_PATH) ?? '/');
        $handler = $this->routes[$method][$path] ?? null;

        if ($handler === null) {
            http_response_code(404);
            if (str_starts_with($path, '/admin')) {
                View::render(
                    'pages/admin/404',
                    ['title' => 'Página não encontrada', 'currentRoute' => $path],
                    'admin'
                );
            } else {
                View::render('pages/404', ['title' => 'Página não encontrada', 'currentRoute' => $path]);
            }
            return;
        }

        if (is_callable($handler)) {
            $handler();
            return;
        }

        [$controllerClass, $action] = $handler;
        if (!class_exists($controllerClass) || !method_exists($controllerClass, $action)) {
            http_response_code(500);
            echo 'Rota inválida.';
            return;
        }

        $controller = new $controllerClass();
        $controller->{$action}();
    }

    private function normalizePath(string $path): string
    {
        if ($path === '') {
            return '/';
        }

        $normalized = '/' . trim($path, '/');
        return $normalized === '/index.php' ? '/' : $normalized;
    }
}
