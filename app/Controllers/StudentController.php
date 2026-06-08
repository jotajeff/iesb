<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Services\AdminService;
use App\Services\AuthService;
use App\Services\CourseService;
use App\Services\EnrollmentService;
use App\Support\Session;

final class StudentController extends Controller
{
    private AuthService $auth;
    private AdminService $admin;
    private CourseService $courses;
    private EnrollmentService $enrollments;

    public function __construct()
    {
        $this->auth = new AuthService();
        $this->admin = new AdminService();
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
            'matriculasDB' => $this->admin->matriculasDoAluno($studentId),
        ], 'aluno');
    }

    public function perfil(): void
    {
        if (!$this->auth->checkRole('aluno')) {
            Session::setFlash('flash', 'Faça login como aluno para acessar o perfil.');
            $this->redirect('/aluno/login');
        }

        $user = Session::get('user');
        $studentId = (int) ($user['id'] ?? 0);
        $aluno = $this->admin->findAluno($studentId);

        $this->render('pages/aluno/perfil', [
            'title' => 'Meu Perfil',
            'currentRoute' => '/aluno/perfil',
            'aluno' => $aluno,
        ], 'aluno');
    }

    public function atualizarPerfil(): void
    {
        if (!$this->auth->checkRole('aluno')) {
            Session::setFlash('flash', 'Faça login como aluno.');
            $this->redirect('/aluno/login');
        }

        $user = Session::get('user');
        $studentId = (int) ($user['id'] ?? 0);

        $nome = trim((string) $this->input('nome', ''));
        $cpf = trim((string) $this->input('cpf', ''));
        $dataNascimento = (string) $this->input('data_nascimento', '');
        $telefone = trim((string) $this->input('telefone', ''));
        $email = trim((string) $this->input('email', ''));
        $senha = (string) $this->input('senha', '');

        $this->admin->atualizarAluno($studentId, $nome, $cpf, $dataNascimento, $telefone, $email, 'S', $senha ?: null);

        Session::setFlash('flash', 'Perfil atualizado com sucesso.');
        $this->redirect('/aluno/perfil');
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
