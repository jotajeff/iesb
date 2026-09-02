<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use PDO;

final class CursoParcelaRepository
{
    public function create(array $data): int
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return 0;
        }

        try {
            $stmt = $pdo->prepare('INSERT INTO curso_parcela (id_curso, id_pagamento, id_turma, numero_parcela, total_parcelas, descricao_pagamento, nome, cpf, email, telefone, valor, data_vencimento, status, recorrencia_cartao) VALUES (:id_curso, :id_pagamento, :id_turma, :numero_parcela, :total_parcelas, :descricao_pagamento, :nome, :cpf, :email, :telefone, :valor, :data_vencimento, :status, :recorrencia_cartao)');
            $stmt->bindValue(':id_curso', (int) ($data['id_curso'] ?? 0), PDO::PARAM_INT);
            $stmt->bindValue(':id_pagamento', (int) ($data['id_pagamento'] ?? 0), PDO::PARAM_INT);
            $stmt->bindValue(':id_turma', isset($data['id_turma']) && (int) $data['id_turma'] > 0 ? (int) $data['id_turma'] : null, isset($data['id_turma']) && (int) $data['id_turma'] > 0 ? PDO::PARAM_INT : PDO::PARAM_NULL);
            $stmt->bindValue(':numero_parcela', (int) ($data['numero_parcela'] ?? 1), PDO::PARAM_INT);
            $stmt->bindValue(':total_parcelas', (int) ($data['total_parcelas'] ?? 1), PDO::PARAM_INT);
            $stmt->bindValue(':descricao_pagamento', (string) ($data['descricao_pagamento'] ?? ''));
            $stmt->bindValue(':nome', (string) ($data['nome'] ?? ''));
            $stmt->bindValue(':cpf', (string) ($data['cpf'] ?? ''));
            $stmt->bindValue(':email', (string) ($data['email'] ?? ''));
            $stmt->bindValue(':telefone', (string) ($data['telefone'] ?? ''));
            $stmt->bindValue(':valor', (float) ($data['valor'] ?? 0));
            $stmt->bindValue(':data_vencimento', $data['data_vencimento'] ?? null, $data['data_vencimento'] ?? null ? PDO::PARAM_STR : PDO::PARAM_NULL);
            $stmt->bindValue(':status', 'PENDENTE');
            $stmt->bindValue(':recorrencia_cartao', (int) ($data['recorrencia_cartao'] ?? 0), PDO::PARAM_INT);
            $stmt->execute();
            return (int) $pdo->lastInsertId();
        } catch (\Throwable $e) {
            error_log('[CURSO_PARCELA] Erro ao criar: ' . $e->getMessage());
            return 0;
        }
    }

    public function createComAcordo(array $data): int
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return 0;
        }

        try {
            $stmt = $pdo->prepare('INSERT INTO curso_parcela (id_curso, id_pagamento, id_turma, id_pre_inscricao, id_acordo_pagamento, numero_parcela, total_parcelas, descricao_pagamento, nome, cpf, email, telefone, valor, data_vencimento, status) VALUES (:id_curso, :id_pagamento, :id_turma, :id_pre_inscricao, :id_acordo_pagamento, :numero_parcela, :total_parcelas, :descricao_pagamento, :nome, :cpf, :email, :telefone, :valor, :data_vencimento, :status)');
            $stmt->bindValue(':id_curso', (int) ($data['id_curso'] ?? 0), PDO::PARAM_INT);
            $stmt->bindValue(':id_pagamento', (int) ($data['id_pagamento'] ?? 0), PDO::PARAM_INT);
            $stmt->bindValue(':id_turma', isset($data['id_turma']) && (int) $data['id_turma'] > 0 ? (int) $data['id_turma'] : null, isset($data['id_turma']) && (int) $data['id_turma'] > 0 ? PDO::PARAM_INT : PDO::PARAM_NULL);
            $stmt->bindValue(':id_pre_inscricao', (int) ($data['id_pre_inscricao'] ?? 0), PDO::PARAM_INT);
            $stmt->bindValue(':id_acordo_pagamento', (int) ($data['id_acordo_pagamento'] ?? 0), PDO::PARAM_INT);
            $stmt->bindValue(':numero_parcela', (int) ($data['numero_parcela'] ?? 1), PDO::PARAM_INT);
            $stmt->bindValue(':total_parcelas', (int) ($data['total_parcelas'] ?? 1), PDO::PARAM_INT);
            $stmt->bindValue(':descricao_pagamento', (string) ($data['descricao_pagamento'] ?? ''));
            $stmt->bindValue(':nome', (string) ($data['nome'] ?? ''));
            $stmt->bindValue(':cpf', (string) ($data['cpf'] ?? ''));
            $stmt->bindValue(':email', (string) ($data['email'] ?? ''));
            $stmt->bindValue(':telefone', (string) ($data['telefone'] ?? ''));
            $stmt->bindValue(':valor', (float) ($data['valor'] ?? 0));
            $stmt->bindValue(':data_vencimento', $data['data_vencimento'] ?? null, $data['data_vencimento'] ?? null ? PDO::PARAM_STR : PDO::PARAM_NULL);
            $stmt->bindValue(':status', 'PENDENTE');
            $stmt->execute();
            return (int) $pdo->lastInsertId();
        } catch (\Throwable $e) {
            error_log('[CURSO_PARCELA] Erro ao criar com acordo: ' . $e->getMessage());
            return 0;
        }
    }

    public function updateAsaasInfo(int $id, array $data): bool
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return false;
        }

        try {
            $fields = [];
            $params = [':id' => $id];

            if (array_key_exists('asaas_customer', $data)) {
                $fields[] = 'asaas_customer = :asaas_customer';
                $params[':asaas_customer'] = $data['asaas_customer'] !== null ? (string) $data['asaas_customer'] : null;
            }

            if (array_key_exists('asaas_subscription', $data)) {
                $fields[] = 'asaas_subscription = :asaas_subscription';
                $params[':asaas_subscription'] = $data['asaas_subscription'] !== null ? (string) $data['asaas_subscription'] : null;
            }

            if (array_key_exists('asaas_payment', $data)) {
                $fields[] = 'asaas_payment = :asaas_payment';
                $params[':asaas_payment'] = $data['asaas_payment'] !== null ? (string) $data['asaas_payment'] : null;
            }

            if (array_key_exists('invoice_url', $data)) {
                $fields[] = 'invoice_url = :invoice_url';
                $params[':invoice_url'] = $data['invoice_url'] !== null ? (string) $data['invoice_url'] : null;
            }

            if (array_key_exists('bank_slip_url', $data)) {
                $fields[] = 'bank_slip_url = :bank_slip_url';
                $params[':bank_slip_url'] = $data['bank_slip_url'] !== null ? (string) $data['bank_slip_url'] : null;
            }

            if (array_key_exists('status', $data)) {
                $fields[] = 'status = :status';
                $params[':status'] = (string) $data['status'];
            }

            if ($fields === []) {
                return true;
            }

            $fields[] = 'updated_at = CURRENT_TIMESTAMP';

            $sql = 'UPDATE curso_parcela SET ' . implode(', ', $fields) . ' WHERE id = :id';
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
            error_log('[CURSO_PARCELA] Erro ao atualizar dados do Asaas: ' . $e->getMessage());
            return false;
        }
    }

    public function vincularAcordo(int $id, int $idAcordo): bool
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO || $id <= 0 || $idAcordo <= 0) {
            return false;
        }

        try {
            $stmt = $pdo->prepare('UPDATE curso_parcela
                                   SET id_acordo_pagamento = :id_acordo, updated_at = CURRENT_TIMESTAMP
                                   WHERE id = :id AND ativo = 1');
            return $stmt->execute([':id_acordo' => $idAcordo, ':id' => $id]);
        } catch (\Throwable $e) {
            error_log('[CURSO_PARCELA] Erro ao vincular acordo: ' . $e->getMessage());
            return false;
        }
    }

    public function findById(int $id): ?array
    {
        try {
            $pdo = Database::connection();
            if (!$pdo instanceof PDO) {
                return null;
            }

            $stmt = $pdo->prepare('SELECT * FROM curso_parcela WHERE id = :id LIMIT 1');
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $row = $stmt->fetch();
            return $row ?: null;
        } catch (\Throwable $e) {
            error_log('[CURSO_PARCELA] Erro ao buscar: ' . $e->getMessage());
            return null;
        }
    }

    public function findByAsaasPayment(string $asaasPayment): ?array
    {
        try {
            $pdo = Database::connection();
            if (!$pdo instanceof PDO) {
                return null;
            }

            $stmt = $pdo->prepare('SELECT * FROM curso_parcela WHERE asaas_payment = :asaas_payment LIMIT 1');
            $stmt->bindValue(':asaas_payment', $asaasPayment);
            $stmt->execute();
            $row = $stmt->fetch();
            return $row ?: null;
        } catch (\Throwable $e) {
            error_log('[CURSO_PARCELA] Erro em findByAsaasPayment: ' . $e->getMessage());
            return null;
        }
    }

    public function findByExternalReference(int $id): ?array
    {
        if ($id < 1) {
            return null;
        }

        try {
            $pdo = Database::connection();
            if (!$pdo instanceof PDO) {
                return null;
            }

            $stmt = $pdo->prepare('SELECT * FROM curso_parcela
                                   WHERE id = :id AND ativo = 1
                                     AND (asaas_payment IS NULL OR asaas_payment = \'\')
                                   LIMIT 1');
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $row = $stmt->fetch();
            return $row ?: null;
        } catch (\Throwable $e) {
            error_log('[CURSO_PARCELA] Erro em findByExternalReference: ' . $e->getMessage());
            return null;
        }
    }

    public function findByAsaasSubscription(string $subscription): ?array
    {
        if ($subscription === '') {
            return null;
        }

        try {
            $pdo = Database::connection();
            if (!$pdo instanceof PDO) {
                return null;
            }

            $stmt = $pdo->prepare('SELECT * FROM curso_parcela WHERE asaas_subscription = :asaas_subscription AND ativo = 1 LIMIT 1');
            $stmt->bindValue(':asaas_subscription', $subscription);
            $stmt->execute();
            $row = $stmt->fetch();
            return $row ?: null;
        } catch (\Throwable $e) {
            error_log('[CURSO_PARCELA] Erro em findByAsaasSubscription: ' . $e->getMessage());
            return null;
        }
    }

    public function atualizarRecorrencia(int $id, array $data): bool
    {
        if ($id < 1) {
            return false;
        }

        try {
            $pdo = Database::connection();
            if (!$pdo instanceof PDO) {
                return false;
            }

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

            $sql = 'UPDATE curso_parcela SET ' . implode(', ', $fields) . ' WHERE id = :id';
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
            error_log('[CURSO_PARCELA] Erro em atualizarRecorrencia: ' . $e->getMessage());
            return false;
        }
    }

    public function listarPagasSemMatricula(): array
    {
        try {
            $pdo = Database::connection();
            if (!$pdo instanceof PDO) {
                return [];
            }

            $stmt = $pdo->prepare('SELECT cp.*
                                   FROM curso_parcela cp
                                   WHERE cp.ativo = 1
                                     AND cp.status IN (\'RECEBIDO\', \'CONFIRMADO\')
                                     AND cp.id_matricula IS NULL
                                   ORDER BY cp.id ASC');
            $stmt->execute();
            $rows = $stmt->fetchAll();
            return is_array($rows) ? $rows : [];
        } catch (\Throwable $e) {
            error_log('[CURSO_PARCELA] Erro em listarPagasSemMatricula: ' . $e->getMessage());
            return [];
        }
    }

    public function listByAluno(int $idAluno): array
    {
        try {
            $pdo = Database::connection();
            if (!$pdo instanceof PDO) {
                return [];
            }

            $sql = 'SELECT cp.*, COALESCE(c.nome, \'-\') AS curso_nome, COALESCE(t.nome, \'-\') AS turma_nome
                    FROM curso_parcela cp
                    LEFT JOIN cursos c ON c.id = cp.id_curso
                    LEFT JOIN turmas t ON t.id = cp.id_turma
                    WHERE cp.ativo = 1
                      AND (
                          cp.id_aluno = :id_aluno
                          OR (
                              cp.id_acordo_pagamento IS NOT NULL
                              AND cp.id_acordo_pagamento IN (
                                  SELECT cp2.id_acordo_pagamento
                                  FROM curso_parcela cp2
                                  WHERE cp2.id_aluno = :id_aluno2
                                    AND cp2.id_acordo_pagamento IS NOT NULL
                                    AND cp2.ativo = 1
                              )
                          )
                      )
                    ORDER BY c.nome ASC, cp.numero_parcela ASC';

            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':id_aluno', $idAluno, PDO::PARAM_INT);
            $stmt->bindValue(':id_aluno2', $idAluno, PDO::PARAM_INT);
            $stmt->execute();

            $rows = $stmt->fetchAll();
            return is_array($rows) ? $rows : [];
        } catch (\Throwable $e) {
            error_log('[CURSO_PARCELA] Erro em listByAluno: ' . $e->getMessage());
            return [];
        }
    }

    public function listByAcordo(int $idAcordo): array
    {
        try {
            $pdo = Database::connection();
            if (!$pdo instanceof PDO) {
                return [];
            }

            $stmt = $pdo->prepare('SELECT cp.*
                                   FROM curso_parcela cp
                                   WHERE cp.id_acordo_pagamento = :id_acordo AND cp.ativo = 1
                                   ORDER BY cp.numero_parcela ASC');
            $stmt->bindValue(':id_acordo', $idAcordo, PDO::PARAM_INT);
            $stmt->execute();

            $rows = $stmt->fetchAll();
            return is_array($rows) ? $rows : [];
        } catch (\Throwable $e) {
            error_log('[CURSO_PARCELA] Erro em listByAcordo: ' . $e->getMessage());
            return [];
        }
    }

    public function listByInscricao(int $idAluno, int $idPagamento, int $idCurso): array
    {
        try {
            $pdo = Database::connection();
            if (!$pdo instanceof PDO) {
                return [];
            }

            $stmt = $pdo->prepare('SELECT cp.*
                                   FROM curso_parcela cp
                                   WHERE cp.id_aluno = :id_aluno
                                     AND cp.id_pagamento = :id_pagamento
                                     AND cp.id_curso = :id_curso
                                     AND cp.ativo = 1
                                   ORDER BY cp.numero_parcela ASC');
            $stmt->bindValue(':id_aluno', $idAluno, PDO::PARAM_INT);
            $stmt->bindValue(':id_pagamento', $idPagamento, PDO::PARAM_INT);
            $stmt->bindValue(':id_curso', $idCurso, PDO::PARAM_INT);
            $stmt->execute();

            $rows = $stmt->fetchAll();
            return is_array($rows) ? $rows : [];
        } catch (\Throwable $e) {
            error_log('[CURSO_PARCELA] Erro em listByInscricao: ' . $e->getMessage());
            return [];
        }
    }

    public function updateStatus(int $id, string $status, ?int $idAluno = null, ?int $idMatricula = null): bool
    {
        try {
            $pdo = Database::connection();
            if (!$pdo instanceof PDO) {
                return false;
            }

            $sql = 'UPDATE curso_parcela SET status = :status, updated_at = CURRENT_TIMESTAMP';
            $params = [':status' => $status, ':id' => $id];

            if ($idAluno !== null) {
                $sql .= ', id_aluno = :id_aluno';
                $params[':id_aluno'] = $idAluno;
            }
            if ($idMatricula !== null) {
                $sql .= ', id_matricula = :id_matricula';
                $params[':id_matricula'] = $idMatricula;
            }

            $sql .= ' WHERE id = :id';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':status', $status);
            if ($idAluno !== null) {
                $stmt->bindValue(':id_aluno', $idAluno, PDO::PARAM_INT);
            }
            if ($idMatricula !== null) {
                $stmt->bindValue(':id_matricula', $idMatricula, PDO::PARAM_INT);
            }
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (\Throwable $e) {
            error_log('[CURSO_PARCELA] Erro em updateStatus: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Propaga id_aluno/id_matricula para as parcelas do acordo que ainda não
     * possuem vínculo (dados gerados antes da correção). Evita que parcelas
     * legadas fiquem ocultas ou sejam rejeitadas no pagamento do painel.
     */
    public function vincularAlunoPorAcordo(int $idAcordo, int $idAluno, int $idMatricula): bool
    {
        if ($idAcordo < 1 || $idAluno < 1) {
            return false;
        }

        try {
            $pdo = Database::connection();
            if (!$pdo instanceof PDO) {
                return false;
            }

            $stmt = $pdo->prepare('UPDATE curso_parcela
                                   SET id_aluno = :id_aluno,
                                       id_matricula = :id_matricula,
                                       updated_at = CURRENT_TIMESTAMP
                                   WHERE id_acordo_pagamento = :id_acordo
                                     AND ativo = 1
                                     AND (id_aluno IS NULL OR id_aluno = 0)');
            $stmt->bindValue(':id_acordo', $idAcordo, PDO::PARAM_INT);
            $stmt->bindValue(':id_aluno', $idAluno, PDO::PARAM_INT);
            $stmt->bindValue(':id_matricula', $idMatricula, PDO::PARAM_INT);
            $stmt->execute();
            return true;
        } catch (\Throwable $e) {
            error_log('[CURSO_PARCELA] Erro em vincularAlunoPorAcordo: ' . $e->getMessage());
            return false;
        }
    }
}
