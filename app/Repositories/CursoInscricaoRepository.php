<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use PDO;

final class CursoInscricaoRepository
{
    public function create(array $data): int
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return 0;
        }

        try {
            $stmt = $pdo->prepare('INSERT INTO cursos_inscricao (id_curso, id_pagamento, descricao_pagamento, nome, cpf, email, telefone, valor, status) VALUES (:id_curso, :id_pagamento, :descricao_pagamento, :nome, :cpf, :email, :telefone, :valor, :status)');
            $stmt->bindValue(':id_curso', (int) ($data['id_curso'] ?? 0), PDO::PARAM_INT);
            $stmt->bindValue(':id_pagamento', (int) ($data['id_pagamento'] ?? 0), PDO::PARAM_INT);
            $stmt->bindValue(':descricao_pagamento', (string) ($data['descricao_pagamento'] ?? ''));
            $stmt->bindValue(':nome', (string) ($data['nome'] ?? ''));
            $stmt->bindValue(':cpf', (string) ($data['cpf'] ?? ''));
            $stmt->bindValue(':email', (string) ($data['email'] ?? ''));
            $stmt->bindValue(':telefone', (string) ($data['telefone'] ?? ''));
            $stmt->bindValue(':valor', (float) ($data['valor'] ?? 0));
            $stmt->bindValue(':status', 'PENDENTE');
            $stmt->execute();
            return (int) $pdo->lastInsertId();
        } catch (\Throwable $e) {
            error_log('[INSCRICAO] Erro ao criar: ' . $e->getMessage());
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

            if (array_key_exists('asaas_payment', $data)) {
                $fields[] = 'asaas_payment = :asaas_payment';
                $params[':asaas_payment'] = $data['asaas_payment'] !== null ? (string) $data['asaas_payment'] : null;
            }

            if (array_key_exists('invoice_url', $data)) {
                $fields[] = 'invoice_url = :invoice_url';
                $params[':invoice_url'] = $data['invoice_url'] !== null ? (string) $data['invoice_url'] : null;
            }

            if (array_key_exists('status', $data)) {
                $fields[] = 'status = :status';
                $params[':status'] = (string) $data['status'];
            }

            if ($fields === []) {
                return true;
            }

            $fields[] = 'updated_at = CURRENT_TIMESTAMP';

            $sql = 'UPDATE cursos_inscricao SET ' . implode(', ', $fields) . ' WHERE id = :id';
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
            error_log('[INSCRICAO] Erro ao atualizar dados do Asaas: ' . $e->getMessage());
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

            $stmt = $pdo->prepare('SELECT * FROM cursos_inscricao WHERE id = :id LIMIT 1');
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $row = $stmt->fetch();
            return $row ?: null;
        } catch (\Throwable $e) {
            error_log('[INSCRICAO] Erro ao buscar: ' . $e->getMessage());
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

            $stmt = $pdo->prepare('SELECT * FROM cursos_inscricao WHERE asaas_payment = :asaas_payment LIMIT 1');
            $stmt->bindValue(':asaas_payment', $asaasPayment);
            $stmt->execute();
            $row = $stmt->fetch();
            return $row ?: null;
        } catch (\Throwable $e) {
            error_log('[INSCRICAO] Erro em findByAsaasPayment: ' . $e->getMessage());
            return null;
        }
    }

    public function updateStatus(int $id, string $status, ?int $idAluno = null, ?int $idMatricula = null): bool
    {
        try {
            $pdo = Database::connection();
            if (!$pdo instanceof PDO) {
                return false;
            }

            $sql = 'UPDATE cursos_inscricao SET status = :status, updated_at = CURRENT_TIMESTAMP';
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
            error_log('[INSCRICAO] Erro em updateStatus: ' . $e->getMessage());
            return false;
        }
    }
}
