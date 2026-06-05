<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use PDO;

final class CommentRepository
{
    public function countByTableAndId(string $table, int $id): int
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO || $id <= 0 || trim($table) === '') {
            return 0;
        }

        try {
            $sql = 'SELECT COUNT(*) FROM comentarios WHERE tabela_fg = :tabela_fg AND id_fg = :id_fg';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':tabela_fg', $table);
            $stmt->bindValue(':id_fg', $id, PDO::PARAM_INT);
            $stmt->execute();

            return (int) $stmt->fetchColumn();
        } catch (\Throwable $e) {
            error_log('[COMENTARIOS] Erro ao contar comentários: ' . $e->getMessage());
            return 0;
        }
    }

    public function listByTableAndId(string $table, int $id): array
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO || $id <= 0 || trim($table) === '') {
            return [];
        }

        try {
            $sql = 'SELECT c.id, c.tabela_fg, c.id_fg, c.comentario, c.criado_em
                    FROM comentarios c
                    WHERE c.tabela_fg = :tabela_fg AND c.id_fg = :id_fg
                    ORDER BY c.id ASC';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':tabela_fg', $table);
            $stmt->bindValue(':id_fg', $id, PDO::PARAM_INT);
            $stmt->execute();

            $rows = $stmt->fetchAll();
            return is_array($rows) ? $rows : [];
        } catch (\Throwable $e) {
            error_log('[COMENTARIOS] Erro ao listar comentários: ' . $e->getMessage());
            return [];
        }
    }

    public function create(string $table, int $id, string $comentario): int
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO || $id <= 0 || trim($table) === '' || trim($comentario) === '') {
            return 0;
        }

        try {
            $sql = 'INSERT INTO comentarios (tabela_fg, id_fg, comentario)
                    VALUES (:tabela_fg, :id_fg, :comentario)';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':tabela_fg', $table);
            $stmt->bindValue(':id_fg', $id, PDO::PARAM_INT);
            $stmt->bindValue(':comentario', trim($comentario));
            $stmt->execute();

            return (int) $pdo->lastInsertId();
        } catch (\Throwable $e) {
            error_log('[COMENTARIOS] Erro ao criar comentário: ' . $e->getMessage());
            return 0;
        }
    }
}
