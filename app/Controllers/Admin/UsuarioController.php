<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Services\ImageService;
use App\Services\UsuarioService;
use App\Services\LogService;
use App\Support\Session;

final class UsuarioController extends Controller
{
    private UsuarioService $usuarioService;
    private LogService $logService;
    private ImageService $imageService;

    public function __construct()
    {
        $this->usuarioService = new UsuarioService();
        $this->logService = new LogService();
        $this->imageService = new ImageService();
    }

    public function index(): void
    {
        if (!$this->isStaff()) {
            Session::setFlash('flash', 'Faça login como admin, operador ou professor para acessar os usuários.');
            $this->redirect('/admin/login');
        }

        $usuarios = $this->usuarioService->usuarios();

        $fotos = [];
        foreach ($usuarios as $usuario) {
            $id = (int) ($usuario['id'] ?? 0);
            try {
                $imagens = $this->imageService->listarPorFk('usuarios', $id);
                $fotos[$id] = !empty($imagens) ? $imagens[0]['path'] : null;
            } catch (\Throwable) {
                $fotos[$id] = null;
            }
        }

        $this->render('pages/admin/usuarios/index', [
            'title' => 'Usuários',
            'currentRoute' => '/admin/usuarios',
            'usuarios' => $usuarios,
            'fotos' => $fotos,
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
        $this->logService->log('criar', 'usuario', $usuarioId, "Usuário criado: $nome");
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
        $this->logService->log('atualizar', 'usuario', $id, 'Usuário atualizado: ' . ($usuario['nome'] ?? ''));
        Session::setFlash('flash', 'Usuário atualizado com sucesso.');
        $this->redirect('/admin/usuarios');
    }

    public function fotos(): void
    {
        if (!$this->isStaff()) {
            Session::setFlash('flash', 'Acesso negado.');
            $this->redirect('/admin/login');
        }

        $id = (int) ($this->input('id', 0) ?: ($_GET['id'] ?? 0));
        $usuario = $this->usuarioService->findUsuario($id);

        if (!$usuario) {
            Session::setFlash('flash', 'Usuário não encontrado.');
            $this->redirect('/admin/usuarios');
            return;
        }

        $imagens = $this->imageService->listarPorFk('usuarios', $id);

        $this->render('pages/admin/usuarios/fotos', [
            'title' => 'Fotos — ' . ($usuario['nome'] ?? ''),
            'currentRoute' => '/admin/usuarios/fotos',
            'usuario' => $usuario,
            'idFk' => $id,
            'tabelaFk' => 'usuarios',
            'imagens' => $imagens,
        ], 'admin');
    }

    public function uploadFoto(): void
    {
        if (!$this->isStaff()) {
            Session::setFlash('flash', 'Acesso negado.');
            $this->redirect('/admin/login');
        }

        $idFk = (int) $this->input('id_fk', 0);
        $tabelaFk = trim((string) $this->input('tabela_fk', ''));
        $legenda = trim((string) $this->input('legenda', ''));

        if ($idFk <= 0 || $tabelaFk === '') {
            Session::setFlash('flash', 'Parâmetros inválidos.');
            $this->redirect('/admin/usuarios');
        }

        $path = '';
        $file = $_FILES['imagem'] ?? null;

        if ($file && $file['error'] === UPLOAD_ERR_OK) {
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            if (in_array($ext, $allowed, true)) {
                $filename = 'usuario-' . $idFk . '-' . time() . '-' . mt_rand(100, 999) . '.' . $ext;
                $destDir = dirname(__DIR__, 3) . '/public/assets/img/usuarios';
                if (!is_dir($destDir)) {
                    mkdir($destDir, 0755, true);
                }
                $destPath = $destDir . '/' . $filename;
                if (move_uploaded_file($file['tmp_name'], $destPath)) {
                    $path = 'assets/img/usuarios/' . $filename;
                }
            }
        }

        if ($path === '') {
            Session::setFlash('flash', 'Erro ao fazer upload da foto. Verifique o formato e tamanho.');
            $this->redirect('/admin/usuarios/fotos?id=' . $idFk);
            return;
        }

        $this->imageService->salvar($tabelaFk, $idFk, $path, $legenda ?: null);
        $this->logService->log('criar', 'imagem', 0, 'Foto adicionada ao usuário ID ' . $idFk);

        Session::setFlash('flash', 'Foto salva com sucesso.');
        $this->redirect('/admin/usuarios/fotos?id=' . $idFk);
    }

    public function deletarFoto(): void
    {
        if (!$this->isStaff()) {
            http_response_code(403);
            echo json_encode(['erro' => 'Acesso negado.']);
            return;
        }

        $id = (int) ($this->input('id', 0) ?: ($_POST['id'] ?? 0));
        $idFk = (int) ($this->input('id_fk', 0) ?: ($_POST['id_fk'] ?? 0));
        $tabelaFk = trim((string) ($this->input('tabela_fk', '') ?: ($_POST['tabela_fk'] ?? '')));

        if ($id <= 0 || $idFk <= 0 || $tabelaFk === '') {
            http_response_code(400);
            echo json_encode(['erro' => 'Parâmetros inválidos.']);
            return;
        }

        $this->imageService->deletar($id);
        $this->logService->log('deletar', 'imagem', $id, 'Foto removida do usuário ID ' . $idFk);
        echo json_encode(['sucesso' => true]);
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
