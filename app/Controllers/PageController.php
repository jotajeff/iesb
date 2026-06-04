<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Services\AdminService;
use App\Support\Session;

final class PageController extends Controller
{
    private AdminService $admin;

    public function __construct()
    {
        $this->admin = new AdminService();
    }

    public function sobre(): void
    {
        $this->render('pages/sobre', ['title' => 'Sobre', 'currentRoute' => '/sobre']);
    }

    public function cursos(): void
    {
        $niveisAtivos = array_values(array_filter(
            $this->admin->niveis(),
            static fn (array $nivel): bool => (int) ($nivel['ativo'] ?? 0) === 1
        ));

        $nivelSlugRequest = trim((string) ($_GET['nivel'] ?? ''));
        $nivelIdRequest = (int) ($_GET['nivel_id'] ?? 0);
        $segmentoIdRequest = (int) ($_GET['segmento_id'] ?? 0);
        $nivelSelecionado = null;

        if ($nivelSlugRequest !== '') {
            $nivelSelecionado = $this->admin->findNivelBySlug($nivelSlugRequest);
        } elseif ($nivelIdRequest > 0) {
            $nivelSelecionado = $this->admin->findNivel($nivelIdRequest);
        }

        if ($nivelSelecionado === null) {
            $nivelSession = Session::get('nivel_selecionado');
            $nivelSessionId = is_array($nivelSession) ? (int) ($nivelSession['id'] ?? 0) : 0;

            if ($nivelSessionId > 0) {
                $nivelSelecionado = $this->admin->findNivel($nivelSessionId);
            }
        }

        if ($nivelSelecionado === null && !empty($niveisAtivos)) {
            $nivelSelecionado = $niveisAtivos[0];
        }

        if ($nivelSelecionado !== null) {
            Session::set('nivel_selecionado', [
                'id' => (int) ($nivelSelecionado['id'] ?? 0),
                'nome' => (string) ($nivelSelecionado['nome'] ?? ''),
                'apresentacao' => (string) ($nivelSelecionado['apresentacao'] ?? ''),
            ]);
        }

        $catalogo = $nivelSelecionado !== null
            ? $this->admin->catalogoCursosPorNivel((int) ($nivelSelecionado['id'] ?? 0), $segmentoIdRequest)
            : [
                'nivel' => null,
                'segmentos' => [],
                'segmentoSelecionado' => null,
                'cursos' => [],
            ];

        $nivelCursoId = (int) ($catalogo['nivel']['id'] ?? ($nivelSelecionado['id'] ?? 0));
        $nivelCursoSlug = trim((string) ($catalogo['nivel']['slug'] ?? ($nivelSelecionado['slug'] ?? '')));
        $segmentosMenu = array_map(
            static function (array $segmento) use ($segmentoIdRequest, $nivelCursoSlug, $nivelCursoId): array {
                $segmentoId = (int) ($segmento['id'] ?? 0);
                $nivelParam = $nivelCursoSlug !== '' ? 'nivel=' . rawurlencode($nivelCursoSlug) : 'nivel_id=' . $nivelCursoId;

                return [
                    'id' => $segmentoId,
                    'nome' => (string) ($segmento['nome'] ?? '-'),
                    'active' => $segmentoId === $segmentoIdRequest,
                    'url' => '/cursos?' . $nivelParam . '&segmento_id=' . $segmentoId . '#lista-cursos',
                ];
            },
            $catalogo['segmentos'] ?? []
        );

        $nivelParam = $nivelCursoSlug !== '' ? 'nivel=' . rawurlencode($nivelCursoSlug) : 'nivel_id=' . $nivelCursoId;

        $this->render('pages/cursos', [
            'title' => 'Cursos',
            'currentRoute' => '/cursos',
            'courses' => $catalogo['cursos'] ?? [],
            'nivelSelecionado' => $catalogo['nivel'] ?? $nivelSelecionado,
            'segmentosMenu' => $segmentosMenu,
            'segmentoSelecionado' => $catalogo['segmentoSelecionado'] ?? null,
            'segmentoSelecionadoId' => $segmentoIdRequest,
            'nivelCursoUrl' => '/cursos?' . $nivelParam,
            'niveisMenu' => $niveisAtivos,
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
