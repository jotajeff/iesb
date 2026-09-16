<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use PDO;

final class ProtocoloRepository
{
    /**
     * Cria o protocolo e a mensagem inicial de forma transacional.
     */
    public function criarComMensagem(int $idAluno, ?int $idMatricula, string $assunto, string $mensagem): int
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO || $idAluno <= 0) {
            return 0;
        }

        try {
            $pdo->beginTransaction();

            $stmt = $pdo->prepare(
                'INSERT INTO protocolos (id_aluno, id_matricula, assunto, status, prioridade, id_usuario_responsavel, created_at, updated_at)'
                . ' VALUES (:id_aluno, :id_matricula, :assunto, \'ABERTO\', \'NORMAL\', NULL, NOW(), NOW())'
            );
            $stmt->bindValue(':id_aluno', $idAluno, PDO::PARAM_INT);
            $stmt->bindValue(':id_matricula', $idMatricula, $idMatricula === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
            $stmt->bindValue(':assunto', $assunto, PDO::PARAM_STR);
            $stmt->execute();

            $idProtocolo = (int) $pdo->lastInsertId();

            $stmt = $pdo->prepare(
                'INSERT INTO protocolo_mensagens (id_protocolo, id_usuario, tipo, mensagem, created_at)'
                . ' VALUES (:id_protocolo, NULL, \'ALUNO\', :mensagem, NOW())'
            );
            $stmt->bindValue(':id_protocolo', $idProtocolo, PDO::PARAM_INT);
            $stmt->bindValue(':mensagem', $mensagem, PDO::PARAM_STR);
            $stmt->execute();

            $pdo->commit();
            return $idProtocolo;
        } catch (\Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            error_log('[PROTOCOLO] Erro em criarComMensagem: ' . $e->getMessage());
            return 0;
        }
    }

    public function adicionarMensagem(int $idProtocolo, ?int $idUsuario, string $tipo, string $mensagem): int
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO || $idProtocolo <= 0) {
            return 0;
        }

        try {
            $stmt = $pdo->prepare(
                'INSERT INTO protocolo_mensagens (id_protocolo, id_usuario, tipo, mensagem, created_at)'
                . ' VALUES (:id_protocolo, :id_usuario, :tipo, :mensagem, NOW())'
            );
            $stmt->bindValue(':id_protocolo', $idProtocolo, PDO::PARAM_INT);
            $stmt->bindValue(':id_usuario', $idUsuario, $idUsuario === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
            $stmt->bindValue(':tipo', $tipo, PDO::PARAM_STR);
            $stmt->bindValue(':mensagem', $mensagem, PDO::PARAM_STR);
            $stmt->execute();

            return (int) $pdo->lastInsertId();
        } catch (\Throwable $e) {
            error_log('[PROTOCOLO] Erro em adicionarMensagem: ' . $e->getMessage());
            return 0;
        }
    }

    public function tocar(int $idProtocolo): void
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO || $idProtocolo <= 0) {
            return;
        }

        try {
            $stmt = $pdo->prepare('UPDATE protocolos SET updated_at = NOW() WHERE id = :id');
            $stmt->bindValue(':id', $idProtocolo, PDO::PARAM_INT);
            $stmt->execute();
        } catch (\Throwable $e) {
            error_log('[PROTOCOLO] Erro em tocar: ' . $e->getMessage());
        }
    }

    public function buscarPorId(int $id): ?array
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO || $id <= 0) {
            return null;
        }

        try {
            $stmt = $pdo->prepare(
                'SELECT p.id, p.id_aluno, p.id_matricula, p.assunto, p.status, p.prioridade,'
                . ' p.id_usuario_responsavel, p.created_at, p.updated_at,'
                . ' a.nome AS aluno_nome, a.email AS aluno_email,'
                . ' m.id AS matricula_id, c.id AS curso_id, c.nome AS curso_nome, t.nome AS turma_nome,'
                . ' u.nome AS responsavel_nome'
                . ' FROM protocolos p'
                . ' LEFT JOIN alunos a ON a.id = p.id_aluno'
                . ' LEFT JOIN matricula m ON m.id = p.id_matricula'
                . ' LEFT JOIN turmas t ON t.id = m.id_turma'
                . ' LEFT JOIN cursos c ON c.id = t.id_curso'
                . ' LEFT JOIN usuarios u ON u.id = p.id_usuario_responsavel'
                . ' WHERE p.id = :id LIMIT 1'
            );
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $row = $stmt->fetch();
            return is_array($row) ? $row : null;
        } catch (\Throwable $e) {
            error_log('[PROTOCOLO] Erro em buscarPorId: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function listarPorAluno(int $idAluno): array
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO || $idAluno <= 0) {
            return [];
        }

        try {
            $stmt = $pdo->prepare(
                'SELECT p.id, p.id_matricula, p.assunto, p.status, p.prioridade, p.created_at, p.updated_at,'
                . ' c.nome AS curso_nome,'
                . ' (SELECT COUNT(*) FROM protocolo_mensagens pm WHERE pm.id_protocolo = p.id) AS total_mensagens'
                . ' FROM protocolos p'
                . ' LEFT JOIN matricula m ON m.id = p.id_matricula'
                . ' LEFT JOIN turmas t ON t.id = m.id_turma'
                . ' LEFT JOIN cursos c ON c.id = t.id_curso'
                . ' WHERE p.id_aluno = :id_aluno'
                . ' ORDER BY p.updated_at DESC, p.id DESC'
            );
            $stmt->bindValue(':id_aluno', $idAluno, PDO::PARAM_INT);
            $stmt->execute();
            $rows = $stmt->fetchAll();
            return is_array($rows) ? $rows : [];
        } catch (\Throwable $e) {
            error_log('[PROTOCOLO] Erro em listarPorAluno: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * @param array<string, mixed> $filtros
     * @return array<int, array<string, mixed>>
     */
    public function listarParaSecretaria(array $filtros = []): array
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return [];
        }

        try {
            $sql = 'SELECT p.id, p.id_aluno, p.id_matricula, p.assunto, p.status, p.prioridade,'
                . ' p.created_at, p.updated_at, a.nome AS aluno_nome, c.nome AS curso_nome,'
                . ' u.nome AS responsavel_nome,'
                . ' (SELECT COUNT(*) FROM protocolo_mensagens pm WHERE pm.id_protocolo = p.id) AS total_mensagens'
                . ' FROM protocolos p'
                . ' LEFT JOIN alunos a ON a.id = p.id_aluno'
                . ' LEFT JOIN matricula m ON m.id = p.id_matricula'
                . ' LEFT JOIN turmas t ON t.id = m.id_turma'
                . ' LEFT JOIN cursos c ON c.id = t.id_curso'
                . ' LEFT JOIN usuarios u ON u.id = p.id_usuario_responsavel'
                . ' WHERE 1 = 1';

            $params = [];
            $status = trim((string) ($filtros['status'] ?? ''));
            if ($status !== '') {
                $sql .= ' AND p.status = :status';
                $params[':status'] = $status;
            }
            $prioridade = trim((string) ($filtros['prioridade'] ?? ''));
            if ($prioridade !== '') {
                $sql .= ' AND p.prioridade = :prioridade';
                $params[':prioridade'] = $prioridade;
            }
            $aluno = trim((string) ($filtros['aluno'] ?? ''));
            if ($aluno !== '') {
                $sql .= ' AND a.nome LIKE :aluno';
                $params[':aluno'] = '%' . $aluno . '%';
            }
            $idCurso = (int) ($filtros['id_curso'] ?? 0);
            if ($idCurso > 0) {
                $sql .= ' AND c.id = :id_curso';
                $params[':id_curso'] = $idCurso;
            }
            $inicio = trim((string) ($filtros['inicio'] ?? ''));
            if ($inicio !== '') {
                $sql .= ' AND p.created_at >= :inicio';
                $params[':inicio'] = $inicio . ' 00:00:00';
            }
            $fim = trim((string) ($filtros['fim'] ?? ''));
            if ($fim !== '') {
                $sql .= ' AND p.created_at <= :fim';
                $params[':fim'] = $fim . ' 23:59:59';
            }

            $sql .= " ORDER BY FIELD(p.prioridade, 'URGENTE', 'ALTA', 'NORMAL') ASC, p.updated_at DESC, p.id DESC";

            $stmt = $pdo->prepare($sql);
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
            }
            $stmt->execute();
            $rows = $stmt->fetchAll();
            return is_array($rows) ? $rows : [];
        } catch (\Throwable $e) {
            error_log('[PROTOCOLO] Erro em listarParaSecretaria: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function listarMensagens(int $idProtocolo): array
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO || $idProtocolo <= 0) {
            return [];
        }

        try {
            $stmt = $pdo->prepare(
                'SELECT pm.id, pm.id_protocolo, pm.id_usuario, pm.tipo, pm.mensagem, pm.created_at, u.nome AS usuario_nome'
                . ' FROM protocolo_mensagens pm'
                . ' LEFT JOIN usuarios u ON u.id = pm.id_usuario'
                . ' WHERE pm.id_protocolo = :id_protocolo'
                . ' ORDER BY pm.created_at ASC, pm.id ASC'
            );
            $stmt->bindValue(':id_protocolo', $idProtocolo, PDO::PARAM_INT);
            $stmt->execute();
            $rows = $stmt->fetchAll();
            return is_array($rows) ? $rows : [];
        } catch (\Throwable $e) {
            error_log('[PROTOCOLO] Erro em listarMensagens: ' . $e->getMessage());
            return [];
        }
    }

    public function atualizarStatus(int $id, string $status): bool
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO || $id <= 0 || !in_array($status, ['ABERTO', 'EM_ATENDIMENTO', 'RESPONDIDO', 'ENCERRADO'], true)) {
            return false;
        }

        try {
            $stmt = $pdo->prepare('UPDATE protocolos SET status = :status, updated_at = NOW() WHERE id = :id');
            $stmt->bindValue(':status', $status, PDO::PARAM_STR);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (\Throwable $e) {
            error_log('[PROTOCOLO] Erro em atualizarStatus: ' . $e->getMessage());
            return false;
        }
    }

    public function atualizarPrioridade(int $id, string $prioridade): bool
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO || $id <= 0 || !in_array($prioridade, ['NORMAL', 'ALTA', 'URGENTE'], true)) {
            return false;
        }

        try {
            $stmt = $pdo->prepare('UPDATE protocolos SET prioridade = :prioridade, updated_at = NOW() WHERE id = :id');
            $stmt->bindValue(':prioridade', $prioridade, PDO::PARAM_STR);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (\Throwable $e) {
            error_log('[PROTOCOLO] Erro em atualizarPrioridade: ' . $e->getMessage());
            return false;
        }
    }

    public function atribuirResponsavel(int $id, int $idUsuario): bool
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO || $id <= 0 || $idUsuario <= 0) {
            return false;
        }

        try {
            $stmt = $pdo->prepare('UPDATE protocolos SET id_usuario_responsavel = :id_usuario, updated_at = NOW() WHERE id = :id');
            $stmt->bindValue(':id_usuario', $idUsuario, PDO::PARAM_INT);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (\Throwable $e) {
            error_log('[PROTOCOLO] Erro em atribuirResponsavel: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * @return array<string, int>
     */
    public function contarPorStatus(): array
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return ['ABERTO' => 0, 'EM_ATENDIMENTO' => 0, 'RESPONDIDO' => 0, 'ENCERRADO' => 0];
        }

        try {
            $stmt = $pdo->query('SELECT status, COUNT(*) AS total FROM protocolos GROUP BY status');
            $contagem = ['ABERTO' => 0, 'EM_ATENDIMENTO' => 0, 'RESPONDIDO' => 0, 'ENCERRADO' => 0];
            foreach ($stmt->fetchAll() ?: [] as $row) {
                $s = (string) ($row['status'] ?? '');
                if (isset($contagem[$s])) {
                    $contagem[$s] = (int) ($row['total'] ?? 0);
                }
            }
            return $contagem;
        } catch (\Throwable $e) {
            error_log('[PROTOCOLO] Erro em contarPorStatus: ' . $e->getMessage());
            return ['ABERTO' => 0, 'EM_ATENDIMENTO' => 0, 'RESPONDIDO' => 0, 'ENCERRADO' => 0];
        }
    }

    /**
     * Lista cursos que possuem protocolos (para filtro).
     *
     * @return array<int, array<string, mixed>>
     */
    public function cursosComProtocolos(): array
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return [];
        }

        try {
            $stmt = $pdo->query(
                'SELECT DISTINCT c.id, c.nome'
                . ' FROM protocolos p'
                . ' JOIN matricula m ON m.id = p.id_matricula'
                . ' JOIN turmas t ON t.id = m.id_turma'
                . ' JOIN cursos c ON c.id = t.id_curso'
                . ' ORDER BY c.nome ASC'
            );
            $rows = $stmt->fetchAll();
            return is_array($rows) ? $rows : [];
        } catch (\Throwable $e) {
            error_log('[PROTOCOLO] Erro em cursosComProtocolos: ' . $e->getMessage());
            return [];
        }
    }
}