<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use PDO;

final class LeadRepository
{
    public function salvar(string $nome, string $telefone, string $interesse, string $ip): int
    {
        $this->ensureSchema();

        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return 0;
        }

        try {
            $stmt = $pdo->prepare(
                'INSERT INTO leads (nome, telefone, interesse, ativo, situacao, ip, created_at)
                 VALUES (:nome, :telefone, :interesse, 1, 1, :ip, NOW())'
            );
            $stmt->bindValue(':nome', $nome, PDO::PARAM_STR);
            $stmt->bindValue(':telefone', $telefone, PDO::PARAM_STR);
            $stmt->bindValue(':interesse', $interesse, PDO::PARAM_STR);
            $stmt->bindValue(':ip', substr($ip, 0, 20), PDO::PARAM_STR);
            $stmt->execute();

            return (int) $pdo->lastInsertId();
        } catch (\Throwable $e) {
            error_log('[LEADS] Erro ao salvar lead: ' . $e->getMessage());
            return 0;
        }
    }

    public function listarPorAtivo(int $ativo = 1): array
    {
        $this->ensureSchema();

        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return [];
        }

        $ativo = $ativo === 2 ? 2 : 1;

        try {
            $stmt = $pdo->prepare(
                'SELECT id, nome, telefone, interesse, curso, mensagem, situacao, ativo, ip, created_at
                 FROM leads
                 WHERE ativo = :ativo
                 ORDER BY created_at DESC, id DESC'
            );
            $stmt->bindValue(':ativo', $ativo, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll() ?: [];
        } catch (\Throwable $e) {
            error_log('[LEADS] Erro ao listar leads: ' . $e->getMessage());
            return [];
        }
    }

    public function findById(int $id): ?array
    {
        $this->ensureSchema();

        $pdo = Database::connection();
        if (!$pdo instanceof PDO || $id <= 0) {
            return null;
        }

        try {
            $stmt = $pdo->prepare(
                'SELECT id, nome, telefone, interesse, curso, mensagem, situacao, ativo, ip, created_at
                 FROM leads
                 WHERE id = :id
                 LIMIT 1'
            );
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $row = $stmt->fetch();

            return is_array($row) ? $row : null;
        } catch (\Throwable $e) {
            error_log('[LEADS] Erro ao buscar lead: ' . $e->getMessage());
            return null;
        }
    }

    public function atualizar(int $id, int $situacao, int $ativo): bool
    {
        $this->ensureSchema();

        $pdo = Database::connection();
        if (!$pdo instanceof PDO || $id <= 0 || !in_array($situacao, [1, 2, 3], true) || !in_array($ativo, [1, 2], true)) {
            return false;
        }

        try {
            $stmt = $pdo->prepare('UPDATE leads SET situacao = :situacao, ativo = :ativo WHERE id = :id');
            $stmt->bindValue(':situacao', $situacao, PDO::PARAM_INT);
            $stmt->bindValue(':ativo', $ativo, PDO::PARAM_INT);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);

            return $stmt->execute();
        } catch (\Throwable $e) {
            error_log('[LEADS] Erro ao atualizar lead: ' . $e->getMessage());
            return false;
        }
    }

    public function ensureSchema(): void
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return;
        }

        try {
            $pdo->exec(
                'CREATE TABLE IF NOT EXISTS `leads` ('
                . ' `id` INT NOT NULL AUTO_INCREMENT,'
                . ' `nome` VARCHAR(50) DEFAULT NULL,'
                . ' `telefone` VARCHAR(20) DEFAULT NULL,'
                . ' `curso` VARCHAR(100) DEFAULT NULL,'
                . ' `interesse` VARCHAR(100) DEFAULT NULL,'
                . ' `mensagem` VARCHAR(256) DEFAULT NULL,'
                . ' `ativo` TINYINT(1) DEFAULT 1,'
                . ' `situacao` TINYINT(1) DEFAULT 1,'
                . ' `ip` VARCHAR(20) NOT NULL,'
                . ' `created_at` DATETIME DEFAULT NULL,'
                . ' PRIMARY KEY (`id`)'
                . ') ENGINE=InnoDB DEFAULT CHARSET=utf8mb4'
            );
        } catch (\Throwable $e) {
            error_log('[LEADS] Erro ao criar tabela leads: ' . $e->getMessage());
        }
    }
}
