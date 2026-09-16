<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Services\AuthService;
use App\Services\LogService;
use App\Services\ProtocoloService;
use App\Support\Session;

final class ProtocoloController extends Controller
{
    private AuthService $auth;
    private ProtocoloService $protocoloService;
    private LogService $logService;

    public function __construct()
    {
        $this->auth = new AuthService();
        $this->protocoloService = new ProtocoloService();
        $this->logService = new LogService();
    }

    public function index(): void
    {
        if (!$this->auth->checkRole('aluno')) {
            Session::setFlash('flash', 'Faça login como aluno.');
            $this->redirect('/aluno/login');
            return;
        }

        $studentId = (int) (Session::get('user')['id'] ?? 0);

        $this->render('pages/aluno/protocolos/index', [
            'title' => 'Protocolos',
            'currentRoute' => '/aluno/protocolos',
            'protocolos' => $this->protocoloService->listarPorAluno($studentId),
        ], 'aluno');
    }

    public function novo(): void
    {
        if (!$this->auth->checkRole('aluno')) {
            Session::setFlash('flash', 'Faça login como aluno.');
            $this->redirect('/aluno/login');
            return;
        }

        $studentId = (int) (Session::get('user')['id'] ?? 0);

        $this->render('pages/aluno/protocolos/novo', [
            'title' => 'Novo Protocolo',
            'currentRoute' => '/aluno/protocolos',
            'matriculas' => $this->protocoloService->matriculasDoAluno($studentId),
        ], 'aluno');
    }

    public function criar(): void
    {
        if (!$this->auth->checkRole('aluno')) {
            Session::setFlash('flash', 'Faça login como aluno.');
            $this->redirect('/aluno/login');
            return;
        }

        $studentId = (int) (Session::get('user')['id'] ?? 0);
        $idMatricula = (int) $this->input('id_matricula', 0);
        $idMatricula = $idMatricula > 0 ? $idMatricula : null;
        $assunto = trim((string) $this->input('assunto', ''));
        $mensagem = trim((string) $this->input('mensagem', ''));

        if ($assunto === '' || $mensagem === '') {
            Session::setFlash('flash', 'Informe o assunto e a mensagem.');
            $this->redirect('/aluno/protocolos/novo');
            return;
        }

        $id = $this->protocoloService->abrir($studentId, $idMatricula, $assunto, $mensagem);

        if ($id === -1) {
            Session::setFlash('flash', 'Não foi possível abrir o protocolo. Verifique a matrícula e os dados informados.');
            $this->redirect('/aluno/protocolos/novo');
            return;
        }

        if ($id <= 0) {
            Session::setFlash('flash', 'Erro ao abrir o protocolo. Tente novamente.');
            $this->redirect('/aluno/protocolos/novo');
            return;
        }

        $this->logService->log('criar', 'protocolo', $id, 'Protocolo aberto pelo aluno');
        Session::setFlash('flash', 'Protocolo aberto com sucesso.');
        $this->redirect('/aluno/protocolos/show?id=' . $id);
    }

    public function show(): void
    {
        if (!$this->auth->checkRole('aluno')) {
            Session::setFlash('flash', 'Faça login como aluno.');
            $this->redirect('/aluno/login');
            return;
        }

        $studentId = (int) (Session::get('user')['id'] ?? 0);
        $id = (int) ($_GET['id'] ?? 0);
        $protocolo = $id > 0 ? $this->protocoloService->buscarPorId($id) : null;

        if (!$protocolo || (int) ($protocolo['id_aluno'] ?? 0) !== $studentId) {
            Session::setFlash('flash', 'Protocolo não encontrado.');
            $this->redirect('/aluno/protocolos');
            return;
        }

        $this->render('pages/aluno/protocolos/show', [
            'title' => 'Protocolo #' . str_pad((string) $id, 6, '0', STR_PAD_LEFT),
            'currentRoute' => '/aluno/protocolos',
            'protocolo' => $protocolo,
            'mensagens' => $this->protocoloService->listarMensagens($id),
        ], 'aluno');
    }

    public function mensagem(): void
    {
        if (!$this->auth->checkRole('aluno')) {
            Session::setFlash('flash', 'Faça login como aluno.');
            $this->redirect('/aluno/login');
            return;
        }

        $studentId = (int) (Session::get('user')['id'] ?? 0);
        $id = (int) $this->input('id', 0);
        $mensagem = trim((string) $this->input('mensagem', ''));

        $protocolo = $id > 0 ? $this->protocoloService->buscarPorId($id) : null;
        if (!$protocolo || (int) ($protocolo['id_aluno'] ?? 0) !== $studentId) {
            Session::setFlash('flash', 'Protocolo não encontrado.');
            $this->redirect('/aluno/protocolos');
            return;
        }

        if ((string) ($protocolo['status'] ?? '') === 'ENCERRADO') {
            Session::setFlash('flash', 'Este protocolo está encerrado e não pode receber novas mensagens.');
            $this->redirect('/aluno/protocolos/show?id=' . $id);
            return;
        }

        if ($mensagem === '') {
            Session::setFlash('flash', 'Digite uma mensagem.');
            $this->redirect('/aluno/protocolos/show?id=' . $id);
            return;
        }

        if ($this->protocoloService->responderAluno($id, $mensagem)) {
            $this->logService->log('criar', 'protocolo_mensagem', $id, 'Mensagem do aluno no protocolo #' . $id);
            Session::setFlash('flash', 'Mensagem enviada.');
        } else {
            Session::setFlash('flash', 'Erro ao enviar a mensagem.');
        }

        $this->redirect('/aluno/protocolos/show?id=' . $id);
    }
}