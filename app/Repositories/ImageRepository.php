<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use PDO;

final class ImageRepository
{
    public function listByFk(string $tabelaFk, int $idFk): array
    {
        try {
            $pdo = Database::connection();
            if (!$pdo instanceof PDO) {
                return [];
            }

            $stmt = $pdo->prepare('SELECT id, path, legenda, ativo, created_at FROM imagem WHERE tabela_fk = :tabela_fk AND id_fk = :id_fk ORDER BY created_at DESC');
            $stmt->bindValue(':tabela_fk', $tabelaFk);
            $stmt->bindValue(':id_fk', $idFk, PDO::PARAM_INT);
            $stmt->execute();
            $rows = $stmt->fetchAll();
            return is_array($rows) ? $rows : [];
        } catch (\Throwable $e) {
            error_log('[IMAGE] Erro ao listar: ' . $e->getMessage());
            return [];
        }
    }

    public function create(string $tabelaFk, int $idFk, string $path, ?string $legenda = null): int
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return 0;
        }

        try {
            $stmt = $pdo->prepare('INSERT INTO imagem (tabela_fk, id_fk, path, legenda) VALUES (:tabela_fk, :id_fk, :path, :legenda)');
            $stmt->bindValue(':tabela_fk', $tabelaFk);
            $stmt->bindValue(':id_fk', $idFk, PDO::PARAM_INT);
            $stmt->bindValue(':path', $path);
            $stmt->bindValue(':legenda', $legenda);
            $stmt->execute();
            return (int) $pdo->lastInsertId();
        } catch (\Throwable $e) {
            error_log('[IMAGE] Erro ao criar: ' . $e->getMessage());
            return 0;
        }
    }

    public function findById(int $id): ?array
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return null;
        }

        try {
            $stmt = $pdo->prepare('SELECT id, path, legenda, tabela_fk, id_fk FROM imagem WHERE id = :id LIMIT 1');
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $row = $stmt->fetch();
            return $row ?: null;
        } catch (\Throwable $e) {
            error_log('[IMAGE] Erro em findById: ' . $e->getMessage());
            return null;
        }
    }

    public function delete(int $id): void
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return;
        }

        try {
            $stmt = $pdo->prepare('DELETE FROM imagem WHERE id = :id');
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
        } catch (\Throwable $e) {
            error_log('[IMAGE] Erro ao deletar: ' . $e->getMessage());
        }
    }
}
