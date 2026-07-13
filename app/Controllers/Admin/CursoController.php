<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Services\CursoPagamentoService;
use App\Services\CursoService;
use App\Services\ConfigService;
use App\Services\LogService;
use App\Support\Session;

final class CursoController extends Controller
{
    private CursoService $cursoService;
    private ConfigService $configService;
    private LogService $logService;
    private CursoPagamentoService $pagamentoService;

    public function __construct()
    {
        $this->cursoService = new CursoService();
        $this->configService = new ConfigService();
        $this->logService = new LogService();
        $this->pagamentoService = new CursoPagamentoService();
    }

    public function index(): void
    {
        if (!$this->isStaff()) {
            Session::setFlash('flash', 'Faça login como admin, operador ou professor para acessar os cursos.');
            $this->redirect('/admin/login');
        }

        $order = strtolower(trim((string) ($_GET['order'] ?? 'desc')));
        if ($order !== 'asc') {
            $order = 'desc';
        }
        $nivelSelecionado = (int) ($_GET['nivel'] ?? 0);
        if ($nivelSelecionado < 0) {
            $nivelSelecionado = 0;
        }

        try {
            $this->cursoService->sincronizarSlugs();
        } catch (\Throwable $e) {
            error_log('[CURSOS] Erro em sincronizarSlugs: ' . $e->getMessage());
        }

        $this->render('pages/admin/cursos/index', [
            'title' => 'Cursos IESB',
            'currentRoute' => '/admin/cursos',
            'courses' => $this->cursoService->cursos($order, 200, $nivelSelecionado),
            'order' => $order,
            'niveis' => $this->configService->niveis(),
            'nivelSelecionado' => $nivelSelecionado,
            'idsComDetalhe' => $this->cursoService->idsCursosComDetalhe(),
            'idsComTurma' => $this->cursoService->idsCursosComTurma(),
        ], 'admin');
    }

    public function novo(): void
    {
        if (!$this->isStaff()) {
            Session::setFlash('flash', 'Acesso negado.');
            $this->redirect('/admin/login');
        }

        $this->render('pages/admin/cursos/new', [
            'title' => 'Novo Curso',
            'currentRoute' => '/admin/cursos/novo',
            'modalidades' => $this->configService->modalidades(),
            'segmentos' => $this->configService->segmentos(),
            'niveis' => $this->configService->niveis(),
        ], 'admin');
    }

    public function salvar(): void
    {
        if (!$this->isStaff()) {
            Session::setFlash('flash', 'Acesso negado.');
            $this->redirect('/admin/login');
        }

        $nome = (string) $this->input('nome', '');
        $dataCurso = (string) $this->input('data_curso', '');
        $horario = (string) $this->input('horario', '');
        $localCurso = (string) $this->input('local_curso', '');
        $linkIngresso = (string) $this->input('link_ingresso', '');
        $cursoCalendario = (string) $this->input('curso_calendario', '');
        $ativo = $this->normalizeAtivo((string) $this->input('ativo', 'S'));
        $exibirHome = $this->normalizeExibirHome((string) $this->input('exibir_home', 'N'));
        $confirmado = $this->normalizeConfirmado((string) $this->input('confirmado', 'N'));
        $modalidadeId = (int) $this->input('modalidade_id', 0);
        $segmentoId = (int) $this->input('segmento_id', 0);
        $nivelId = (int) $this->input('nivel_id', 0);
        $cargaHoraria = (int) $this->input('carga_horaria', 0);

        if ($nome === '' || $localCurso === '') {
            Session::setFlash('flash', 'Preencha ao menos nome e local do curso.');
            $this->redirect('/admin/cursos/novo');
            return;
        }

        $cursoId = $this->cursoService->criarCurso($nome, $dataCurso, $horario, $localCurso, $linkIngresso, $cursoCalendario, $ativo, $exibirHome, $confirmado, '', $modalidadeId, $segmentoId, $nivelId, $cargaHoraria);
        $this->logService->log('criar', 'curso', $cursoId, "Curso criado: $nome");
        Session::setFlash('flash', 'Curso criado com sucesso.');
        $this->redirect('/admin/cursos');
    }

    public function editar(): void
    {
        if (!$this->isStaff()) {
            Session::setFlash('flash', 'Acesso negado.');
            $this->redirect('/admin/login');
        }

        $id = (int) ($this->input('id', 0) ?: ($_GET['id'] ?? 0));
        $course = $this->cursoService->findCurso($id);

        if (!$course) {
            Session::setFlash('flash', 'Curso nao encontrado.');
            $this->redirect('/admin/cursos');
            return;
        }

        $this->render('pages/admin/cursos/edit', [
            'title' => 'Editar Curso',
            'currentRoute' => '/admin/cursos/editar',
            'course' => $course,
            'modalidades' => $this->configService->modalidades(),
            'segmentos' => $this->configService->segmentos(),
            'niveis' => $this->configService->niveis(),
        ], 'admin');
    }

    public function atualizar(): void
    {
        if (!$this->isStaff()) {
            Session::setFlash('flash', 'Acesso negado.');
            $this->redirect('/admin/login');
        }

        $id = (int) $this->input('id', 0);
        $nome = (string) $this->input('nome', '');
        $dataCurso = (string) $this->input('data_curso', '');
        $horario = (string) $this->input('horario', '');
        $localCurso = (string) $this->input('local_curso', '');
        $linkIngresso = (string) $this->input('link_ingresso', '');
        $cursoCalendario = (string) $this->input('curso_calendario', '');
        $ativo = $this->normalizeAtivo((string) $this->input('ativo', 'S'));
        $exibirHome = $this->normalizeExibirHome((string) $this->input('exibir_home', 'N'));
        $confirmado = $this->normalizeConfirmado((string) $this->input('confirmado', 'N'));
        $modalidadeId = (int) $this->input('modalidade_id', 0);
        $segmentoId = (int) $this->input('segmento_id', 0);
        $nivelId = (int) $this->input('nivel_id', 0);
        $cargaHoraria = (int) $this->input('carga_horaria', 0);

        if ($nome === '' || $localCurso === '') {
            Session::setFlash('flash', 'Preencha ao menos nome e local do curso.');
            $this->redirect('/admin/cursos/editar?id=' . $id);
            return;
        }

        $existingCourse = $this->cursoService->findCurso($id);
        if (!$existingCourse) {
            Session::setFlash('flash', 'Curso nao encontrado.');
            $this->redirect('/admin/cursos');
            return;
        }

        $imagemCard = (string) ($existingCourse['imagem_card'] ?? '');
        $this->cursoService->atualizarCurso($id, $nome, $dataCurso, $horario, $localCurso, $linkIngresso, $cursoCalendario, $ativo, $exibirHome, $confirmado, $imagemCard, $modalidadeId, $segmentoId, $nivelId, $cargaHoraria);
        $this->logService->log('atualizar', 'curso', $id, "Curso atualizado: $nome");
        Session::setFlash('flash', 'Curso atualizado com sucesso.');
        $this->redirect('/admin/cursos');
    }

    public function show(): void
    {
        if (!$this->isStaff()) {
            Session::setFlash('flash', 'Acesso negado.');
            $this->redirect('/admin/login');
        }

        $id = (int) ($_GET['id'] ?? 0);
        $course = $this->cursoService->findCurso($id);

        if (!$course) {
            Session::setFlash('flash', 'Curso nao encontrado.');
            $this->redirect('/admin/cursos');
            return;
        }

        $this->render('pages/admin/cursos/show', [
            'title' => $course['nome'] ?? 'Curso',
            'currentRoute' => '/admin/cursos/show',
            'course' => $course,
            'detalhe' => $this->cursoService->findDetalheByCurso($id),
            'pagamentos' => $this->pagamentoService->listarPorCurso($id),
        ], 'admin');
    }

    public function detalhes(): void
    {
        if (!$this->isStaff()) {
            Session::setFlash('flash', 'Acesso negado.');
            $this->redirect('/admin/login');
        }

        $id = (int) ($_GET['id'] ?? 0);
        $course = $this->cursoService->findCurso($id);

        if (!$course) {
            Session::setFlash('flash', 'Curso nao encontrado.');
            $this->redirect('/admin/cursos');
            return;
        }

        $detalhe = $this->cursoService->findDetalheByCurso($id);

        $this->render('pages/admin/cursos/detalhes', [
            'title' => 'Detalhes do Curso - ' . ($course['nome'] ?? ''),
            'currentRoute' => '/admin/cursos/detalhes',
            'course' => $course,
            'detalhe' => $detalhe,
        ], 'admin');
    }

    public function salvarDetalhe(): void
    {
        if (!$this->isStaff()) {
            Session::setFlash('flash', 'Acesso negado.');
            $this->redirect('/admin/login');
        }

        $cursoId = (int) $this->input('curso_id', 0);
        $detalheId = (int) $this->input('detalhe_id', 0);
        $detalheTexto = (string) $this->input('detalhe', '');

        if ($cursoId <= 0) {
            Session::setFlash('flash', 'Curso inválido.');
            $this->redirect('/admin/cursos');
            return;
        }

        $payload = [
            'id_curso' => $cursoId,
            'detalhe' => $detalheTexto,
            'ativo' => 'S',
        ];

        if ($detalheId > 0) {
            $this->cursoService->atualizarDetalhe($detalheId, $payload);
            $this->logService->log('atualizar', 'detalhe', $detalheId, "Detalhe atualizado para o curso #$cursoId");
            Session::setFlash('flash', 'Detalhe atualizado com sucesso.');
        } else {
            $novoId = $this->cursoService->salvarDetalhe($payload);
            $this->logService->log('criar', 'detalhe', $novoId, "Detalhe criado para o curso #$cursoId");
            Session::setFlash('flash', 'Detalhe criado com sucesso.');
        }

        $this->redirect('/admin/cursos');
    }

    public function uploadForm(): void
    {
        if (!$this->isStaff()) {
            Session::setFlash('flash', 'Acesso negado.');
            $this->redirect('/admin/login');
        }

        $id = (int) ($_GET['id'] ?? 0);
        $course = $this->cursoService->findCurso($id);

        if (!$course) {
            Session::setFlash('flash', 'Curso nao encontrado.');
            $this->redirect('/admin/cursos');
            return;
        }

        $this->render('pages/admin/cursos/upload', [
            'title' => 'Upload Imagem - ' . ($course['nome'] ?? ''),
            'currentRoute' => '/admin/cursos/upload',
            'course' => $course,
        ], 'admin');
    }

    public function uploadImagem(): void
    {
        if (!$this->isStaff()) {
            Session::setFlash('flash', 'Acesso negado.');
            $this->redirect('/admin/login');
        }

        $id = (int) ($this->input('id', 0) ?: ($_GET['id'] ?? 0));
        $curso = $this->cursoService->findCurso($id);

        if (!$curso) {
            Session::setFlash('flash', 'Curso nao encontrado.');
            $this->redirect('/admin/cursos');
            return;
        }

        $file = $_FILES['imagem_card'] ?? null;

        if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
            Session::setFlash('flash', 'Erro ao enviar o arquivo.');
            $this->redirect('/admin/cursos/upload?id=' . $id);
            return;
        }

        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

        if (!in_array($ext, $allowed, true)) {
            Session::setFlash('flash', 'Formato nao permitido. Use jpg, png, gif ou webp.');
            $this->redirect('/admin/cursos/upload?id=' . $id);
            return;
        }

        $slug = trim((string) ($curso['slug'] ?? ''));
        if ($slug === '') {
            $slug = CursoService::slugify((string) ($curso['nome'] ?? 'curso'));
        }
        $filename = $slug . '-' . $id . '.' . $ext;

        $destDir = dirname(__DIR__, 3) . '/public/assets/img/cursos';
        if (!is_dir($destDir)) {
            mkdir($destDir, 0755, true);
        }

        $destPath = $destDir . '/' . $filename;

        if (!move_uploaded_file($file['tmp_name'], $destPath)) {
            Session::setFlash('flash', 'Falha ao salvar a imagem.');
            $this->redirect('/admin/cursos/upload?id=' . $id);
            return;
        }

        $this->cursoService->atualizarImagem($id, 'assets/img/cursos/' . $filename);
        $this->logService->log('upload_imagem', 'curso', $id, "Imagem do card enviada: $filename");
        Session::setFlash('flash', 'Imagem do card atualizada com sucesso.');
        $this->redirect('/admin/cursos');
    }

    public function definirValor(): void
    {
        if (!$this->isStaff()) {
            Session::setFlash('flash', 'Acesso negado.');
            $this->redirect('/admin/login');
        }

        $idCurso = (int) ($_GET['id'] ?? 0);
        $curso = $this->cursoService->findCurso($idCurso);

        if (!$curso) {
            Session::setFlash('flash', 'Curso não encontrado.');
            $this->redirect('/admin/cursos');
        }

        $pagamentos = $this->pagamentoService->listarPorCurso($idCurso);

        $this->render('pages/admin/cursos/definir_valor', [
            'title' => 'Pagamento — ' . ($curso['nome'] ?? ''),
            'currentRoute' => '/admin/cursos',
            'curso' => $curso,
            'pagamentos' => $pagamentos,
        ], 'admin');
    }

    public function salvarPagamento(): void
    {
        if (!$this->isStaff()) {
            Session::setFlash('flash', 'Acesso negado.');
            $this->redirect('/admin/login');
        }

        $idCurso = (int) $this->input('id_curso', 0);
        $curso = $this->cursoService->findCurso($idCurso);

        if (!$curso) {
            Session::setFlash('flash', 'Curso não encontrado.');
            $this->redirect('/admin/cursos');
        }

        $idPagamento = (int) $this->input('id', 0);
        $descricao = trim((string) $this->input('descricao', ''));
        $tipo = (string) $this->input('tipo', '');
        $parcelas = max(1, (int) $this->input('parcelas', 1));
        $valor = (float) str_replace(',', '.', (string) $this->input('valor', '0'));
        $ativo = (string) $this->input('ativo', 'S');

        if ($descricao === '') {
            Session::setFlash('flash', 'Informe a descrição.');
            $this->redirect('/admin/cursos/definir-valor?id=' . $idCurso);
        }

        $result = $this->pagamentoService->salvar([
            'id' => $idPagamento,
            'id_curso' => $idCurso,
            'descricao' => $descricao,
            'tipo' => $tipo,
            'parcelas' => $parcelas,
            'valor' => $valor,
            'ativo' => $ativo,
        ]);

        if ($result <= 0) {
            Session::setFlash('flash', 'Erro ao salvar forma de pagamento.');
            $this->redirect('/admin/cursos/definir-valor?id=' . $idCurso);
        }

        $this->logService->log($idPagamento > 0 ? 'atualizar' : 'criar', 'cursos_iesb_pagamento', $result, ($idPagamento > 0 ? 'Pagamento atualizado' : 'Pagamento criado') . ' para o curso #' . $idCurso);
        Session::setFlash('flash', 'Forma de pagamento salva com sucesso.');
        $this->redirect('/admin/cursos/definir-valor?id=' . $idCurso);
    }

    public function cursosTurma(): void
    {
        if (!$this->isStaff()) {
            Session::setFlash('flash', 'Acesso negado.');
            $this->redirect('/admin/login');
        }

        $cursosTurmas = $this->cursoService->listarCursosTurmas();

        $this->render('pages/admin/cursos/cursos_turma', [
            'title' => 'Cursos-turma',
            'currentRoute' => '/admin/cursos/cursos-turma',
            'cursosTurmas' => $cursosTurmas,
        ], 'admin');
    }

    private function normalizeAtivo(string $value): string
    {
        $normalized = strtoupper(trim($value));
        return $normalized === 'N' ? 'N' : 'S';
    }

    private function normalizeConfirmado(string $value): string
    {
        $normalized = strtoupper(trim($value));
        return $normalized === 'S' ? 'S' : 'N';
    }

    private function normalizeExibirHome(string $value): string
    {
        $normalized = strtoupper(trim($value));
        return $normalized === 'S' ? 'S' : 'N';
    }

    private function isStaff(): bool
    {
        return (new \App\Services\AuthService())->isStaff();
    }
}
