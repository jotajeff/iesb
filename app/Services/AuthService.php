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

        if (($user['role'] ?? null) !== $expectedRole) {
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
            'role' => $user['role'],
            'type' => $user['role'],
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
        return is_array($user) && ($user['role'] ?? null) === $role;
    }

    public function isAdmin(): bool
    {
        return $this->checkRole('admin');
    }

    public function isOperador(): bool
    {
        return $this->checkRole('operador');
    }

    public function isProfessor(): bool
    {
        return $this->checkRole('professor');
    }

    public function isStaff(): bool
    {
        return $this->isAdmin() || $this->isOperador() || $this->isProfessor();
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

        $sessionRole = (string) ($sessionUser['role'] ?? $sessionUser['type'] ?? '');
        $storedRole = (string) ($user['role'] ?? $user['tipo'] ?? $user['type'] ?? '');
        $passwordHash = (string) ($user['password'] ?? $user['senha'] ?? '');

        if ($user === null || $storedRole !== $sessionRole || $passwordHash === '') {
            return false;
        }

        return password_verify($password, $passwordHash);
    }
}
