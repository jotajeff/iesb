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
        if (!$this->auth->isStaff()) {
            Session::setFlash('flash', 'Faça login como admin ou operador para acessar o painel.');
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
        if (!$this->auth->isStaff()) {
            Session::setFlash('flash', 'Faça login como admin ou operador para acessar os logs.');
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
        if (!$this->auth->isStaff()) {
            Session::setFlash('flash', 'Faça login como admin ou operador para acessar os cursos.');
            $this->redirect('/admin/login');
        }

        $order = strtolower(trim((string) ($_GET['order'] ?? 'desc')));
        if ($order !== 'asc') {
            $order = 'desc';
        }

        try {
            $this->admin->sincronizarSlugsCursos();
        } catch (\Throwable $e) {
            error_log('[CURSOS] Erro em sincronizarSlugsCursos: ' . $e->getMessage());
        }

        $this->render('pages/admin/cursos/index', [
            'title' => 'Cursos IESB',
            'currentRoute' => '/admin/cursos',
            'courses' => $this->admin->cursos($order),
            'order' => $order,
        ], 'admin');
    }

    public function novoCursoForm(): void
    {
        if (!$this->auth->isStaff()) {
            Session::setFlash('flash', 'Acesso negado.');
            $this->redirect('/admin/login');
        }

        $this->render('pages/admin/cursos/new', [
            'title' => 'Novo Curso',
            'currentRoute' => '/admin/cursos/novo',
            'cursosTipos' => $this->admin->cursosTipos(),
            'modalidades' => $this->admin->modalidades(),
            'niveis' => $this->admin->niveis(),
        ], 'admin');
    }

    public function createCourse(): void
    {
        if (!$this->auth->isStaff()) {
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
        $confirmado = $this->normalizeConfirmado((string) $this->input('confirmado', 'N'));
        $modalidadeId = (int) $this->input('modalidade_id', 0);
        $nivelId = (int) $this->input('nivel_id', 0);

        if ($nome === '' || $localCurso === '') {
            Session::setFlash('flash', 'Preencha ao menos nome e local do curso.');
            $this->redirect('/admin/cursos/novo');
            return;
        }

        $cursoId = $this->admin->criarCurso($nome, $dataCurso, $horario, $localCurso, $linkIngresso, $tipoCurso, $cursoCalendario, $ativo, $confirmado, '', $modalidadeId, $nivelId);
        $this->admin->log('criar', 'curso', $cursoId, "Curso criado: $nome");
        Session::setFlash('flash', 'Curso criado com sucesso.');
        $this->redirect('/admin/cursos');
    }

    public function editarCursoForm(): void
    {
        if (!$this->auth->isStaff()) {
            Session::setFlash('flash', 'Acesso negado.');
            $this->redirect('/admin/login');
        }

        $id = (int) ($this->input('id', 0) ?: ($_GET['id'] ?? 0));
        $course = $this->admin->findCurso($id);

        if (!$course) {
            Session::setFlash('flash', 'Curso nao encontrado.');
            $this->redirect('/admin/cursos');
            return;
        }

        $this->render('pages/admin/cursos/edit', [
            'title' => 'Editar Curso',
            'currentRoute' => '/admin/cursos/editar',
            'course' => $course,
            'cursosTipos' => $this->admin->cursosTipos(),
            'modalidades' => $this->admin->modalidades(),
            'niveis' => $this->admin->niveis(),
        ], 'admin');
    }

    public function updateCourse(): void
    {
        if (!$this->auth->isStaff()) {
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
        $confirmado = $this->normalizeConfirmado((string) $this->input('confirmado', 'N'));
        $modalidadeId = (int) $this->input('modalidade_id', 0);
        $nivelId = (int) $this->input('nivel_id', 0);

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
        $this->admin->atualizarCurso($id, $nome, $dataCurso, $horario, $localCurso, $linkIngresso, $tipoCurso, $cursoCalendario, $ativo, $confirmado, $imagemCard, $modalidadeId, $nivelId);
        $this->admin->log('atualizar', 'curso', $id, "Curso atualizado: $nome");
        Session::setFlash('flash', 'Curso atualizado com sucesso.');
        $this->redirect('/admin/cursos');
    }

    public function showCurso(): void
    {
        if (!$this->auth->isStaff()) {
            Session::setFlash('flash', 'Acesso negado.');
            $this->redirect('/admin/login');
        }

        $id = (int) ($_GET['id'] ?? 0);
        $course = $this->admin->findCurso($id);

        if (!$course) {
            Session::setFlash('flash', 'Curso nao encontrado.');
            $this->redirect('/admin/cursos');
            return;
        }

        $this->render('pages/admin/cursos/show', [
            'title' => $course['nome'] ?? 'Curso',
            'currentRoute' => '/admin/cursos/show',
            'course' => $course,
        ], 'admin');
    }

    public function uploadCursoForm(): void
    {
        if (!$this->auth->isStaff()) {
            Session::setFlash('flash', 'Acesso negado.');
            $this->redirect('/admin/login');
        }

        $id = (int) ($_GET['id'] ?? 0);
        $course = $this->admin->findCurso($id);

        if (!$course) {
            Session::setFlash('flash', 'Curso nao encontrado.');
            $this->redirect('/admin/cursos');
            return;
        }

        $this->render('pages/admin/cursos/upload', [
            'title' => 'Upload Imagem - ' . ($course['nome'] ?? ''),
            'currentRoute' => '/admin/cursos/upload',
            'course' => $course,
        ], 'admin');
    }

    public function uploadCursoImage(): void
    {
        if (!$this->auth->isStaff()) {
            Session::setFlash('flash', 'Acesso negado.');
            $this->redirect('/admin/login');
        }

        $id = (int) ($this->input('id', 0) ?: ($_GET['id'] ?? 0));
        $curso = $this->admin->findCurso($id);

        if (!$curso) {
            Session::setFlash('flash', 'Curso nao encontrado.');
            $this->redirect('/admin/cursos');
            return;
        }

        $file = $_FILES['imagem_card'] ?? null;

        if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
            Session::setFlash('flash', 'Erro ao enviar o arquivo.');
            $this->redirect('/admin/cursos/upload?id=' . $id);
            return;
        }

        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

        if (!in_array($ext, $allowed, true)) {
            Session::setFlash('flash', 'Formato nao permitido. Use jpg, png, gif ou webp.');
            $this->redirect('/admin/cursos/upload?id=' . $id);
            return;
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
            return;
        }

        $this->admin->atualizarCursoImagem($id, 'assets/img/cursos/' . $filename);
        $this->admin->log('upload_imagem', 'curso', $id, "Imagem do card enviada: $filename");
        Session::setFlash('flash', 'Imagem do card atualizada com sucesso.');
        $this->redirect('/admin/cursos');
    }

    // ==================== USUÁRIOS (APENAS ADMIN) ====================

    public function usuarios(): void
    {
        if (!$this->auth->isStaff()) {
            Session::setFlash('flash', 'Faça login como admin ou operador para acessar os usuários.');
            $this->redirect('/admin/login');
        }

        $this->render('pages/admin/usuarios/index', [
            'title' => 'Usuários',
            'currentRoute' => '/admin/usuarios',
            'usuarios' => $this->admin->usuarios(),
        ], 'admin');
    }

    public function novoUsuarioForm(): void
    {
        if (!$this->auth->isStaff()) {
            Session::setFlash('flash', 'Acesso negado.');
            $this->redirect('/admin/login');
        }

        $this->render('pages/admin/usuarios/novo', [
            'title' => 'Novo Usuário',
            'currentRoute' => '/admin/usuarios/novo',
        ], 'admin');
    }

    public function createUsuario(): void
    {
        if (!$this->auth->isStaff()) {
            Session::setFlash('flash', 'Acesso negado.');
            $this->redirect('/admin/login');
        }

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

        $tiposValidos = ['admin', 'operador'];
        if (!in_array($tipo, $tiposValidos, true)) {
            $tipo = 'aluno';
        }

        $usuarioId = $this->admin->criarUsuario($nome, $email, $senha, $tipo, $ativo);
        $this->admin->log('criar', 'usuario', $usuarioId, "Usuário criado: $nome");
        Session::setFlash('flash', 'Usuário criado com sucesso.');
        $this->redirect('/admin/usuarios');
    }

    public function editarUsuarioForm(): void
    {
        if (!$this->auth->isStaff()) {
            Session::setFlash('flash', 'Acesso negado.');
            $this->redirect('/admin/login');
        }

        $loggedUser = Session::get('user') ?? [];
        $loggedRole = (string) ($loggedUser['role'] ?? '');
        $loggedId = (int) ($loggedUser['id'] ?? 0);
        $isAdmin = $loggedRole === 'admin';

        $id = (int) ($this->input('id', 0) ?: ($_GET['id'] ?? 0));
        $usuario = $this->admin->findUsuario($id);

        if (!$usuario) {
            Session::setFlash('flash', 'Usuário não encontrado.');
            $this->redirect('/admin/usuarios');
            return;
        }

        // Operador só pode editar a si mesmo
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

    public function updateUsuario(): void
    {
        if (!$this->auth->isStaff()) {
            Session::setFlash('flash', 'Acesso negado.');
            $this->redirect('/admin/login');
        }

        $loggedUser = Session::get('user') ?? [];
        $loggedRole = (string) ($loggedUser['role'] ?? '');
        $loggedId = (int) ($loggedUser['id'] ?? 0);
        $isAdmin = $loggedRole === 'admin';

        $id = (int) $this->input('id', 0);
        $senha = (string) $this->input('senha', '');

        $usuario = $this->admin->findUsuario($id);
        if (!$usuario) {
            Session::setFlash('flash', 'Usuário não encontrado.');
            $this->redirect('/admin/usuarios');
            return;
        }

        // Operador só pode editar a si mesmo
        if (!$isAdmin && $id !== $loggedId) {
            Session::setFlash('flash', 'Você só pode editar seu próprio usuário.');
            $this->redirect('/admin/usuarios');
            return;
        }

        // Se for operador, ignora alterações de tipo e ativo (só permite senha)
        if ($isAdmin) {
            $ativo = (string) $this->input('ativo', '1');
            $tipo = (string) $this->input('tipo', '');

            if ($tipo === '') {
                $tipo = (string) ($usuario['tipo'] ?? '');
            }

            $tiposValidos = ['admin', 'operador'];
            if (!in_array($tipo, $tiposValidos, true)) {
                $tipo = (string) ($usuario['tipo'] ?? 'operador');
            }
        } else {
            // Operador: mantém os valores originais
            $ativo = (string) ($usuario['ativo'] ?? '1');
            $tipo = (string) ($usuario['tipo'] ?? 'operador');
        }

        $this->admin->atualizarUsuario($id, $senha, $ativo, '', '', $tipo);
        $this->admin->log('atualizar', 'usuario', $id, 'Usuário atualizado: ' . ($usuario['nome'] ?? ''));
        Session::setFlash('flash', 'Usuário atualizado com sucesso.');
        $this->redirect('/admin/usuarios');
    }

    // ==================== VISITAS (STAFF) ====================

    public function visitas(): void
    {
        if (!$this->auth->isStaff()) {
            Session::setFlash('flash', 'Faça login como admin ou operador para acessar as visitas.');
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
        if (!$this->auth->isStaff()) {
            Session::setFlash('flash', 'Faça login como admin ou operador para acessar as visitas.');
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
        if (!$this->auth->isStaff()) {
            Session::setFlash('flash', 'Faça login como admin ou operador para acessar as visitas.');
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
        if (!$this->auth->isStaff()) {
            Session::setFlash('flash', 'Faça login como admin ou operador para acessar as visitas.');
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

    public function dbase(): void
    {
        if (!$this->auth->isStaff()) {
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

    private function normalizeAtivo(string $value): string
    {
        $normalized = strtoupper(trim($value));
        return $normalized === 'N' ? 'N' : 'S';
    }

    private function normalizeConfirmado(string $value): string
    {
        $normalized = strtoupper(trim($value));
        return $normalized === 'S' ? 'S' : 'N';
    }
}
