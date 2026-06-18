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

        $carousels = $this->carouselService->carouselsAtivos();
        $carouselData = [];
        foreach ($carousels as $carousel) {
            $items = $this->carouselService->carouselItems((int) $carousel['id']);
            if (!empty($items)) {
                $carouselData[] = [
                    'carousel' => $carousel,
                    'items' => $items,
                ];
            }
        }

        $this->render('pages/home', [
            'title' => 'IESB :: Inteligência Educacional Souza Brazil',
            'currentRoute' => '/home',
            'cursosDestaque' => $cursosDestaque,
            'carousels' => $carouselData,
        ]);
    }
}
