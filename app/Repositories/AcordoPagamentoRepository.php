<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use PDO;

final class AcordoPagamentoRepository
{
    public function salvar(array $data): int
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return 0;
        }

        try {
            $sql = 'INSERT INTO acordo_pagamento
                    (id_pre_inscricao, id_curso_pagamento, id_usuario_autorizacao, cpf, token,
                     parcelas_negociadas, valor_negociado, desconto, tipo_desconto, motivo, observacao,
                     utilizado, ativo)
                    VALUES
                    (:id_pre_inscricao, :id_curso_pagamento, :id_usuario_autorizacao, :cpf, :token,
                     :parcelas_negociadas, :valor_negociado, :desconto, :tipo_desconto, :motivo, :observacao,
                     :utilizado, :ativo)';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':id_pre_inscricao', (int) ($data['id_pre_inscricao'] ?? 0), PDO::PARAM_INT);
            $stmt->bindValue(':id_curso_pagamento', (int) ($data['id_curso_pagamento'] ?? 0), PDO::PARAM_INT);
            $stmt->bindValue(':id_usuario_autorizacao', (int) ($data['id_usuario_autorizacao'] ?? 0), PDO::PARAM_INT);
            $stmt->bindValue(':cpf', preg_replace('/\D/', '', (string) ($data['cpf'] ?? '')));
            $stmt->bindValue(':token', (string) ($data['token'] ?? ''));
            $stmt->bindValue(':parcelas_negociadas', (int) ($data['parcelas_negociadas'] ?? 1), PDO::PARAM_INT);
            $stmt->bindValue(':valor_negociado', (float) ($data['valor_negociado'] ?? 0));
            $stmt->bindValue(':desconto', (float) ($data['desconto'] ?? 0));
            $stmt->bindValue(':tipo_desconto', (string) ($data['tipo_desconto'] ?? 'NEGOCIACAO'));
            $stmt->bindValue(':motivo', (string) ($data['motivo'] ?? ''));
            $stmt->bindValue(':observacao', (string) ($data['observacao'] ?? ''));
            $stmt->bindValue(':utilizado', (int) ($data['utilizado'] ?? 0), PDO::PARAM_INT);
            $stmt->bindValue(':ativo', (int) ($data['ativo'] ?? 1), PDO::PARAM_INT);
            $stmt->execute();
            return (int) $pdo->lastInsertId();
        } catch (\Throwable $e) {
            error_log('[ACORDO_PAGAMENTO] Erro ao salvar: ' . $e->getMessage());
            return 0;
        }
    }

    public function findByToken(string $token): ?array
    {
        if ($token === '') {
            return null;
        }

        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return null;
        }

        try {
            $sql = 'SELECT ap.*,
                           COALESCE(c.nome, \'-\') AS curso_nome,
                           cp.id AS plano_id,
                           cp.id_curso AS plano_id_curso,
                           cp.descricao AS plano_descricao,
                           cp.tipo AS plano_tipo,
                           cp.valor AS plano_valor,
                           cp.parcelas AS plano_parcelas,
                           p.nome AS candidato_nome,
                           p.email AS candidato_email,
                           p.whatsapp AS candidato_telefone,
                           p.curso_id
                    FROM acordo_pagamento ap
                    LEFT JOIN cursos_pagamento cp ON cp.id = ap.id_curso_pagamento
                    LEFT JOIN cursos c ON c.id = cp.id_curso
                    LEFT JOIN pre_inscricao p ON p.id = ap.id_pre_inscricao
                    WHERE ap.token = :token
                    LIMIT 1';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':token', $token);
            $stmt->execute();
            $row = $stmt->fetch();
            return is_array($row) ? $row : null;
        } catch (\Throwable $e) {
            error_log('[ACORDO_PAGAMENTO] Erro ao buscar por token: ' . $e->getMessage());
            return null;
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
            $stmt = $pdo->prepare('SELECT * FROM acordo_pagamento WHERE id = :id LIMIT 1');
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $row = $stmt->fetch();
            return is_array($row) ? $row : null;
        } catch (\Throwable $e) {
            error_log('[ACORDO_PAGAMENTO] Erro ao buscar por id: ' . $e->getMessage());
            return null;
        }
    }

    public function listarPorPreInscricao(int $idPreInscricao): array
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return [];
        }

        try {
            $sql = 'SELECT ap.*,
                           cp.descricao AS plano_descricao,
                           cp.tipo AS plano_tipo
                    FROM acordo_pagamento ap
                    LEFT JOIN cursos_pagamento cp ON cp.id = ap.id_curso_pagamento
                    WHERE ap.id_pre_inscricao = :id_pre_inscricao
                    ORDER BY ap.created_at DESC';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':id_pre_inscricao', $idPreInscricao, PDO::PARAM_INT);
            $stmt->execute();
            $rows = $stmt->fetchAll();
            return is_array($rows) ? $rows : [];
        } catch (\Throwable $e) {
            error_log('[ACORDO_PAGAMENTO] Erro ao listar por pré-inscrição: ' . $e->getMessage());
            return [];
        }
    }

    public function marcarUtilizado(int $id): bool
    {
        if ($id < 1) {
            return false;
        }

        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return false;
        }

        try {
            $stmt = $pdo->prepare('UPDATE acordo_pagamento SET utilizado = 1, updated_at = CURRENT_TIMESTAMP WHERE id = :id');
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->rowCount() > 0;
        } catch (\Throwable $e) {
            error_log('[ACORDO_PAGAMENTO] Erro ao marcar utilizado: ' . $e->getMessage());
            return false;
        }
    }
}
