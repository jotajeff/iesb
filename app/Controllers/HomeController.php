<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;

final class HomeController extends Controller
{
    public function index(): void
    {
        $this->render('pages/home', [
            'title' => 'IESB :: Inteligência Educacional Souza Brazil',
            'currentRoute' => '/home',
        ]);
    }
}
