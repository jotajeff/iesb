<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use PDO;

final class PreInscricaoRepository
{
    public function listar(string $situacao = 'recebido'): array
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return [];
        }

        try {
            $sql = 'SELECT p.id, p.nome, p.email, p.whatsapp, p.ip, p.curso_id,
                           COALESCE(c.nome, \'-\') AS curso_nome,
                           p.situacao, p.criado_em
                    FROM pre_inscricao p
                    LEFT JOIN cursos_iesb c ON c.id = p.curso_id
                    WHERE p.situacao = :situacao
                    ORDER BY p.criado_em DESC';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':situacao', $situacao);
            $stmt->execute();
            $rows = $stmt->fetchAll();
            return is_array($rows) ? $rows : [];
        } catch (\Throwable $e) {
            error_log('[PRE_INSCRICAO] Erro ao listar: ' . $e->getMessage());
            return [];
        }
    }

    public function listarTodos(): array
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return [];
        }

        try {
            $sql = 'SELECT p.id, p.nome, p.email, p.whatsapp, p.ip, p.curso_id,
                           COALESCE(c.nome, \'-\') AS curso_nome,
                           p.situacao, p.criado_em
                    FROM pre_inscricao p
                    LEFT JOIN cursos_iesb c ON c.id = p.curso_id
                    ORDER BY p.criado_em DESC';
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $rows = $stmt->fetchAll();
            return is_array($rows) ? $rows : [];
        } catch (\Throwable $e) {
            error_log('[PRE_INSCRICAO] Erro ao listar todos: ' . $e->getMessage());
            return [];
        }
    }

    public function salvar(array $data): int
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return 0;
        }

        try {
            $sql = 'INSERT INTO pre_inscricao (nome, email, whatsapp, ip, curso_id, situacao)
                    VALUES (:nome, :email, :whatsapp, :ip, :curso_id, :situacao)';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':nome', trim((string) ($data['nome'] ?? '')));
            $stmt->bindValue(':email', trim((string) ($data['email'] ?? '')));
            $stmt->bindValue(':whatsapp', trim((string) ($data['whatsapp'] ?? '')));
            $stmt->bindValue(':ip', trim((string) ($data['ip'] ?? '')));
            $stmt->bindValue(':curso_id', (int) ($data['curso_id'] ?? 0), PDO::PARAM_INT);
            $stmt->bindValue(':situacao', 'recebido');
            $stmt->execute();
            return (int) $pdo->lastInsertId();
        } catch (\Throwable $e) {
            error_log('[PRE_INSCRICAO] Erro ao salvar: ' . $e->getMessage());
            return 0;
        }
    }

    public function findById(int $id): ?array
    {
        if ($id < 1) {
            return null;
        }

        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return null;
        }

        try {
            $sql = 'SELECT p.id, p.nome, p.email, p.whatsapp, p.ip, p.curso_id,
                           COALESCE(c.nome, \'-\') AS curso_nome,
                           p.situacao, p.criado_em
                    FROM pre_inscricao p
                    LEFT JOIN cursos_iesb c ON c.id = p.curso_id
                    WHERE p.id = :id
                    LIMIT 1';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $row = $stmt->fetch();
            return is_array($row) ? $row : null;
        } catch (\Throwable $e) {
            error_log('[PRE_INSCRICAO] Erro ao buscar: ' . $e->getMessage());
            return null;
        }
    }

    public function atualizarSituacao(int $id, string $situacao): bool
    {
        if ($id < 1 || !in_array($situacao, ['recebido', 'atendimento', 'finalizado'], true)) {
            return false;
        }

        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return false;
        }

        try {
            $sql = 'UPDATE pre_inscricao SET situacao = :situacao WHERE id = :id';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':situacao', $situacao);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->rowCount() > 0;
        } catch (\Throwable $e) {
            error_log('[PRE_INSCRICAO] Erro ao atualizar situação: ' . $e->getMessage());
            return false;
        }
    }
}
