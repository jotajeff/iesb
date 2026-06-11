<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use PDO;

final class UsuarioRepository
{
    public function list(int $limit = 200): array
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return [];
        }

        $sql = 'SELECT id, nome, email, telefone, tipo, ativo, created_at, updated_at
                FROM usuarios
                ORDER BY nome ASC
                LIMIT :limit';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        $rows = $stmt->fetchAll();
        return is_array($rows) ? $rows : [];
    }

    public function listByTipo(string $tipo, int $limit = 200): array
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return [];
        }

        $sql = 'SELECT id, nome, email, telefone, tipo, ativo, created_at, updated_at
                FROM usuarios
                WHERE tipo = :tipo
                ORDER BY id DESC
                LIMIT :limit';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':tipo', $tipo, PDO::PARAM_STR);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        $rows = $stmt->fetchAll();
        return is_array($rows) ? $rows : [];
    }

    public function findById(int $id): ?array
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return null;
        }

        $sql = 'SELECT id, nome, email, telefone, tipo, ativo, created_at, updated_at
                FROM usuarios
                WHERE id = :id';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function create(array $payload): int
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return 0;
        }

        $sql = 'INSERT INTO usuarios (nome, email, senha, tipo, telefone, ativo)
                VALUES (:nome, :email, :senha, :tipo, :telefone, :ativo)';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':nome', $payload['nome']);
        $stmt->bindValue(':email', $payload['email']);
        $stmt->bindValue(':senha', $payload['senha']);
        $stmt->bindValue(':tipo', $payload['tipo']);
        $stmt->bindValue(':telefone', $payload['telefone'] ?? null, PDO::PARAM_STR);
        $stmt->bindValue(':ativo', (int) $payload['ativo'], PDO::PARAM_INT);
        $stmt->execute();

        return (int) $pdo->lastInsertId();
    }

    public function update(int $id, array $payload): void
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return;
        }

        $sets = [];
        $params = [':id' => $id];

        if (isset($payload['nome'])) {
            $sets[] = 'nome = :nome';
            $params[':nome'] = $payload['nome'];
        }
        if (isset($payload['email'])) {
            $sets[] = 'email = :email';
            $params[':email'] = $payload['email'];
        }
        if (isset($payload['senha'])) {
            $sets[] = 'senha = :senha';
            $params[':senha'] = $payload['senha'];
        }
        if (isset($payload['tipo'])) {
            $sets[] = 'tipo = :tipo';
            $params[':tipo'] = $payload['tipo'];
        }
        if (isset($payload['ativo'])) {
            $sets[] = 'ativo = :ativo';
            $params[':ativo'] = (int) $payload['ativo'];
        }
        if (array_key_exists('telefone', $payload)) {
            $sets[] = 'telefone = :telefone';
            $params[':telefone'] = $payload['telefone'];
        }

        if (empty($sets)) {
            return;
        }

        $sql = 'UPDATE usuarios SET ' . implode(', ', $sets) . ' WHERE id = :id';
        $stmt = $pdo->prepare($sql);
        foreach ($params as $key => $value) {
            $paramType = PDO::PARAM_STR;
            if ($value === null) {
                $paramType = PDO::PARAM_NULL;
            } elseif (is_int($value)) {
                $paramType = PDO::PARAM_INT;
            }
            $stmt->bindValue($key, $value, $paramType);
        }
        $stmt->execute();
    }
}
