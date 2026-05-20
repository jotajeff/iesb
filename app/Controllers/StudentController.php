<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Services\AuthService;
use App\Services\CourseService;
use App\Services\EnrollmentService;
use App\Support\Session;

final class StudentController extends Controller
{
    private AuthService $auth;
    private CourseService $courses;
    private EnrollmentService $enrollments;

    public function __construct()
    {
        $this->auth = new AuthService();
        $this->courses = new CourseService();
        $this->enrollments = new EnrollmentService();
    }

    public function dashboard(): void
    {
        if (!$this->auth->checkRole('aluno')) {
            Session::setFlash('flash', 'Faça login como aluno para acessar o painel.');
            $this->redirect('/aluno/login');
        }

        $user = Session::get('user');
        $studentId = (int) ($user['id'] ?? 0);

        $this->render('pages/aluno/dashboard', [
            'title' => 'Área do Aluno',
            'currentRoute' => '/area-do-aluno',
            'courses' => $this->courses->list(),
            'enrollments' => $this->enrollments->studentCourses($studentId),
        ]);
    }

    public function enroll(): void
    {
        if (!$this->auth->checkRole('aluno')) {
            Session::setFlash('flash', 'Faça login como aluno para se matricular.');
            $this->redirect('/aluno/login');
        }

        $user = Session::get('user');
        $studentId = (int) ($user['id'] ?? 0);
        $courseId = (int) $this->input('course_id', 0);

        $result = $this->enrollments->enroll($studentId, $courseId);
        Session::setFlash('flash', $result['message']);
        $this->redirect('/aluno');
    }
}
