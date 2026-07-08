<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\SessaoRepository;

final class SessaoService
{
    public function __construct(
        private readonly SessaoRepository $repository = new SessaoRepository(),
    ) {
    }

    public function list(): array
    {
        return $this->repository->list();
    }

    public function find(int $id): ?array
    {
        return $this->repository->findById($id);
    }

    public function save(array $data): int
    {
        if (trim((string) ($data['slug'] ?? '')) === '') {
            $data['slug'] = CursoService::slugify((string) ($data['titulo'] ?? ''));
        }

        return $this->repository->save($data);
    }

    public function delete(int $id): void
    {
        $this->repository->delete($id);
    }
}
