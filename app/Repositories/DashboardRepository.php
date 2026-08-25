<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use PDO;

final class DashboardRepository
{
    public function indicators(?int $userId = null, bool $isAdmin = true): array
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return [
                'total_alunos' => 0,
                'total_cursos' => 0,
                'total_matricula' => 0,
                'total_pre_inscricoes' => 0,
            ];
        }

        if ($isAdmin) {
            return [
                'total_alunos' => $this->count($pdo, 'SELECT COUNT(*) FROM alunos WHERE ativo = 1'),
                'total_cursos' => $this->count($pdo, 'SELECT COUNT(*) FROM cursos'),
                'total_matricula' => $this->count($pdo, 'SELECT COUNT(*) FROM matricula WHERE ativo = 1'),
                'total_pre_inscricoes' => $this->count($pdo, "SELECT COUNT(*) FROM pre_inscricao WHERE situacao = 'recebido'"),
            ];
        }

        if (!$userId) {
            return [
                'total_alunos' => 0,
                'total_cursos' => 0,
                'total_matricula' => 0,
                'total_pre_inscricoes' => 0,
            ];
        }

        $stmt = $pdo->prepare(
            'SELECT COUNT(DISTINCT t.id) FROM turmas t
             WHERE ' . $this->turmaProfessorCondition()
        );
        $this->bindProfessorCondition($stmt, $userId);
        $stmt->execute();
        $totalCursos = (int) $stmt->fetchColumn();

        $stmt = $pdo->prepare(
            'SELECT COUNT(DISTINCT a.id) FROM alunos a
             JOIN matricula m ON m.id_aluno = a.id
             JOIN turmas t ON t.id = m.id_turma
             WHERE a.ativo = 1 AND ' . $this->turmaProfessorCondition()
        );
        $this->bindProfessorCondition($stmt, $userId);
        $stmt->execute();
        $totalAlunos = (int) $stmt->fetchColumn();

        $stmt = $pdo->prepare(
            'SELECT COUNT(*) FROM matricula m
             JOIN turmas t ON t.id = m.id_turma
             WHERE m.ativo = 1 AND ' . $this->turmaProfessorCondition()
        );
        $this->bindProfessorCondition($stmt, $userId);
        $stmt->execute();
        $totalMatriculas = (int) $stmt->fetchColumn();

        return [
            'total_alunos' => $totalAlunos,
            'total_cursos' => $totalCursos,
            'total_matricula' => $totalMatriculas,
            'total_pre_inscricoes' => 0,
        ];
    }

    private function turmaProfessorCondition(): string
    {
        return '(EXISTS (
                    SELECT 1 FROM turma_professor tp
                    WHERE tp.id_turma = t.id AND tp.id_usuario = :userId_direct AND tp.status = "A"
                ) OR EXISTS (
                    SELECT 1 FROM turma_disciplina td
                    WHERE td.id_turma = t.id AND td.id_usuario_professor = :userId_legacy AND td.ativo = 1
                ) OR EXISTS (
                    SELECT 1 FROM turma_disciplina td
                    JOIN turma_disciplina_professor tdp ON tdp.id_turma_disciplina = td.id
                    WHERE td.id_turma = t.id AND tdp.id_usuario_professor = :userId_discipline AND tdp.ativo = 1 AND td.ativo = 1
                ))';
    }

    private function bindProfessorCondition(\PDOStatement $stmt, int $userId): void
    {
        $stmt->bindValue(':userId_direct', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':userId_legacy', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':userId_discipline', $userId, PDO::PARAM_INT);
    }

    public function taskIndicators(int $userId, bool $isAdmin): array
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return [
                'tarefas_criadas' => 0,
                'tarefas_execucao' => 0,
                'tarefas_finalizadas' => 0,
            ];
        }

        try {
            $sql = 'SELECT
                        COALESCE(SUM(CASE WHEN t.situacao = "criada" THEN 1 ELSE 0 END), 0) AS tarefas_criadas,
                        COALESCE(SUM(CASE WHEN t.situacao IN ("execucao", "revisao") THEN 1 ELSE 0 END), 0) AS tarefas_execucao,
                        COALESCE(SUM(CASE WHEN t.situacao = "finalizada" THEN 1 ELSE 0 END), 0) AS tarefas_finalizadas
                    FROM tarefas t';

            $params = [];
            if (!$isAdmin && $userId > 0) {
                $sql .= ' WHERE t.responsavel = :responsavel';
                $params[':responsavel'] = $userId;
            }

            $stmt = $pdo->prepare($sql);
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value, PDO::PARAM_INT);
            }
            $stmt->execute();

            $row = $stmt->fetch();
            if (!is_array($row)) {
                return [
                    'tarefas_criadas' => 0,
                    'tarefas_execucao' => 0,
                    'tarefas_finalizadas' => 0,
                ];
            }

            return [
                'tarefas_criadas' => (int) ($row['tarefas_criadas'] ?? 0),
                'tarefas_execucao' => (int) ($row['tarefas_execucao'] ?? 0),
                'tarefas_finalizadas' => (int) ($row['tarefas_finalizadas'] ?? 0),
            ];
        } catch (\Throwable $e) {
            error_log('[TAREFAS] Erro em dashboardTaskIndicators: ' . $e->getMessage());
            return [
                'tarefas_criadas' => 0,
                'tarefas_execucao' => 0,
                'tarefas_finalizadas' => 0,
            ];
        }
    }

    private function count(PDO $pdo, string $sql): int
    {
        try {
            $result = $pdo->query($sql)->fetchColumn();
            return (int) $result;
        } catch (\Throwable) {
            return 0;
        }
    }
}
