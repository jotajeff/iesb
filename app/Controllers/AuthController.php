<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Services\AdminService;
use App\Services\AuthService;
use App\Support\Session;

final class AuthController extends Controller
{
    private AuthService $auth;
    private AdminService $admin;

    public function __construct()
    {
        $this->auth = new AuthService();
        $this->admin = new AdminService();
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

        if ($user !== null) {
            Session::set('user', [
                'id'   => (int) $user['id'],
                'name' => $user['name'],
                'email'=> $user['email'],
                'role' => $user['role'],
            ]);

            $this->admin->log('login', 'admin', 0, "Login realizado: $email");
            $this->redirect('/admin');
        }

        Session::setFlash('flash', 'Credenciais inválidas.');
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

        if (!$this->auth->login($email, $password, 'aluno')) {
            Session::setFlash('flash', 'Credenciais de aluno inválidas.');
            $this->redirect('/aluno/login');
        }

        $this->redirect('/aluno');
    }

    public function logout(): void
    {
        $this->auth->logout();
        Session::setFlash('flash', 'Sessão encerrada com sucesso.');
        $this->redirect('/home');
    }
}
