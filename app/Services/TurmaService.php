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

    public function turmas(int $limit = 200): array
    {
        return $this->repository->list($limit);
    }

    public function findTurma(int $id): ?array
    {
        if ($id <= 0) {
            return null;
        }

        return $this->repository->findById($id);
    }

    public function criarTurma(string $nome, int $curso, string $dataInicio, string $ativa = 'N'): int
    {
        $payload = [
            'nome' => trim($nome),
            'id_curso' => $curso > 0 ? $curso : null,
            'data_inicio' => $dataInicio,
            'ativa' => strtoupper(trim($ativa)) === 'S' ? 'S' : 'N',
        ];
        return $this->repository->save($payload);
    }

    public function atualizarTurma(int $id, string $nome, int $curso, string $dataInicio, string $ativa = 'N'): void
    {
        $payload = [
            'id' => $id,
            'nome' => trim($nome),
            'id_curso' => $curso > 0 ? $curso : null,
            'data_inicio' => $dataInicio,
            'ativa' => strtoupper(trim($ativa)) === 'S' ? 'S' : 'N',
        ];
        $this->repository->save($payload);
    }

    public function inscritosPorTurma(int $idTurma): array
    {
        return $this->repository->listInscritosPorTurma($idTurma);
    }
}
