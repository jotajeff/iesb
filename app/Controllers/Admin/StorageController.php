<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Services\Storage\StorageService;
use App\Services\Storage\StorageException;
use App\Support\Session;

final class StorageController extends Controller
{
    private StorageService $storageService;

    public function __construct()
    {
        $this->storageService = new StorageService();
    }

    public function index(): void
    {
        if (!$this->isStaff()) {
            Session::setFlash('flash', 'Faça login como admin, operador ou professor para acessar o Storage.');
            $this->redirect('/admin/login');
        }

        $this->render('pages/admin/storage/index', [
            'title' => 'Storage (Google Drive)',
            'currentRoute' => '/admin/storage',
            'conectado' => $this->storageService->isConnected(),
            'connectionInfo' => $this->storageService->connectionInfo(),
            'connectUrl' => $this->storageService->connectUrl(),
        ], 'admin');
    }

    public function callback(): void
    {
        $code = (string) ($_GET['code'] ?? '');
        if ($code === '') {
            Session::setFlash('flash', 'Falha na autenticação: código OAuth ausente.');
            $this->redirect('/admin/storage');
        }

        try {
            $result = $this->storageService->callback($code);
            Session::setFlash('flash', 'Google Drive conectado como ' . ($result['email'] !== '' ? $result['email'] : 'conta Google'));
        } catch (StorageException $e) {
            Session::setFlash('flash', 'Erro na conexão: ' . $e->getMessage());
        } catch (\Throwable $e) {
            Session::setFlash('flash', 'Erro na conexão: ' . $e->getMessage());
        }

        $this->redirect('/admin/storage');
    }

    public function disconnect(): void
    {
        if (!$this->isStaff()) {
            Session::setFlash('flash', 'Acesso negado.');
            $this->redirect('/admin/login');
        }

        $this->storageService->disconnect();
        Session::setFlash('flash', 'Integração com Google Drive desconectada.');
        $this->redirect('/admin/storage');
    }

    public function estrutura(): void
    {
        if (!$this->isStaff()) {
            Session::setFlash('flash', 'Acesso negado.');
            $this->redirect('/admin/login');
        }

        if (!$this->storageService->isConnected()) {
            Session::setFlash('flash', 'Conecte o Google Drive antes de criar a estrutura.');
            $this->redirect('/admin/storage');
        }

        try {
            $estrutura = $this->storageService->ensureStructure();
            Session::setFlash('flash', 'Estrutura de pastas criada/verificada no Google Drive.');
        } catch (StorageException $e) {
            Session::setFlash('flash', 'Erro ao criar estrutura: ' . $e->getMessage());
        } catch (\Throwable $e) {
            Session::setFlash('flash', 'Erro ao criar estrutura: ' . $e->getMessage());
        }

        $this->redirect('/admin/storage');
    }

    private function isStaff(): bool
    {
        return (new \App\Services\AuthService())->isStaff();
    }
}
