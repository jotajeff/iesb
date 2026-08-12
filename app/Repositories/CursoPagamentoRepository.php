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

            $stmt = $pdo->prepare('SELECT id, id_curso, descricao, tipo, parcelas, valor, ativo FROM cursos_pagamento WHERE id_curso = :id_curso ORDER BY id ASC');
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
            $tipo = in_array((string) ($data['tipo'] ?? ''), ['PIX', 'BOLETO', 'CARTAO'], true) ? (string) $data['tipo'] : null;
            $parcelas = (int) ($data['parcelas'] ?? 1);
            $valor = (float) ($data['valor'] ?? 0);
            $ativo = $this->normalizarAtivo($data['ativo'] ?? 1);

            if ($id > 0) {
                $sql = 'UPDATE cursos_pagamento SET descricao = :descricao, tipo = :tipo, parcelas = :parcelas, valor = :valor, ativo = :ativo WHERE id = :id';
                $stmt = $pdo->prepare($sql);
                $stmt->bindValue(':id', $id, PDO::PARAM_INT);
                $stmt->bindValue(':descricao', $descricao);
                $stmt->bindValue(':tipo', $tipo);
                $stmt->bindValue(':parcelas', $parcelas, PDO::PARAM_INT);
                $stmt->bindValue(':valor', $valor);
                $stmt->bindValue(':ativo', $ativo);
                $stmt->execute();
                return $id;
            }

            $sql = 'INSERT INTO cursos_pagamento (id_curso, descricao, tipo, parcelas, valor, ativo) VALUES (:id_curso, :descricao, :tipo, :parcelas, :valor, :ativo)';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':id_curso', $idCurso, PDO::PARAM_INT);
            $stmt->bindValue(':descricao', $descricao);
            $stmt->bindValue(':tipo', $tipo);
            $stmt->bindValue(':parcelas', $parcelas, PDO::PARAM_INT);
            $stmt->bindValue(':valor', $valor);
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
