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

        $sql = 'SELECT id, nome, email, tipo, ativo, created_at, updated_at
                FROM usuarios
                ORDER BY id DESC
                LIMIT :limit';
        $stmt = $pdo->prepare($sql);
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

        $sql = 'SELECT id, nome, email, tipo, ativo, created_at, updated_at
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

        $sql = 'INSERT INTO usuarios (nome, email, senha, tipo, ativo)
                VALUES (:nome, :email, :senha, :tipo, :ativo)';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':nome', $payload['nome']);
        $stmt->bindValue(':email', $payload['email']);
        $stmt->bindValue(':senha', $payload['senha']);
        $stmt->bindValue(':tipo', $payload['tipo']);
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

        if (empty($sets)) {
            return;
        }

        $sql = 'UPDATE usuarios SET ' . implode(', ', $sets) . ' WHERE id = :id';
        $stmt = $pdo->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }
        $stmt->execute();
    }
}
