<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\CursoInscricaoRepository;

final class CursoInscricaoService
{
    public function __construct(
        private readonly CursoInscricaoRepository $repository = new CursoInscricaoRepository(),
    ) {
    }

    public function criar(array $data): int
    {
        return $this->repository->create($data);
    }

    public function criarComAcordo(array $data): int
    {
        return $this->repository->createComAcordo($data);
    }

    public function buscar(int $id): ?array
    {
        return $this->repository->findById($id);
    }

    public function atualizarAsaasInfo(int $id, array $data): bool
    {
        return $this->repository->updateAsaasInfo($id, $data);
    }

    public function findByAsaasPayment(string $asaasPayment): ?array
    {
        return $this->repository->findByAsaasPayment($asaasPayment);
    }

    public function atualizarStatus(int $id, string $status, ?int $idAluno = null, ?int $idMatricula = null): bool
    {
        return $this->repository->updateStatus($id, $status, $idAluno, $idMatricula);
    }
}
