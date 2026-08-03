<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
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
            'tipo_curso' => (int) ($curso['tipo_curso'] ?? 0),
            'tipo_curso_nome' => trim((string) ($curso['tipo_curso_nome'] ?? '')),
            'tipo_curso_slug' => trim((string) ($curso['tipo_curso_slug'] ?? '')),
            'link_ingresso' => trim((string) ($curso['link_ingresso'] ?? '')),
        ], $cursos);

        $this->json([
            'success' => true,
            'total' => count($data),
            'data' => $data,
        ]);
    }

    public function cursosAtivos(): void
    {
        $this->allowCors();

        $cursos = [];
        $pdo = Database::connection();
        if ($pdo instanceof \PDO) {
            try {
                $stmt = $pdo->query(
                    'SELECT c.id, c.nome, c.slug, c.data_curso, c.horario, c.local_curso, c.imagem_card, c.carga_horaria, c.tipo_curso, c.link_ingresso, s.nome AS segmento_nome, m.nome AS modalidade_nome, tc.nome AS tipo_curso_nome, tc.slug AS tipo_curso_slug'
                    . ' FROM cursos c'
                    . ' LEFT JOIN segmento s ON s.id = c.segmento'
                    . ' LEFT JOIN modalidade m ON m.id = c.modalidade'
                    . ' LEFT JOIN tipo_curso tc ON tc.id = c.tipo_curso'
                    . ' WHERE c.ativo = 1'
                    . ' ORDER BY c.nome ASC'
                );
                $cursos = $stmt->fetchAll() ?: [];
            } catch (\Throwable $e) {
                error_log('[API] Erro cursosAtivos: ' . $e->getMessage());
            }
        }

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
            'tipo_curso' => (int) ($curso['tipo_curso'] ?? 0),
            'tipo_curso_nome' => trim((string) ($curso['tipo_curso_nome'] ?? '')),
            'tipo_curso_slug' => trim((string) ($curso['tipo_curso_slug'] ?? '')),
            'link_ingresso' => trim((string) ($curso['link_ingresso'] ?? '')),
        ], $cursos);

        $this->json([
            'success' => true,
            'total' => count($data),
            'data' => $data,
        ]);
    }
}
