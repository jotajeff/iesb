<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Services\LogService;
use App\Services\AlunoService;
use App\Services\AuthService;
use App\Services\EmailService;
use App\Support\Session;

final class AuthController extends Controller
{
    private AuthService $auth;
    private LogService $logService;
    private AlunoService $alunoService;

    public function __construct()
    {
        $this->auth = new AuthService();
        $this->logService = new LogService();
        $this->alunoService = new AlunoService();
    }

    public function adminLoginForm(): void
    {
        $this->render('pages/admin/login', ['title' => 'Login Admin', 'currentRoute' => '/admin/login'], 'admin');
    }

    public function adminLogin(): void
    {
        $email = (string) $this->input('email', '');
        $password = (string) $this->input('password', '');

        $user = $this->auth->authenticate($email, $password);
        $role = (string) ($user['role'] ?? '');
        $staffRoles = ['admin', 'operador', 'professor'];

        if ($user !== null && in_array($role, $staffRoles, true)) {
            Session::set('user', [
                'id'   => (int) $user['id'],
                'name' => $user['name'],
                'email'=> $user['email'],
                'role' => $role,
                'type' => $role,
            ]);

            $this->logService->log('login', 'admin', 0, "Login realizado: $email");
            $this->redirect('/admin');
        }

        Session::setFlash('flash', 'Credenciais inválidas ou perfil sem acesso.');
        $this->redirect('/admin/login');
    }

    public function alunoLoginForm(): void
    {
        $this->render('pages/aluno/login', ['title' => 'Login Aluno', 'currentRoute' => '/aluno/login']);
    }

    public function alunoLogin(): void
    {
        $email = (string) $this->input('email', '');
        $password = (string) $this->input('password', '');

        if (!$this->auth->alunoLogin($email, $password)) {
            Session::setFlash('flash', 'Credenciais de aluno inválidas.');
            $this->redirect('/aluno/login');
        }

        $this->logService->log('login', 'aluno', 0, "Login aluno: $email");
        $this->redirect('/aluno');
    }

    public function mostrarSolicitarRedefinicao(): void
    {
        $this->render('pages/aluno/solicitar_redefinicao', [
            'title' => 'Redefinir Senha',
            'currentRoute' => '/aluno/login',
        ]);
    }

    public function solicitarRedefinicao(): void
    {
        $email = strtolower(trim((string) $this->input('email', '')));

        if ($email === '') {
            Session::setFlash('flash', 'Informe seu e-mail.');
            $this->redirect('/aluno/solicitar-redefinicao');
        }

        $aluno = $this->alunoService->findByEmail($email);

        if (!$aluno || strtoupper(trim((string) ($aluno['ativo'] ?? 'N'))) !== 'S') {
            Session::setFlash('flash', 'E-mail não encontrado ou conta inativa.');
            $this->redirect('/aluno/solicitar-redefinicao');
        }

        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

        $this->alunoService->salvarResetToken((int) $aluno['id'], $token, $expires);

        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = (string) ($_SERVER['HTTP_HOST'] ?? 'localhost');
        $link = "{$scheme}://{$host}/aluno/redefinir-senha?token={$token}";

        $emailService = new EmailService();
        $enviado = $emailService->enviarRedefinicaoSenha($email, (string) ($aluno['nome'] ?? ''), $link);

        if ($enviado) {
            Session::setFlash('flash', 'Enviamos um link de redefinição para seu e-mail.');
        } else {
            $erro = $emailService->getLastError();
            $configInfo = $emailService->getDebugInfo();
            $msg = 'Erro ao enviar o e-mail.';
            if ($configInfo !== '') {
                $msg .= ' Config: ' . $configInfo . '.';
            }
            if ($erro !== '') {
                $msg .= ' Erro: ' . $erro;
            }
            Session::setFlash('flash', $msg);
        }

        $this->redirect('/aluno/solicitar-redefinicao');
    }

    public function mostrarRedefinirSenha(): void
    {
        $token = (string) ($_GET['token'] ?? '');

        if ($token === '') {
            Session::setFlash('flash', 'Link inválido.');
            $this->redirect('/aluno/login');
        }

        $aluno = $this->alunoService->buscarPorResetToken($token);

        if (!$aluno) {
            Session::setFlash('flash', 'Link inválido ou expirado.');
            $this->redirect('/aluno/login');
        }

        $this->render('pages/aluno/redefinir_senha', [
            'title' => 'Nova Senha',
            'currentRoute' => '/aluno/login',
            'token' => $token,
        ]);
    }

    public function redefinirSenha(): void
    {
        $token = (string) $this->input('token', '');
        $senha = (string) $this->input('senha', '');
        $senhaConfirmar = (string) $this->input('senha_confirmar', '');

        if ($token === '' || $senha === '' || $senhaConfirmar === '') {
            Session::setFlash('flash', 'Preencha todos os campos.');
            $this->redirect('/aluno/redefinir-senha?token=' . urlencode($token));
        }

        if (strlen($senha) < 6) {
            Session::setFlash('flash', 'A senha deve ter no mínimo 6 caracteres.');
            $this->redirect('/aluno/redefinir-senha?token=' . urlencode($token));
        }

        if ($senha !== $senhaConfirmar) {
            Session::setFlash('flash', 'As senhas não conferem.');
            $this->redirect('/aluno/redefinir-senha?token=' . urlencode($token));
        }

        $aluno = $this->alunoService->buscarPorResetToken($token);

        if (!$aluno) {
            Session::setFlash('flash', 'Link inválido ou expirado.');
            $this->redirect('/aluno/login');
        }

        $this->alunoService->atualizarSenha((int) $aluno['id'], $senha);
        $this->alunoService->limparResetToken((int) $aluno['id']);

        $this->logService->log('redefinir_senha', 'aluno', (int) $aluno['id'], "Senha redefinida via email: {$aluno['email']}");

        Session::setFlash('flash', 'Senha redefinida com sucesso! Faça login com sua nova senha.');
        $this->redirect('/aluno/login');
    }

    public function logout(): void
    {
        $this->auth->logout();
        Session::setFlash('flash', 'Sessão encerrada com sucesso.');
        $this->redirect('/home');
    }
}
