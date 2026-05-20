<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use PDO;

final class UserRepository extends JsonRepository
{
    public function __construct()
    {
        parent::__construct(dirname(__DIR__, 2) . '/storage/users.json');
    }

    public function findByEmail(string $email): ?array
    {
        $fromDatabase = $this->findByEmailInDatabase($email);
        if ($fromDatabase !== null) {
            return $fromDatabase;
        }

        foreach ($this->allData() as $user) {
            if (mb_strtolower((string) $user['email']) === mb_strtolower($email)) {
                return $user;
            }
        }

        return null;
    }

    public function findById(int $id): ?array
    {
        $fromDatabase = $this->findByIdInDatabase($id);
        if ($fromDatabase !== null) {
            return $fromDatabase;
        }

        foreach ($this->allData() as $user) {
            if ((int) $user['id'] === $id) {
                return $user;
            }
        }

        return null;
    }

    private function findByEmailInDatabase(string $email): ?array
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return null;
        }

        $sql = 'SELECT id, nome, email, senha, tipo, ativo FROM usuarios WHERE email = :email LIMIT 1';
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['email' => $email]);
        $row = $stmt->fetch();

        if (!is_array($row) || (int) ($row['ativo'] ?? 0) !== 1) {
            return null;
        }

        return [
            'id' => (int) $row['id'],
            'name' => (string) $row['nome'],
            'email' => (string) $row['email'],
            'password' => (string) $row['senha'],
            'role' => (string) $row['tipo'],
        ];
    }

    private function findByIdInDatabase(int $id): ?array
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return null;
        }

        $sql = 'SELECT id, nome, email, senha, tipo, ativo FROM usuarios WHERE id = :id LIMIT 1';
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();

        if (!is_array($row) || (int) ($row['ativo'] ?? 0) !== 1) {
            return null;
        }

        return [
            'id' => (int) $row['id'],
            'name' => (string) $row['nome'],
            'email' => (string) $row['email'],
            'password' => (string) $row['senha'],
            'role' => (string) $row['tipo'],
        ];
    }
}
