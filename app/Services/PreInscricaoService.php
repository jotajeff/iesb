<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\PreInscricaoRepository;

final class PreInscricaoService
{
    public function __construct(
        private readonly PreInscricaoRepository $repository = new PreInscricaoRepository(),
    ) {
    }

    public function listarRecebidos(): array
    {
        return $this->repository->listar('recebido');
    }

    public function listarTodos(?string $situacao = null): array
    {
        return $this->repository->listarTodos($situacao);
    }

    public function salvar(array $data): int
    {
        return $this->repository->salvar($data);
    }

    public function findById(int $id): ?array
    {
        return $this->repository->findById($id);
    }

    public function atualizarSituacao(int $id, string $situacao): bool
    {
        return $this->repository->atualizarSituacao($id, $situacao);
    }

    public function findByAsaasPayment(string $asaasPayment): ?array
    {
        return $this->repository->findByAsaasPayment($asaasPayment);
    }

    public function atualizarWebhook(int $id, array $data): bool
    {
        return $this->repository->atualizarWebhook($id, $data);
    }
}
