<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use PDO;

final class CursoPagamentoRepository
{
    public function listarPorCurso(int $idCurso): array
    {
        try {
            $pdo = Database::connection();
            if (!$pdo instanceof PDO) {
                return [];
            }

            $stmt = $pdo->prepare('SELECT id, id_curso, descricao, tipo, parcelas, valor, desconto_percentual, desconto_data_limite, ativo FROM cursos_pagamento WHERE id_curso = :id_curso ORDER BY id ASC');
            $stmt->bindValue(':id_curso', $idCurso, PDO::PARAM_INT);
            $stmt->execute();
            $rows = $stmt->fetchAll();

            if (is_array($rows)) {
                foreach ($rows as &$row) {
                    $row['ativo'] = $this->normalizarAtivo($row['ativo'] ?? null);
                }
                unset($row);
            }

            return is_array($rows) ? $rows : [];
        } catch (\Throwable $e) {
            error_log('[CURSO_PAGAMENTO] Erro ao listar: ' . $e->getMessage());
            return [];
        }
    }

    public function findById(int $id): ?array
    {
        try {
            $pdo = Database::connection();
            if (!$pdo instanceof PDO) {
                return null;
            }

            $stmt = $pdo->prepare('SELECT * FROM cursos_pagamento WHERE id = :id LIMIT 1');
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $row = $stmt->fetch();

            if (is_array($row)) {
                $row['ativo'] = $this->normalizarAtivo($row['ativo'] ?? null);
            }

            return $row ?: null;
        } catch (\Throwable $e) {
            error_log('[CURSO_PAGAMENTO] Erro ao buscar: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Normaliza o campo ativo (que historicamente foi gravado como 'S'/'N'
     * ou '1'/'0') para 0/1.
     */
    private function normalizarAtivo(mixed $ativo): int
    {
        $normalized = strtoupper(trim((string) $ativo));

        return in_array($normalized, ['1', 'S', 'Y', 'TRUE'], true) ? 1 : 0;
    }

    public function save(array $data): int
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return 0;
        }

        try {
            $id = (int) ($data['id'] ?? 0);
            $idCurso = (int) ($data['id_curso'] ?? 0);
            $descricao = trim((string) ($data['descricao'] ?? ''));
            $tipo = in_array((string) ($data['tipo'] ?? ''), ['TODOS', 'PIX', 'BOLETO', 'CARTAO'], true) ? (string) $data['tipo'] : null;
            $parcelas = (int) ($data['parcelas'] ?? 1);
            $valor = (float) ($data['valor'] ?? 0);
            $descontoPercentual = max(0.0, min(100.0, (float) ($data['desconto_percentual'] ?? 0)));
            $descontoDataLimite = isset($data['desconto_data_limite']) && $data['desconto_data_limite'] !== '' ? (string) $data['desconto_data_limite'] : null;
            $ativo = $this->normalizarAtivo($data['ativo'] ?? 1);

            if ($id > 0) {
                $sql = 'UPDATE cursos_pagamento SET descricao = :descricao, tipo = :tipo, parcelas = :parcelas, valor = :valor, desconto_percentual = :desconto_percentual, desconto_data_limite = :desconto_data_limite, ativo = :ativo WHERE id = :id';
                $stmt = $pdo->prepare($sql);
                $stmt->bindValue(':id', $id, PDO::PARAM_INT);
                $stmt->bindValue(':descricao', $descricao);
                $stmt->bindValue(':tipo', $tipo);
                $stmt->bindValue(':parcelas', $parcelas, PDO::PARAM_INT);
                $stmt->bindValue(':valor', $valor);
                $stmt->bindValue(':desconto_percentual', $descontoPercentual);
                $stmt->bindValue(':desconto_data_limite', $descontoDataLimite, $descontoDataLimite !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
                $stmt->bindValue(':ativo', $ativo);
                $stmt->execute();
                return $id;
            }

            $sql = 'INSERT INTO cursos_pagamento (id_curso, descricao, tipo, parcelas, valor, desconto_percentual, desconto_data_limite, ativo) VALUES (:id_curso, :descricao, :tipo, :parcelas, :valor, :desconto_percentual, :desconto_data_limite, :ativo)';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':id_curso', $idCurso, PDO::PARAM_INT);
            $stmt->bindValue(':descricao', $descricao);
            $stmt->bindValue(':tipo', $tipo);
            $stmt->bindValue(':parcelas', $parcelas, PDO::PARAM_INT);
            $stmt->bindValue(':valor', $valor);
            $stmt->bindValue(':desconto_percentual', $descontoPercentual);
            $stmt->bindValue(':desconto_data_limite', $descontoDataLimite, $descontoDataLimite !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
            $stmt->bindValue(':ativo', $ativo);
            $stmt->execute();
            return (int) $pdo->lastInsertId();
        } catch (\Throwable $e) {
            error_log('[CURSO_PAGAMENTO] Erro ao salvar: ' . $e->getMessage());
            return 0;
        }
    }

    public function delete(int $id): void
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return;
        }

        try {
            $stmt = $pdo->prepare('DELETE FROM cursos_pagamento WHERE id = :id');
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
        } catch (\Throwable $e) {
            error_log('[CURSO_PAGAMENTO] Erro ao deletar: ' . $e->getMessage());
        }
    }
}
