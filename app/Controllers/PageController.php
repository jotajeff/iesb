<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Services\CourseService;

final class PageController extends Controller
{
    public function sobre(): void
    {
        $this->render('pages/sobre', ['title' => 'Sobre', 'currentRoute' => '/sobre']);
    }

    public function cursos(): void
    {
        $this->render('pages/cursos', [
            'title' => 'Cursos',
            'currentRoute' => '/cursos',
            'courses' => (new CourseService())->list(),
        ]);
    }

    public function eventos(): void
    {
        $this->render('pages/eventos', ['title' => 'Eventos', 'currentRoute' => '/eventos']);
    }

    public function parcerias(): void
    {
        $this->render('pages/parcerias', ['title' => 'Parcerias', 'currentRoute' => '/parcerias']);
    }
}
