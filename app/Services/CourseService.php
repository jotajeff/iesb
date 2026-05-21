<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\AdminRepository;

final class CourseService
{
    public function __construct(private readonly AdminRepository $admin = new AdminRepository())
    {
    }

    public function list(): array
    {
        return $this->admin->listCursosDisponiveis();
    }
}
