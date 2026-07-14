<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use PDO;

final class MatriculaRepository
{
    public function create(array $data): int
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return 0;
        }

        try {
            $stmt = $pdo->prepare('INSERT INTO matriculas (id_aluno, id_turma, status) VALUES (:id_aluno, :id_turma, :status)');
            $stmt->bindValue(':id_aluno', (int) ($data['id_aluno'] ?? 0), PDO::PARAM_INT);
            $stmt->bindValue(':id_turma', (int) ($data['id_turma'] ?? 0), PDO::PARAM_INT);
            $stmt->bindValue(':status', (string) ($data['status'] ?? 'matriculado'));
            $stmt->execute();
            return (int) $pdo->lastInsertId();
        } catch (\Throwable $e) {
            error_log('[MATRICULA] Erro ao criar: ' . $e->getMessage());
            return 0;
        }
    }
}
