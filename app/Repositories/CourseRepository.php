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
        return array_map(
            fn (array $course): array => $this->normalizeCourse($course),
            $this->allData()
        );
    }

    public function findById(int $id): ?array
    {
        foreach ($this->allData() as $course) {
            if ((int) $course['id'] === $id) {
                return $this->normalizeCourse($course);
            }
        }

        return null;
    }

    public function create(array $payload): void
    {
        $courses = $this->allData();
        $payload = $this->normalizeCourse($payload);
        $payload['id'] = $this->nextId($courses);
        $courses[] = $payload;
        $this->saveAllData($courses);
    }

    public function update(int $id, array $payload): void
    {
        $courses = $this->allData();

        foreach ($courses as $index => $course) {
            if ((int) $course['id'] !== $id) {
                continue;
            }

            $courses[$index] = $this->normalizeCourse(array_replace($course, $payload));
            $courses[$index]['id'] = $id;
            $this->saveAllData($courses);
            return;
        }
    }

    private function normalizeCourse(array $course): array
    {
        $course['exibir_home'] = strtoupper(trim((string) ($course['exibir_home'] ?? 'N'))) === 'S' ? 'S' : 'N';

        return $course;
    }
}
