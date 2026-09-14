<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use PDO;

final class HistoricoVencimentoParcelaRepository
{
    public function reagendar(int $idParcela, string $novaData, ?int $idUsuario, string $motivo = ''): bool
    {
        if ($idParcela <= 0 || $novaData === '') {
            return false;
        }

        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return false;
        }

        try {
            $pdo->beginTransaction();

            $stmt = $pdo->prepare('SELECT data_vencimento FROM curso_parcela WHERE id = :id AND ativo = 1 FOR UPDATE');
            $stmt->execute([':id' => $idParcela]);
            $parcela = $stmt->fetch();
            if (!is_array($parcela)) {
                $pdo->rollBack();
                return false;
            }

            $dataAnterior = (string) ($parcela['data_vencimento'] ?? '');
            if ($dataAnterior === $novaData) {
                $pdo->rollBack();
                return false;
            }

            $update = $pdo->prepare('UPDATE curso_parcela
                                     SET data_vencimento = :data_vencimento,
                                         updated_at = CURRENT_TIMESTAMP
                                     WHERE id = :id AND ativo = 1');
            $update->execute([
                ':data_vencimento' => $novaData,
                ':id' => $idParcela,
            ]);

            $history = $pdo->prepare('INSERT INTO historico_vencimento_parcela
                                      (id_curso_parcela, data_anterior, data_nova, id_usuario, motivo)
                                      VALUES (:id_curso_parcela, :data_anterior, :data_nova, :id_usuario, :motivo)');
            $history->bindValue(':id_curso_parcela', $idParcela, PDO::PARAM_INT);
            $history->bindValue(':data_anterior', $dataAnterior);
            $history->bindValue(':data_nova', $novaData);
            $history->bindValue(':id_usuario', $idUsuario, $idUsuario === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
            $history->bindValue(':motivo', $motivo !== '' ? $motivo : null, $motivo !== '' ? PDO::PARAM_STR : PDO::PARAM_NULL);
            $history->execute();

            $pdo->commit();
            return true;
        } catch (\Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            error_log('[HISTORICO_VENCIMENTO] Erro ao reagendar parcela: ' . $e->getMessage());
            return false;
        }
    }

    public function listByAluno(int $idAluno): array
    {
        if ($idAluno <= 0) {
            return [];
        }

        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return [];
        }

        try {
            $stmt = $pdo->prepare('SELECT h.*, cp.numero_parcela, cp.total_parcelas,
                                          cp.id_aluno, c.nome AS curso_nome,
                                          u.nome AS usuario_nome
                                   FROM historico_vencimento_parcela h
                                   INNER JOIN curso_parcela cp ON cp.id = h.id_curso_parcela
                                   LEFT JOIN cursos c ON c.id = cp.id_curso
                                   LEFT JOIN usuarios u ON u.id = h.id_usuario
                                   WHERE cp.id_aluno = :id_aluno
                                   ORDER BY h.created_at DESC, h.id DESC');
            $stmt->execute([':id_aluno' => $idAluno]);
            $rows = $stmt->fetchAll();
            return is_array($rows) ? $rows : [];
        } catch (\Throwable $e) {
            error_log('[HISTORICO_VENCIMENTO] Erro ao listar histórico: ' . $e->getMessage());
            return [];
        }
    }
}
