<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use PDO;

final class LinkRepository
{
    public function ensureSchema(): void
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return;
        }

        try {
            $pdo->exec(
                'CREATE TABLE IF NOT EXISTS `link` ('
                . ' `id` INT NOT NULL AUTO_INCREMENT,'
                . ' `titulo` VARCHAR(256) NOT NULL,'
                . ' `link` TEXT NOT NULL,'
                . ' `id_fk` INT NOT NULL,'
                . ' `id_disciplina` INT NOT NULL DEFAULT 0,'
                . ' `extra` TINYINT(1) NOT NULL DEFAULT 0,'
                . ' `ativo` TINYINT(1) NOT NULL DEFAULT 1,'
                . ' `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,'
                . ' `updated_at` DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,'
                . ' PRIMARY KEY (`id`),'
                . ' KEY `idx_link_id_fk` (`id_fk`),'
                . ' KEY `idx_link_id_disciplina` (`id_disciplina`)'
                . ') ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci'
            );
        } catch (\Throwable $e) {
            error_log('[LINK] Erro ao criar tabela link: ' . $e->getMessage());
        }
    }

    public function insert(string $titulo, string $url, int $idTurma, int $idDisciplina = 0, int $extra = 0): int
    {
        $this->ensureSchema();
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return 0;
        }

        try {
            $stmt = $pdo->prepare(
                'INSERT INTO `link` (titulo, link, id_fk, id_disciplina, extra) VALUES (:titulo, :link, :id_fk, :id_disciplina, :extra)'
            );
            $stmt->bindValue(':titulo', $titulo, PDO::PARAM_STR);
            $stmt->bindValue(':link', $url, PDO::PARAM_STR);
            $stmt->bindValue(':id_fk', $idTurma, PDO::PARAM_INT);
            $stmt->bindValue(':id_disciplina', $idDisciplina, PDO::PARAM_INT);
            $stmt->bindValue(':extra', $extra === 1 ? 1 : 0, PDO::PARAM_INT);
            $stmt->execute();

            return (int) $pdo->lastInsertId();
        } catch (\Throwable $e) {
            error_log('[LINK] Erro ao inserir: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function listarPorTurma(int $idTurma): array
    {
        $this->ensureSchema();
        $pdo = Database::connection();
        if (!$pdo instanceof PDO || $idTurma <= 0) {
            return [];
        }

        try {
            $stmt = $pdo->prepare(
                'SELECT l.id, l.titulo, l.link, \'link\' AS tipo, l.extra, l.id_fk, l.id_disciplina, l.created_at,'
                . ' d.nome AS disciplina_nome'
                . ' FROM `link` l'
                . ' LEFT JOIN disciplina d ON d.id = l.id_disciplina'
                . ' WHERE l.id_fk = :id_fk AND l.ativo = 1'
                . ' ORDER BY l.id_disciplina ASC, d.nome ASC, l.created_at DESC'
            );
            $stmt->bindValue(':id_fk', $idTurma, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll() ?: [];
        } catch (\Throwable $e) {
            error_log('[LINK] Erro em listarPorTurma: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function listarAdmin(?int $idTurma = null): array
    {
        $this->ensureSchema();
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return [];
        }

        try {
            $sql = 'SELECT l.id, l.titulo, l.link, \'link\' AS tipo, l.extra, l.id_fk, l.id_disciplina, l.created_at,'
                 . ' t.nome AS turma_nome, d.nome AS disciplina_nome'
                 . ' FROM `link` l'
                 . ' JOIN turmas t ON t.id = l.id_fk AND t.ativo = 1'
                 . ' LEFT JOIN disciplina d ON d.id = l.id_disciplina'
                 . ' WHERE l.ativo = 1';
            if ($idTurma !== null && $idTurma > 0) {
                $sql .= ' AND l.id_fk = :id_turma';
            }
            $sql .= ' ORDER BY l.created_at DESC, l.id DESC';

            $stmt = $pdo->prepare($sql);
            if ($idTurma !== null && $idTurma > 0) {
                $stmt->bindValue(':id_turma', $idTurma, PDO::PARAM_INT);
            }
            $stmt->execute();
            return $stmt->fetchAll() ?: [];
        } catch (\Throwable $e) {
            error_log('[LINK] Erro em listarAdmin: ' . $e->getMessage());
            return [];
        }
    }

    public function buscarPorId(int $id): ?array
    {
        $this->ensureSchema();
        $pdo = Database::connection();
        if (!$pdo instanceof PDO || $id <= 0) {
            return null;
        }

        try {
            $stmt = $pdo->prepare('SELECT * FROM `link` WHERE id = :id AND ativo = 1 LIMIT 1');
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $row = $stmt->fetch();
            return is_array($row) ? $row : null;
        } catch (\Throwable $e) {
            error_log('[LINK] Erro em buscarPorId: ' . $e->getMessage());
            return null;
        }
    }

    public function atualizar(int $id, string $titulo, string $url, int $idTurma, int $idDisciplina): bool
    {
        $this->ensureSchema();
        $pdo = Database::connection();
        if (!$pdo instanceof PDO || $id <= 0) {
            return false;
        }

        try {
            $stmt = $pdo->prepare(
                'UPDATE `link` SET titulo = :titulo, link = :link, id_fk = :id_fk, id_disciplina = :id_disciplina, updated_at = NOW() WHERE id = :id'
            );
            $stmt->bindValue(':titulo', $titulo, PDO::PARAM_STR);
            $stmt->bindValue(':link', $url, PDO::PARAM_STR);
            $stmt->bindValue(':id_fk', $idTurma, PDO::PARAM_INT);
            $stmt->bindValue(':id_disciplina', $idDisciplina, PDO::PARAM_INT);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (\Throwable $e) {
            error_log('[LINK] Erro em atualizar: ' . $e->getMessage());
            return false;
        }
    }

    public function softDelete(int $id): bool
    {
        $this->ensureSchema();
        $pdo = Database::connection();
        if (!$pdo instanceof PDO || $id <= 0) {
            return false;
        }

        try {
            $stmt = $pdo->prepare('UPDATE `link` SET ativo = 0, updated_at = NOW() WHERE id = :id');
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (\Throwable $e) {
            error_log('[LINK] Erro em softDelete: ' . $e->getMessage());
            return false;
        }
    }
}
