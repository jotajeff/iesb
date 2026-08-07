<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\TurmaRepository;

final class TurmaService
{
    public function __construct(
        private readonly TurmaRepository $repository = new TurmaRepository(),
    ) {
    }

    public function turmas(int $limit = 200, ?int $ativo = null): array
    {
        return $this->repository->list($limit, $ativo);
    }

    public function turmasAtivas(int $limit = 500): array
    {
        return $this->repository->listAtivas($limit);
    }

    public function findTurma(int $id): ?array
    {
        if ($id <= 0) {
            return null;
        }

        return $this->repository->findById($id);
    }

    public function criarTurma(string $nome, int $curso, string $dataInicio, int $ativo = 0): int
    {
        $payload = [
            'nome' => trim($nome),
            'id_curso' => $curso > 0 ? $curso : null,
            'data_inicio' => $dataInicio,
            'ativo' => $ativo ? 1 : 0,
        ];
        return $this->repository->save($payload);
    }

    public function atualizarTurma(int $id, string $nome, int $curso, string $dataInicio, int $ativo = 0): void
    {
        $payload = [
            'id' => $id,
            'nome' => trim($nome),
            'id_curso' => $curso > 0 ? $curso : null,
            'data_inicio' => $dataInicio,
            'ativo' => $ativo ? 1 : 0,
        ];
        $this->repository->save($payload);
    }

    public function trocaHistorico(int $limit = 200): array
    {
        return $this->repository->listTrocaHistorico($limit);
    }

    public function turmasDoCurso(int $idCurso): array
    {
        return $this->repository->listByCurso($idCurso);
    }

    public function inscritosPorTurma(int $idTurma): array
    {
        return $this->repository->listInscritosPorTurma($idTurma);
    }
}
