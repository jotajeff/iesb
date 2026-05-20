<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\CourseRepository;
use App\Repositories\EnrollmentRepository;

final class EnrollmentService
{
    public function __construct(
        private readonly EnrollmentRepository $enrollments = new EnrollmentRepository(),
        private readonly CourseRepository $courses = new CourseRepository(),
    ) {
    }

    public function enroll(int $studentId, int $courseId): array
    {
        $course = $this->courses->findById($courseId);
        if ($course === null) {
            return ['ok' => false, 'message' => 'Curso não encontrado.'];
        }

        $exists = $this->enrollments->findByStudentAndCourse($studentId, $courseId);
        if ($exists !== null) {
            return ['ok' => false, 'message' => 'Você já está matriculado neste curso.'];
        }

        $this->enrollments->create([
            'student_id' => $studentId,
            'course_id' => $courseId,
            'status' => 'matriculado',
            'progress' => 0,
        ]);

        return ['ok' => true, 'message' => 'Matrícula realizada com sucesso!'];
    }

    public function studentCourses(int $studentId): array
    {
        $items = [];
        foreach ($this->enrollments->allByStudentId($studentId) as $enrollment) {
            $course = $this->courses->findById((int) $enrollment['course_id']);
            if ($course === null) {
                continue;
            }

            $items[] = [
                'enrollment_id' => (int) $enrollment['id'],
                'name' => $course['name'],
                'duration' => $course['duration'],
                'price' => (float) $course['price'],
                'status' => $enrollment['status'],
                'progress' => (int) $enrollment['progress'],
                'created_at' => $enrollment['created_at'],
            ];
        }

        return $items;
    }
}
