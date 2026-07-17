<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\ImageRepository;

final class ImageService
{
    public function __construct(
        private readonly ImageRepository $repository = new ImageRepository(),
    ) {
    }

    public function listarPorFk(string $tabelaFk, int $idFk): array
    {
        return $this->repository->listByFk($tabelaFk, $idFk);
    }

    public function salvar(string $tabelaFk, int $idFk, string $path, ?string $legenda = null): int
    {
        return $this->repository->create($tabelaFk, $idFk, $path, $legenda);
    }

    public function deletar(int $id): void
    {
        $img = $this->repository->findById($id);
        if ($img) {
            $filePath = dirname(__DIR__, 2) . '/public/' . ltrim((string) ($img['path'] ?? ''), '/');
            if (is_file($filePath)) {
                @unlink($filePath);
            }
        }
        $this->repository->delete($id);
    }
}
