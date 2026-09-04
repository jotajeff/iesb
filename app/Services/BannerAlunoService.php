<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\BannerAlunoRepository;

final class BannerAlunoService
{
    public function __construct(
        private readonly BannerAlunoRepository $repository = new BannerAlunoRepository(),
    ) {
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function list(): array
    {
        return $this->repository->list();
    }

    public function find(int $id): ?array
    {
        return $this->repository->find($id);
    }

    public function save(int $id, string $banner, ?string $texto, string $link, ?int $idCurso, int $ativo): int
    {
        return $this->repository->save($id, $banner, $texto, $link, $idCurso, $ativo);
    }
}
