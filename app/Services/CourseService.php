<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\CourseRepository;

final class CourseService
{
    public function __construct(private readonly CourseRepository $courses = new CourseRepository())
    {
    }

    public function list(): array
    {
        return $this->courses->all();
    }

    public function create(string $name, string $description, string $duration, float $price): void
    {
        $this->courses->create([
            'name' => trim($name),
            'description' => trim($description),
            'duration' => trim($duration),
            'price' => $price,
        ]);
    }
}
