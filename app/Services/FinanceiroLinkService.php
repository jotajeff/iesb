<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\FinanceiroLinkRepository;

final class FinanceiroLinkService
{
    public function __construct(
        private readonly FinanceiroLinkRepository $repository = new FinanceiroLinkRepository(),
    ) {
    }

    public function criar(array $data): int
    {
        return $this->repository->create($data);
    }

    public function listarPorAluno(int $idAluno): array
    {
        return $this->repository->listByAluno($idAluno);
    }

    public function buscar(int $id): ?array
    {
        return $this->repository->findById($id);
    }

    public function marcarReenvio(int $id): bool
    {
        return $this->repository->marcarReenvio($id);
    }
}
