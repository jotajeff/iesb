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

    public function listByTipoPaginated(string $tipo, int $limit, int $offset): array
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return ['data' => [], 'total' => 0];
        }

        $countStmt = $pdo->prepare('SELECT COUNT(*) FROM usuarios WHERE tipo = :tipo');
        $countStmt->bindValue(':tipo', $tipo, PDO::PARAM_STR);
        $countStmt->execute();
        $total = (int) $countStmt->fetchColumn();

        $stmt = $pdo->prepare(
            'SELECT id, nome, email, telefone, tipo, ativo, created_at, updated_at
             FROM usuarios
             WHERE tipo = :tipo
             ORDER BY nome ASC, id ASC
             LIMIT :limit OFFSET :offset'
        );
        $stmt->bindValue(':tipo', $tipo, PDO::PARAM_STR);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        $rows = $stmt->fetchAll();
        return [
            'data' => is_array($rows) ? $rows : [],
            'total' => $total,
        ];
    }

    public function findById(int $id): ?array
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return null;
        }

        $sql = 'SELECT id, nome, email, telefone, titulacao, tipo, ativo, created_at, updated_at
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

        $sql = 'INSERT INTO usuarios (nome, email, senha, tipo, telefone, titulacao, ativo)
                VALUES (:nome, :email, :senha, :tipo, :telefone, :titulacao, :ativo)';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':nome', $payload['nome']);
        $stmt->bindValue(':email', $payload['email']);
        $stmt->bindValue(':senha', $payload['senha']);
        $stmt->bindValue(':tipo', $payload['tipo']);
        $stmt->bindValue(':telefone', $payload['telefone'] ?? null, PDO::PARAM_STR);
        $stmt->bindValue(':titulacao', $payload['titulacao'] ?? null, PDO::PARAM_STR);
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
        if (array_key_exists('titulacao', $payload)) {
            $sets[] = 'titulacao = :titulacao';
            $params[':titulacao'] = $payload['titulacao'];
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

    public function findByEmailStaff(string $email): ?array
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return null;
        }

        $sql = 'SELECT id, nome, email, senha, tipo, ativo, reset_token, reset_token_expires
                FROM usuarios
                WHERE email = :email
                LIMIT 1';
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['email' => $email]);
        $row = $stmt->fetch();

        return is_array($row) ? $row : null;
    }

    public function saveResetToken(int $id, string $token, string $expires): void
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return;
        }

        $sql = 'UPDATE usuarios SET reset_token = :token, reset_token_expires = :expires WHERE id = :id';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':token', $token);
        $stmt->bindValue(':expires', $expires);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
    }

    public function findByResetToken(string $token): ?array
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return null;
        }

        $sql = 'SELECT id, nome, email, tipo, ativo, reset_token, reset_token_expires
                FROM usuarios
                WHERE reset_token = :token
                LIMIT 1';
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['token' => $token]);
        $row = $stmt->fetch();

        if (!is_array($row)) {
            return null;
        }

        $expires = (string) ($row['reset_token_expires'] ?? '');
        if ($expires === '' || strtotime($expires) < time()) {
            return null;
        }

        return $row;
    }

    public function updateSenha(int $id, string $senhaHash): void
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return;
        }

        $sql = 'UPDATE usuarios SET senha = :senha, reset_token = NULL, reset_token_expires = NULL WHERE id = :id';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':senha', $senhaHash);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
    }

    public function clearResetToken(int $id): void
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return;
        }

        $sql = 'UPDATE usuarios SET reset_token = NULL, reset_token_expires = NULL WHERE id = :id';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
    }
}
