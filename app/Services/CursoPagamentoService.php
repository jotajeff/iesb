<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\CursoPagamentoRepository;

final class CursoPagamentoService
{
    public function __construct(
        private readonly CursoPagamentoRepository $repository = new CursoPagamentoRepository(),
    ) {
    }

    public function listarPorCurso(int $idCurso): array
    {
        return $this->repository->listarPorCurso($idCurso);
    }

    public function find(int $id): ?array
    {
        return $this->repository->findById($id);
    }

    public function salvar(array $data): int
    {
        return $this->repository->save($data);
    }

    public function deletar(int $id): void
    {
        $this->repository->delete($id);
    }
}
