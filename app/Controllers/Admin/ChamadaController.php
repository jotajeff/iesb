<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Services\AuthService;
use App\Services\ChamadaService;
use App\Services\LogService;
use App\Services\Storage\StorageService;
use App\Support\Session;

final class ChamadaController extends Controller
{
    private const PASTA_CHAMADAS_DRIVE = '1BT9S9tEKDAkzw8rXzK2rUPM9ixwSM45B';

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

        $idTurma = (int) ($_GET['id_turma'] ?? 0);
        $relatorio = ['turma' => null, 'alunos' => [], 'chamadas' => [], 'presencas' => []];
        if ($idTurma > 0) {
            $relatorio = $this->chamadaService->relatorioPresencas($idTurma);
        }

        $this->render('pages/admin/chamada/relatorio', [
            'title' => 'Relatório de Presenças',
            'currentRoute' => '/admin/chamadas/relatorio',
            'turmas' => $this->chamadaService->turmas(),
            'idTurma' => $idTurma,
            'relatorio' => $relatorio,
        ], 'admin');
    }

    public function relatorioExcel(): void
    {
        if (!$this->isStaff()) {
            Session::setFlash('flash', 'Acesso negado.');
            $this->redirect('/admin/login');
        }

        $idTurma = (int) ($_GET['id_turma'] ?? 0);
        $relatorio = $this->chamadaService->relatorioPresencas($idTurma);

        $turma = is_array($relatorio['turma'] ?? null) ? $relatorio['turma'] : null;
        $alunos = is_array($relatorio['alunos'] ?? null) ? $relatorio['alunos'] : [];
        $chamadas = is_array($relatorio['chamadas'] ?? null) ? $relatorio['chamadas'] : [];
        $presencas = is_array($relatorio['presencas'] ?? null) ? $relatorio['presencas'] : [];

        $cursoNome = trim((string) ($turma['curso_nome'] ?? ''));
        $turmaNome = trim((string) ($turma['turma_nome'] ?? 'turma-' . $idTurma));
        $arquivo = 'relatorio-presencas-' . ($turmaNome !== '' ? $turmaNome : (string) $idTurma) . '.csv';
        $arquivo = preg_replace('/[^A-Za-z0-9._-]/', '_', $arquivo) ?: 'relatorio-presencas.csv';

        $linhas = [];
        $linhas[] = ['Relatório de Presenças'];
        $linhas[] = ['Curso', $cursoNome];
        $linhas[] = ['Turma', $turmaNome];

        $cabecalho = ['Aluno'];
        foreach ($chamadas as $ch) {
            $chData = (string) ($ch['data_aula'] ?? '');
            $chDt = $chData !== '' ? date_create($chData) : false;
            $label = $chDt ? $chDt->format('d/m/Y') : $chData;
            $label .= ' - ' . trim((string) ($ch['disciplina_nome'] ?? ''));
            $cabecalho[] = $label;
        }
        $linhas[] = $cabecalho;

        foreach ($alunos as $aluno) {
            $idMatricula = (int) ($aluno['id_matricula'] ?? 0);
            $linha = [(string) ($aluno['aluno_nome'] ?? '-')];
            foreach ($chamadas as $ch) {
                $presenca = $presencas[(int) ($ch['id'] ?? 0) . ':' . $idMatricula] ?? '';
                $linha[] = match ($presenca) {
                    'PRESENTE' => 'PRESENTE',
                    'AUSENTE' => 'AUSENTE',
                    'JUSTIFICADA' => 'JUSTIFICADA',
                    default => 'FALTA',
                };
            }
            $linhas[] = $linha;
        }

        $linhas[] = [];
        $linhas[] = ['Total de alunos da turma', (string) count($alunos)];

        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $arquivo . '"');
        header('Cache-Control: no-cache, no-store, must-revalidate');

        $saida = fopen('php://output', 'w');
        if ($saida === false) {
            return;
        }

        fwrite($saida, "\xEF\xBB\xBF");
        foreach ($linhas as $linha) {
            fputcsv($saida, $linha, ';');
        }
        fclose($saida);
        exit;
    }

    public function alterarStatus(): void
    {
        if (!$this->isStaff()) {
            $this->json(['erro' => 'Acesso negado.'], 403);
            return;
        }

        $id = (int) $this->input('id', 0);
        $status = trim((string) $this->input('status', ''));

        if ($id <= 0 || !in_array($status, ['ABERTA', 'FECHADA', 'CANCELADA'], true)) {
            $this->json(['erro' => 'Dados inválidos.'], 400);
            return;
        }

        if ($this->chamadaService->alterarStatus($id, $status)) {
            $this->logService->log('atualizar', 'chamada', $id, 'Status da chamada #' . $id . ' alterado para ' . $status);
            $this->json(['sucesso' => true, 'status' => $status]);
            return;
        }

        $this->json(['erro' => 'Erro ao alterar o status.'], 500);
    }

    public function lancamento(): void
    {
        if (!$this->isStaff()) {
            Session::setFlash('flash', 'Acesso negado.');
            $this->redirect('/admin/login');
        }

        $id = (int) ($_GET['id'] ?? 0);
        $chamada = $id > 0 ? $this->chamadaService->buscarPorId($id) : null;

        if (!$chamada) {
            Session::setFlash('flash', 'Chamada não encontrada.');
            $this->redirect('/admin/chamadas');
            return;
        }

        $this->render('pages/admin/chamada/lancamento', [
            'title' => 'Lançamento de Presença',
            'currentRoute' => '/admin/chamadas',
            'chamada' => $chamada,
            'inscritos' => $this->chamadaService->inscritosDaChamada($id),
            'presencas' => $this->chamadaService->presencasDaChamada($id),
        ], 'admin');
    }

    public function registrarPresenca(): void
    {
        if (!$this->isStaff()) {
            Session::setFlash('flash', 'Acesso negado.');
            $this->redirect('/admin/login');
        }

        $idChamada = (int) $this->input('id_chamada', 0);
        $idMatricula = (int) $this->input('id_matricula', 0);
        $presenca = trim((string) $this->input('presenca', 'PRESENTE'));
        $entrada = trim((string) $this->input('entrada', ''));
        $permanencia = trim((string) $this->input('permanencia', ''));
        $observacao = trim((string) $this->input('observacao', ''));

        $chamada = $idChamada > 0 ? $this->chamadaService->buscarPorId($idChamada) : null;
        if (!$chamada) {
            Session::setFlash('flash', 'Chamada não encontrada.');
            $this->redirect('/admin/chamadas');
            return;
        }

        if ((string) ($chamada['status'] ?? '') !== 'ABERTA') {
            Session::setFlash('flash', 'Esta chamada não está aberta para lançamento.');
            $this->redirect('/admin/chamadas');
            return;
        }

        $ok = $this->chamadaService->registrarPresenca(
            $idChamada,
            $idMatricula,
            in_array($presenca, ['PRESENTE', 'AUSENTE', 'JUSTIFICADA'], true) ? $presenca : 'PRESENTE',
            $entrada !== '' ? $entrada : null,
            $permanencia !== '' ? $permanencia : null,
            $observacao !== '' ? $observacao : null,
            (string) ($_SERVER['REMOTE_ADDR'] ?? ''),
            'secretaria'
        );

        if ($ok) {
            $this->logService->log('criar', 'chamada_presenca', $idChamada, 'Presença lançada pela secretaria (matrícula #' . $idMatricula . ')');
            Session::setFlash('flash', 'Presença registrada com sucesso.');
        } else {
            Session::setFlash('flash', 'Erro ao registrar a presença.');
        }

        $this->redirect('/admin/chamadas/lancamento?id=' . $idChamada);
    }

    public function encerrarLancamento(): void
    {
        if (!$this->isStaff()) {
            Session::setFlash('flash', 'Acesso negado.');
            $this->redirect('/admin/login');
        }

        $id = (int) $this->input('id', 0);
        if ($id > 0 && $this->chamadaService->alterarStatus($id, 'FECHADA')) {
            $this->logService->log('atualizar', 'chamada', $id, 'Chamada encerrada (lançamento de presença)');
            Session::setFlash('flash', 'Chamada encerrada com sucesso.');
        } else {
            Session::setFlash('flash', 'Não foi possível encerrar a chamada.');
        }

        $this->redirect('/admin/chamadas');
    }

    public function upload(): void
    {
        if (!$this->isStaff()) {
            Session::setFlash('flash', 'Acesso negado.');
            $this->redirect('/admin/login');
        }

        $id = (int) ($_GET['id'] ?? 0);
        $chamada = $id > 0 ? $this->chamadaService->buscarPorId($id) : null;

        if (!$chamada) {
            Session::setFlash('flash', 'Chamada não encontrada.');
            $this->redirect('/admin/chamadas');
            return;
        }

        $this->render('pages/admin/chamada/upload', [
            'title' => 'Origem da Chamada',
            'currentRoute' => '/admin/chamadas',
            'chamada' => $chamada,
        ], 'admin');
    }

    public function uploadOrigem(): void
    {
        if (!$this->isStaff()) {
            Session::setFlash('flash', 'Acesso negado.');
            $this->redirect('/admin/login');
        }

        $id = (int) $this->input('id', 0);
        $chamada = $id > 0 ? $this->chamadaService->buscarPorId($id) : null;

        if (!$chamada) {
            Session::setFlash('flash', 'Chamada não encontrada.');
            $this->redirect('/admin/chamadas');
            return;
        }

        $file = $_FILES['arquivo'] ?? null;
        if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
            Session::setFlash('flash', 'Selecione um arquivo para enviar.');
            $this->redirect('/admin/chamadas/upload?id=' . $id);
            return;
        }

        try {
            $storage = new StorageService();
            if (!$storage->isConnected()) {
                Session::setFlash('flash', 'Storage não conectado. Conecte em /admin/storage.');
                $this->redirect('/admin/chamadas/upload?id=' . $id);
                return;
            }

            $ext = strtolower(pathinfo((string) ($file['name'] ?? ''), PATHINFO_EXTENSION));
            $nomeDrive = $id . '_' . date('Ymd') . ($ext !== '' ? '.' . $ext : '');

            $resultado = $storage->uploadFile($file, self::PASTA_CHAMADAS_DRIVE, $nomeDrive);
            $link = (string) ($resultado['link'] ?? '');

            if ($link === '') {
                Session::setFlash('flash', 'Não foi possível obter o link do arquivo no Drive.');
                $this->redirect('/admin/chamadas/upload?id=' . $id);
                return;
            }

            $this->chamadaService->atualizarOrigem($id, $link);
            $this->logService->log('criar', 'chamada', $id, 'Origem da chamada #' . $id . ' enviada ao Drive');
            Session::setFlash('flash', 'Documento de origem enviado com sucesso.');
        } catch (\Throwable $e) {
            error_log('[CHAMADA] Erro no upload da origem: ' . $e->getMessage());
            Session::setFlash('flash', 'Erro ao enviar o documento para o Drive.');
            $this->redirect('/admin/chamadas/upload?id=' . $id);
            return;
        }

        $this->redirect('/admin/chamadas/editar?id=' . $id);
    }

    public function editar(): void
    {
        if (!$this->isStaff()) {
            Session::setFlash('flash', 'Acesso negado.');
            $this->redirect('/admin/login');
        }

        $id = (int) ($_GET['id'] ?? 0);
        $chamada = $id > 0 ? $this->chamadaService->buscarPorId($id) : null;

        if (!$chamada) {
            Session::setFlash('flash', 'Chamada não encontrada.');
            $this->redirect('/admin/chamadas');
            return;
        }

        $idTurma = (int) ($chamada['id_turma'] ?? 0);

        $this->render('pages/admin/chamada/editar', [
            'title' => 'Editar Chamada',
            'currentRoute' => '/admin/chamadas',
            'chamada' => $chamada,
            'professores' => $idTurma > 0 ? $this->chamadaService->professoresDaTurma($idTurma) : [],
        ], 'admin');
    }

    public function atualizar(): void
    {
        if (!$this->isStaff()) {
            Session::setFlash('flash', 'Acesso negado.');
            $this->redirect('/admin/login');
        }

        $id = (int) $this->input('id', 0);
        $dataAula = trim((string) $this->input('data_aula', ''));

        if ($id <= 0 || $dataAula === '') {
            Session::setFlash('flash', 'Informe a data da aula.');
            $this->redirect('/admin/chamadas');
            return;
        }

        $resultado = $this->chamadaService->atualizarChamada($id, [
            'id_usuario_professor' => (int) $this->input('id_usuario_professor', 0),
            'data_aula' => $dataAula,
            'numero_aula' => (int) $this->input('numero_aula', 0),
            'hora_inicio' => trim((string) $this->input('hora_inicio', '')),
            'hora_fim' => trim((string) $this->input('hora_fim', '')),
            'conteudo' => trim((string) $this->input('conteudo', '')),
            'observacao' => trim((string) $this->input('observacao', '')),
            'status' => (string) $this->input('status', 'ABERTA'),
            'modo' => (int) $this->input('modo', 1),
        ]);

        if ($resultado === -1) {
            Session::setFlash('flash', 'Já existe uma chamada para esta disciplina nesta data.');
            $this->redirect('/admin/chamadas/editar?id=' . $id);
            return;
        }

        if ($resultado <= 0) {
            Session::setFlash('flash', 'Erro ao atualizar a chamada.');
            $this->redirect('/admin/chamadas/editar?id=' . $id);
            return;
        }

        $this->logService->log('atualizar', 'chamada', $id, 'Chamada #' . $id . ' editada');
        Session::setFlash('flash', 'Chamada atualizada com sucesso.');
        $this->redirect('/admin/chamadas');
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
            'modo' => (int) $this->input('modo', 1),
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