<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\ChamadaRepository;

final class ChamadaService
{
    public function __construct(
        private readonly ChamadaRepository $repository = new ChamadaRepository(),
    ) {
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function list(): array
    {
        return $this->repository->list();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function turmas(): array
    {
        return $this->repository->turmas();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function professoresDaTurma(int $idTurma): array
    {
        return $this->repository->professoresDaTurma($idTurma);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function disciplinasDaTurma(int $idTurma, ?int $idProfessor): array
    {
        return $this->repository->disciplinasDaTurma($idTurma, $idProfessor);
    }

    /**
     * @return array<string, mixed>
     */
    public function relatorioPresencas(int $idTurma): array
    {
        return $this->repository->relatorioPresencas($idTurma);
    }

    public function buscarPorId(int $id): ?array
    {
        return $this->repository->buscarPorId($id);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function inscritosDaChamada(int $idChamada): array
    {
        return $this->repository->inscritosDaChamada($idChamada);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function presencasDaChamada(int $idChamada): array
    {
        return $this->repository->presencasDaChamada($idChamada);
    }

    public function registrarPresenca(
        int $idChamada,
        int $idMatricula,
        string $presenca,
        ?string $entrada,
        ?string $permanencia,
        ?string $observacao,
        string $ip,
        string $responsavel
    ): bool {
        return $this->repository->registrarPresenca($idChamada, $idMatricula, $presenca, $entrada, $permanencia, $observacao, $ip, $responsavel);
    }

    public function alterarStatus(int $id, string $status): bool
    {
        return $this->repository->alterarStatus($id, $status);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function gerar(array $data): int
    {
        return $this->repository->save($data);
    }
}