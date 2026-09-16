<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Services\AuthService;
use App\Services\LogService;
use App\Services\ProtocoloService;
use App\Support\Session;

final class ProtocoloController extends Controller
{
    private ProtocoloService $protocoloService;
    private LogService $logService;

    public function __construct()
    {
        $this->protocoloService = new ProtocoloService();
        $this->logService = new LogService();
    }

    public function index(): void
    {
        if (!$this->isStaff()) {
            Session::setFlash('flash', 'Acesso negado.');
            $this->redirect('/admin/login');
        }

        $filtros = [
            'status' => trim((string) ($_GET['status'] ?? '')),
            'prioridade' => trim((string) ($_GET['prioridade'] ?? '')),
            'aluno' => trim((string) ($_GET['aluno'] ?? '')),
            'id_curso' => (int) ($_GET['id_curso'] ?? 0),
            'inicio' => trim((string) ($_GET['inicio'] ?? '')),
            'fim' => trim((string) ($_GET['fim'] ?? '')),
        ];

        $this->render('pages/admin/protocolos/index', [
            'title' => 'Protocolos',
            'currentRoute' => '/admin/protocolos',
            'protocolos' => $this->protocoloService->listarParaSecretaria($filtros),
            'contagem' => $this->protocoloService->contarPorStatus(),
            'cursos' => $this->protocoloService->cursosComProtocolos(),
            'filtros' => $filtros,
        ], 'admin');
    }

    public function show(): void
    {
        if (!$this->isStaff()) {
            Session::setFlash('flash', 'Acesso negado.');
            $this->redirect('/admin/login');
        }

        $id = (int) ($_GET['id'] ?? 0);
        $protocolo = $id > 0 ? $this->protocoloService->buscarPorId($id) : null;

        if (!$protocolo) {
            Session::setFlash('flash', 'Protocolo não encontrado.');
            $this->redirect('/admin/protocolos');
            return;
        }

        $this->render('pages/admin/protocolos/show', [
            'title' => 'Protocolo #' . str_pad((string) $id, 6, '0', STR_PAD_LEFT),
            'currentRoute' => '/admin/protocolos',
            'protocolo' => $protocolo,
            'mensagens' => $this->protocoloService->listarMensagens($id),
        ], 'admin');
    }

    public function responder(): void
    {
        if (!$this->isStaff()) {
            Session::setFlash('flash', 'Acesso negado.');
            $this->redirect('/admin/login');
        }

        $id = (int) $this->input('id', 0);
        $mensagem = trim((string) $this->input('mensagem', ''));
        $userId = (int) (Session::get('user')['id'] ?? 0);

        if ($id <= 0 || $mensagem === '') {
            Session::setFlash('flash', 'Digite uma mensagem.');
            $this->redirect('/admin/protocolos/show?id=' . $id);
            return;
        }

        if ($this->protocoloService->responderSecretaria($id, $userId, $mensagem)) {
            $this->logService->log('criar', 'protocolo_mensagem', $id, 'Resposta da secretaria no protocolo #' . $id);
            Session::setFlash('flash', 'Resposta enviada ao aluno.');
        } else {
            Session::setFlash('flash', 'Erro ao enviar a resposta.');
        }

        $this->redirect('/admin/protocolos/show?id=' . $id);
    }

    public function status(): void
    {
        if (!$this->isStaff()) {
            Session::setFlash('flash', 'Acesso negado.');
            $this->redirect('/admin/login');
        }

        $id = (int) $this->input('id', 0);
        $status = trim((string) $this->input('status', ''));

        if ($this->protocoloService->atualizarStatus($id, $status)) {
            $this->logService->log('atualizar', 'protocolo', $id, 'Status alterado para ' . $status . ' no protocolo #' . $id);
            Session::setFlash('flash', 'Status atualizado para ' . $status . '.');
        } else {
            Session::setFlash('flash', 'Não foi possível atualizar o status.');
        }

        $this->redirect('/admin/protocolos/show?id=' . $id);
    }

    public function prioridade(): void
    {
        if (!$this->isStaff()) {
            Session::setFlash('flash', 'Acesso negado.');
            $this->redirect('/admin/login');
        }

        $id = (int) $this->input('id', 0);
        $prioridade = trim((string) $this->input('prioridade', ''));

        if ($this->protocoloService->atualizarPrioridade($id, $prioridade)) {
            $this->logService->log('atualizar', 'protocolo', $id, 'Prioridade alterada para ' . $prioridade . ' no protocolo #' . $id);
            Session::setFlash('flash', 'Prioridade atualizada para ' . $prioridade . '.');
        } else {
            Session::setFlash('flash', 'Não foi possível atualizar a prioridade.');
        }

        $this->redirect('/admin/protocolos/show?id=' . $id);
    }

    public function assumir(): void
    {
        if (!$this->isStaff()) {
            Session::setFlash('flash', 'Acesso negado.');
            $this->redirect('/admin/login');
        }

        $id = (int) $this->input('id', 0);
        $userId = (int) (Session::get('user')['id'] ?? 0);

        if ($this->protocoloService->assumir($id, $userId)) {
            $this->logService->log('atualizar', 'protocolo', $id, 'Protocolo #' . $id . ' assumido pela secretaria');
            Session::setFlash('flash', 'Você assumiu este protocolo.');
        } else {
            Session::setFlash('flash', 'Não foi possível assumir o protocolo.');
        }

        $this->redirect('/admin/protocolos/show?id=' . $id);
    }

    private function isStaff(): bool
    {
        return (new AuthService())->isStaff();
    }
}