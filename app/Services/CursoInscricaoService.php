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

    public function buscar(int $id): ?array
    {
        return $this->repository->findById($id);
    }

    public function atualizarAsaasInfo(int $id, array $data): bool
    {
        return $this->repository->updateAsaasInfo($id, $data);
    }
}
