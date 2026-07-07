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
                'total_matriculas' => 0,
                'total_pre_inscricoes' => 0,
            ];
        }

        if ($isAdmin || !$userId) {
            return [
                'total_alunos' => $this->count($pdo, 'SELECT COUNT(*) FROM alunos'),
                'total_cursos' => $this->count($pdo, 'SELECT COUNT(*) FROM cursos_iesb'),
                'total_matriculas' => $this->count($pdo, 'SELECT COUNT(*) FROM matriculas'),
                'total_pre_inscricoes' => $this->count($pdo, "SELECT COUNT(*) FROM pre_inscricao WHERE situacao = 'recebido'"),
            ];
        }

        $stmt = $pdo->prepare('SELECT COUNT(DISTINCT c.id) FROM cursos_iesb c JOIN turmas t ON t.id_curso = c.id JOIN turma_professor tp ON tp.id_turma = t.id WHERE tp.id_usuario = :userId');
        $stmt->bindValue(':userId', $userId, PDO::PARAM_INT);
        $stmt->execute();
        $totalCursos = (int) $stmt->fetchColumn();

        $stmt = $pdo->prepare('SELECT COUNT(DISTINCT a.id) FROM alunos a JOIN matriculas m ON m.id_aluno = a.id JOIN turmas t ON t.id = m.id_turma JOIN turma_professor tp ON tp.id_turma = t.id WHERE tp.id_usuario = :userId');
        $stmt->bindValue(':userId', $userId, PDO::PARAM_INT);
        $stmt->execute();
        $totalAlunos = (int) $stmt->fetchColumn();

        $stmt = $pdo->prepare('SELECT COUNT(*) FROM matriculas m JOIN turma_professor tp ON tp.id_turma = m.id_turma WHERE tp.id_usuario = :userId');
        $stmt->bindValue(':userId', $userId, PDO::PARAM_INT);
        $stmt->execute();
        $totalMatriculas = (int) $stmt->fetchColumn();

        return [
            'total_alunos' => $totalAlunos,
            'total_cursos' => $totalCursos,
            'total_matriculas' => $totalMatriculas,
        ];
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
