<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Repositories\ProtocoloRepository;
use PDO;

final class ProtocoloService
{
    public const STATUS = ['ABERTO', 'EM_ATENDIMENTO', 'RESPONDIDO', 'ENCERRADO'];
    public const PRIORIDADES = ['NORMAL', 'ALTA', 'URGENTE'];

    public function __construct(
        private readonly ProtocoloRepository $repository = new ProtocoloRepository(),
    ) {
    }

    public function abrir(int $idAluno, ?int $idMatricula, string $assunto, string $mensagem): int
    {
        $assunto = trim($assunto);
        $mensagem = trim($mensagem);

        if ($idAluno <= 0 || $assunto === '' || $mensagem === '') {
            return -1;
        }

        if (mb_strlen($assunto) > 255) {
            return -1;
        }

        if ($idMatricula !== null && !$this->matriculaPertenceAoAluno($idAluno, $idMatricula)) {
            return -1;
        }

        return $this->repository->criarComMensagem($idAluno, $idMatricula, $assunto, $mensagem);
    }

    public function responderAluno(int $idProtocolo, string $mensagem): bool
    {
        $mensagem = trim($mensagem);
        if ($idProtocolo <= 0 || $mensagem === '') {
            return false;
        }

        $protocolo = $this->repository->buscarPorId($idProtocolo);
        if (!$protocolo) {
            return false;
        }

        $id = $this->repository->adicionarMensagem($idProtocolo, null, 'ALUNO', $mensagem);
        if ($id <= 0) {
            return false;
        }

        if ((string) ($protocolo['status'] ?? '') === 'RESPONDIDO') {
            $this->repository->atualizarStatus($idProtocolo, 'EM_ATENDIMENTO');
        } else {
            $this->repository->tocar($idProtocolo);
        }

        return true;
    }

    public function responderSecretaria(int $idProtocolo, int $idUsuario, string $mensagem): bool
    {
        $mensagem = trim($mensagem);
        if ($idProtocolo <= 0 || $idUsuario <= 0 || $mensagem === '') {
            return false;
        }

        $id = $this->repository->adicionarMensagem($idProtocolo, $idUsuario, 'SECRETARIA', $mensagem);
        if ($id <= 0) {
            return false;
        }

        $this->repository->atualizarStatus($idProtocolo, 'RESPONDIDO');
        return true;
    }

    public function assumir(int $idProtocolo, int $idUsuario): bool
    {
        if ($idProtocolo <= 0 || $idUsuario <= 0) {
            return false;
        }
        if (!$this->repository->atribuirResponsavel($idProtocolo, $idUsuario)) {
            return false;
        }
        return $this->repository->atualizarStatus($idProtocolo, 'EM_ATENDIMENTO');
    }

    public function atualizarStatus(int $idProtocolo, string $status): bool
    {
        if (!in_array($status, self::STATUS, true)) {
            return false;
        }
        return $this->repository->atualizarStatus($idProtocolo, $status);
    }

    public function atualizarPrioridade(int $idProtocolo, string $prioridade): bool
    {
        if (!in_array($prioridade, self::PRIORIDADES, true)) {
            return false;
        }
        return $this->repository->atualizarPrioridade($idProtocolo, $prioridade);
    }

    public function buscarPorId(int $id): ?array
    {
        return $this->repository->buscarPorId($id);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function listarPorAluno(int $idAluno): array
    {
        return $this->repository->listarPorAluno($idAluno);
    }

    /**
     * @param array<string, mixed> $filtros
     * @return array<int, array<string, mixed>>
     */
    public function listarParaSecretaria(array $filtros = []): array
    {
        return $this->repository->listarParaSecretaria($filtros);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function listarMensagens(int $idProtocolo): array
    {
        return $this->repository->listarMensagens($idProtocolo);
    }

    /**
     * @return array<string, int>
     */
    public function contarPorStatus(): array
    {
        return $this->repository->contarPorStatus();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function cursosComProtocolos(): array
    {
        return $this->repository->cursosComProtocolos();
    }

    /**
     * Matrículas do aluno (para o select de abertura).
     *
     * @return array<int, array<string, mixed>>
     */
    public function matriculasDoAluno(int $idAluno): array
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO || $idAluno <= 0) {
            return [];
        }

        try {
            $stmt = $pdo->prepare(
                'SELECT m.id AS matricula_id, m.status, m.data_matricula,'
                . ' t.nome AS turma_nome, c.id AS curso_id, c.nome AS curso_nome'
                . ' FROM matricula m'
                . ' INNER JOIN turmas t ON m.id_turma = t.id'
                . ' INNER JOIN cursos c ON t.id_curso = c.id'
                . ' WHERE m.id_aluno = :id_aluno'
                . ' ORDER BY c.nome ASC'
            );
            $stmt->bindValue(':id_aluno', $idAluno, PDO::PARAM_INT);
            $stmt->execute();
            $rows = $stmt->fetchAll();
            return is_array($rows) ? $rows : [];
        } catch (\Throwable $e) {
            error_log('[PROTOCOLO] Erro em matriculasDoAluno: ' . $e->getMessage());
            return [];
        }
    }

    public function matriculaPertenceAoAluno(int $idAluno, int $idMatricula): bool
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO || $idAluno <= 0 || $idMatricula <= 0) {
            return false;
        }

        try {
            $stmt = $pdo->prepare('SELECT 1 FROM matricula WHERE id = :id_matricula AND id_aluno = :id_aluno LIMIT 1');
            $stmt->bindValue(':id_matricula', $idMatricula, PDO::PARAM_INT);
            $stmt->bindValue(':id_aluno', $idAluno, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchColumn() !== false;
        } catch (\Throwable $e) {
            error_log('[PROTOCOLO] Erro em matriculaPertenceAoAluno: ' . $e->getMessage());
            return false;
        }
    }
}