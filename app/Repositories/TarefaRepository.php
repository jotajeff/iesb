<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use PDO;

final class TarefaRepository
{
    public function list(int $limit = 300): array
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return [];
        }

        try {
            return $this->fetchTarefas($pdo, $limit, true);
        } catch (\Throwable $e) {
            error_log('[TAREFAS] Erro ao listar tarefas com comentarios: ' . $e->getMessage());

            try {
                return $this->fetchTarefas($pdo, $limit, false);
            } catch (\Throwable $fallbackError) {
                error_log('[TAREFAS] Erro no fallback da listagem: ' . $fallbackError->getMessage());
                return [];
            }
        }
    }

    public function findById(int $id): ?array
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return null;
        }

        try {
            return $this->fetchTarefaById($pdo, $id, true);
        } catch (\Throwable $e) {
            error_log('[TAREFAS] Erro ao buscar tarefa com comentarios: ' . $e->getMessage());

            try {
                return $this->fetchTarefaById($pdo, $id, false);
            } catch (\Throwable $fallbackError) {
                error_log('[TAREFAS] Erro no fallback da busca: ' . $fallbackError->getMessage());
                return null;
            }
        }
    }

    public function create(array $payload): int
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return 0;
        }

        try {
            $sql = 'INSERT INTO tarefas (setor, tarefa, prioridade, criado_por, responsavel, situacao)
                    VALUES (:setor, :tarefa, :prioridade, :criado_por, :responsavel, :situacao)';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':setor', (int) $payload['setor'], PDO::PARAM_INT);
            $stmt->bindValue(':tarefa', (string) $payload['tarefa']);
            $stmt->bindValue(':prioridade', (int) ($payload['prioridade'] ?? 1), PDO::PARAM_INT);
            $stmt->bindValue(':criado_por', (int) $payload['criado_por'], PDO::PARAM_INT);
            $responsavel = isset($payload['responsavel']) && (int) $payload['responsavel'] > 0 ? (int) $payload['responsavel'] : null;
            $stmt->bindValue(':responsavel', $responsavel, $responsavel !== null ? PDO::PARAM_INT : PDO::PARAM_NULL);
            $stmt->bindValue(':situacao', (string) $payload['situacao']);
            $stmt->execute();

            return (int) $pdo->lastInsertId();
        } catch (\Throwable $e) {
            error_log('[TAREFAS] Erro ao criar tarefa: ' . $e->getMessage());
            return 0;
        }
    }

    public function update(int $id, array $payload): void
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return;
        }

        try {
            $sql = 'UPDATE tarefas
                    SET setor = :setor,
                        tarefa = :tarefa,
                        prioridade = :prioridade,
                        responsavel = :responsavel,
                        situacao = :situacao
                    WHERE id = :id';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->bindValue(':setor', (int) $payload['setor'], PDO::PARAM_INT);
            $stmt->bindValue(':tarefa', (string) $payload['tarefa']);
            $stmt->bindValue(':prioridade', (int) ($payload['prioridade'] ?? 1), PDO::PARAM_INT);
            $responsavel = isset($payload['responsavel']) && (int) $payload['responsavel'] > 0 ? (int) $payload['responsavel'] : null;
            $stmt->bindValue(':responsavel', $responsavel, $responsavel !== null ? PDO::PARAM_INT : PDO::PARAM_NULL);
            $stmt->bindValue(':situacao', (string) $payload['situacao']);
            $stmt->execute();
        } catch (\Throwable $e) {
            error_log('[TAREFAS] Erro ao atualizar tarefa: ' . $e->getMessage());
        }
    }

    public function listSetores(): array
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return [];
        }

        try {
            $sql = 'SELECT id, setor FROM setores ORDER BY setor ASC';
            $stmt = $pdo->query($sql);
            $rows = $stmt->fetchAll();

            return is_array($rows) ? $rows : [];
        } catch (\Throwable $e) {
            error_log('[TAREFAS] Erro ao carregar setores: ' . $e->getMessage());
            return [];
        }
    }

    public function findSetorById(int $id): ?array
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return null;
        }

        try {
            $sql = 'SELECT id, setor FROM setores WHERE id = :id LIMIT 1';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            $row = $stmt->fetch();
            return $row ?: null;
        } catch (\Throwable $e) {
            error_log('[TAREFAS] Erro ao buscar setor: ' . $e->getMessage());
            return null;
        }
    }

    public function saveSetor(array $payload): int
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return 0;
        }

        try {
            $id = (int) ($payload['id'] ?? 0);
            $setor = trim((string) ($payload['setor'] ?? ''));

            if ($id > 0) {
                $sql = 'UPDATE setores SET setor = :setor WHERE id = :id';
                $stmt = $pdo->prepare($sql);
                $stmt->bindValue(':id', $id, PDO::PARAM_INT);
                $stmt->bindValue(':setor', $setor);
                $stmt->execute();
                return $id;
            }

            $nextId = (int) ($pdo->query('SELECT COALESCE(MAX(id), 0) + 1 FROM setores')->fetchColumn() ?: 1);

            $sql = 'INSERT INTO setores (id, setor) VALUES (:id, :setor)';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':id', $nextId, PDO::PARAM_INT);
            $stmt->bindValue(':setor', $setor);
            $stmt->execute();

            return $nextId;
        } catch (\Throwable $e) {
            error_log('[TAREFAS] Erro ao salvar setor: ' . $e->getMessage());
            return 0;
        }
    }

    private function fetchTarefas(PDO $pdo, int $limit, bool $withComments): array
    {
        $sql = 'SELECT t.id,
                       t.setor AS setor_id,
                       s.setor AS setor_nome,
                       t.tarefa,
                       t.prioridade,
                       t.criado_em,
                       t.criado_por AS criado_por_id,
                       uc.nome AS criado_por_nome,
                       t.responsavel AS responsavel_id,
                       ur.nome AS responsavel_nome,
                       t.situacao';

        if ($withComments) {
            $sql .= ',
                       COALESCE(com.total_comentarios, 0) AS comentarios_total';
        } else {
            $sql .= ',
                       0 AS comentarios_total';
        }

        $sql .= ' FROM tarefas t
                  LEFT JOIN setores s ON s.id = t.setor
                  LEFT JOIN usuarios uc ON uc.id = t.criado_por
                  LEFT JOIN usuarios ur ON ur.id = t.responsavel';

        if ($withComments) {
            $sql .= '
                  LEFT JOIN (
                      SELECT id_fg, COUNT(*) AS total_comentarios
                      FROM comentarios
                      WHERE tabela_fg = "tarefas"
                      GROUP BY id_fg
                  ) com ON com.id_fg = t.id';
        }

        $sql .= ' ORDER BY t.id DESC
                  LIMIT :limit';

        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        $rows = $stmt->fetchAll();
        return is_array($rows) ? $rows : [];
    }

    private function fetchTarefaById(PDO $pdo, int $id, bool $withComments): ?array
    {
        $sql = 'SELECT t.id,
                       t.setor AS setor_id,
                       s.setor AS setor_nome,
                       t.tarefa,
                       t.prioridade,
                       t.criado_em,
                       t.criado_por AS criado_por_id,
                       uc.nome AS criado_por_nome,
                       t.responsavel AS responsavel_id,
                       ur.nome AS responsavel_nome,
                       t.situacao';

        if ($withComments) {
            $sql .= ',
                       COALESCE(com.total_comentarios, 0) AS comentarios_total';
        } else {
            $sql .= ',
                       0 AS comentarios_total';
        }

        $sql .= ' FROM tarefas t
                  LEFT JOIN setores s ON s.id = t.setor
                  LEFT JOIN usuarios uc ON uc.id = t.criado_por
                  LEFT JOIN usuarios ur ON ur.id = t.responsavel';

        if ($withComments) {
            $sql .= '
                  LEFT JOIN (
                      SELECT id_fg, COUNT(*) AS total_comentarios
                      FROM comentarios
                      WHERE tabela_fg = "tarefas"
                      GROUP BY id_fg
                  ) com ON com.id_fg = t.id';
        }

        $sql .= ' WHERE t.id = :id
                  LIMIT 1';

        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        $row = $stmt->fetch();
        return is_array($row) ? $row : null;
    }
}
