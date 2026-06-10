<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Services\ConfigService;
use App\Services\AdminService;
use App\Support\Session;

final class ConfigController extends Controller
{
    private ConfigService $configService;
    private AdminService $adminService;

    public function __construct()
    {
        $this->configService = new ConfigService();
        $this->adminService = new AdminService();
    }

    public function modalidade(): void
    {
        if (!$this->isStaff()) {
            Session::setFlash('flash', 'Acesso negado.');
            $this->redirect('/admin/login');
        }

        $this->render('pages/admin/modalidade/index', [
            'title' => 'Modalidades',
            'currentRoute' => '/admin/modalidade',
            'modalidades' => $this->configService->modalidades(),
        ], 'admin');
    }

    public function editModalidade(): void
    {
        if (!$this->isStaff()) {
            Session::setFlash('flash', 'Acesso negado.');
            $this->redirect('/admin/login');
        }

        $id = (int) ($this->input('id', 0) ?: ($_GET['id'] ?? 0));
        $modalidade = $id > 0 ? $this->configService->findModalidade($id) : null;

        if ($id > 0 && !$modalidade) {
            Session::setFlash('flash', 'Modalidade não encontrada.');
            $this->redirect('/admin/modalidade');
            return;
        }

        $this->render('pages/admin/modalidade/edit', [
            'title' => $id > 0 ? 'Editar Modalidade' : 'Nova Modalidade',
            'currentRoute' => '/admin/modalidade',
            'modalidade' => $modalidade,
        ], 'admin');
    }

    public function updateModalidade(): void
    {
        if (!$this->isStaff()) {
            Session::setFlash('flash', 'Acesso negado.');
            $this->redirect('/admin/login');
        }

        $id = (int) $this->input('id', 0);
        $nome = trim((string) $this->input('nome', ''));
        $ativo = (int) $this->input('ativo', 1);

        if ($nome === '') {
            Session::setFlash('flash', 'Informe o nome da modalidade.');
            $suffix = $id > 0 ? '?id=' . $id : '';
            $this->redirect('/admin/modalidade/edit' . $suffix);
            return;
        }

        $modalidadeId = $this->configService->saveModalidade($id, $nome, $ativo);
        $acao = $id > 0 ? 'atualizar' : 'criar';
        $descricao = ($id > 0 ? 'Modalidade atualizada: ' : 'Modalidade criada: ') . $nome;
        $this->adminService->log($acao, 'modalidade', $modalidadeId, $descricao);

        Session::setFlash('flash', $id > 0 ? 'Modalidade atualizada com sucesso.' : 'Modalidade criada com sucesso.');
        $this->redirect('/admin/modalidade');
    }

    public function segmento(): void
    {
        if (!$this->isStaff()) {
            Session::setFlash('flash', 'Acesso negado.');
            $this->redirect('/admin/login');
        }

        $this->render('pages/admin/segmento/index', [
            'title' => 'Segmentos',
            'currentRoute' => '/admin/segmento',
            'segmentos' => $this->configService->segmentos(),
        ], 'admin');
    }

    public function editSegmento(): void
    {
        if (!$this->isStaff()) {
            Session::setFlash('flash', 'Acesso negado.');
            $this->redirect('/admin/login');
        }

        $id = (int) ($this->input('id', 0) ?: ($_GET['id'] ?? 0));
        $segmento = $id > 0 ? $this->configService->findSegmento($id) : null;

        if ($id > 0 && !$segmento) {
            Session::setFlash('flash', 'Segmento não encontrado.');
            $this->redirect('/admin/segmento');
            return;
        }

        $this->render('pages/admin/segmento/edit', [
            'title' => $id > 0 ? 'Editar Segmento' : 'Novo Segmento',
            'currentRoute' => '/admin/segmento',
            'segmento' => $segmento,
        ], 'admin');
    }

    public function updateSegmento(): void
    {
        if (!$this->isStaff()) {
            Session::setFlash('flash', 'Acesso negado.');
            $this->redirect('/admin/login');
        }

        $id = (int) $this->input('id', 0);
        $nome = trim((string) $this->input('nome', ''));
        $ativo = strtoupper(trim((string) $this->input('ativo', 'S')));

        if ($nome === '') {
            Session::setFlash('flash', 'Informe o nome do segmento.');
            $suffix = $id > 0 ? '?id=' . $id : '';
            $this->redirect('/admin/segmento/edit' . $suffix);
            return;
        }

        $segmentoId = $this->configService->saveSegmento($id, $nome, $ativo);
        $acao = $id > 0 ? 'atualizar' : 'criar';
        $descricao = ($id > 0 ? 'Segmento atualizado: ' : 'Segmento criado: ') . $nome;
        $this->adminService->log($acao, 'segmento', $segmentoId, $descricao);

        Session::setFlash('flash', $id > 0 ? 'Segmento atualizado com sucesso.' : 'Segmento criado com sucesso.');
        $this->redirect('/admin/segmento');
    }

    public function nivel(): void
    {
        if (!$this->isStaff()) {
            Session::setFlash('flash', 'Acesso negado.');
            $this->redirect('/admin/login');
        }

        $this->render('pages/admin/nivel/index', [
            'title' => 'Níveis',
            'currentRoute' => '/admin/nivel',
            'niveis' => $this->configService->niveis(),
        ], 'admin');
    }

    public function editNivel(): void
    {
        if (!$this->isStaff()) {
            Session::setFlash('flash', 'Acesso negado.');
            $this->redirect('/admin/login');
        }

        $id = (int) ($this->input('id', 0) ?: ($_GET['id'] ?? 0));
        $nivel = $id > 0 ? $this->configService->findNivel($id) : null;

        if ($id > 0 && !$nivel) {
            Session::setFlash('flash', 'Nível não encontrado.');
            $this->redirect('/admin/nivel');
            return;
        }

        $this->render('pages/admin/nivel/edit', [
            'title' => $id > 0 ? 'Editar Nível' : 'Novo Nível',
            'currentRoute' => '/admin/nivel',
            'nivel' => $nivel,
        ], 'admin');
    }

    public function updateNivel(): void
    {
        if (!$this->isStaff()) {
            Session::setFlash('flash', 'Acesso negado.');
            $this->redirect('/admin/login');
        }

        $id = (int) $this->input('id', 0);
        $nome = trim((string) $this->input('nome', ''));
        $ativo = (int) $this->input('ativo', 1);
        $apresentacao = (string) $this->input('apresentacao', '');

        if ($nome === '') {
            Session::setFlash('flash', 'Informe o nome do nível.');
            $suffix = $id > 0 ? '?id=' . $id : '';
            $this->redirect('/admin/nivel/edit' . $suffix);
            return;
        }

        $nivelId = $this->configService->saveNivel($id, $nome, $ativo, $apresentacao);
        $acao = $id > 0 ? 'atualizar' : 'criar';
        $descricao = ($id > 0 ? 'Nível atualizado: ' : 'Nível criado: ') . $nome;
        $this->adminService->log($acao, 'nivel', $nivelId, $descricao);

        Session::setFlash('flash', $id > 0 ? 'Nível atualizado com sucesso.' : 'Nível criado com sucesso.');
        $this->redirect('/admin/nivel');
    }

    private function isStaff(): bool
    {
        return (new \App\Services\AuthService())->isStaff();
    }
}
