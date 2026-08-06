<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Services\VisitaService;
use App\Support\Session;

final class VisitaController extends Controller
{
    private VisitaService $visitaService;

    public function __construct()
    {
        $this->visitaService = new VisitaService();
    }

    private function paisFiltro(): string
    {
        $pais = trim((string) ($_GET['pais'] ?? 'br'));
        return $pais === 'todos' ? 'todos' : 'br';
    }

    public function index(): void
    {
        if (!$this->isStaff()) {
            Session::setFlash('flash', 'Faça login como admin, operador ou professor para acessar as visitas.');
            $this->redirect('/admin/login');
        }

        $pais = $this->paisFiltro();
        $this->render('pages/admin/visitas/index', [
            'title' => 'Visitas de Páginas',
            'currentRoute' => '/admin/visitas',
            'visits' => $this->visitaService->visits(100, $pais),
            'pais' => $pais,
        ], 'admin');
    }

    public function mensal(): void
    {
        if (!$this->isStaff()) {
            Session::setFlash('flash', 'Faça login como admin, operador ou professor para acessar as visitas.');
            $this->redirect('/admin/login');
        }

        $month = isset($_GET['month']) ? (int) $_GET['month'] : null;
        $year = isset($_GET['year']) ? (int) $_GET['year'] : null;
        $pais = $this->paisFiltro();

        $this->render('pages/admin/visitas/monthly', [
            'title' => 'Visitas por Mês',
            'currentRoute' => '/admin/visitas',
            'monthly' => $this->visitaService->visitsByMonthDaily($month, $year, $pais),
            'pais' => $pais,
        ], 'admin');
    }

    public function analytics(): void
    {
        if (!$this->isStaff()) {
            Session::setFlash('flash', 'Faça login como admin, operador ou professor para acessar as visitas.');
            $this->redirect('/admin/login');
        }

        $month = isset($_GET['month']) ? (int) $_GET['month'] : null;
        $year = isset($_GET['year']) ? (int) $_GET['year'] : null;
        $pais = $this->paisFiltro();

        $this->render('pages/admin/visitas/analytics', [
            'title' => 'Analytics de Visitas',
            'currentRoute' => '/admin/visitas',
            'analytics' => $this->visitaService->visitsAnalytics($month, $year, $pais),
            'pais' => $pais,
        ], 'admin');
    }

    public function paginas(): void
    {
        if (!$this->isStaff()) {
            Session::setFlash('flash', 'Faça login como admin, operador ou professor para acessar as visitas.');
            $this->redirect('/admin/login');
        }

        $month = isset($_GET['month']) ? (int) $_GET['month'] : null;
        $year = isset($_GET['year']) ? (int) $_GET['year'] : null;
        $pais = $this->paisFiltro();

        $this->render('pages/admin/visitas/pages', [
            'title' => 'Visitas por Página',
            'currentRoute' => '/admin/visitas',
            'pagesStats' => $this->visitaService->visitsByPage($month, $year, $pais),
            'pais' => $pais,
        ], 'admin');
    }

    public function cursos(): void
    {
        if (!$this->isStaff()) {
            Session::setFlash('flash', 'Faça login como admin, operador ou professor para acessar as visitas.');
            $this->redirect('/admin/login');
        }

        $pais = $this->paisFiltro();
        $this->render('pages/admin/visitas/cursos', [
            'title' => 'Ranking de Visitas — Cursos',
            'currentRoute' => '/admin/visitas',
            'coursesStats' => $this->visitaService->visitsByCoursePages($pais),
            'pais' => $pais,
        ], 'admin');
    }

    public function referer(): void
    {
        if (!$this->isStaff()) {
            Session::setFlash('flash', 'Faça login como admin, operador ou professor para acessar as visitas.');
            $this->redirect('/admin/login');
        }

        $month = isset($_GET['month']) ? (int) $_GET['month'] : null;
        $year = isset($_GET['year']) ? (int) $_GET['year'] : null;
        $pais = $this->paisFiltro();

        $this->render('pages/admin/visitas/referer', [
            'title' => 'Referer & UTM',
            'currentRoute' => '/admin/visitas/referer',
            'refererStats' => $this->visitaService->refererStats($month, $year, $pais),
            'utmStats' => $this->visitaService->utmStats($month, $year, $pais),
            'pais' => $pais,
        ], 'admin');
    }

    private function isStaff(): bool
    {
        return (new \App\Services\AuthService())->isStaff();
    }
}
