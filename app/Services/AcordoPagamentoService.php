<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\AcordoPagamentoRepository;

final class AcordoPagamentoService
{
    public function __construct(
        private readonly AcordoPagamentoRepository $repository = new AcordoPagamentoRepository(),
    ) {
    }

    public function salvar(array $data): int
    {
        return $this->repository->salvar($data);
    }

    public function findByToken(string $token): ?array
    {
        return $this->repository->findByToken($token);
    }

    public function findById(int $id): ?array
    {
        return $this->repository->findById($id);
    }

    public function listarPorPreInscricao(int $idPreInscricao): array
    {
        return $this->repository->listarPorPreInscricao($idPreInscricao);
    }

    public function marcarUtilizado(int $id): bool
    {
        return $this->repository->marcarUtilizado($id);
    }

    public function gerarToken(): string
    {
        return bin2hex(random_bytes(32));
    }
}
