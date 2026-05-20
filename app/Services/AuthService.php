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
        if ($user === null) {
            return false;
        }

        if (($user['role'] ?? null) !== $expectedRole) {
            return false;
        }

        if (!password_verify($password, (string) $user['password'])) {
            return false;
        }

        Session::set('user', [
            'id' => (int) $user['id'],
            'name' => $user['name'],
            'email' => $user['email'],
            'role' => $user['role'],
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
}
