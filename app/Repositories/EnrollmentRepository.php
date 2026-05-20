<?php

declare(strict_types=1);

namespace App\Repositories;

final class EnrollmentRepository extends JsonRepository
{
    public function __construct()
    {
        parent::__construct(dirname(__DIR__, 2) . '/storage/enrollments.json');
    }

    public function all(): array
    {
        return $this->allData();
    }

    public function allByStudentId(int $studentId): array
    {
        return array_values(array_filter(
            $this->allData(),
            static fn (array $row): bool => (int) $row['student_id'] === $studentId
        ));
    }

    public function findByStudentAndCourse(int $studentId, int $courseId): ?array
    {
        foreach ($this->allData() as $row) {
            if ((int) $row['student_id'] === $studentId && (int) $row['course_id'] === $courseId) {
                return $row;
            }
        }

        return null;
    }

    public function create(array $payload): void
    {
        $rows = $this->allData();
        $payload['id'] = $this->nextId($rows);
        $payload['created_at'] = date('Y-m-d H:i:s');
        $rows[] = $payload;
        $this->saveAllData($rows);
    }
}
