<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\CommentRepository;

final class CommentService
{
    public function __construct(
        private readonly CommentRepository $repository = new CommentRepository(),
    ) {
    }

    public function countFor(string $table, int $id): int
    {
        return $this->repository->countByTableAndId($table, $id);
    }

    public function listFor(string $table, int $id): array
    {
        return $this->repository->listByTableAndId($table, $id);
    }

    public function createFor(string $table, int $id, string $comentario): int
    {
        return $this->repository->create($table, $id, $comentario);
    }
}
