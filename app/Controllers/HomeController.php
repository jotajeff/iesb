<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Repositories\LeadRepository;
use App\Services\CursoService;
use App\Services\CarouselService;

final class HomeController extends Controller
{
    private CursoService $cursoService;
    private CarouselService $carouselService;
    private LeadRepository $leadRepository;

    public function __construct()
    {
        $this->cursoService = new CursoService();
        $this->carouselService = new CarouselService();
        $this->leadRepository = new LeadRepository();
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

    public function salvarLead(): void
    {
        if (trim((string) $this->input('website', '')) !== '') {
            $this->json([
                'success' => true,
                'message' => 'Tudo certo! Seus dados foram recebidos.',
            ]);
        }

        $nome = preg_replace('/\s+/', ' ', trim((string) $this->input('nome', ''))) ?? '';
        $telefone = trim((string) $this->input('telefone', ''));
        $interesse = preg_replace('/\s+/', ' ', trim((string) $this->input('interesse', ''))) ?? '';
        $telefoneDigitos = preg_replace('/\D+/', '', $telefone) ?? '';

        if (mb_strlen($nome) < 2 || mb_strlen($nome) > 50) {
            $this->json([
                'success' => false,
                'message' => 'Informe seu nome completo.',
            ], 422);
        }

        if (strlen($telefoneDigitos) < 10 || strlen($telefoneDigitos) > 11) {
            $this->json([
                'success' => false,
                'message' => 'Informe um telefone válido com DDD.',
            ], 422);
        }

        if (mb_strlen($interesse) < 2 || mb_strlen($interesse) > 100) {
            $this->json([
                'success' => false,
                'message' => 'Conte o que você gostaria de estudar.',
            ], 422);
        }

        $id = $this->leadRepository->salvar($nome, $telefone, $interesse, $this->resolveIp());

        if ($id <= 0) {
            $this->json([
                'success' => false,
                'message' => 'Não foi possível salvar seus dados agora. Tente novamente em instantes.',
            ], 500);
        }

        $this->json([
            'success' => true,
            'message' => 'Tudo certo! Seus dados foram recebidos. Avisaremos você em primeira mão.',
        ], 201);
    }

    private function resolveIp(): string
    {
        $candidates = [
            $_SERVER['HTTP_CF_CONNECTING_IP'] ?? null,
            $_SERVER['HTTP_X_FORWARDED_FOR'] ?? null,
            $_SERVER['REMOTE_ADDR'] ?? null,
        ];

        foreach ($candidates as $candidate) {
            if (!is_string($candidate) || trim($candidate) === '') {
                continue;
            }

            $ip = trim(explode(',', $candidate)[0]);
            if (filter_var($ip, FILTER_VALIDATE_IP)) {
                return $ip;
            }
        }

        return '0.0.0.0';
    }
}
