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
}
