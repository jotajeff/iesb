<?php

declare(strict_types=1);

namespace App\Repositories;

final class CourseRepository extends JsonRepository
{
    public function __construct()
    {
        parent::__construct(dirname(__DIR__, 2) . '/storage/courses.json');
    }

    public function all(): array
    {
        return $this->allData();
    }

    public function findById(int $id): ?array
    {
        foreach ($this->allData() as $course) {
            if ((int) $course['id'] === $id) {
                return $course;
            }
        }

        return null;
    }

    public function create(array $payload): void
    {
        $courses = $this->allData();
        $payload['id'] = $this->nextId($courses);
        $courses[] = $payload;
        $this->saveAllData($courses);
    }
}
