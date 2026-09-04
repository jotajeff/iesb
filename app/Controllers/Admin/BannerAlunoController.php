<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Database;
use App\Services\AuthService;
use App\Services\BannerAlunoService;
use App\Services\LogService;
use App\Support\Session;
use PDO;

final class BannerAlunoController extends Controller
{
    private BannerAlunoService $bannerAlunoService;
    private LogService $logService;

    public function __construct()
    {
        $this->bannerAlunoService = new BannerAlunoService();
        $this->logService = new LogService();
    }

    public function index(): void
    {
        if (!$this->isStaff()) {
            Session::setFlash('flash', 'Acesso negado.');
            $this->redirect('/admin/login');
        }

        $this->render('pages/admin/banner/index', [
            'title' => 'Banner Aluno',
            'currentRoute' => '/admin/config/banner-aluno',
            'banners' => $this->bannerAlunoService->list(),
        ], 'admin');
    }

    public function novo(): void
    {
        if (!$this->isStaff()) {
            Session::setFlash('flash', 'Acesso negado.');
            $this->redirect('/admin/login');
        }

        $this->render('pages/admin/banner/novo', [
            'title' => 'Novo Banner Aluno',
            'currentRoute' => '/admin/config/banner-aluno',
            'banner' => null,
            'cursos' => $this->cursos(),
        ], 'admin');
    }

    public function editar(): void
    {
        if (!$this->isStaff()) {
            Session::setFlash('flash', 'Acesso negado.');
            $this->redirect('/admin/login');
        }

        $id = (int) ($_GET['id'] ?? 0);
        $banner = $id > 0 ? $this->bannerAlunoService->find($id) : null;

        if (!$banner) {
            Session::setFlash('flash', 'Banner não encontrado.');
            $this->redirect('/admin/config/banner-aluno');
            return;
        }

        $this->render('pages/admin/banner/novo', [
            'title' => 'Editar Banner Aluno',
            'currentRoute' => '/admin/config/banner-aluno',
            'banner' => $banner,
            'cursos' => $this->cursos(),
        ], 'admin');
    }

    public function salvar(): void
    {
        if (!$this->isStaff()) {
            Session::setFlash('flash', 'Acesso negado.');
            $this->redirect('/admin/login');
        }

        $id = (int) $this->input('id', 0);
        $texto = trim((string) $this->input('texto', ''));
        $link = trim((string) $this->input('link', ''));
        $idCursoInput = (int) $this->input('id_curso', 0);
        $idCurso = $idCursoInput > 0 ? $idCursoInput : null;
        $ativo = (int) $this->input('ativo', 1);

        $banner = $this->uploadBanner();
        if ($banner === false) {
            Session::setFlash('flash', 'Erro no upload da imagem. Verifique o formato e o tamanho.');
            $this->redirect('/admin/config/banner-aluno/novo' . ($id > 0 ? '?id=' . $id : ''));
            return;
        }

        if ($banner === '' && $id > 0) {
            $existing = $this->bannerAlunoService->find($id);
            $banner = (string) ($existing['banner'] ?? '');
        }

        if ($banner === '' || $link === '') {
            Session::setFlash('flash', 'Informe a imagem e o link do banner.');
            $this->redirect('/admin/config/banner-aluno/novo' . ($id > 0 ? '?id=' . $id : ''));
            return;
        }

        $bannerId = $this->bannerAlunoService->save($id, $banner, $texto !== '' ? $texto : null, $link, $idCurso, $ativo);

        if ($bannerId <= 0) {
            Session::setFlash('flash', 'Erro ao salvar o banner.');
            $this->redirect('/admin/config/banner-aluno');
            return;
        }

        $this->logService->log($id > 0 ? 'atualizar' : 'criar', 'banner_aluno', $bannerId, ($id > 0 ? 'Banner aluno atualizado' : 'Banner aluno criado'));
        Session::setFlash('flash', 'Banner salvo com sucesso.');
        $this->redirect('/admin/config/banner-aluno');
    }

    private function uploadBanner(): string|false
    {
        $file = $_FILES['banner'] ?? null;
        if (!$file || $file['error'] === UPLOAD_ERR_NO_FILE) {
            return '';
        }

        if ($file['error'] !== UPLOAD_ERR_OK) {
            return false;
        }

        $ext = strtolower(pathinfo((string) $file['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        if (!in_array($ext, $allowed, true)) {
            return false;
        }

        $destDir = dirname(__DIR__, 3) . '/public/assets/img/banner';
        if (!is_dir($destDir)) {
            mkdir($destDir, 0755, true);
        }

        $filename = 'aluno_' . time() . '-' . mt_rand(100, 999) . '.' . $ext;
        $destPath = $destDir . '/' . $filename;

        if (move_uploaded_file($file['tmp_name'], $destPath)) {
            return 'assets/img/banner/' . $filename;
        }

        return false;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function cursos(): array
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return [];
        }

        try {
            $stmt = $pdo->query('SELECT id, nome FROM cursos ORDER BY nome ASC');
            $rows = $stmt->fetchAll();
            return is_array($rows) ? $rows : [];
        } catch (\Throwable $e) {
            error_log('[BANNER_ALUNO] Erro ao listar cursos: ' . $e->getMessage());
            return [];
        }
    }

    private function isStaff(): bool
    {
        return (new AuthService())->isStaff();
    }
}
