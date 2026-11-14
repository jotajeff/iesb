<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Repositories\LeadRepository;
use App\Services\AuthService;
use App\Services\CommentService;
use App\Services\IpLocationService;
use App\Services\LogService;
use App\Support\Session;

final class LeadController extends Controller
{
    private LeadRepository $leads;
    private CommentService $comments;
    private IpLocationService $ipLocation;
    private LogService $logService;

    public function __construct()
    {
        $this->leads = new LeadRepository();
        $this->comments = new CommentService();
        $this->ipLocation = new IpLocationService();
        $this->logService = new LogService();
    }

    public function index(): void
    {
        $this->guard();

        $ativoFiltro = (int) ($_GET['ativo'] ?? 1);
        if (!in_array($ativoFiltro, [1, 2], true)) {
            $ativoFiltro = 1;
        }

        $colunas = [
            1 => [],
            2 => [],
            3 => [],
        ];

        foreach ($this->leads->listarPorAtivo($ativoFiltro) as $lead) {
            $situacao = (int) ($lead['situacao'] ?? 1);
            if (!isset($colunas[$situacao])) {
                $situacao = 1;
            }

            $lead = $this->withLocation($lead);
            $lead['situacao'] = $situacao;
            $lead['qtd_comentarios'] = $this->comments->countFor('leads', (int) ($lead['id'] ?? 0));
            $colunas[$situacao][] = $lead;
        }

        $this->render('pages/admin/leads/index', [
            'title' => 'Leads',
            'currentRoute' => '/admin/leads',
            'colunas' => $colunas,
            'ativoFiltro' => $ativoFiltro,
        ], 'admin');
    }

    public function editar(): void
    {
        $this->guard();

        $id = (int) ($_GET['id'] ?? 0);
        $lead = $this->leads->findById($id);

        if ($lead === null) {
            Session::setFlash('flash', 'Lead não encontrado.');
            $this->redirect('/admin/leads');
            return;
        }

        $lead = $this->withLocation($lead);
        $lead['situacao'] = (int) ($lead['situacao'] ?? 1);
        $lead['ativo'] = (int) ($lead['ativo'] ?? 1);
        if (!in_array($lead['ativo'], [1, 2], true)) {
            $lead['ativo'] = 1;
        }

        $this->render('pages/admin/leads/editar', [
            'title' => 'Editar lead #' . $id,
            'currentRoute' => '/admin/leads',
            'lead' => $lead,
            'comentarios' => $this->comments->listFor('leads', $id),
            'comentariosTotal' => $this->comments->countFor('leads', $id),
        ], 'admin');
    }

    public function atualizar(): void
    {
        $this->guard();

        $id = (int) $this->input('id', 0);
        $situacao = (int) $this->input('situacao', 0);
        $ativo = (int) $this->input('ativo', 1);
        $comentario = trim((string) $this->input('comentario', ''));

        if ($id <= 0 || !in_array($situacao, [1, 2, 3], true) || !in_array($ativo, [1, 2], true)) {
            Session::setFlash('flash', 'Dados inválidos para atualizar o lead.');
            $this->redirect('/admin/leads');
            return;
        }

        $lead = $this->leads->findById($id);
        if ($lead === null) {
            Session::setFlash('flash', 'Lead não encontrado.');
            $this->redirect('/admin/leads');
            return;
        }

        $situacaoAnterior = (int) ($lead['situacao'] ?? 1);
        $ativoAnterior = (int) ($lead['ativo'] ?? 1);
        $atualizou = $this->leads->atualizar($id, $situacao, $ativo);
        $comentarioId = 0;

        if ($comentario !== '') {
            $comentarioId = $this->comments->createFor('leads', $id, $comentario);
        }

        if (!$atualizou || ($comentario !== '' && $comentarioId <= 0)) {
            Session::setFlash('flash', 'Não foi possível salvar todas as alterações do lead.');
            $this->redirect('/admin/leads/editar?id=' . $id);
            return;
        }

        if ($situacaoAnterior !== $situacao) {
            $situacoes = [1 => 'Recebido', 2 => 'Atendido', 3 => 'Encerrado'];
            $this->logService->log(
                'atualizar',
                'lead',
                $id,
                'Situação alterada de ' . ($situacoes[$situacaoAnterior] ?? $situacaoAnterior) . ' para ' . $situacoes[$situacao]
            );
        }

        if ($ativoAnterior !== $ativo) {
            $this->logService->log(
                'atualizar',
                'lead',
                $id,
                $ativo === 1 ? 'Lead ativado' : 'Lead desativado'
            );
        }

        if ($comentarioId > 0) {
            $this->logService->log('criar', 'comentario', $comentarioId, 'Comentário adicionado no lead #' . $id);
        }

        Session::setFlash('flash', 'Lead atualizado com sucesso.');
        $this->redirect('/admin/leads/editar?id=' . $id);
    }

    private function withLocation(array $lead): array
    {
        $ip = trim((string) ($lead['ip'] ?? ''));
        $location = $ip !== '' ? $this->ipLocation->resolve($ip) : [];

        $lead['cidade'] = trim((string) ($location['city'] ?? '-'));
        $lead['pais'] = trim((string) ($location['country'] ?? '-'));
        $lead['bandeira'] = trim((string) ($location['flag'] ?? '🏳️'));

        return $lead;
    }

    private function guard(): void
    {
        if (!(new AuthService())->isStaff()) {
            Session::setFlash('flash', 'Acesso negado.');
            $this->redirect('/admin/login');
        }
    }
}
