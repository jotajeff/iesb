<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Services\CursoService;

final class ApiController extends Controller
{
    private CursoService $cursoService;

    public function __construct()
    {
        $this->cursoService = new CursoService();
    }

    private function allowCors(): void
    {
        header('Access-Control-Allow-Origin: https://www.magdabrazilcursos.com.br');
        header('Access-Control-Allow-Methods: GET, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type');
        header('Access-Control-Max-Age: 86400');

        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(204);
            exit;
        }
    }

    public function cursosHome(): void
    {
        $this->allowCors();

        $cursos = $this->cursoService->cursosDisponiveisParaHome(6);

        $data = array_map(fn (array $curso): array => [
            'id' => (int) ($curso['id'] ?? 0),
            'nome' => trim((string) ($curso['nome'] ?? '')),
            'slug' => trim((string) ($curso['slug'] ?? '')),
            'data_curso' => trim((string) ($curso['data_curso'] ?? '')),
            'horario' => trim((string) ($curso['horario'] ?? '')),
            'local_curso' => trim((string) ($curso['local_curso'] ?? '')),
            'imagem_card' => trim((string) ($curso['imagem_card'] ?? '')),
            'carga_horaria' => (int) ($curso['carga_horaria'] ?? 0),
            'segmento_nome' => trim((string) ($curso['segmento_nome'] ?? '')),
            'modalidade_nome' => trim((string) ($curso['modalidade_nome'] ?? '')),
            'link_ingresso' => trim((string) ($curso['link_ingresso'] ?? '')),
        ], $cursos);

        $this->json([
            'success' => true,
            'total' => count($data),
            'data' => $data,
        ]);
    }
}
