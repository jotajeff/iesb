<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Services\CursoService;
use App\Services\CarouselService;

final class HomeController extends Controller
{
    private CursoService $cursoService;
    private CarouselService $carouselService;

    public function __construct()
    {
        $this->cursoService = new CursoService();
        $this->carouselService = new CarouselService();
    }

    public function index(): void
    {
        $cursosDestaque = $this->cursoService->cursosDisponiveisParaHome();
        $carouselItems = $this->carouselService->allItemsAtivos();

        $this->render('pages/home', [
            'title' => 'IESB :: Inteligência Educacional Souza Brazil',
            'currentRoute' => '/home',
            'cursosDestaque' => $cursosDestaque,
            'carouselItems' => $carouselItems,
        ]);
    }
}
