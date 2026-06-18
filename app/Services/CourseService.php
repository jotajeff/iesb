<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\CursoRepository;

final class CourseService
{
    public function __construct(private readonly CursoRepository $repository = new CursoRepository())
    {
    }

    public function list(): array
    {
        return $this->repository->listDisponiveis();
    }
}
