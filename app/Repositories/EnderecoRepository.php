<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use PDO;

final class EnderecoRepository
{
    public function findByTipoAndFk(string $tipo, int $idFk): ?array
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return null;
        }

        $sql = 'SELECT id, tipo, id_fk, cep, logradouro, numero, cidade, uf
                FROM endereco
                WHERE tipo = :tipo AND id_fk = :id_fk
                LIMIT 1';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':tipo', $tipo, PDO::PARAM_STR);
        $stmt->bindValue(':id_fk', $idFk, PDO::PARAM_INT);
        $stmt->execute();

        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function create(array $data): int
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return 0;
        }

        $sql = 'INSERT INTO endereco (tipo, id_fk, cep, logradouro, numero, cidade, uf)
                VALUES (:tipo, :id_fk, :cep, :logradouro, :numero, :cidade, :uf)';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':tipo', $data['tipo'], PDO::PARAM_STR);
        $stmt->bindValue(':id_fk', $data['id_fk'], PDO::PARAM_INT);
        $stmt->bindValue(':cep', $data['cep'], PDO::PARAM_STR);
        $stmt->bindValue(':logradouro', $data['logradouro'], PDO::PARAM_STR);
        $stmt->bindValue(':numero', $data['numero'] ?? null, PDO::PARAM_STR);
        $stmt->bindValue(':cidade', $data['cidade'], PDO::PARAM_STR);
        $stmt->bindValue(':uf', $data['uf'], PDO::PARAM_STR);
        $stmt->execute();

        return (int) $pdo->lastInsertId();
    }

    public function update(int $id, array $data): void
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return;
        }

        $sets = [];
        $params = [':id' => $id];

        foreach (['cep', 'logradouro', 'numero', 'cidade', 'uf'] as $field) {
            if (array_key_exists($field, $data)) {
                $sets[] = "$field = :$field";
                $params[":$field"] = $data[$field];
            }
        }

        if (empty($sets)) {
            return;
        }

        $sql = 'UPDATE endereco SET ' . implode(', ', $sets) . ' WHERE id = :id';
        $stmt = $pdo->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }
        $stmt->execute();
    }
}
