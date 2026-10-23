<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Services\AuthService;
use App\Services\FuncaoUsuarioService;
use App\Services\LogService;
use App\Support\Session;

final class FuncaoController extends Controller
{
    private FuncaoUsuarioService $funcaoUsuarioService;
    private LogService $logService;

    public function __construct()
    {
        $this->funcaoUsuarioService = new FuncaoUsuarioService();
        $this->logService = new LogService();
    }

    private function isStaff(): bool
    {
        return (new AuthService())->isStaff();
    }

    public function index(): void
    {
        if (!$this->isStaff()) {
            Session::setFlash('flash', 'Acesso negado.');
            $this->redirect('/admin/login');
        }

        $this->render('pages/admin/funcao/index', [
            'title' => 'Função',
            'currentRoute' => '/admin/funcao',
            'usuarios' => $this->funcaoUsuarioService->usuariosComFuncoes(),
        ], 'admin');
    }

    public function usuario(): void
    {
        if (!$this->isStaff()) {
            Session::setFlash('flash', 'Acesso negado.');
            $this->redirect('/admin/login');
        }

        $id = (int) ($_GET['id'] ?? 0);
        $usuario = $this->funcaoUsuarioService->usuario($id);

        if (!$usuario) {
            Session::setFlash('flash', 'Usuário não encontrado.');
            $this->redirect('/admin/funcao');
            return;
        }

        $this->render('pages/admin/funcao/usuario', [
            'title' => 'Função do Usuário',
            'currentRoute' => '/admin/funcao',
            'usuario' => $usuario,
            'funcoes' => $this->funcaoUsuarioService->funcoes(),
            'funcoesSelecionadas' => $this->funcaoUsuarioService->funcoesDoUsuario($id),
        ], 'admin');
    }

    public function salvar(): void
    {
        if (!$this->isStaff()) {
            Session::setFlash('flash', 'Acesso negado.');
            $this->redirect('/admin/login');
            return;
        }

        $id = (int) $this->input('id', 0);
        $usuario = $this->funcaoUsuarioService->usuario($id);

        if (!$usuario) {
            Session::setFlash('flash', 'Usuário não encontrado.');
            $this->redirect('/admin/funcao');
            return;
        }

        $funcoes = array_map('intval', (array) ($_POST['funcoes'] ?? []));

        if (!$this->funcaoUsuarioService->salvarFuncoesDoUsuario($id, $funcoes)) {
            Session::setFlash('flash', 'Não foi possível salvar as funções do usuário.');
            $this->redirect('/admin/funcao/usuario?id=' . $id);
            return;
        }

        $this->logService->log(
            'atualizar',
            'usuario_liga_funcao',
            $id,
            'Funções atualizadas para o usuário: ' . ($usuario['nome'] ?? '')
        );
        Session::setFlash('flash', 'Funções do usuário atualizadas com sucesso.');
        $this->redirect('/admin/funcao');
    }
}
