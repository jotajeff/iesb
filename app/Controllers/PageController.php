<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Services\ConfigService;
use App\Services\CursoService;
use App\Services\LogService;
use App\Services\PreInscricaoService;
use App\Support\Session;

final class PageController extends Controller
{
    private ConfigService $configService;
    private CursoService $cursoService;

    public function __construct()
    {
        $this->configService = new ConfigService();
        $this->cursoService = new CursoService();
    }

    public function sobre(): void
    {
        $this->render('pages/sobre', ['title' => 'Sobre', 'currentRoute' => '/sobre']);
    }

    public function cursos(): void
    {
        $niveisAtivos = array_values(array_filter(
            $this->configService->niveis(),
            static fn (array $nivel): bool => (int) ($nivel['ativo'] ?? 0) === 1
        ));

        $nivelSlugRequest = trim((string) ($_GET['nivel'] ?? ''));
        $nivelIdRequest = (int) ($_GET['nivel_id'] ?? 0);
        $segmentoIdRequest = (int) ($_GET['segmento_id'] ?? 0);
        $nivelSelecionado = null;

        if ($nivelSlugRequest !== '') {
            $nivelSelecionado = $this->configService->findNivelBySlug($nivelSlugRequest);
        } elseif ($nivelIdRequest > 0) {
            $nivelSelecionado = $this->configService->findNivel($nivelIdRequest);
        }

        if ($nivelSelecionado === null) {
            $nivelSession = Session::get('nivel_selecionado');
            $nivelSessionId = is_array($nivelSession) ? (int) ($nivelSession['id'] ?? 0) : 0;

            if ($nivelSessionId > 0) {
                $nivelSelecionado = $this->configService->findNivel($nivelSessionId);
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
            ? $this->cursoService->catalogoCursosPorNivel((int) ($nivelSelecionado['id'] ?? 0), $segmentoIdRequest)
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

    public function privacidade(): void
    {
        $this->render('pages/privacidade', ['title' => 'Política de Privacidade', 'currentRoute' => '/privacidade']);
    }

    public function preInscricao(): void
    {
        $cursoId = (int) ($_GET['curso_id'] ?? 0);
        $cursoNome = '';

        if ($cursoId > 0) {
            $curso = $this->cursoService->findCurso($cursoId);
            if ($curso) {
                $cursoNome = (string) ($curso['nome'] ?? '');
            }
        }

        $this->render('pages/pre-inscricao', [
            'title' => 'Pré-inscrição',
            'currentRoute' => '/pre-inscricao',
            'enviado' => false,
            'cursoNome' => $cursoNome,
            'cursoId' => $cursoId,
        ]);
    }

    public function enviarPreInscricao(): void
    {
        $nome = trim((string) $this->input('nome', ''));
        $email = trim((string) $this->input('email', ''));
        $whatsapp = trim((string) $this->input('whatsapp', ''));
        $cursoId = (int) $this->input('curso_id', 0);
        $ip = (string) ($_SERVER['REMOTE_ADDR'] ?? '');

        if ($nome === '' || $email === '' || $whatsapp === '') {
            $this->render('pages/pre-inscricao', [
                'title' => 'Pré-inscrição',
                'currentRoute' => '/pre-inscricao',
                'enviado' => false,
                'erro' => 'Preencha todos os campos.',
                'nome' => $nome,
                'email' => $email,
                'whatsapp' => $whatsapp,
                'cursoNome' => $this->getCursoNome($cursoId),
                'cursoId' => $cursoId,
            ]);
            return;
        }

        $cursoNome = $this->getCursoNome($cursoId);

        $preService = new PreInscricaoService();
        $id = $preService->salvar([
            'nome' => $nome,
            'email' => $email,
            'whatsapp' => $whatsapp,
            'ip' => $ip,
            'curso_id' => $cursoId,
        ]);

        if ($id > 0) {
            (new LogService())->log('criar', 'pre_inscricao', $id, 'Pré-inscrição recebida: ' . $nome);
        }

        $this->render('pages/pre-inscricao', [
            'title' => 'Pré-inscrição',
            'currentRoute' => '/pre-inscricao',
            'enviado' => true,
            'cursoNome' => $cursoNome,
            'cursoId' => $cursoId,
        ]);
    }

    private function getCursoNome(int $cursoId): string
    {
        if ($cursoId < 1) {
            return '';
        }
        $curso = $this->cursoService->findCurso($cursoId);
        return $curso ? (string) ($curso['nome'] ?? '') : '';
    }
}
