<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\NoticiaRepository;

final class NoticiaService
{
    public function __construct(
        private readonly NoticiaRepository $repository = new NoticiaRepository(),
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

    public function categorias(): array
    {
        return $this->repository->listCategorias();
    }

    public function save(array $data): int
    {
        if (trim((string) ($data['slug'] ?? '')) === '') {
            $data['slug'] = CursoService::slugify((string) ($data['titulo'] ?? ''));
        }

        if ($data['id_categoria'] === '' || $data['id_categoria'] === null) {
            $data['id_categoria'] = null;
        }

        return $this->repository->save($data);
    }

    public function delete(int $id): void
    {
        $this->repository->delete($id);
    }
}
