<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Services\AdminService;

final class HomeController extends Controller
{
    private AdminService $admin;

    public function __construct()
    {
        $this->admin = new AdminService();
    }

    public function index(): void
    {
        $cursosDestaque = $this->admin->cursosDisponiveisParaHome();

        $this->render('pages/home', [
            'title' => 'IESB :: Inteligência Educacional Souza Brazil',
            'currentRoute' => '/home',
            'cursosDestaque' => $cursosDestaque,
        ]);
    }
}
