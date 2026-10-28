<?php

declare(strict_types=1);

namespace App\Core;

use App\Services\AuthService;
use App\Support\Session;

final class Router
{
    private array $routes = [];
    private array $paramRoutes = [];

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
        $normalized = $this->normalizePath($path);

        if (str_contains($normalized, '{')) {
            $regex = preg_replace('/\{(\w+)\}/', '(?P<$1>[^/]+)', $normalized);
            $this->paramRoutes[$method][] = ['regex' => '#^' . $regex . '$#', 'handler' => $handler, 'path' => $normalized];
        } else {
            $this->routes[$method][$normalized] = $handler;
        }
    }

    public function dispatch(string $method, string $uri): void
    {
        $path = $this->normalizePath(parse_url($uri, PHP_URL_PATH) ?? '/');
        $handler = $this->routes[$method][$path] ?? null;

        if ($handler === null) {
            $params = [];
            $paramRoutes = $this->paramRoutes[$method] ?? [];

            foreach ($paramRoutes as $pr) {
                if (preg_match($pr['regex'], $path, $matches)) {
                    $handler = $pr['handler'];
                    foreach ($matches as $key => $value) {
                        if (is_string($key)) {
                            $_GET[$key] = $value;
                        }
                    }
                    break;
                }
            }
        }

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

        if (str_starts_with($path, '/admin') && !$this->isPublicAdminPath($path)) {
            $auth = new AuthService();
            if (!$auth->isStaff()) {
                Session::setFlash('flash', 'Faça login para acessar o painel.');
                header('Location: /admin/login');
                return;
            }

            $action = $this->permissionAction($method, $path);
            if (!$auth->hasPermission($path, $action)) {
                http_response_code(403);
                Session::setFlash('flash', 'Você não tem permissão para acessar este recurso.');
                echo 'Acesso negado.';
                return;
            }
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

    private function isPublicAdminPath(string $path): bool
    {
        return in_array($path, [
            '/admin/login',
            '/admin/solicitar-redefinicao',
            '/admin/redefinir-senha',
        ], true);
    }

    private function permissionAction(string $method, string $path): string
    {
        if ($method !== 'POST') {
            return 'consultar';
        }

        $lastSegment = strtolower((string) basename($path));
        if (preg_match('/(excluir|deletar|remover|cancelar|desativar|alternar)/', $lastSegment)) {
            return 'excluir';
        }
        if (preg_match('/(atualizar|editar|update)/', $lastSegment)) {
            return 'editar';
        }
        if (preg_match('/(salvar|novo|criar|enviar|upload|gerar|registrar|assumir|responder|marcar|compartilhar|migrar|reenviar)/', $lastSegment)) {
            return 'inserir';
        }

        return 'editar';
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
