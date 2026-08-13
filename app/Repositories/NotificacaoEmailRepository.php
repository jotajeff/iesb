<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use PDO;

final class NotificacaoEmailRepository
{
    public function criar(array $data): int
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return 0;
        }

        try {
            $sql = 'INSERT INTO notificacao_email
                    (tipo_origem, id_origem, id_pre_inscricao, id_acordo_pagamento, id_aluno,
                     nome_destinatario, email_destinatario, assunto, mensagem, link,
                     status, id_usuario_envio)
                    VALUES
                    (:tipo_origem, :id_origem, :id_pre_inscricao, :id_acordo_pagamento, :id_aluno,
                     :nome_destinatario, :email_destinatario, :assunto, :mensagem, :link,
                     :status, :id_usuario_envio)';
            $stmt = $pdo->prepare($sql);

            $stmt->bindValue(':tipo_origem', (string) ($data['tipo_origem'] ?? 'ACORDO'));
            $stmt->bindValue(':id_origem', (int) ($data['id_origem'] ?? 0), PDO::PARAM_INT);

            $idPreInscricao = (int) ($data['id_pre_inscricao'] ?? 0);
            $stmt->bindValue(':id_pre_inscricao', $idPreInscricao > 0 ? $idPreInscricao : null, $idPreInscricao > 0 ? PDO::PARAM_INT : PDO::PARAM_NULL);

            $idAcordo = (int) ($data['id_acordo_pagamento'] ?? 0);
            $stmt->bindValue(':id_acordo_pagamento', $idAcordo > 0 ? $idAcordo : null, $idAcordo > 0 ? PDO::PARAM_INT : PDO::PARAM_NULL);

            $idAluno = isset($data['id_aluno']) ? (int) $data['id_aluno'] : 0;
            $stmt->bindValue(':id_aluno', $idAluno > 0 ? $idAluno : null, $idAluno > 0 ? PDO::PARAM_INT : PDO::PARAM_NULL);

            $stmt->bindValue(':nome_destinatario', isset($data['nome_destinatario']) && $data['nome_destinatario'] !== '' ? (string) $data['nome_destinatario'] : null, isset($data['nome_destinatario']) && $data['nome_destinatario'] !== '' ? PDO::PARAM_STR : PDO::PARAM_NULL);
            $stmt->bindValue(':email_destinatario', (string) ($data['email_destinatario'] ?? ''));
            $stmt->bindValue(':assunto', (string) ($data['assunto'] ?? ''));
            $stmt->bindValue(':mensagem', (string) ($data['mensagem'] ?? ''));
            $stmt->bindValue(':link', isset($data['link']) && $data['link'] !== '' ? (string) $data['link'] : null, isset($data['link']) && $data['link'] !== '' ? PDO::PARAM_STR : PDO::PARAM_NULL);
            $stmt->bindValue(':status', (string) ($data['status'] ?? 'PENDENTE'));

            $idUsuario = (int) ($data['id_usuario_envio'] ?? 0);
            $stmt->bindValue(':id_usuario_envio', $idUsuario > 0 ? $idUsuario : null, $idUsuario > 0 ? PDO::PARAM_INT : PDO::PARAM_NULL);

            $stmt->execute();
            return (int) $pdo->lastInsertId();
        } catch (\Throwable $e) {
            error_log('[NOTIFICACAO_EMAIL] Erro ao criar registro: ' . $e->getMessage());
            return 0;
        }
    }

    public function marcarEnviado(int $id): bool
    {
        if ($id < 1) {
            return false;
        }

        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return false;
        }

        try {
            $stmt = $pdo->prepare('UPDATE notificacao_email
                                   SET status = \'ENVIADO\', data_envio = CURRENT_TIMESTAMP, erro = NULL, updated_at = CURRENT_TIMESTAMP
                                   WHERE id = :id');
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->rowCount() > 0;
        } catch (\Throwable $e) {
            error_log('[NOTIFICACAO_EMAIL] Erro ao marcar enviado: ' . $e->getMessage());
            return false;
        }
    }

    public function marcarErro(int $id, string $erro): bool
    {
        if ($id < 1) {
            return false;
        }

        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return false;
        }

        try {
            $stmt = $pdo->prepare('UPDATE notificacao_email
                                   SET status = \'ERRO\', erro = :erro, updated_at = CURRENT_TIMESTAMP
                                   WHERE id = :id');
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->bindValue(':erro', $erro);
            $stmt->execute();
            return $stmt->rowCount() > 0;
        } catch (\Throwable $e) {
            error_log('[NOTIFICACAO_EMAIL] Erro ao marcar erro: ' . $e->getMessage());
            return false;
        }
    }

    public function buscarUltimoPorAcordo(int $idAcordo): ?array
    {
        if ($idAcordo < 1) {
            return null;
        }

        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return null;
        }

        try {
            $stmt = $pdo->prepare('SELECT ne.* FROM notificacao_email ne
                                   WHERE ne.id_acordo_pagamento = :id
                                   ORDER BY ne.id DESC
                                   LIMIT 1');
            $stmt->bindValue(':id', $idAcordo, PDO::PARAM_INT);
            $stmt->execute();
            $row = $stmt->fetch();
            return is_array($row) ? $row : null;
        } catch (\Throwable $e) {
            error_log('[NOTIFICACAO_EMAIL] Erro ao buscar último envio: ' . $e->getMessage());
            return null;
        }
    }

    public function listarPorAcordo(int $idAcordo): array
    {
        if ($idAcordo < 1) {
            return [];
        }

        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return [];
        }

        try {
            $stmt = $pdo->prepare('SELECT ne.id,
                                          ne.email_destinatario,
                                          ne.nome_destinatario,
                                          ne.assunto,
                                          ne.status,
                                          ne.data_envio,
                                          ne.erro,
                                          ne.created_at
                                   FROM notificacao_email ne
                                   WHERE ne.id_acordo_pagamento = :id
                                   ORDER BY ne.id DESC');
            $stmt->bindValue(':id', $idAcordo, PDO::PARAM_INT);
            $stmt->execute();
            $rows = $stmt->fetchAll();
            return is_array($rows) ? $rows : [];
        } catch (\Throwable $e) {
            error_log('[NOTIFICACAO_EMAIL] Erro ao listar histórico: ' . $e->getMessage());
            return [];
        }
    }

    public function listarPorAcordos(array $acordoIds): array
    {
        $acordoIds = array_values(array_unique(array_filter($acordoIds, static fn ($id): bool => (int) $id > 0)));

        if ($acordoIds === []) {
            return [];
        }

        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return [];
        }

        try {
            $placeholders = implode(',', array_fill(0, count($acordoIds), '?'));
            $sql = 'SELECT ne.id,
                           ne.id_acordo_pagamento,
                           ne.email_destinatario,
                           ne.nome_destinatario,
                           ne.assunto,
                           ne.status,
                           ne.data_envio,
                           ne.erro,
                           ne.created_at
                    FROM notificacao_email ne
                    WHERE ne.id_acordo_pagamento IN (' . $placeholders . ')
                    ORDER BY ne.id DESC';
            $stmt = $pdo->prepare($sql);

            foreach ($acordoIds as $i => $id) {
                $stmt->bindValue($i + 1, (int) $id, PDO::PARAM_INT);
            }

            $stmt->execute();
            $rows = $stmt->fetchAll();
            return is_array($rows) ? $rows : [];
        } catch (\Throwable $e) {
            error_log('[NOTIFICACAO_EMAIL] Erro ao listar histórico por acordos: ' . $e->getMessage());
            return [];
        }
    }
}
