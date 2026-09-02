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

        $sql = 'INSERT INTO matricula
                (numero, id_aluno, id_curso, id_turma, id_pagamento, origem, status, data_matricula, ativo)
                VALUES (:numero, :id_aluno, :id_curso, :id_turma, :id_pagamento, :origem, :status, :data_matricula, :ativo)';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':numero', $data['numero'] ?? '');
        $stmt->bindValue(':id_aluno', (int) ($data['id_aluno'] ?? 0), PDO::PARAM_INT);
        $stmt->bindValue(':id_curso', (int) ($data['id_curso'] ?? 0), PDO::PARAM_INT);
        $stmt->bindValue(':id_turma', (int) ($data['id_turma'] ?? 0), PDO::PARAM_INT);
        $stmt->bindValue(':id_pagamento', (int) ($data['id_pagamento'] ?? 0), PDO::PARAM_INT);
        $stmt->bindValue(':origem', $data['origem'] ?? 'SITE');
        $stmt->bindValue(':status', $data['status'] ?? 'ATIVA');
        $stmt->bindValue(':data_matricula', $data['data_matricula'] ?? date('Y-m-d H:i:s'));
        $stmt->bindValue(':ativo', (int) ($data['ativo'] ?? 1), PDO::PARAM_INT);
        $stmt->execute();

        return (int) $pdo->lastInsertId();
    }

    public function findByAlunoTurma(int $idAluno, int $idTurma): ?array
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return null;
        }

        $sql = 'SELECT id, numero, id_aluno, id_curso, id_turma, id_pagamento, origem, status, data_matricula, ativo
                FROM matricula
                WHERE id_aluno = :id_aluno AND id_turma = :id_turma AND status = :status
                ORDER BY id DESC
                LIMIT 1';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':id_aluno', $idAluno, PDO::PARAM_INT);
        $stmt->bindValue(':id_turma', $idTurma, PDO::PARAM_INT);
        $stmt->bindValue(':status', 'ATIVA');
        $stmt->execute();
        $row = $stmt->fetch();

        return $row ?: null;
    }

    public function findByPagamento(int $idPagamento): ?array
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return null;
        }

        $sql = 'SELECT id, numero, id_aluno, id_curso, id_turma, id_pagamento, origem, status, data_matricula, ativo
                FROM matricula
                WHERE id_pagamento = :id_pagamento
                ORDER BY id DESC
                LIMIT 1';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':id_pagamento', $idPagamento, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch();

        return $row ?: null;
    }

    public function proximoNumero(int $ano): int
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return $ano * 100000;
        }

        $prefixo = (string) $ano;
        $sql = 'SELECT COUNT(*) FROM matricula WHERE numero LIKE :prefixo';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':prefixo', $prefixo . '%');
        $stmt->execute();
        $count = (int) $stmt->fetchColumn();

        return $ano * 100000 + $count + 1;
    }

    public function updateStatus(int $id, string $status): void
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return;
        }

        $sql = 'UPDATE matricula SET status = :status, updated_at = CURRENT_TIMESTAMP WHERE id = :id';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':status', $status);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
    }

    public function cancelar(int $idMatricula): bool
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO || $idMatricula <= 0) {
            return false;
        }

        try {
            $pdo->beginTransaction();
            $stmt = $pdo->prepare('UPDATE matricula
                SET ativo = 0, status = :status, updated_at = CURRENT_TIMESTAMP
                WHERE id = :id AND ativo = 1');
            $stmt->execute([':status' => 'cancelado', ':id' => $idMatricula]);

            if ($stmt->rowCount() !== 1) {
                $pdo->rollBack();
                return false;
            }

            $stmtParcelas = $pdo->prepare('UPDATE curso_parcela
                SET ativo = 0, updated_at = CURRENT_TIMESTAMP
                WHERE id_matricula = :id_matricula AND ativo = 1');
            $stmtParcelas->execute([':id_matricula' => $idMatricula]);
            $pdo->commit();
            return true;
        } catch (\Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            error_log('[MATRICULA] Erro ao cancelar matrícula: ' . $e->getMessage());
            return false;
        }
    }
}
