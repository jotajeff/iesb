<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Services\AdminService;
use App\Services\AuthService;
use App\Support\Session;
use PDO;

final class AdminController extends Controller
{
    private AuthService $auth;
    private AdminService $admin;

    public function __construct()
    {
        $this->auth = new AuthService();
        $this->admin = new AdminService();
    }

    public function dashboard(): void
    {
        if (!$this->auth->checkRole('admin')) {
            Session::setFlash('flash', 'Faça login como admin para acessar o painel.');
            $this->redirect('/admin/login');
        }

        $this->render('pages/admin/dashboard/index', [
            'title' => 'Painel Admin',
            'currentRoute' => '/admin',
            'indicators' => $this->admin->indicators(),
        ], 'admin');
    }

    public function logs(): void
    {
        if (!$this->auth->checkRole('admin')) {
            Session::setFlash('flash', 'Faça login como admin para acessar os logs.');
            $this->redirect('/admin/login');
        }

        $this->render('pages/admin/logs/index', [
            'title' => 'Logs de Auditoria',
            'currentRoute' => '/admin/logs',
            'logs' => $this->admin->logs(),
        ], 'admin');
    }

    public function cursos(): void
    {
        if (!$this->auth->checkRole('admin')) {
            Session::setFlash('flash', 'Faça login como admin para acessar os cursos.');
            $this->redirect('/admin/login');
        }

        $order = strtolower(trim((string) ($_GET['order'] ?? 'desc')));
        if ($order !== 'asc') {
            $order = 'desc';
        }

        $this->admin->sincronizarSlugsCursos();

        $this->render('pages/admin/cursos/index', [
            'title' => 'Cursos IESB',
            'currentRoute' => '/admin/cursos',
            'courses' => $this->admin->cursos($order),
            'order' => $order,
        ], 'admin');
    }

    public function visitas(): void
    {
        if (!$this->auth->checkRole('admin')) {
            Session::setFlash('flash', 'Faça login como admin para acessar as visitas.');
            $this->redirect('/admin/login');
        }

        $this->render('pages/admin/visitas/index', [
            'title' => 'Visitas de Páginas',
            'currentRoute' => '/admin/visitas',
            'visits' => $this->admin->visits(),
        ], 'admin');
    }

    public function visitasMensal(): void
    {
        if (!$this->auth->checkRole('admin')) {
            Session::setFlash('flash', 'Faça login como admin para acessar as visitas.');
            $this->redirect('/admin/login');
        }

        $month = isset($_GET['month']) ? (int) $_GET['month'] : null;
        $year = isset($_GET['year']) ? (int) $_GET['year'] : null;

        $this->render('pages/admin/visitas/monthly', [
            'title' => 'Visitas por Mês',
            'currentRoute' => '/admin/visitas',
            'monthly' => $this->admin->visitsByMonthDaily($month, $year),
        ], 'admin');
    }

    public function visitasAnalytics(): void
    {
        if (!$this->auth->checkRole('admin')) {
            Session::setFlash('flash', 'Faça login como admin para acessar as visitas.');
            $this->redirect('/admin/login');
        }

        $month = isset($_GET['month']) ? (int) $_GET['month'] : null;
        $year = isset($_GET['year']) ? (int) $_GET['year'] : null;

        $this->render('pages/admin/visitas/analytics', [
            'title' => 'Analytics de Visitas',
            'currentRoute' => '/admin/visitas',
            'analytics' => $this->admin->visitsAnalytics($month, $year),
        ], 'admin');
    }

    public function visitasPaginas(): void
    {
        if (!$this->auth->checkRole('admin')) {
            Session::setFlash('flash', 'Faça login como admin para acessar as visitas.');
            $this->redirect('/admin/login');
        }

        $month = isset($_GET['month']) ? (int) $_GET['month'] : null;
        $year = isset($_GET['year']) ? (int) $_GET['year'] : null;

        $this->render('pages/admin/visitas/pages', [
            'title' => 'Visitas por Página',
            'currentRoute' => '/admin/visitas',
            'pagesStats' => $this->admin->visitsByPage($month, $year),
        ], 'admin');
    }

    public function createCourse(): void
    {
        if (!$this->auth->checkRole('admin')) {
            Session::setFlash('flash', 'Acesso negado.');
            $this->redirect('/admin/login');
        }

        $nome = (string) $this->input('nome', '');
        $dataCurso = (string) $this->input('data_curso', '');
        $horario = (string) $this->input('horario', '');
        $localCurso = (string) $this->input('local_curso', '');
        $linkIngresso = (string) $this->input('link_ingresso', '');
        $tipoCurso = (int) $this->input('tipo_curso', 3);
        $cursoCalendario = (string) $this->input('curso_calendario', '');
        $ativo = $this->normalizeAtivo((string) $this->input('ativo', 'S'));

        if ($nome === '' || $localCurso === '') {
            Session::setFlash('flash', 'Preencha ao menos nome e local do curso.');
            $this->redirect('/admin/cursos');
        }

        $cursoId = $this->admin->criarCurso($nome, $dataCurso, $horario, $localCurso, $linkIngresso, $tipoCurso, $cursoCalendario, $ativo);
        $this->admin->log('criar', 'curso', $cursoId, "Curso criado: $nome");
        Session::setFlash('flash', 'Curso criado com sucesso.');
        $this->redirect('/admin/cursos');
    }

    public function novoCursoForm(): void
    {
        if (!$this->auth->checkRole('admin')) {
            Session::setFlash('flash', 'Acesso negado.');
            $this->redirect('/admin/login');
        }

        $this->render('pages/admin/cursos/new', [
            'title' => 'Novo Curso',
            'currentRoute' => '/admin/cursos/novo',
            'cursosTipos' => $this->admin->cursosTipos(),
        ], 'admin');
    }

    public function editarCursoForm(): void
    {
        if (!$this->auth->checkRole('admin')) {
            Session::setFlash('flash', 'Acesso negado.');
            $this->redirect('/admin/login');
        }

        $id = (int) ($this->input('id', 0) ?: ($_GET['id'] ?? 0));
        $course = $this->admin->findCurso($id);

        if (!$course) {
            Session::setFlash('flash', 'Curso nao encontrado.');
            $this->redirect('/admin/cursos');
        }

        $this->render('pages/admin/cursos/edit', [
            'title' => 'Editar Curso',
            'currentRoute' => '/admin/cursos/editar',
            'course' => $course,
            'cursosTipos' => $this->admin->cursosTipos(),
        ], 'admin');
    }

    public function updateCourse(): void
    {
        if (!$this->auth->checkRole('admin')) {
            Session::setFlash('flash', 'Acesso negado.');
            $this->redirect('/admin/login');
        }

        $id = (int) $this->input('id', 0);
        $nome = (string) $this->input('nome', '');
        $dataCurso = (string) $this->input('data_curso', '');
        $horario = (string) $this->input('horario', '');
        $localCurso = (string) $this->input('local_curso', '');
        $linkIngresso = (string) $this->input('link_ingresso', '');
        $tipoCurso = (int) $this->input('tipo_curso', 3);
        $cursoCalendario = (string) $this->input('curso_calendario', '');
        $ativo = $this->normalizeAtivo((string) $this->input('ativo', 'S'));

        if ($nome === '' || $localCurso === '') {
            Session::setFlash('flash', 'Preencha ao menos nome e local do curso.');
            $this->redirect('/admin/cursos/editar?id=' . $id);
            return;
        }

        $existingCourse = $this->admin->findCurso($id);
        if (!$existingCourse) {
            Session::setFlash('flash', 'Curso nao encontrado.');
            $this->redirect('/admin/cursos');
            return;
        }

        $imagemCard = (string) ($existingCourse['imagem_card'] ?? '');
        $this->admin->atualizarCurso($id, $nome, $dataCurso, $horario, $localCurso, $linkIngresso, $tipoCurso, $cursoCalendario, $ativo, $imagemCard);
        $this->admin->log('atualizar', 'curso', $id, "Curso atualizado: $nome");
        Session::setFlash('flash', 'Curso atualizado com sucesso.');
        $this->redirect('/admin/cursos');
    }

    private function normalizeAtivo(string $value): string
    {
        $normalized = strtoupper(trim($value));
        return $normalized === 'N' ? 'N' : 'S';
    }

    public function showCurso(): void
    {
        if (!$this->auth->checkRole('admin')) {
            Session::setFlash('flash', 'Acesso negado.');
            $this->redirect('/admin/login');
        }

        $id = (int) ($_GET['id'] ?? 0);
        $course = $this->admin->findCurso($id);

        if (!$course) {
            Session::setFlash('flash', 'Curso nao encontrado.');
            $this->redirect('/admin/cursos');
        }

        $this->render('pages/admin/cursos/show', [
            'title' => $course['nome'] ?? 'Curso',
            'currentRoute' => '/admin/cursos/show',
            'course' => $course,
        ], 'admin');
    }

    public function uploadCursoForm(): void
    {
        if (!$this->auth->checkRole('admin')) {
            Session::setFlash('flash', 'Acesso negado.');
            $this->redirect('/admin/login');
        }

        $id = (int) ($_GET['id'] ?? 0);
        $course = $this->admin->findCurso($id);

        if (!$course) {
            Session::setFlash('flash', 'Curso nao encontrado.');
            $this->redirect('/admin/cursos');
        }

        $this->render('pages/admin/cursos/upload', [
            'title' => 'Upload Imagem - ' . ($course['nome'] ?? ''),
            'currentRoute' => '/admin/cursos/upload',
            'course' => $course,
        ], 'admin');
    }

    public function uploadCursoImage(): void
    {
        if (!$this->auth->checkRole('admin')) {
            Session::setFlash('flash', 'Acesso negado.');
            $this->redirect('/admin/login');
        }

        $id = (int) ($this->input('id', 0) ?: ($_GET['id'] ?? 0));
        $curso = $this->admin->findCurso($id);

        if (!$curso) {
            Session::setFlash('flash', 'Curso nao encontrado.');
            $this->redirect('/admin/cursos');
        }

        $file = $_FILES['imagem_card'] ?? null;

        if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
            Session::setFlash('flash', 'Erro ao enviar o arquivo.');
            $this->redirect('/admin/cursos/upload?id=' . $id);
        }

        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

        if (!in_array($ext, $allowed, true)) {
            Session::setFlash('flash', 'Formato nao permitido. Use jpg, png, gif ou webp.');
            $this->redirect('/admin/cursos/upload?id=' . $id);
        }

        $slug = trim((string) ($curso['slug'] ?? ''));
        if ($slug === '') {
            $slug = AdminService::slugify((string) ($curso['nome'] ?? 'curso'));
        }
        $filename = $slug . '-' . $id . '.' . $ext;

        $destDir = dirname(__DIR__, 2) . '/public/assets/img/cursos';
        if (!is_dir($destDir)) {
            mkdir($destDir, 0755, true);
        }

        $destPath = $destDir . '/' . $filename;

        if (!move_uploaded_file($file['tmp_name'], $destPath)) {
            Session::setFlash('flash', 'Falha ao salvar a imagem.');
            $this->redirect('/admin/cursos/upload?id=' . $id);
        }

        $this->admin->atualizarCursoImagem($id, 'assets/img/cursos/' . $filename);
        $this->admin->log('upload_imagem', 'curso', $id, "Imagem do card enviada: $filename");
        Session::setFlash('flash', 'Imagem do card atualizada com sucesso.');
        $this->redirect('/admin/cursos');
    }

    public function dbase(): void
    {
        if (!$this->auth->checkRole('admin')) {
            Session::setFlash('flash', 'Acesso negado.');
            $this->redirect('/admin/login');
        }

        $pdo = Database::connection();
        $dbName = getenv('DB_NAME') ?: '';
        $tables = [];
        $rows = [];
        $columns = [];
        $currentTable = '';
        $totalRows = 0;
        $error = '';
        $viewMode = 'structure';

        if (!$pdo instanceof PDO) {
            $error = 'Nao foi possivel conectar ao banco de dados.';
        } else {
            $table = trim((string) ($_GET['table'] ?? ''));

            if ($table !== '') {
                $currentTable = $table;

                $stmt = $pdo->prepare('SHOW COLUMNS FROM `' . $table . '`');
                $stmt->execute();
                $columns = $stmt->fetchAll();

                $stmt = $pdo->prepare('SELECT COUNT(*) FROM `' . $table . '`');
                $stmt->execute();
                $totalRows = (int) $stmt->fetchColumn();

                $viewMode = ($_GET['view'] ?? '') === 'records' ? 'records' : 'structure';

                if ($viewMode === 'records') {
                    $limit = 200;
                    $stmt = $pdo->prepare('SELECT * FROM `' . $table . '` LIMIT :limit');
                    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
                    $stmt->execute();
                    $rows = $stmt->fetchAll();
                }
            } else {
                $stmt = $pdo->prepare(
                    "SELECT t.table_name AS name,
                            t.table_rows AS row_count
                     FROM information_schema.tables t
                     WHERE t.table_schema = :schema
                     ORDER BY t.table_name ASC"
                );
                $stmt->bindValue(':schema', $dbName);
                $stmt->execute();
                $tables = $stmt->fetchAll();
            }
        }

        $this->render('pages/admin/dbase/index', [
            'title' => 'Explorador de Banco de Dados',
            'currentRoute' => '/admin/dbase',
            'dbName' => $dbName,
            'tables' => $tables,
            'rows' => $rows,
            'columns' => $columns,
            'currentTable' => $currentTable,
            'totalRows' => $totalRows,
            'viewMode' => $viewMode,
            'error' => $error,
        ], 'admin');
    }
}
