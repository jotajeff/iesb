<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Services\AuthService;
use App\Services\CommentService;
use App\Services\CursoService;
use App\Services\IpLocationService;
use App\Services\LogService;
use App\Services\PreInscricaoService;
use App\Support\Session;

final class PreInscricaoController extends Controller
{
    private PreInscricaoService $preService;
    private IpLocationService $ipLocation;
    private CursoService $cursoService;
    private CommentService $comments;
    private LogService $logService;

    public function __construct()
    {
        $this->preService = new PreInscricaoService();
        $this->ipLocation = new IpLocationService();
        $this->cursoService = new CursoService();
        $this->comments = new CommentService();
        $this->logService = new LogService();
    }

    public function index(): void
    {
        if (!$this->isStaff()) {
            Session::setFlash('flash', 'Acesso negado.');
            $this->redirect('/admin/login');
        }

        $preInscricoes = $this->preService->listarTodos();

        foreach ($preInscricoes as &$p) {
            $ip = (string) ($p['ip'] ?? '');
            $location = $ip !== '' ? $this->ipLocation->resolve($ip) : [];
            $p['cidade'] = (string) ($location['city'] ?? '-');
            $p['pais'] = (string) ($location['country'] ?? '-');
            $p['bandeira'] = (string) ($location['flag'] ?? '🏳️');

            $cursoId = (int) ($p['curso_id'] ?? 0);
            if ($cursoId > 0) {
                $curso = $this->cursoService->findCurso($cursoId);
                $p['curso_nome'] = $curso ? (string) ($curso['nome'] ?? '-') : '-';
            }
        }
        unset($p);

        $this->render('pages/admin/preinscricao/index', [
            'title' => 'Pré-inscrições',
            'currentRoute' => '/admin/preinscricao',
            'preInscricoes' => $preInscricoes,
        ], 'admin');
    }

    public function kanban(): void
    {
        if (!$this->isStaff()) {
            Session::setFlash('flash', 'Acesso negado.');
            $this->redirect('/admin/login');
        }

        $todas = $this->preService->listarTodos();

        $colunas = ['recebido' => [], 'atendimento' => [], 'finalizado' => []];

        foreach ($todas as &$p) {
            $sit = (string) ($p['situacao'] ?? 'recebido');
            $cursoId = (int) ($p['curso_id'] ?? 0);
            if ($cursoId > 0) {
                $curso = $this->cursoService->findCurso($cursoId);
                $p['curso_nome'] = $curso ? (string) ($curso['nome'] ?? '-') : '-';
            }
            $p['qtd_comentarios'] = $this->comments->countFor('pre_inscricao', (int) ($p['id'] ?? 0));
            $colunas[$sit][] = $p;
        }
        unset($p);

        $this->render('pages/admin/preinscricao/kanban', [
            'title' => 'Kanban — Pré-inscrições',
            'currentRoute' => '/admin/preinscricao',
            'colunas' => $colunas,
        ], 'admin');
    }

    public function detalhe(): void
    {
        if (!$this->isStaff()) {
            Session::setFlash('flash', 'Acesso negado.');
            $this->redirect('/admin/login');
        }

        $id = (int) ($_GET['id'] ?? 0);
        $pre = $this->preService->findById($id);

        if ($pre === null) {
            Session::setFlash('flash', 'Pré-inscrição não encontrada.');
            $this->redirect('/admin/preinscricao');
        }

        $cursoId = (int) ($pre['curso_id'] ?? 0);
        if ($cursoId > 0) {
            $curso = $this->cursoService->findCurso($cursoId);
            $pre['curso_nome'] = $curso ? (string) ($curso['nome'] ?? '-') : '-';
        }

        $comentarios = $this->comments->listFor('pre_inscricao', $id);
        $comentariosTotal = $this->comments->countFor('pre_inscricao', $id);
        $ip = (string) ($pre['ip'] ?? '');
        $location = $ip !== '' ? $this->ipLocation->resolve($ip) : [];

        $this->render('pages/admin/preinscricao/detalhe', [
            'title' => 'Pré-inscrição #' . $id,
            'currentRoute' => '/admin/preinscricao',
            'pre' => $pre,
            'comentarios' => $comentarios,
            'comentariosTotal' => $comentariosTotal,
            'cidade' => (string) ($location['city'] ?? '-'),
            'pais' => (string) ($location['country'] ?? '-'),
            'bandeira' => (string) ($location['flag'] ?? '🏳️'),
        ], 'admin');
    }

    public function comentario(): void
    {
        if (!$this->isStaff()) {
            $this->json(['erro' => 'Acesso negado.'], 403);
        }

        $id = (int) $this->input('pre_id', 0);
        $texto = trim((string) $this->input('comentario', ''));

        if ($id < 1 || $texto === '') {
            Session::setFlash('flash', 'Dados inválidos para o comentário.');
            $this->redirect('/admin/preinscricao/detalhe?id=' . $id);
        }

        $result = $this->comments->createFor('pre_inscricao', $id, $texto);

        if ($result > 0) {
            $this->logService->log('criar', 'comentario', $result, 'Comentário adicionado na pré-inscrição #' . $id);
            Session::setFlash('flash', 'Comentário salvo com sucesso.');
        } else {
            Session::setFlash('flash', 'Erro ao salvar comentário.');
        }

        $this->redirect('/admin/preinscricao/detalhe?id=' . $id);
    }

    public function atualizarSituacao(): void
    {
        if (!$this->isStaff()) {
            $this->json(['erro' => 'Acesso negado.'], 403);
        }

        $id = (int) $this->input('id', 0);
        $situacao = trim((string) $this->input('situacao', ''));

        if (!in_array($situacao, ['recebido', 'atendimento', 'finalizado'], true)) {
            $this->json(['erro' => 'Situação inválida.'], 400);
        }

        $resultado = $this->preService->atualizarSituacao($id, $situacao);

        if ($resultado) {
            $this->logService->log('atualizar', 'pre_inscricao', $id, 'Situação alterada para: ' . $situacao);
            $this->json(['sucesso' => true]);
        }

        $this->json(['erro' => 'Erro ao atualizar situação.'], 500);
    }

    private function isStaff(): bool
    {
        return (new AuthService())->isStaff();
    }
}
