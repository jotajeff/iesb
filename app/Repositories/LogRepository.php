<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use PDO;

final class LogRepository
{
    public function recent(int $page = 1, int $perPage = 50, ?string $perfil = null, ?string $nome = null): array
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return ['data' => [], 'total' => 0];
        }

        $offset = max(0, ($page - 1) * $perPage);
        $isAluno = $perfil === 'aluno';

        $join = $isAluno
            ? 'LEFT JOIN alunos a ON a.id = l.usuario_id'
            : 'LEFT JOIN usuarios u ON u.id = l.usuario_id';
        $select = $isAluno
            ? 'a.nome AS usuario_nome'
            : 'u.nome AS usuario_nome';
        $where = $isAluno
            ? 'l.perfil = :perfil_val'
            : 'l.perfil != :exclude_perfil';

        $countSql = "SELECT COUNT(*) FROM logs_auditoria l WHERE $where";
        $countStmt = $pdo->prepare($countSql);
        if ($isAluno) {
            $countStmt->bindValue(':perfil_val', 'aluno', PDO::PARAM_STR);
        } else {
            $countStmt->bindValue(':exclude_perfil', 'aluno', PDO::PARAM_STR);
        }
        $countStmt->execute();
        $total = (int) $countStmt->fetchColumn();

        $sql = "SELECT l.id, l.usuario_id, l.perfil, l.acao, l.entidade, l.entidade_id, l.descricao, l.ip, l.sucesso, l.created_at,
                       $select
                FROM logs_auditoria l
                $join
                WHERE $where";
        $params = [];
        if ($isAluno) {
            $params[':perfil_val'] = ['value' => 'aluno', 'type' => PDO::PARAM_STR];
        } else {
            $params[':exclude_perfil'] = ['value' => 'aluno', 'type' => PDO::PARAM_STR];
        }

        if ($nome !== null && $nome !== '') {
            $nomeCol = $isAluno ? 'a.nome' : 'u.nome';
            $sql .= " AND $nomeCol LIKE :nome";
            $params[':nome'] = ['value' => "%$nome%", 'type' => PDO::PARAM_STR];
        }

        $sql .= ' ORDER BY l.id DESC LIMIT :limit OFFSET :offset';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        foreach ($params as $key => $binding) {
            $stmt->bindValue($key, $binding['value'], $binding['type']);
        }
        $stmt->execute();

        $rows = $stmt->fetchAll();
        return [
            'data' => is_array($rows) ? $rows : [],
            'total' => $total,
        ];
    }

    public function registrar(int $usuarioId, string $perfil, string $acao, string $entidade, int $entidadeId, string $descricao, bool $sucesso = true): void
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return;
        }

        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';

        $sql = 'INSERT INTO logs_auditoria (usuario_id, perfil, acao, entidade, entidade_id, descricao, ip, sucesso)
                VALUES (:usuario_id, :perfil, :acao, :entidade, :entidade_id, :descricao, :ip, :sucesso)';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':usuario_id', $usuarioId, PDO::PARAM_INT);
        $stmt->bindValue(':perfil', $perfil);
        $stmt->bindValue(':acao', $acao);
        $stmt->bindValue(':entidade', $entidade);
        $stmt->bindValue(':entidade_id', $entidadeId, PDO::PARAM_INT);
        $stmt->bindValue(':descricao', $descricao);
        $stmt->bindValue(':ip', $ip);
        $stmt->bindValue(':sucesso', $sucesso ? 1 : 0, PDO::PARAM_INT);
        $stmt->execute();
    }
}
