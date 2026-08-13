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
                    (tipo, id_pre_inscricao, id_curso_pagamento, id_curso_parcela_origem, id_usuario_autorizacao, cpf, token,
                     valor_entrada, data_vencimento_entrada, total_parcelas, valor_demais_parcelas,
                     desconto, tipo_desconto, motivo, observacao,
                     utilizado, ativo)
                    VALUES
                    (:tipo, :id_pre_inscricao, :id_curso_pagamento, :id_curso_parcela_origem, :id_usuario_autorizacao, :cpf, :token,
                     :valor_entrada, :data_vencimento_entrada, :total_parcelas, :valor_demais_parcelas,
                     :desconto, :tipo_desconto, :motivo, :observacao,
                     :utilizado, :ativo)';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':tipo', (int) ($data['tipo'] ?? 1), PDO::PARAM_INT);
            $stmt->bindValue(':id_pre_inscricao', (int) ($data['id_pre_inscricao'] ?? 0), PDO::PARAM_INT);
            $stmt->bindValue(':id_curso_pagamento', (int) ($data['id_curso_pagamento'] ?? 0), PDO::PARAM_INT);
            $stmt->bindValue(':id_curso_parcela_origem', isset($data['id_curso_parcela_origem']) && (int) $data['id_curso_parcela_origem'] > 0 ? (int) $data['id_curso_parcela_origem'] : null, isset($data['id_curso_parcela_origem']) && (int) $data['id_curso_parcela_origem'] > 0 ? PDO::PARAM_INT : PDO::PARAM_NULL);
            $stmt->bindValue(':id_usuario_autorizacao', (int) ($data['id_usuario_autorizacao'] ?? 0), PDO::PARAM_INT);
            $stmt->bindValue(':cpf', preg_replace('/\D/', '', (string) ($data['cpf'] ?? '')));
            $stmt->bindValue(':token', (string) ($data['token'] ?? ''));
            $stmt->bindValue(':valor_entrada', (float) ($data['valor_entrada'] ?? 0));
            $stmt->bindValue(':data_vencimento_entrada', $data['data_vencimento_entrada'] ?? null, $data['data_vencimento_entrada'] ?? null ? PDO::PARAM_STR : PDO::PARAM_NULL);
            $stmt->bindValue(':total_parcelas', (int) ($data['total_parcelas'] ?? 1), PDO::PARAM_INT);
            $stmt->bindValue(':valor_demais_parcelas', (float) ($data['valor_demais_parcelas'] ?? 0));
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

    public function findByAsaasSubscription(string $subscription): ?array
    {
        if ($subscription === '') {
            return null;
        }

        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return null;
        }

        try {
            $stmt = $pdo->prepare('SELECT * FROM acordo_pagamento WHERE asaas_subscription = :asaas_subscription LIMIT 1');
            $stmt->bindValue(':asaas_subscription', $subscription);
            $stmt->execute();
            $row = $stmt->fetch();
            return is_array($row) ? $row : null;
        } catch (\Throwable $e) {
            error_log('[ACORDO_PAGAMENTO] Erro em findByAsaasSubscription: ' . $e->getMessage());
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

    public function listarComPreInscrito(): array
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return [];
        }

        try {
            $sql = 'SELECT ap.id,
                           ap.tipo,
                           ap.id_pre_inscricao,
                           ap.cpf,
                           ap.valor_entrada,
                           ap.data_vencimento_entrada,
                           ap.total_parcelas,
                           ap.valor_demais_parcelas,
                           ap.desconto,
                           ap.utilizado,
                           ap.ativo,
                           ap.created_at,
                           p.nome AS pre_inscrito_nome,
                           p.email AS pre_inscrito_email,
                           p.whatsapp AS pre_inscrito_whatsapp,
                           COALESCE(c.nome, \'-\') AS curso_nome,
                           cp.descricao AS plano_descricao,
                           cp.tipo AS plano_tipo,
                           pc.pago,
                           pc.ultimo_status,
                           pc.id_aluno,
                           pc.id_matricula,
                           pc.numero_matricula
                    FROM acordo_pagamento ap
                    LEFT JOIN pre_inscricao p ON p.id = ap.id_pre_inscricao
                    LEFT JOIN cursos_pagamento cp ON cp.id = ap.id_curso_pagamento
                    LEFT JOIN cursos c ON c.id = cp.id_curso
                    LEFT JOIN (
                        SELECT cp2.id_acordo_pagamento,
                               MAX(CASE WHEN cp2.status IN (\'RECEBIDO\', \'CONFIRMADO\') THEN 1 ELSE 0 END) AS pago,
                               MAX(cp2.status) AS ultimo_status,
                               MAX(cp2.id_aluno) AS id_aluno,
                               MAX(cp2.id_matricula) AS id_matricula,
                               MAX(m.numero) AS numero_matricula
                        FROM curso_parcela cp2
                        LEFT JOIN matricula m ON m.id = cp2.id_matricula
                        WHERE cp2.id_acordo_pagamento IS NOT NULL
                        GROUP BY cp2.id_acordo_pagamento
                    ) pc ON pc.id_acordo_pagamento = ap.id
                    ORDER BY ap.created_at DESC, ap.id DESC';
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $rows = $stmt->fetchAll();
            return is_array($rows) ? $rows : [];
        } catch (\Throwable $e) {
            error_log('[ACORDO_PAGAMENTO] Erro ao listar acordos: ' . $e->getMessage());
            return [];
        }
    }

    public function buscarIdAlunoPorAcordo(int $idAcordo): ?int
    {
        if ($idAcordo < 1) {
            return null;
        }

        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return null;
        }

        try {
            $stmt = $pdo->prepare('SELECT MAX(id_aluno) FROM curso_parcela WHERE id_acordo_pagamento = :id AND id_aluno IS NOT NULL');
            $stmt->bindValue(':id', $idAcordo, PDO::PARAM_INT);
            $stmt->execute();
            $value = (int) $stmt->fetchColumn();
            return $value > 0 ? $value : null;
        } catch (\Throwable $e) {
            error_log('[ACORDO_PAGAMENTO] Erro ao buscar aluno do acordo: ' . $e->getMessage());
            return null;
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

    public function atualizarRecorrencia(int $id, array $data): bool
    {
        if ($id < 1) {
            return false;
        }

        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return false;
        }

        try {
            $fields = [];
            $params = [':id' => $id];

            if (array_key_exists('recorrencia_cartao', $data)) {
                $fields[] = 'recorrencia_cartao = :recorrencia_cartao';
                $params[':recorrencia_cartao'] = (int) $data['recorrencia_cartao'];
            }

            if (array_key_exists('asaas_subscription', $data)) {
                $fields[] = 'asaas_subscription = :asaas_subscription';
                $params[':asaas_subscription'] = $data['asaas_subscription'] !== null && $data['asaas_subscription'] !== ''
                    ? (string) $data['asaas_subscription']
                    : null;
            }

            if (array_key_exists('data_inicio_recorrencia', $data)) {
                $fields[] = 'data_inicio_recorrencia = :data_inicio_recorrencia';
                $params[':data_inicio_recorrencia'] = $data['data_inicio_recorrencia'] ?? null;
            }

            if (array_key_exists('data_fim_recorrencia', $data)) {
                $fields[] = 'data_fim_recorrencia = :data_fim_recorrencia';
                $params[':data_fim_recorrencia'] = $data['data_fim_recorrencia'] ?? null;
            }

            if (array_key_exists('status_recorrencia', $data)) {
                $fields[] = 'status_recorrencia = :status_recorrencia';
                $params[':status_recorrencia'] = $data['status_recorrencia'] !== null && $data['status_recorrencia'] !== ''
                    ? (string) $data['status_recorrencia']
                    : null;
            }

            if ($fields === []) {
                return true;
            }

            $fields[] = 'updated_at = CURRENT_TIMESTAMP';

            $sql = 'UPDATE acordo_pagamento SET ' . implode(', ', $fields) . ' WHERE id = :id';
            $stmt = $pdo->prepare($sql);

            foreach ($params as $key => $value) {
                if ($value === null) {
                    $stmt->bindValue($key, null, PDO::PARAM_NULL);
                    continue;
                }

                $stmt->bindValue($key, $value);
            }

            return $stmt->execute();
        } catch (\Throwable $e) {
            error_log('[ACORDO_PAGAMENTO] Erro ao atualizar recorrência: ' . $e->getMessage());
            return false;
        }
    }
}
