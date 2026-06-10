<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use PDO;

final class TurmaRepository
{
    public function list(int $limit = 200): array
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return [];
        }

        try {
            $sql = 'SELECT t.id, t.nome, c.nome AS curso_nome, n.nome AS nivel_nome, t.data_inicio, t.ativa,'
                 . ' (SELECT COUNT(*) FROM matriculas WHERE id_turma = t.id) AS total_inscritos'
                 . ' FROM turmas t'
                 . ' LEFT JOIN cursos_iesb c ON t.id_curso = c.id'
                 . ' LEFT JOIN nivel n ON c.nivel = n.id'
                 . ' ORDER BY t.id DESC'
                 . ' LIMIT :limit';

            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();

            $rows = $stmt->fetchAll();
            return is_array($rows) ? $rows : [];
        } catch (\Throwable $e) {
            error_log('[TURMAS] Erro em list: ' . $e->getMessage());
            return [];
        }
    }

    public function findById(int $id): ?array
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return null;
        }

        try {
            $sql = 'SELECT t.id, t.nome, t.id_curso, t.data_inicio, t.ativa, '
                 . 'c.nome AS curso_nome, n.nome AS nivel_nome'
                 . ' FROM turmas t'
                 . ' LEFT JOIN cursos_iesb c ON t.id_curso = c.id'
                 . ' LEFT JOIN nivel n ON c.nivel = n.id'
                 . ' WHERE t.id = :id'
                 . ' LIMIT 1';

            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            $row = $stmt->fetch();
            return $row ?: null;
        } catch (\Throwable $e) {
            error_log('[TURMAS] Erro em findById: ' . $e->getMessage());
            return null;
        }
    }

    public function save(array $payload): int
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return 0;
        }

        try {
            if (!empty($payload['id'])) {
                $sql = 'UPDATE turmas SET nome = :nome, id_curso = :id_curso, data_inicio = :data_inicio, ativa = :ativa WHERE id = :id';
                $stmt = $pdo->prepare($sql);
                $stmt->bindValue(':id', $payload['id'], PDO::PARAM_INT);
            } else {
                $sql = 'INSERT INTO turmas (nome, id_curso, data_inicio, ativa) VALUES (:nome, :id_curso, :data_inicio, :ativa)';
                $stmt = $pdo->prepare($sql);
            }

            $stmt->bindValue(':nome', trim($payload['nome'] ?? ''), PDO::PARAM_STR);
            $stmt->bindValue(':id_curso', $payload['id_curso'], PDO::PARAM_INT);
            $stmt->bindValue(':data_inicio', $payload['data_inicio'], PDO::PARAM_STR);
            $stmt->bindValue(':ativa', strtoupper(trim($payload['ativa'] ?? 'N')), PDO::PARAM_STR);
            $stmt->execute();

            if (empty($payload['id'])) {
                return (int) $pdo->lastInsertId();
            }
            return $payload['id'];
        } catch (\Throwable $e) {
            error_log('[TURMAS] Erro em save: ' . $e->getMessage());
            return 0;
        }
    }

    public function listByCurso(int $idCurso): array
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return [];
        }

        try {
            $sql = 'SELECT id, nome, data_inicio, ativa FROM turmas WHERE id_curso = :id_curso AND ativa = "S" ORDER BY data_inicio DESC LIMIT 10';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':id_curso', $idCurso, PDO::PARAM_INT);
            $stmt->execute();

            $rows = $stmt->fetchAll();
            return is_array($rows) ? $rows : [];
        } catch (\Throwable $e) {
            error_log('[TURMAS] Erro em listByCurso: ' . $e->getMessage());
            return [];
        }
    }

    public function listInscritosPorTurma(int $idTurma): array
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return [];
        }

        try {
            $sql = 'SELECT a.id, a.nome, a.cpf, a.email, a.telefone, m.status, m.data_matricula'
                 . ' FROM matriculas m'
                 . ' INNER JOIN alunos a ON m.id_aluno = a.id'
                 . ' WHERE m.id_turma = :id_turma'
                 . ' ORDER BY a.nome ASC';

            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':id_turma', $idTurma, PDO::PARAM_INT);
            $stmt->execute();

            $rows = $stmt->fetchAll();
            return is_array($rows) ? $rows : [];
        } catch (\Throwable $e) {
            error_log('[TURMAS] Erro em listInscritosPorTurma: ' . $e->getMessage());
            return [];
        }
    }

    public function saveMatricula(array $payload): int
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return 0;
        }

        try {
            $sql = 'INSERT INTO matriculas (id_aluno, id_turma, status) VALUES (:id_aluno, :id_turma, :status)';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':id_aluno', $payload['id_aluno'], PDO::PARAM_INT);
            $stmt->bindValue(':id_turma', $payload['id_turma'], PDO::PARAM_INT);
            $stmt->bindValue(':status', $payload['status'] ?? 'matriculado', PDO::PARAM_STR);
            $stmt->execute();

            return (int) $pdo->lastInsertId();
        } catch (\Throwable $e) {
            error_log('[MATRICULAS] Erro em saveMatricula: ' . $e->getMessage());
            return 0;
        }
    }

    public function findMatriculaByAlunoAndTurma(int $idAluno, int $idTurma): ?array
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return null;
        }

        try {
            $sql = 'SELECT id FROM matriculas'
                 . ' WHERE id_aluno = :id_aluno AND id_turma = :id_turma'
                 . ' LIMIT 1';

            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':id_aluno', $idAluno, PDO::PARAM_INT);
            $stmt->bindValue(':id_turma', $idTurma, PDO::PARAM_INT);
            $stmt->execute();

            $row = $stmt->fetch();
            return $row ?: null;
        } catch (\Throwable $e) {
            error_log('[MATRICULAS] Erro em findMatriculaByAlunoAndTurma: ' . $e->getMessage());
            return null;
        }
    }
}
