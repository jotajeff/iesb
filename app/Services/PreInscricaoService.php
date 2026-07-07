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

    public function salvar(array $data): int
    {
        return $this->repository->salvar($data);
    }
}
