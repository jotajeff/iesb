<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use PDO;

final class FinanceiroLinkRepository
{
    public function create(array $data): int
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) return 0;

        try {
            $stmt = $pdo->prepare('INSERT INTO financeiro_link
                (id_aluno, id_matricula, id_parcela_origem, id_acordo_pagamento, token, url, nome_aluno, email_destino, valor_parcela, enviado_em, ativo)
                VALUES (:id_aluno, :id_matricula, :id_parcela_origem, :id_acordo_pagamento, :token, :url, :nome_aluno, :email_destino, :valor_parcela, :enviado_em, 1)');
            $stmt->execute([
                ':id_aluno' => (int) ($data['id_aluno'] ?? 0),
                ':id_matricula' => (int) ($data['id_matricula'] ?? 0),
                ':id_parcela_origem' => (int) ($data['id_parcela_origem'] ?? 0),
                ':id_acordo_pagamento' => (int) ($data['id_acordo_pagamento'] ?? 0) > 0 ? (int) $data['id_acordo_pagamento'] : null,
                ':token' => (string) ($data['token'] ?? ''),
                ':url' => (string) ($data['url'] ?? ''),
                ':nome_aluno' => (string) ($data['nome_aluno'] ?? ''),
                ':email_destino' => (string) ($data['email_destino'] ?? ''),
                ':valor_parcela' => (float) ($data['valor_parcela'] ?? 0),
                ':enviado_em' => $data['enviado_em'] ?? null,
            ]);
            return (int) $pdo->lastInsertId();
        } catch (\Throwable $e) {
            error_log('[FINANCEIRO_LINK] Erro ao criar: ' . $e->getMessage());
            return 0;
        }
    }

    public function listByAluno(int $idAluno): array
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO || $idAluno <= 0) return [];

        try {
            $stmt = $pdo->prepare('SELECT * FROM financeiro_link
                                   WHERE id_aluno = :id_aluno AND ativo = 1
                                   ORDER BY id DESC');
            $stmt->execute([':id_aluno' => $idAluno]);
            $rows = $stmt->fetchAll();
            return is_array($rows) ? $rows : [];
        } catch (\Throwable $e) {
            error_log('[FINANCEIRO_LINK] Erro ao listar: ' . $e->getMessage());
            return [];
        }
    }

    public function findById(int $id): ?array
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO || $id <= 0) return null;

        try {
            $stmt = $pdo->prepare('SELECT * FROM financeiro_link WHERE id = :id AND ativo = 1 LIMIT 1');
            $stmt->execute([':id' => $id]);
            $row = $stmt->fetch();
            return is_array($row) ? $row : null;
        } catch (\Throwable $e) {
            error_log('[FINANCEIRO_LINK] Erro ao buscar: ' . $e->getMessage());
            return null;
        }
    }

    public function marcarReenvio(int $id): bool
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO || $id <= 0) return false;

        try {
            $stmt = $pdo->prepare('UPDATE financeiro_link
                                   SET ultimo_reenvio_em = CURRENT_TIMESTAMP,
                                       updated_at = CURRENT_TIMESTAMP
                                   WHERE id = :id AND ativo = 1');
            return $stmt->execute([':id' => $id]);
        } catch (\Throwable $e) {
            error_log('[FINANCEIRO_LINK] Erro ao marcar reenvio: ' . $e->getMessage());
            return false;
        }
    }
}
