<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Services\AuthService;
use App\Services\ChamadaService;
use App\Services\LogService;
use App\Support\Session;

final class ChamadaController extends Controller
{
    private ChamadaService $chamadaService;
    private LogService $logService;

    public function __construct()
    {
        $this->chamadaService = new ChamadaService();
        $this->logService = new LogService();
    }

    public function index(): void
    {
        if (!$this->isStaff()) {
            Session::setFlash('flash', 'Acesso negado.');
            $this->redirect('/admin/login');
        }

        $this->render('pages/admin/chamada/index', [
            'title' => 'Chamadas',
            'currentRoute' => '/admin/chamadas',
            'chamadas' => $this->chamadaService->list(),
        ], 'admin');
    }

    public function novo(): void
    {
        if (!$this->isStaff()) {
            Session::setFlash('flash', 'Acesso negado.');
            $this->redirect('/admin/login');
        }

        $this->render('pages/admin/chamada/novo', [
            'title' => 'Gerar Chamada',
            'currentRoute' => '/admin/chamadas',
            'turmas' => $this->chamadaService->turmas(),
        ], 'admin');
    }

    public function ajaxProfessores(): void
    {
        if (!$this->isStaff()) {
            $this->json(['erro' => 'Acesso negado.'], 403);
            return;
        }

        $idTurma = (int) ($_GET['id_turma'] ?? 0);
        if ($idTurma <= 0) {
            $this->json([]);
            return;
        }

        $this->json($this->chamadaService->professoresDaTurma($idTurma));
    }

    public function ajaxDisciplinas(): void
    {
        if (!$this->isStaff()) {
            $this->json(['erro' => 'Acesso negado.'], 403);
            return;
        }

        $idTurma = (int) ($_GET['id_turma'] ?? 0);
        $idProfessor = (int) ($_GET['id_professor'] ?? 0);

        if ($idTurma <= 0) {
            $this->json([]);
            return;
        }

        $this->json($this->chamadaService->disciplinasDaTurma($idTurma, $idProfessor > 0 ? $idProfessor : null));
    }

    public function relatorio(): void
    {
        if (!$this->isStaff()) {
            Session::setFlash('flash', 'Acesso negado.');
            $this->redirect('/admin/login');
        }

        $idCurso = (int) ($_GET['id_curso'] ?? 0);
        $relatorio = ['alunos' => [], 'chamadas' => [], 'presencas' => []];
        if ($idCurso > 0) {
            $relatorio = $this->chamadaService->relatorioPresencas($idCurso);
        }

        $this->render('pages/admin/chamada/relatorio', [
            'title' => 'Relatório de Presenças',
            'currentRoute' => '/admin/chamadas/relatorio',
            'cursos' => $this->chamadaService->cursos(),
            'idCurso' => $idCurso,
            'relatorio' => $relatorio,
        ], 'admin');
    }

    public function gerar(): void
    {
        if (!$this->isStaff()) {
            Session::setFlash('flash', 'Acesso negado.');
            $this->redirect('/admin/login');
        }

        $idTurma = (int) $this->input('id_turma', 0);
        $idTurmaDisciplina = (int) $this->input('id_turma_disciplina', 0);
        $dataAula = trim((string) $this->input('data_aula', ''));

        if ($idTurma <= 0 || $idTurmaDisciplina <= 0 || $dataAula === '') {
            Session::setFlash('flash', 'Selecione a turma, o professor e a disciplina, e informe a data da aula.');
            $this->redirect('/admin/chamadas/novo');
            return;
        }

        $id = $this->chamadaService->gerar([
            'id_turma' => $idTurma,
            'id_turma_disciplina' => $idTurmaDisciplina,
            'id_usuario_professor' => (int) $this->input('id_usuario_professor', 0),
            'data_aula' => $dataAula,
            'numero_aula' => (int) $this->input('numero_aula', 0),
            'hora_inicio' => trim((string) $this->input('hora_inicio', '')),
            'hora_fim' => trim((string) $this->input('hora_fim', '')),
            'conteudo' => trim((string) $this->input('conteudo', '')),
            'observacao' => trim((string) $this->input('observacao', '')),
            'status' => (string) $this->input('status', 'ABERTA'),
        ]);

        if ($id === -1) {
            Session::setFlash('flash', 'Já existe uma chamada para esta disciplina nesta data.');
            $this->redirect('/admin/chamadas/novo');
            return;
        }

        if ($id <= 0) {
            Session::setFlash('flash', 'Erro ao gerar a chamada.');
            $this->redirect('/admin/chamadas/novo');
            return;
        }

        $this->logService->log('criar', 'chamada', $id, 'Chamada gerada para turma #' . $idTurma . ' em ' . $dataAula);
        Session::setFlash('flash', 'Chamada gerada com sucesso.');
        $this->redirect('/admin/chamadas');
    }

    private function isStaff(): bool
    {
        return (new AuthService())->isStaff();
    }
}