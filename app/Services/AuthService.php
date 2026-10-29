<?php

declare(strict_types=1);

namespace App\Services;


use App\Repositories\UserRepository;
use App\Support\Session;

final class AuthService
{
    public function __construct(private readonly UserRepository $users = new UserRepository())
    {
    }

    public function login(string $email, string $password, string $expectedRole): bool
    {
        $user = $this->users->findByEmail($email);
        error_log(sprintf('[DEBUG LOGIN] findByEmail for "%s": %s', $email, $user !== null ? 'encontrado role=' . ($user['role'] ?? '?') : 'null'));

        if ($user === null) {
            return false;
        }

        if (!in_array($expectedRole, (array) ($user['roles'] ?? [$user['role'] ?? '']), true)) {
            error_log(sprintf('[DEBUG LOGIN] role mismatch: esperado=%s, encontrado=%s', $expectedRole, $user['role'] ?? 'null'));
            return false;
        }

        if (!password_verify($password, (string) $user['password'])) {
            error_log(sprintf('[DEBUG LOGIN] password_verify FAILED for email=%s', $email));
            return false;
        }

        error_log(sprintf('[DEBUG LOGIN] LOGIN OK email=%s role=%s', $email, $user['role'] ?? ''));

        Session::set('user', [
            'id' => (int) $user['id'],
            'name' => $user['name'],
            'email' => $user['email'],
            'role' => $user['role'] ?? '',
            'type' => $user['role'] ?? '',
            'legacy_role' => $user['legacy_role'] ?? '',
            'roles' => $user['roles'] ?? [],
            'functions' => $user['functions'] ?? [],
            'permissions' => $user['permissions'] ?? [],
        ]);

        return true;
    }

    public function alunoLogin(string $email, string $password): bool
    {
        $repo = new \App\Repositories\AlunoRepository();
        $aluno = $repo->findByEmail($email);

        if ($aluno === null) {
            return false;
        }

        if (intval($aluno['ativo'] ?? 0) !== 1) {
            return false;
        }

        if (!password_verify($password, (string) $aluno['senha'])) {
            return false;
        }

        Session::set('user', [
            'id' => (int) $aluno['id'],
            'name' => $aluno['nome'],
            'email' => $aluno['email'],
            'role' => 'aluno',
            'type' => 'aluno',
        ]);

        return true;
    }

    public function logout(): void
    {
        Session::forget('user');
    }

    public function checkRole(string $role): bool
    {
        $user = Session::get('user');
        return is_array($user) && in_array($role, (array) ($user['roles'] ?? [$user['role'] ?? '']), true);
    }

    public function isAdmin(): bool
    {
        $user = Session::get('user');
        return is_array($user)
            && ($this->checkRole('admin') || strtolower((string) ($user['legacy_role'] ?? $user['type'] ?? '')) === 'admin');
    }

    public function isOperador(): bool
    {
        return $this->checkRole('operador') || $this->checkRole('secretaria');
    }

    public function isProfessor(): bool
    {
        return $this->checkRole('professor');
    }

    public function isStaff(): bool
    {
        $user = Session::get('user');
        if (!is_array($user)) {
            return false;
        }

        $roles = (array) ($user['roles'] ?? [$user['role'] ?? '']);
        $legacyRole = strtolower((string) ($user['legacy_role'] ?? $user['type'] ?? ''));

        return $legacyRole === 'admin'
            || (bool) array_intersect(
            ['admin', 'professor', 'coordenador', 'secretaria', 'operador'],
            $roles
        );
    }

    public function hasPermission(string $route, string $action = 'consultar'): bool
    {
        $user = Session::get('user');
        if (!is_array($user) || !$this->isStaff()) {
            return false;
        }

        // O administrador continua sendo superusuário durante a transição
        // dos vínculos legados para usuario_liga_funcao.
        if ($this->isAdmin()) {
            return true;
        }

        $route = '/' . trim(parse_url($route, PHP_URL_PATH) ?: $route, '/');
        $permissions = (array) ($user['permissions'] ?? []);
        if ($route === '/admin' && !isset($permissions['/admin'])) {
            $route = '/admin/dashboard';
        }
        $matched = null;
        $matchedLength = -1;
        foreach ($permissions as $moduleRoute => $permission) {
            $moduleRoute = '/' . trim((string) $moduleRoute, '/');
            if ($route === $moduleRoute || str_starts_with($route, $moduleRoute . '/')) {
                if (strlen($moduleRoute) > $matchedLength) {
                    $matched = (array) $permission;
                    $matchedLength = strlen($moduleRoute);
                }
            }
        }

        return $matched !== null && !empty($matched[$action]);
    }

    /**
     * Verifica apenas email + senha (sem exigir tipo/role).
     * Retorna os dados do usuário se as credenciais estiverem corretas, ou null.
     */
    public function authenticate(string $email, string $password): ?array
    {
        $user = $this->users->findByEmail($email);

        if ($user === null) {
            return null;
        }

        if (!password_verify($password, (string) $user['password'])) {
            return null;
        }

        return $user;
    }

    public function userCanAccessAdmin(array $user): bool
    {
        if (strtolower((string) ($user['legacy_role'] ?? '')) === 'admin'
            || in_array('admin', (array) ($user['roles'] ?? []), true)) {
            return true;
        }

        $hasStaffFunction = (bool) array_intersect(
            ['admin', 'professor', 'coordenador', 'secretaria', 'operador'],
            (array) ($user['roles'] ?? [])
        );

        $hasRoutePermission = false;
        foreach ((array) ($user['permissions'] ?? []) as $route => $permission) {
            if (str_starts_with((string) $route, '/admin') && !empty($permission['consultar'])) {
                $hasRoutePermission = true;
                break;
            }
        }

        return $hasStaffFunction && $hasRoutePermission;
    }

    public function userCanAccessAdminById(int $id): bool
    {
        $user = $id > 0 ? $this->users->findById($id) : null;
        return is_array($user) && $this->userCanAccessAdmin($user);
    }

    public function adminLandingPath(array $user): string
    {
        $permissions = (array) ($user['permissions'] ?? []);
        if (!empty($permissions['/admin']['consultar'])) {
            return '/admin';
        }
        if (!empty($permissions['/admin/dashboard']['consultar'])) {
            return '/admin/dashboard';
        }

        foreach ($permissions as $route => $permission) {
            if (!empty($permission['consultar']) && str_starts_with((string) $route, '/admin')) {
                return (string) $route;
            }
        }

        return '/admin';
    }

    public function reautenticarSessao(string $password): bool
    {
        $sessionUser = Session::get('user');
        if (!is_array($sessionUser) || !$this->isStaff() || trim($password) === '') {
            return false;
        }

        // O e-mail é estável entre as bases (produção/Sandbox); o ID pode mudar.
        $email = trim((string) ($sessionUser['email'] ?? ''));
        $user = $email !== '' ? $this->users->findByEmail($email) : null;

        if ($user === null) {
            $userId = (int) ($sessionUser['id'] ?? 0);
            $user = $userId > 0 ? $this->users->findById($userId) : null;
        }

        $sessionRoles = (array) ($sessionUser['roles'] ?? [$sessionUser['role'] ?? $sessionUser['type'] ?? '']);
        $storedRoles = (array) ($user['roles'] ?? []);
        $passwordHash = (string) ($user['password'] ?? $user['senha'] ?? '');

        if ($user === null || !array_intersect($sessionRoles, $storedRoles) || $passwordHash === '') {
            return false;
        }

        return password_verify($password, $passwordHash);
    }
}
