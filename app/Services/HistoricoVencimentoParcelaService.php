<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\HistoricoVencimentoParcelaRepository;

final class HistoricoVencimentoParcelaService
{
    public function __construct(
        private readonly HistoricoVencimentoParcelaRepository $repository = new HistoricoVencimentoParcelaRepository(),
    ) {
    }

    public function reagendar(int $idParcela, string $novaData, ?int $idUsuario, string $motivo = ''): bool
    {
        return $this->repository->reagendar($idParcela, $novaData, $idUsuario, $motivo);
    }

    public function listarPorAluno(int $idAluno): array
    {
        return $this->repository->listByAluno($idAluno);
    }
}
