<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Services\UsuarioService;
use App\Services\AdminService;
use App\Support\Session;

final class UsuarioController extends Controller
{
    private UsuarioService $usuarioService;
    private AdminService $adminService;

    public function __construct()
    {
        $this->usuarioService = new UsuarioService();
        $this->adminService = new AdminService();
    }

    public function index(): void
    {
        if (!$this->isStaff()) {
            Session::setFlash('flash', 'Faça login como admin, operador ou professor para acessar os usuários.');
            $this->redirect('/admin/login');
        }

        $this->render('pages/admin/usuarios/index', [
            'title' => 'Usuários',
            'currentRoute' => '/admin/usuarios',
            'usuarios' => $this->usuarioService->usuarios(),
        ], 'admin');
    }

    public function novo(): void
    {
        if (!$this->isStaff()) {
            Session::setFlash('flash', 'Acesso negado.');
            $this->redirect('/admin/login');
        }

        $this->render('pages/admin/usuarios/novo', [
            'title' => 'Novo Usuário',
            'currentRoute' => '/admin/usuarios/novo',
        ], 'admin');
    }

    public function salvar(): void
    {
        if (!$this->isStaff()) {
            Session::setFlash('flash', 'Acesso negado.');
            $this->redirect('/admin/login');
        }

        $authUser = $this->authUser();
        $currentRole = (string) ($authUser['role'] ?? $authUser['type'] ?? '');
        $isAdmin = $currentRole === 'admin';
        $isOperador = $currentRole === 'operador';

        $nome = (string) $this->input('nome', '');
        $email = (string) $this->input('email', '');
        $senha = (string) $this->input('senha', '');
        $tipo = (string) $this->input('tipo', 'aluno');
        $ativo = (string) $this->input('ativo', '1');

        if ($nome === '' || $email === '' || $senha === '') {
            Session::setFlash('flash', 'Preencha nome, email e senha.');
            $this->redirect('/admin/usuarios/novo');
            return;
        }

        $tiposValidos = ['admin', 'operador', 'professor'];
        if (!in_array($tipo, $tiposValidos, true)) {
            $tipo = $isOperador ? 'professor' : 'operador';
        }

        if ($isOperador) {
            $tipo = 'professor';
        }

        $usuarioId = $this->usuarioService->criarUsuario($nome, $email, $senha, $tipo, $ativo);
        $this->adminService->log('criar', 'usuario', $usuarioId, "Usuário criado: $nome");
        Session::setFlash('flash', 'Usuário criado com sucesso.');
        $this->redirect('/admin/usuarios');
    }

    public function editar(): void
    {
        if (!$this->isStaff()) {
            Session::setFlash('flash', 'Acesso negado.');
            $this->redirect('/admin/login');
        }

        $loggedUser = Session::get('user') ?? [];
        $loggedRole = (string) ($loggedUser['role'] ?? '');
        $loggedId = (int) ($loggedUser['id'] ?? 0);
        $isAdmin = $loggedRole === 'admin';

        $id = (int) ($this->input('id', 0) ?: ($_GET['id'] ?? 0));
        $usuario = $this->usuarioService->findUsuario($id);

        if (!$usuario) {
            Session::setFlash('flash', 'Usuário não encontrado.');
            $this->redirect('/admin/usuarios');
            return;
        }

        if (!$isAdmin && $id !== $loggedId) {
            Session::setFlash('flash', 'Você só pode editar seu próprio usuário.');
            $this->redirect('/admin/usuarios');
            return;
        }

        $this->render('pages/admin/usuarios/edit', [
            'title' => 'Editar Usuário',
            'currentRoute' => '/admin/usuarios/editar',
            'usuario' => $usuario,
            'isAdmin' => $isAdmin,
        ], 'admin');
    }

    public function atualizar(): void
    {
        if (!$this->isStaff()) {
            Session::setFlash('flash', 'Acesso negado.');
            $this->redirect('/admin/login');
        }

        $loggedUser = Session::get('user') ?? [];
        $loggedRole = (string) ($loggedUser['role'] ?? '');
        $loggedId = (int) ($loggedUser['id'] ?? 0);
        $isAdmin = $loggedRole === 'admin';

        $id = (int) $this->input('id', 0);
        $senha = (string) $this->input('senha', '');

        $usuario = $this->usuarioService->findUsuario($id);
        if (!$usuario) {
            Session::setFlash('flash', 'Usuário não encontrado.');
            $this->redirect('/admin/usuarios');
            return;
        }

        if (!$isAdmin && $id !== $loggedId) {
            Session::setFlash('flash', 'Você só pode editar seu próprio usuário.');
            $this->redirect('/admin/usuarios');
            return;
        }

        if ($isAdmin) {
            $ativo = (string) $this->input('ativo', '1');
            $tipo = (string) $this->input('tipo', '');

            if ($tipo === '') {
                $tipo = (string) ($usuario['tipo'] ?? '');
            }

            $tiposValidos = ['admin', 'operador', 'professor'];
            if (!in_array($tipo, $tiposValidos, true)) {
                $tipo = (string) ($usuario['tipo'] ?? 'operador');
            }
        } else {
            $ativo = (string) ($usuario['ativo'] ?? '1');
            $tipo = (string) ($usuario['tipo'] ?? 'operador');
        }

        $this->usuarioService->atualizarUsuario($id, $senha, $ativo, '', '', $tipo);
        $this->adminService->log('atualizar', 'usuario', $id, 'Usuário atualizado: ' . ($usuario['nome'] ?? ''));
        Session::setFlash('flash', 'Usuário atualizado com sucesso.');
        $this->redirect('/admin/usuarios');
    }

    private function isStaff(): bool
    {
        return (new \App\Services\AuthService())->isStaff();
    }

    private function authUser(): array
    {
        $user = Session::get('user');
        return is_array($user) ? $user : [];
    }
}
