<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\EstruturaCurricularRepository;

final class EstruturaCurricularService
{
    public function __construct(
        private readonly EstruturaCurricularRepository $repository = new EstruturaCurricularRepository(),
        private readonly CursoService $cursoService = new CursoService(),
    ) {
    }

    // ==================== MATRIZ ====================

    public function listarMatrizes(?int $idCurso = null, ?int $ativo = null): array
    {
        return $this->repository->listarMatrizes($idCurso, $ativo);
    }

    public function findMatriz(int $id): ?array
    {
        return $this->repository->findMatriz($id);
    }

    public function salvarMatriz(array $data): int
    {
        return $this->repository->salvarMatriz($data);
    }

    public function desativarMatriz(int $id): bool
    {
        return $this->repository->desativarMatriz($id);
    }

    // ==================== MÓDULOS ====================

    public function listarModulos(int $idEstrutura): array
    {
        return $this->repository->listarModulos($idEstrutura);
    }

    public function listarModulosDaTurma(int $idTurma): array
    {
        return $this->repository->listarModulosDaTurma($idTurma);
    }

    public function listarModulosComContexto(?int $idEstrutura = null, ?int $idTurma = null): array
    {
        return $this->repository->listarModulosComContexto($idEstrutura, $idTurma);
    }

    public function findModulo(int $id): ?array
    {
        return $this->repository->findModulo($id);
    }

    public function salvarModulo(array $data): int
    {
        return $this->repository->salvarModulo($data);
    }

    public function desativarModulo(int $id): bool
    {
        return $this->repository->desativarModulo($id);
    }

    // ==================== DISCIPLINAS DA MATRIZ ====================

    public function listarDisciplinasDoModulo(int $idModulo): array
    {
        return $this->repository->listarDisciplinasDoModulo($idModulo);
    }

    public function findDisciplinaDaMatriz(int $id): ?array
    {
        return $this->repository->findDisciplinaDaMatriz($id);
    }

    public function salvarDisciplinaDaMatriz(array $data): int
    {
        return $this->repository->salvarDisciplinaDaMatriz($data);
    }

    public function desativarDisciplinaDaMatriz(int $id): bool
    {
        return $this->repository->desativarDisciplinaDaMatriz($id);
    }

    // ==================== DISCIPLINAS DA TURMA ====================

    public function listarDisciplinasDaTurma(int $idTurma): array
    {
        return $this->repository->listarDisciplinasDaTurma($idTurma);
    }

    public function findDisciplinaDaTurma(int $id): ?array
    {
        return $this->repository->findDisciplinaDaTurma($id);
    }

    public function salvarDisciplinaDaTurma(array $data): int
    {
        return $this->repository->salvarDisciplinaDaTurma($data);
    }

    public function desativarDisciplinaDaTurma(int $id): bool
    {
        return $this->repository->desativarDisciplinaDaTurma($id);
    }

    // ==================== DISCIPLINAS DA MATRÍCULA ====================

    public function listarDisciplinasDaMatricula(int $idMatricula): array
    {
        return $this->repository->listarDisciplinasDaMatricula($idMatricula);
    }

    public function listarIdsDisciplinasDaMatricula(int $idMatricula): array
    {
        return $this->repository->listarIdsDisciplinasDaMatricula($idMatricula);
    }

    public function vincularDisciplinaDaMatricula(int $idMatricula, int $idTurmaDisciplina): bool
    {
        return $this->repository->vincularDisciplinaDaMatricula($idMatricula, $idTurmaDisciplina);
    }

    public function desvincularDisciplinaDaMatricula(int $idMatricula, int $idTurmaDisciplina): bool
    {
        return $this->repository->desvincularDisciplinaDaMatricula($idMatricula, $idTurmaDisciplina);
    }

    // ==================== SITUAÇÃO ACADÊMICA ====================

    public function buscarMatriculasSituacao(?string $termo = null, ?int $idCurso = null, ?int $idTurma = null): array
    {
        return $this->repository->buscarMatriculasSituacao($termo, $idCurso, $idTurma);
    }

    public function findMatriculaParaSituacao(int $idMatricula): ?array
    {
        return $this->repository->findMatriculaParaSituacao($idMatricula);
    }

    public function atualizarDisciplinaDaMatricula(int $id, array $data): bool
    {
        return $this->repository->atualizarDisciplinaDaMatricula($id, $data);
    }

    /**
     * Lista os professores vinculados à turma (para atribuir a disciplinas).
     */
    public function listarProfessoresDaTurma(int $idTurma): array
    {
        $pdo = \App\Core\Database::connection();
        if (!$pdo instanceof \PDO) {
            return [];
        }

        try {
            $stmt = $pdo->prepare('SELECT u.id, u.nome, u.email
                                   FROM turma_professor tp
                                   JOIN usuarios u ON tp.id_usuario = u.id
                                   WHERE tp.id_turma = :id_turma AND tp.status = :status
                                   ORDER BY u.nome ASC');
            $stmt->bindValue(':id_turma', $idTurma, \PDO::PARAM_INT);
            $stmt->bindValue(':status', 'A', \PDO::PARAM_STR);
            $stmt->execute();
            $rows = $stmt->fetchAll();
            return is_array($rows) ? $rows : [];
        } catch (\Throwable $e) {
            error_log('[ESTRUTURA] Erro ao listar professores da turma: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Lista cursos para o seletor (foco em Pós-graduação, mas permite todos).
     */
    public function listarCursosParaSeletor(): array
    {
        return $this->cursoService->cursos();
    }

    /**
     * Lista disciplinas pertencentes ao curso da matriz.
     */
    public function listarDisciplinasDoCurso(int $idCurso): array
    {
        $pdo = \App\Core\Database::connection();
        if (!$pdo instanceof \PDO) {
            return [];
        }

        try {
            $stmt = $pdo->prepare('SELECT id, nome, carga_horaria, ordem, ativo FROM disciplina WHERE id_curso = :id_curso AND ativo = 1 ORDER BY ordem ASC, nome ASC');
            $stmt->bindValue(':id_curso', $idCurso, \PDO::PARAM_INT);
            $stmt->execute();
            $rows = $stmt->fetchAll();
            return is_array($rows) ? $rows : [];
        } catch (\Throwable $e) {
            error_log('[ESTRUTURA] Erro ao listar disciplinas do curso: ' . $e->getMessage());
            return [];
        }
    }

    public function listarDisciplinasDaMatriz(int $idEstrutura): array
    {
        return $this->repository->listarDisciplinasDaMatriz($idEstrutura);
    }
}
