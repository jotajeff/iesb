<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Services\AdminService;
use App\Services\AuthService;
use App\Services\CourseService;
use App\Support\Session;

final class AdminController extends Controller
{
    private AuthService $auth;
    private CourseService $courses;
    private AdminService $admin;

    public function __construct()
    {
        $this->auth = new AuthService();
        $this->courses = new CourseService();
        $this->admin = new AdminService();
    }

    public function dashboard(): void
    {
        if (!$this->auth->checkRole('admin')) {
            Session::setFlash('flash', 'Faça login como admin para acessar o painel.');
            $this->redirect('/admin/login');
        }

        $this->render('pages/admin/dashboard', [
            'title' => 'Painel Admin',
            'currentRoute' => '/admin',
            'courses' => $this->courses->list(),
            'indicators' => $this->admin->indicators(),
        ], 'admin');
    }

    public function logs(): void
    {
        if (!$this->auth->checkRole('admin')) {
            Session::setFlash('flash', 'Faça login como admin para acessar os logs.');
            $this->redirect('/admin/login');
        }

        $this->render('pages/admin/logs', [
            'title' => 'Logs de Auditoria',
            'currentRoute' => '/admin/logs',
            'logs' => $this->admin->logs(),
        ], 'admin');
    }

    public function visits(): void
    {
        if (!$this->auth->checkRole('admin')) {
            Session::setFlash('flash', 'Faça login como admin para acessar as visitas.');
            $this->redirect('/admin/login');
        }

        $this->render('pages/admin/visits', [
            'title' => 'Visitas de Páginas',
            'currentRoute' => '/admin/visitas',
            'visits' => $this->admin->visits(),
        ], 'admin');
    }

    public function visitsMonthly(): void
    {
        if (!$this->auth->checkRole('admin')) {
            Session::setFlash('flash', 'Faça login como admin para acessar as visitas.');
            $this->redirect('/admin/login');
        }

        $month = isset($_GET['month']) ? (int) $_GET['month'] : null;
        $year = isset($_GET['year']) ? (int) $_GET['year'] : null;

        $this->render('pages/admin/visits_monthly', [
            'title' => 'Visitas por Mês',
            'currentRoute' => '/admin/visitas',
            'monthly' => $this->admin->visitsByMonthDaily($month, $year),
        ], 'admin');
    }

    public function visitsAnalytics(): void
    {
        if (!$this->auth->checkRole('admin')) {
            Session::setFlash('flash', 'Faça login como admin para acessar as visitas.');
            $this->redirect('/admin/login');
        }

        $month = isset($_GET['month']) ? (int) $_GET['month'] : null;
        $year = isset($_GET['year']) ? (int) $_GET['year'] : null;

        $this->render('pages/admin/visits_analytics', [
            'title' => 'Analytics de Visitas',
            'currentRoute' => '/admin/visitas',
            'analytics' => $this->admin->visitsAnalytics($month, $year),
        ], 'admin');
    }

    public function visitsPages(): void
    {
        if (!$this->auth->checkRole('admin')) {
            Session::setFlash('flash', 'Faça login como admin para acessar as visitas.');
            $this->redirect('/admin/login');
        }

        $month = isset($_GET['month']) ? (int) $_GET['month'] : null;
        $year = isset($_GET['year']) ? (int) $_GET['year'] : null;

        $this->render('pages/admin/visits_pages', [
            'title' => 'Visitas por Página',
            'currentRoute' => '/admin/visitas',
            'pagesStats' => $this->admin->visitsByPage($month, $year),
        ], 'admin');
    }

    public function createCourse(): void
    {
        if (!$this->auth->checkRole('admin')) {
            Session::setFlash('flash', 'Acesso negado.');
            $this->redirect('/admin/login');
        }

        $name = (string) $this->input('name', '');
        $description = (string) $this->input('description', '');
        $duration = (string) $this->input('duration', '');
        $price = (float) $this->input('price', 0);

        if ($name === '' || $description === '' || $duration === '' || $price <= 0) {
            Session::setFlash('flash', 'Preencha todos os dados do curso corretamente.');
            $this->redirect('/admin');
        }

        $this->courses->create($name, $description, $duration, $price);
        Session::setFlash('flash', 'Curso criado com sucesso.');
        $this->redirect('/admin');
    }
}
