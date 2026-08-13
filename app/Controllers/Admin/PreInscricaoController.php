<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Services\AcordoPagamentoService;
use App\Services\AuthService;
use App\Services\CommentService;
use App\Services\CursoPagamentoService;
use App\Services\CursoService;
use App\Services\IpLocationService;
use App\Services\LogService;
use App\Services\MatriculaService;
use App\Services\NotificacaoEmailService;
use App\Services\PreInscricaoService;
use App\Support\Session;

final class PreInscricaoController extends Controller
{
    private PreInscricaoService $preService;
    private IpLocationService $ipLocation;
    private CursoService $cursoService;
    private CursoPagamentoService $pagamentoService;
    private AcordoPagamentoService $acordoService;
    private CommentService $comments;
    private LogService $logService;
    private MatriculaService $matriculaService;
    private NotificacaoEmailService $notificacaoEmailService;

    public function __construct()
    {
        $this->preService = new PreInscricaoService();
        $this->ipLocation = new IpLocationService();
        $this->cursoService = new CursoService();
        $this->pagamentoService = new CursoPagamentoService();
        $this->acordoService = new AcordoPagamentoService();
        $this->comments = new CommentService();
        $this->logService = new LogService();
        $this->matriculaService = new MatriculaService();
        $this->notificacaoEmailService = new NotificacaoEmailService();
    }

    public function index(): void
    {
        if (!$this->isStaff()) {
            Session::setFlash('flash', 'Acesso negado.');
            $this->redirect('/admin/login');
        }

        $situacao = trim((string) ($_GET['situacao'] ?? ''));
        $situacoesValidas = ['recebido', 'atendimento', 'finalizado'];
        if (!in_array($situacao, $situacoesValidas, true)) {
            $situacao = '';
        }

        $preInscricoes = $this->preService->listarTodos($situacao !== '' ? $situacao : null);

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
            'situacaoFiltro' => $situacao,
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

        $planos = [];
        $acordos = [];
        if ($cursoId > 0) {
            $planos = array_values(array_filter(
                $this->pagamentoService->listarPorCurso($cursoId),
                static fn (array $plano): bool => (int) ($plano['ativo'] ?? 0) === 1
            ));
            $acordos = $this->acordoService->listarPorPreInscricao($id);
        }

        $this->render('pages/admin/preinscricao/detalhe', [
            'title' => 'Pré-inscrição #' . $id,
            'currentRoute' => '/admin/preinscricao',
            'pre' => $pre,
            'comentarios' => $comentarios,
            'comentariosTotal' => $comentariosTotal,
            'cidade' => (string) ($location['city'] ?? '-'),
            'pais' => (string) ($location['country'] ?? '-'),
            'bandeira' => (string) ($location['flag'] ?? '🏳️'),
            'planos' => $planos,
            'acordos' => $acordos,
        ], 'admin');
    }

    public function salvarAcordo(): void
    {
        if (!$this->isStaff()) {
            $this->json(['erro' => 'Acesso negado.'], 403);
        }

        $id = (int) $this->input('pre_id', 0);
        $tipo = (int) $this->input('tipo', 1);
        $idCursoPagamento = (int) $this->input('id_curso_pagamento', 0);
        $cpf = preg_replace('/\D/', '', trim((string) $this->input('cpf', '')));
        $valorEntrada = (float) $this->input('valor_entrada', 0);
        $dataVencimentoEntrada = trim((string) $this->input('data_vencimento_entrada', ''));
        $totalParcelas = (int) $this->input('total_parcelas', 0);
        $valorDemaisParcelas = (float) $this->input('valor_demais_parcelas', 0);
        $desconto = (float) $this->input('desconto', 0);
        $tipoDesconto = trim((string) $this->input('tipo_desconto', 'NEGOCIACAO'));
        $motivo = trim((string) $this->input('motivo', ''));
        $observacao = trim((string) $this->input('observacao', ''));

        $pre = $id > 0 ? $this->preService->findById($id) : null;
        if ($pre === null) {
            $this->json(['erro' => 'Pré-inscrição não encontrada.'], 404);
        }

        $plano = $idCursoPagamento > 0 ? $this->pagamentoService->find($idCursoPagamento) : null;
        if ($plano === null || (int) ($plano['ativo'] ?? 0) !== 1) {
            $this->json(['erro' => 'Plano de pagamento inválido.'], 400);
        }

        if ($tipo < 1) {
            $tipo = 1;
        }

        $valoresTipoDesconto = ['ALUNO', 'CONVENIO', 'BOLSA', 'CAMPANHA', 'NEGOCIACAO', 'OUTRO'];
        if (!in_array($tipoDesconto, $valoresTipoDesconto, true)) {
            $tipoDesconto = 'NEGOCIACAO';
        }

        if ($cpf === '') {
            $this->json(['erro' => 'CPF é obrigatório.'], 400);
        }

        if ($totalParcelas < 1) {
            $totalParcelas = max(1, (int) ($plano['parcelas'] ?? 1));
        }
        if ($valorEntrada <= 0) {
            $valorEntrada = (float) ($plano['valor'] ?? 0);
        }
        if ($valorEntrada <= 0) {
            $this->json(['erro' => 'Valor de entrada inválido.'], 400);
        }
        if ($totalParcelas > 1 && $valorDemaisParcelas <= 0) {
            $valorDemaisParcelas = $valorEntrada;
        }
        if ($dataVencimentoEntrada !== '' && strtotime($dataVencimentoEntrada) === false) {
            $dataVencimentoEntrada = '';
        }

        $user = Session::get('user');
        $idUsuarioAutorizacao = is_array($user) ? (int) ($user['id'] ?? 0) : 0;

        $acordoId = $this->acordoService->salvar([
            'tipo' => $tipo,
            'id_pre_inscricao' => $id,
            'id_curso_pagamento' => $idCursoPagamento,
            'id_usuario_autorizacao' => $idUsuarioAutorizacao,
            'cpf' => $cpf,
            'token' => $this->acordoService->gerarToken(),
            'valor_entrada' => $valorEntrada,
            'data_vencimento_entrada' => $dataVencimentoEntrada !== '' ? $dataVencimentoEntrada : null,
            'total_parcelas' => $totalParcelas,
            'valor_demais_parcelas' => $totalParcelas > 1 ? $valorDemaisParcelas : 0,
            'desconto' => $desconto,
            'tipo_desconto' => $tipoDesconto,
            'motivo' => $motivo,
            'observacao' => $observacao,
            'utilizado' => 0,
            'ativo' => 1,
        ]);

        if ($acordoId <= 0) {
            $this->json(['erro' => 'Erro ao salvar o acordo. Tente novamente.'], 500);
        }

        $acordo = $this->acordoService->findById($acordoId);
        $token = (string) ($acordo['token'] ?? '');
        $linkFinanceiro = $token !== '' ? '/financeiro/' . $token : '';

        $this->logService->log('criar', 'acordo_pagamento', $acordoId, 'Acordo criado para a pré-inscrição #' . $id);

        $this->json([
            'sucesso' => true,
            'acordo_id' => $acordoId,
            'token' => $token,
            'link_financeiro' => $linkFinanceiro,
        ]);
    }

    public function acordos(): void
    {
        if (!$this->isStaff()) {
            Session::setFlash('flash', 'Acesso negado.');
            $this->redirect('/admin/login');
        }

        // Reconciliação: parcelas com status RECEBIDO/CONFIRMADO que ainda não
        // geraram matrícula (webhook interrompido) são efetivadas aqui.
        try {
            $reprocessados = $this->matriculaService->reprocessarParcelasSemMatricula();
        } catch (\Throwable $e) {
            error_log('[PREINSCRICAO ACORDOS] Erro na reconciliação: ' . $e->getMessage());
            $reprocessados = [];
        }

        $acordos = $this->acordoService->listarComPreInscrito();

        $enviosPorAcordo = $this->notificacaoEmailService->listarPorAcordos(
            array_map(static fn (array $acordo): int => (int) ($acordo['id'] ?? 0), $acordos)
        );

        $this->render('pages/admin/preinscricao/acordo', [
            'title' => 'Acordos',
            'currentRoute' => '/admin/preinscricao/acordos',
            'acordos' => $acordos,
            'reprocessados' => $reprocessados,
            'enviosPorAcordo' => $enviosPorAcordo,
        ], 'admin');
    }

    public function enviarEmailAcordo(): void
    {
        if (!$this->isStaff()) {
            $this->json(['erro' => 'Acesso negado.'], 403);
        }

        $id = (int) $this->input('acordo_id', 0);
        if ($id < 1) {
            $this->json(['erro' => 'ID do acordo inválido.'], 400);
        }

        $user = Session::get('user');
        $idUsuario = is_array($user) ? (int) ($user['id'] ?? 0) : 0;

        $resultado = $this->notificacaoEmailService->enviarEmailAcordo($id, $idUsuario);

        if ($resultado['sucesso']) {
            $this->logService->log(
                'criar',
                'notificacao_email',
                (int) ($resultado['registro_id'] ?? 0),
                'E-mail de acordo #' . $id . ' enviado para ' . (string) ($resultado['destinatario'] ?? '')
            );

            $this->json(['sucesso' => true, 'mensagem' => $resultado['mensagem']]);
        }

        $this->json(['erro' => $resultado['mensagem']], 400);
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
