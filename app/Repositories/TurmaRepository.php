<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use PDO;

final class TurmaRepository
{
    public function list(int $limit = 200, ?int $ativo = null): array
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return [];
        }

        try {
            $sql = 'SELECT t.id, t.nome, c.nome AS curso_nome, n.nome AS nivel_nome, t.data_inicio, t.ativo,'
                 . ' (SELECT COUNT(*) FROM matricula WHERE id_turma = t.id) AS total_inscritos'
                 . ' FROM turmas t'
                 . ' LEFT JOIN cursos c ON t.id_curso = c.id'
                 . ' LEFT JOIN tipo_curso n ON c.tipo_curso = n.id';

            $params = [];
            if ($ativo !== null) {
                $sql .= ' WHERE t.ativo = :ativo';
                $params[':ativo'] = $ativo === 1 ? 1 : 0;
            }

            $sql .= ' ORDER BY t.id DESC LIMIT :limit';

            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value, PDO::PARAM_INT);
            }
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
            $sql = 'SELECT t.id, t.nome, t.id_curso, t.data_inicio, t.ativo, '
                 . 'c.nome AS curso_nome, n.nome AS nivel_nome'
                 . ' FROM turmas t'
                 . ' LEFT JOIN cursos c ON t.id_curso = c.id'
                 . ' LEFT JOIN tipo_curso n ON c.tipo_curso = n.id'
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
                $sql = 'UPDATE turmas SET nome = :nome, id_curso = :id_curso, data_inicio = :data_inicio, ativo = :ativo WHERE id = :id';
                $stmt = $pdo->prepare($sql);
                $stmt->bindValue(':id', $payload['id'], PDO::PARAM_INT);
            } else {
                $sql = 'INSERT INTO turmas (nome, id_curso, data_inicio, ativo) VALUES (:nome, :id_curso, :data_inicio, :ativo)';
                $stmt = $pdo->prepare($sql);
            }

            $stmt->bindValue(':nome', trim($payload['nome'] ?? ''), PDO::PARAM_STR);
            $stmt->bindValue(':id_curso', $payload['id_curso'], PDO::PARAM_INT);
            $stmt->bindValue(':data_inicio', $payload['data_inicio'], PDO::PARAM_STR);
            $stmt->bindValue(':ativo', intval($payload['ativo'] ?? 0) ? 1 : 0, PDO::PARAM_INT);
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

    public function listAtivas(int $limit = 500): array
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return [];
        }

        try {
            $sql = 'SELECT t.id, t.nome, t.id_curso, c.nome AS curso_nome, n.nome AS nivel_nome, t.data_inicio'
                 . ' FROM turmas t'
                 . ' INNER JOIN cursos c ON t.id_curso = c.id'
                 . ' LEFT JOIN tipo_curso n ON c.tipo_curso = n.id'
                 . ' WHERE t.ativo = 1'
                 . ' ORDER BY c.nome ASC, t.nome ASC'
                 . ' LIMIT :limit';

            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();

            $rows = $stmt->fetchAll();
            return is_array($rows) ? $rows : [];
        } catch (\Throwable $e) {
            error_log('[TURMAS] Erro em listAtivas: ' . $e->getMessage());
            return [];
        }
    }

    public function listMatriculasAtivas(): array
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return [];
        }

        try {
            $sql = 'SELECT m.id, m.id_aluno, m.id_curso, m.id_turma, m.data_matricula,
                           a.nome AS aluno_nome, t.nome AS turma_nome, c.nome AS curso_nome
                    FROM matricula m
                    INNER JOIN alunos a ON a.id = m.id_aluno
                    INNER JOIN turmas t ON t.id = m.id_turma
                    INNER JOIN cursos c ON c.id = m.id_curso
                    WHERE m.ativo = 1
                    ORDER BY c.nome ASC, t.nome ASC, a.nome ASC';

            $stmt = $pdo->query($sql);
            $rows = $stmt->fetchAll();
            return is_array($rows) ? $rows : [];
        } catch (\Throwable $e) {
            error_log('[MATRICULAS] Erro em listMatriculasAtivas: ' . $e->getMessage());
            return [];
        }
    }

    public function listByCurso(int $idCurso): array
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return [];
        }

        try {
            $sql = 'SELECT id, nome, data_inicio, ativo FROM turmas WHERE id_curso = :id_curso AND ativo = 1 ORDER BY data_inicio DESC LIMIT 10';
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
                 . ' FROM matricula m'
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
            $idAluno = (int) ($payload['id_aluno'] ?? 0);
            $idTurma = (int) ($payload['id_turma'] ?? 0);

            $idCurso = 0;
            if ($idTurma > 0) {
                $stmt = $pdo->prepare('SELECT id_curso FROM turmas WHERE id = :id LIMIT 1');
                $stmt->bindValue(':id', $idTurma, PDO::PARAM_INT);
                $stmt->execute();
                $idCurso = (int) $stmt->fetchColumn();
            }

            $ano = (int) date('Y');
            $stmt = $pdo->prepare('SELECT COUNT(*) FROM matricula WHERE numero LIKE :prefixo');
            $stmt->bindValue(':prefixo', (string) $ano . '%');
            $stmt->execute();
            $numero = (string) ($ano * 100000 + (int) $stmt->fetchColumn() + 1);

            $sql = 'INSERT INTO matricula
                    (numero, id_aluno, id_curso, id_turma, id_pagamento, origem, status, data_matricula, ativo)
                    VALUES (:numero, :id_aluno, :id_curso, :id_turma, :id_pagamento, :origem, :status, :data_matricula, :ativo)';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':numero', $numero, PDO::PARAM_STR);
            $stmt->bindValue(':id_aluno', $idAluno, PDO::PARAM_INT);
            $stmt->bindValue(':id_curso', $idCurso, PDO::PARAM_INT);
            $stmt->bindValue(':id_turma', $idTurma, PDO::PARAM_INT);
            $stmt->bindValue(':id_pagamento', 0, PDO::PARAM_INT);
            $stmt->bindValue(':origem', 'ADMIN', PDO::PARAM_STR);
            $stmt->bindValue(':status', $payload['status'] ?? 'matriculado', PDO::PARAM_STR);
            $stmt->bindValue(':data_matricula', date('Y-m-d H:i:s'), PDO::PARAM_STR);
            $stmt->bindValue(':ativo', 1, PDO::PARAM_INT);
            $stmt->execute();

            return (int) $pdo->lastInsertId();
        } catch (\Throwable $e) {
            error_log('[MATRICULA] Erro em saveMatricula: ' . $e->getMessage());
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
            $sql = 'SELECT id FROM matricula'
                 . ' WHERE id_aluno = :id_aluno AND id_turma = :id_turma'
                 . ' LIMIT 1';

            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':id_aluno', $idAluno, PDO::PARAM_INT);
            $stmt->bindValue(':id_turma', $idTurma, PDO::PARAM_INT);
            $stmt->execute();

            $row = $stmt->fetch();
            return $row ?: null;
        } catch (\Throwable $e) {
            error_log('[MATRICULA] Erro em findMatriculaByAlunoAndTurma: ' . $e->getMessage());
            return null;
        }
    }

    public function findMatriculaById(int $idMatricula): ?array
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return null;
        }

        try {
            $sql = 'SELECT m.id, m.id_aluno, m.id_turma, m.data_matricula, m.status,'
                 . ' t.nome AS turma_nome, c.nome AS curso_nome'
                 . ' FROM matricula m'
                 . ' INNER JOIN turmas t ON m.id_turma = t.id'
                 . ' INNER JOIN cursos c ON t.id_curso = c.id'
                 . ' WHERE m.id = :id'
                 . ' LIMIT 1';

            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':id', $idMatricula, PDO::PARAM_INT);
            $stmt->execute();

            $row = $stmt->fetch();
            return $row ?: null;
        } catch (\Throwable $e) {
            error_log('[MATRICULA] Erro em findMatriculaById: ' . $e->getMessage());
            return null;
        }
    }

    public function updateMatriculaTurma(int $idMatricula, int $idNovaTurma): bool
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return false;
        }

        try {
            $sql = 'UPDATE matricula SET id_turma = :id_turma WHERE id = :id';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':id_turma', $idNovaTurma, PDO::PARAM_INT);
            $stmt->bindValue(':id', $idMatricula, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (\Throwable $e) {
            error_log('[MATRICULA] Erro em updateMatriculaTurma: ' . $e->getMessage());
            return false;
        }
    }

    public function listTrocaHistorico(int $limit = 200): array
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return [];
        }

        try {
            $sql = 'SELECT tt.id, tt.id_aluno, tt.id_origem, tt.id_destino, tt.motivo, tt.created_at,'
                 . ' a.nome AS aluno_nome,'
                 . ' to_nome.nome AS turma_origem_nome, co.nome AS curso_origem_nome,'
                 . ' td_nome.nome AS turma_destino_nome, cd.nome AS curso_destino_nome'
                 . ' FROM turma_troca tt'
                 . ' INNER JOIN alunos a ON tt.id_aluno = a.id'
                 . ' INNER JOIN turmas to_nome ON tt.id_origem = to_nome.id'
                 . ' INNER JOIN cursos co ON to_nome.id_curso = co.id'
                 . ' INNER JOIN turmas td_nome ON tt.id_destino = td_nome.id'
                 . ' INNER JOIN cursos cd ON td_nome.id_curso = cd.id'
                 . ' ORDER BY tt.id DESC'
                 . ' LIMIT :limit';

            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();

            $rows = $stmt->fetchAll();
            return is_array($rows) ? $rows : [];
        } catch (\Throwable $e) {
            error_log('[TURMAS] Erro em listTrocaHistorico: ' . $e->getMessage());
            return [];
        }
    }

    public function insertTroca(int $idOrigem, int $idDestino, int $idAluno, string $motivo): int
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return 0;
        }

        try {
            $sql = 'INSERT INTO turma_troca (id_origem, id_destino, id_aluno, motivo)'
                 . ' VALUES (:id_origem, :id_destino, :id_aluno, :motivo)';

            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':id_origem', $idOrigem, PDO::PARAM_INT);
            $stmt->bindValue(':id_destino', $idDestino, PDO::PARAM_INT);
            $stmt->bindValue(':id_aluno', $idAluno, PDO::PARAM_INT);
            $stmt->bindValue(':motivo', $motivo, PDO::PARAM_STR);
            $stmt->execute();

            return (int) $pdo->lastInsertId();
        } catch (\Throwable $e) {
            error_log('[MATRICULA] Erro em insertTroca: ' . $e->getMessage());
            return 0;
        }
    }
}
