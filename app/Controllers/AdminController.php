<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Services\CommentService;
use App\Services\LogService;
use App\Services\DashboardService;
use App\Services\VisitaService;
use App\Services\CursoService;
use App\Services\TurmaService;
use App\Services\AlunoService;
use App\Services\UsuarioService;
use App\Services\TarefaService;
use App\Services\ConfigService;
use App\Services\AuthService;
use App\Support\Session;
use PDO;

final class AdminController extends Controller
{
    private AuthService $auth;
    private LogService $logService;
    private DashboardService $dashboardService;
    private VisitaService $visitaService;
    private CursoService $cursoService;
    private TurmaService $turmaService;
    private AlunoService $alunoService;
    private UsuarioService $usuarioService;
    private TarefaService $tarefaService;
    private ConfigService $configService;
    private CommentService $comments;

    public function __construct()
    {
        $this->auth = new AuthService();
        $this->logService = new LogService();
        $this->dashboardService = new DashboardService();
        $this->visitaService = new VisitaService();
        $this->cursoService = new CursoService();
        $this->turmaService = new TurmaService();
        $this->alunoService = new AlunoService();
        $this->usuarioService = new UsuarioService();
        $this->tarefaService = new TarefaService();
        $this->configService = new ConfigService();
        $this->comments = new CommentService();
    }

    public function dashboard(): void
    {
        if (!$this->auth->isStaff()) {
            Session::setFlash('flash', 'Faça login como admin, operador ou professor para acessar o painel.');
            $this->redirect('/admin/login');
        }

        $authUser = $this->authUser();
        $isAdmin = (string) ($authUser['role'] ?? $authUser['type'] ?? '') === 'admin';
        $userId = (int) ($authUser['id'] ?? 0);

        $this->render('pages/admin/dashboard/index', [
            'title' => 'Painel Admin',
            'currentRoute' => '/admin',
            'indicators' => $this->dashboardService->indicators(),
            'taskIndicators' => $this->dashboardService->taskIndicators($userId, $isAdmin),
            'isAdmin' => $isAdmin,
        ], 'admin');
    }

    public function logs(): void
    {
        if (!$this->auth->isStaff()) {
            Session::setFlash('flash', 'Faça login como admin, operador ou professor para acessar os logs.');
            $this->redirect('/admin/login');
        }

        $page = max(1, (int) ($_GET['page'] ?? 1));
        $result = $this->logService->logs($page, 50);

        $this->render('pages/admin/logs/index', [
            'title' => 'Logs de Auditoria',
            'currentRoute' => '/admin/logs',
            'logs' => $result['data'],
            'pagination' => $result['pagination'],
        ], 'admin');
    }

    public function cursos(): void
    {
        if (!$this->auth->isStaff()) {
            Session::setFlash('flash', 'Faça login como admin, operador ou professor para acessar os cursos.');
            $this->redirect('/admin/login');
        }

        $order = strtolower(trim((string) ($_GET['order'] ?? 'desc')));
        if ($order !== 'asc') {
            $order = 'desc';
        }
        $nivelSelecionado = (int) ($_GET['nivel'] ?? 0);
        if ($nivelSelecionado < 0) {
            $nivelSelecionado = 0;
        }

        try {
            $this->cursoService->sincronizarSlugs();
        } catch (\Throwable $e) {
            error_log('[CURSOS] Erro em sincronizarSlugsCursos: ' . $e->getMessage());
        }

        $this->render('pages/admin/cursos/index', [
            'title' => 'Cursos IESB',
            'currentRoute' => '/admin/cursos',
            'courses' => $this->cursoService->cursos($order, 200, $nivelSelecionado),
            'order' => $order,
            'niveis' => $this->configService->niveis(),
            'nivelSelecionado' => $nivelSelecionado,
            'idsComDetalhe' => $this->cursoService->idsCursosComDetalhe(),
            'idsComTurma' => $this->cursoService->idsCursosComTurma(),
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
            'modalidades' => $this->configService->modalidades(),
            'segmentos' => $this->configService->segmentos(),
            'niveis' => $this->configService->niveis(),
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
        $cursoCalendario = (string) $this->input('curso_calendario', '');
        $ativo = $this->normalizeAtivo((string) $this->input('ativo', 'S'));
        $exibirHome = $this->normalizeExibirHome((string) $this->input('exibir_home', 'N'));
        $confirmado = $this->normalizeConfirmado((string) $this->input('confirmado', 'N'));
        $modalidadeId = (int) $this->input('modalidade_id', 0);
        $segmentoId = (int) $this->input('segmento_id', 0);
        $nivelId = (int) $this->input('nivel_id', 0);

        if ($nome === '' || $localCurso === '') {
            Session::setFlash('flash', 'Preencha ao menos nome e local do curso.');
            $this->redirect('/admin/cursos/novo');
            return;
        }

        $cursoId = $this->cursoService->criarCurso($nome, $dataCurso, $horario, $localCurso, $linkIngresso, $cursoCalendario, $ativo, $exibirHome, $confirmado, '', $modalidadeId, $segmentoId, $nivelId);
        $this->logService->log('criar', 'curso', $cursoId, "Curso criado: $nome");
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
        $course = $this->cursoService->findCurso($id);

        if (!$course) {
            Session::setFlash('flash', 'Curso nao encontrado.');
            $this->redirect('/admin/cursos');
            return;
        }

        $this->render('pages/admin/cursos/edit', [
            'title' => 'Editar Curso',
            'currentRoute' => '/admin/cursos/editar',
            'course' => $course,
            'modalidades' => $this->configService->modalidades(),
            'segmentos' => $this->configService->segmentos(),
            'niveis' => $this->configService->niveis(),
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
        $cursoCalendario = (string) $this->input('curso_calendario', '');
        $ativo = $this->normalizeAtivo((string) $this->input('ativo', '1'));
        $exibirHome = $this->normalizeExibirHome((string) $this->input('exibir_home', '0'));
        $confirmado = $this->normalizeConfirmado((string) $this->input('confirmado', '0'));
        $modalidadeId = (int) $this->input('modalidade_id', 0);
        $segmentoId = (int) $this->input('segmento_id', 0);
        $nivelId = (int) $this->input('nivel_id', 0);

        if ($nome === '' || $localCurso === '') {
            Session::setFlash('flash', 'Preencha ao menos nome e local do curso.');
            $this->redirect('/admin/cursos/editar?id=' . $id);
            return;
        }

        $existingCourse = $this->cursoService->findCurso($id);
        if (!$existingCourse) {
            Session::setFlash('flash', 'Curso nao encontrado.');
            $this->redirect('/admin/cursos');
            return;
        }

        $imagemCard = (string) ($existingCourse['imagem_card'] ?? '');
        $this->cursoService->atualizarCurso($id, $nome, $dataCurso, $horario, $localCurso, $linkIngresso, $cursoCalendario, $ativo, $exibirHome, $confirmado, $imagemCard, $modalidadeId, $segmentoId, $nivelId);
        $this->logService->log('atualizar', 'curso', $id, "Curso atualizado: $nome");
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
        $course = $this->cursoService->findCurso($id);

        if (!$course) {
            Session::setFlash('flash', 'Curso nao encontrado.');
            $this->redirect('/admin/cursos');
            return;
        }

        $this->render('pages/admin/cursos/show', [
            'title' => $course['nome'] ?? 'Curso',
            'currentRoute' => '/admin/cursos/show',
            'course' => $course,
            'detalhe' => $this->cursoService->findDetalheByCurso($id),
        ], 'admin');
    }

    public function cursoDetalheForm(): void
    {
        if (!$this->auth->isStaff()) {
            Session::setFlash('flash', 'Acesso negado.');
            $this->redirect('/admin/login');
        }

        $id = (int) ($_GET['id'] ?? 0);
        $course = $this->cursoService->findCurso($id);

        if (!$course) {
            Session::setFlash('flash', 'Curso nao encontrado.');
            $this->redirect('/admin/cursos');
            return;
        }

        $detalhe = $this->cursoService->findDetalheByCurso($id);

        $this->render('pages/admin/cursos/detalhes', [
            'title' => 'Detalhes do Curso - ' . ($course['nome'] ?? ''),
            'currentRoute' => '/admin/cursos/detalhes',
            'course' => $course,
            'detalhe' => $detalhe,
        ], 'admin');
    }

    public function saveCursoDetalhe(): void
    {
        if (!$this->auth->isStaff()) {
            Session::setFlash('flash', 'Acesso negado.');
            $this->redirect('/admin/login');
        }

        $cursoId = (int) $this->input('curso_id', 0);
        $detalheId = (int) $this->input('detalhe_id', 0);
        $detalheTexto = (string) $this->input('detalhe', '');

        if ($cursoId <= 0) {
            Session::setFlash('flash', 'Curso inválido.');
            $this->redirect('/admin/cursos');
            return;
        }

        $payload = [
            'id_curso' => $cursoId,
            'detalhe' => $detalheTexto,
            'ativo' => 1,
        ];
    {
        if (!$this->auth->isStaff()) {
            Session::setFlash('flash', 'Acesso negado.');
            $this->redirect('/admin/login');
        }

        $id = (int) ($_GET['id'] ?? 0);
        $course = $this->cursoService->findCurso($id);

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
        $curso = $this->cursoService->findCurso($id);

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
            $slug = CursoService::slugify((string) ($curso['nome'] ?? 'curso'));
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

        $this->cursoService->atualizarImagem($id, 'assets/img/cursos/' . $filename);
        $this->logService->log('upload_imagem', 'curso', $id, "Imagem do card enviada: $filename");
        Session::setFlash('flash', 'Imagem do card atualizada com sucesso.');
        $this->redirect('/admin/cursos');
    }

    // ==================== USUÁRIOS (APENAS ADMIN) ====================

    public function usuarios(): void
    {
        if (!$this->auth->isStaff()) {
            Session::setFlash('flash', 'Faça login como admin, operador ou professor para acessar os usuários.');
            $this->redirect('/admin/login');
        }

        $this->render('pages/admin/usuarios/index', [
            'title' => 'Usuários',
            'currentRoute' => '/admin/usuarios',
            'usuarios' => $this->usuarioService->usuarios(),
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
        $usuario = $this->usuarioService->findUsuario($id);

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

        $usuario = $this->usuarioService->findUsuario($id);
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

            $tiposValidos = ['admin', 'operador', 'professor'];
            if (!in_array($tipo, $tiposValidos, true)) {
                $tipo = (string) ($usuario['tipo'] ?? 'operador');
            }
        } else {
            // Operador: mantém os valores originais
            $ativo = (string) ($usuario['ativo'] ?? '1');
            $tipo = (string) ($usuario['tipo'] ?? 'operador');
        }

        $this->usuarioService->atualizarUsuario($id, $senha, $ativo, '', '', $tipo);
        $this->logService->log('atualizar', 'usuario', $id, 'Usuário atualizado: ' . ($usuario['nome'] ?? ''));
        Session::setFlash('flash', 'Usuário atualizado com sucesso.');
        $this->redirect('/admin/usuarios');
    }

    public function modalidade(): void
    {
        if (!$this->auth->isStaff()) {
            Session::setFlash('flash', 'Acesso negado.');
            $this->redirect('/admin/login');
        }

        $this->render('pages/admin/modalidade/index', [
            'title' => 'Modalidades',
            'currentRoute' => '/admin/modalidade',
            'modalidades' => $this->configService->modalidades(),
        ], 'admin');
    }

    public function editModalidadeForm(): void
    {
        if (!$this->auth->isStaff()) {
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
        if (!$this->auth->isStaff()) {
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
        $this->logService->log($acao, 'modalidade', $modalidadeId, $descricao);

        Session::setFlash('flash', $id > 0 ? 'Modalidade atualizada com sucesso.' : 'Modalidade criada com sucesso.');
        $this->redirect('/admin/modalidade');
    }

    public function segmento(): void
    {
        if (!$this->auth->isStaff()) {
            Session::setFlash('flash', 'Acesso negado.');
            $this->redirect('/admin/login');
        }

        $this->render('pages/admin/segmento/index', [
            'title' => 'Segmentos',
            'currentRoute' => '/admin/segmento',
            'segmentos' => $this->configService->segmentos(),
        ], 'admin');
    }

    public function editSegmentoForm(): void
    {
        if (!$this->auth->isStaff()) {
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
        if (!$this->auth->isStaff()) {
            Session::setFlash('flash', 'Acesso negado.');
            $this->redirect('/admin/login');
        }

        $id = (int) $this->input('id', 0);
        $nome = trim((string) $this->input('nome', ''));
        $ativo = (int) $this->input('ativo', 1);

        if ($nome === '') {
            Session::setFlash('flash', 'Informe o nome do segmento.');
            $suffix = $id > 0 ? '?id=' . $id : '';
            $this->redirect('/admin/segmento/edit' . $suffix);
            return;
        }

        $segmentoId = $this->configService->saveSegmento($id, $nome, $ativo);
        $acao = $id > 0 ? 'atualizar' : 'criar';
        $descricao = ($id > 0 ? 'Segmento atualizado: ' : 'Segmento criado: ') . $nome;
        $this->logService->log($acao, 'segmento', $segmentoId, $descricao);

        Session::setFlash('flash', $id > 0 ? 'Segmento atualizado com sucesso.' : 'Segmento criado com sucesso.');
        $this->redirect('/admin/segmento');
    }

    public function setor(): void
    {
        if (!$this->auth->isStaff()) {
            Session::setFlash('flash', 'Acesso negado.');
            $this->redirect('/admin/login');
        }

        $this->render('pages/admin/setor/index', [
            'title' => 'Setores',
            'currentRoute' => '/admin/setor',
            'setores' => $this->tarefaService->setores(),
        ], 'admin');
    }

    public function editSetorForm(): void
    {
        if (!$this->auth->isStaff()) {
            Session::setFlash('flash', 'Acesso negado.');
            $this->redirect('/admin/login');
        }

        $id = (int) ($this->input('id', 0) ?: ($_GET['id'] ?? 0));
        $setor = $id > 0 ? $this->tarefaService->findSetor($id) : null;

        if ($id > 0 && !$setor) {
            Session::setFlash('flash', 'Setor não encontrado.');
            $this->redirect('/admin/setor');
            return;
        }

        $this->render('pages/admin/setor/edit', [
            'title' => $id > 0 ? 'Editar Setor' : 'Novo Setor',
            'currentRoute' => '/admin/setor',
            'setor' => $setor,
        ], 'admin');
    }

    public function updateSetor(): void
    {
        if (!$this->auth->isStaff()) {
            Session::setFlash('flash', 'Acesso negado.');
            $this->redirect('/admin/login');
        }

        $id = (int) $this->input('id', 0);
        $setorNome = trim((string) $this->input('setor', ''));

        if ($setorNome === '') {
            Session::setFlash('flash', 'Informe o nome do setor.');
            $suffix = $id > 0 ? '?id=' . $id : '';
            $this->redirect('/admin/setor/edit' . $suffix);
            return;
        }

        $setorId = $this->tarefaService->saveSetor($id, $setorNome);
        if ($setorId <= 0) {
            Session::setFlash('flash', 'Nao foi possivel salvar o setor. Verifique a tabela setores e tente novamente.');
            $suffix = $id > 0 ? '?id=' . $id : '';
            $this->redirect('/admin/setor/edit' . $suffix);
            return;
        }

        $acao = $id > 0 ? 'atualizar' : 'criar';
        $descricao = ($id > 0 ? 'Setor atualizado: ' : 'Setor criado: ') . $setorNome;
        $this->logService->log($acao, 'setor', $setorId, $descricao);

        Session::setFlash('flash', $id > 0 ? 'Setor atualizado com sucesso.' : 'Setor criado com sucesso.');
        $this->redirect('/admin/setor');
    }

    public function nivel(): void
    {
        if (!$this->auth->isStaff()) {
            Session::setFlash('flash', 'Acesso negado.');
            $this->redirect('/admin/login');
        }

        $this->render('pages/admin/nivel/index', [
            'title' => 'Níveis',
            'currentRoute' => '/admin/nivel',
            'niveis' => $this->configService->niveis(),
        ], 'admin');
    }

    public function editNivelForm(): void
    {
        if (!$this->auth->isStaff()) {
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
        if (!$this->auth->isStaff()) {
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
        $this->logService->log($acao, 'nivel', $nivelId, $descricao);

        Session::setFlash('flash', $id > 0 ? 'Nível atualizado com sucesso.' : 'Nível criado com sucesso.');
        $this->redirect('/admin/nivel');
    }

    // ==================== TAREFAS (STAFF) ====================

    public function tarefas(): void
    {
        if (!$this->auth->isStaff()) {
            Session::setFlash('flash', 'Faça login como admin, operador ou professor para acessar as tarefas.');
            $this->redirect('/admin/login');
        }

        [$tarefas, $authUser, $isAdmin] = $this->prepareTarefasData();
        $colunas = [
            'tarefa' => [],
            'execucao' => [],
            'finalizado' => [],
        ];

        foreach ($tarefas as $tarefa) {
            $coluna = $this->taskColumnForStatus((string) ($tarefa['situacao'] ?? 'criada'));
            $tarefa['coluna'] = $coluna;
            $colunas[$coluna][] = $tarefa;
        }

        $this->render('pages/admin/tarefas/index', [
            'title' => 'Tarefas',
            'currentRoute' => '/admin/tarefas',
            'colunas' => $colunas,
            'setores' => $this->tarefaService->setores(),
            'usuarios' => $this->usuarioService->usuarios(1000),
            'isAdmin' => $isAdmin,
            'authUser' => $authUser,
        ], 'admin');
    }

    public function turmas(): void
    {
        if (!$this->auth->isStaff()) {
            Session::setFlash('flash', 'Faça login como admin, operador ou professor para acessar as turmas.');
            $this->redirect('/admin/login');
        }

        $turmas = $this->turmaService->turmas();

        $this->render('pages/admin/turmas/index', [
            'title' => 'Turmas',
            'currentRoute' => '/admin/turmas',
            'turmas' => $turmas,
        ], 'admin');
    }

    public function showTurma(): void
    {
        if (!$this->auth->isStaff()) {
            Session::setFlash('flash', 'Faça login como admin, operador ou professor para acessar as turmas.');
            $this->redirect('/admin/login');
        }

        $id = (int) ($this->input('id', 0) ?: ($_GET['id'] ?? 0));
        $turma = $this->turmaService->findTurma($id);

        if (!$turma) {
            Session::setFlash('flash', 'Turma não encontrada.');
            $this->redirect('/admin/turmas');
            return;
        }

        $this->render('pages/admin/turmas/show', [
            'title' => 'Turma: ' . ($turma['nome'] ?? ''),
            'currentRoute' => '/admin/turmas/show',
            'turma' => $turma,
            'inscritos' => $this->turmaService->inscritosPorTurma($id),
        ], 'admin');
    }

    public function novoTurmaForm(): void
    {
        if (!$this->auth->isStaff()) {
            Session::setFlash('flash', 'Acesso negado.');
            $this->redirect('/admin/login');
        }

        $this->render('pages/admin/turmas/new', [
            'title' => 'Nova Turma',
            'currentRoute' => '/admin/turmas/novo',
            'cursos' => $this->cursoService->cursos('asc', 500),
        ], 'admin');
    }

    public function editarTurmaForm(): void
    {
        if (!$this->auth->isStaff()) {
            Session::setFlash('flash', 'Acesso negado.');
            $this->redirect('/admin/login');
        }

        $id = (int) ($this->input('id', 0) ?: ($_GET['id'] ?? 0));
        if ($id <= 0) {
            Session::setFlash('flash', 'Turma nao encontrada.');
            $this->redirect('/admin/turmas');
            return;
        }

        $turma = $this->turmaService->findTurma($id);
        if (!$turma) {
            Session::setFlash('flash', 'Turma nao encontrada.');
            $this->redirect('/admin/turmas');
            return;
        }

        $this->render('pages/admin/turmas/edit', [
            'title' => 'Editar Turma',
            'currentRoute' => '/admin/turmas/editar',
            'turma' => $turma,
            'cursos' => $this->cursoService->cursos('asc', 500),
        ], 'admin');
    }

    public function createTurma(): void
    {
        if (!$this->auth->isStaff()) {
            Session::setFlash('flash', 'Acesso negado.');
            $this->redirect('/admin/login');
        }

        $nome = trim((string) $this->input('nome', ''));
        $curso = (int) $this->input('curso', 0);
        $dataInicio = (string) $this->input('data_inicio', '');
        $ativo = intval($this->input('ativo', 0));

        if ($nome === '' || $curso <= 0) {
            Session::setFlash('flash', 'Informe o nome da turma e selecione o curso.');
            $this->redirect('/admin/turmas/novo');
            return;
        }

        $ativo = intval($ativo) ? 1 : 0;

        $turmaId = $this->turmaService->criarTurma($nome, $curso, $dataInicio, $ativo);

        if ($turmaId > 0) {
            $this->logService->log('criar', 'turma', $turmaId, "Turma criada: $nome");
            Session::setFlash('flash', 'Turma criada com sucesso.');
            $this->redirect('/admin/turmas');
        } else {
            Session::setFlash('flash', 'Erro ao criar turma. Tente novamente.');
            $this->redirect('/admin/turmas/novo');
        }
    }

    public function updateTurma(): void
    {
        if (!$this->auth->isStaff()) {
            Session::setFlash('flash', 'Acesso negado.');
            $this->redirect('/admin/login');
        }

        $id = (int) $this->input('id', 0);
        $nome = trim((string) $this->input('nome', ''));
        $curso = (int) $this->input('curso', 0);
        $dataInicio = (string) $this->input('data_inicio', '');
        $ativo = intval($this->input('ativo', 0));

        if ($id <= 0 || $nome === '' || $curso <= 0) {
            Session::setFlash('flash', 'Dados inválidos para atualização.');
            $this->redirect('/admin/turmas/editar?id=' . $id);
            return;
        }

        $ativo = intval($ativo) ? 1 : 0;

        $this->turmaService->atualizarTurma($id, $nome, $curso, $dataInicio, $ativo);

        $this->logService->log('atualizar', 'turma', $id, "Turma atualizada: $nome");
        Session::setFlash('flash', 'Turma atualizada com sucesso.');
        $this->redirect('/admin/turmas');
    }

    public function alunos(): void
    {
        if (!$this->auth->isStaff()) {
            Session::setFlash('flash', 'Faça login como admin, operador ou professor para acessar os alunos.');
            $this->redirect('/admin/login');
        }

        $this->render('pages/admin/alunos/index', [
            'title' => 'Alunos',
            'currentRoute' => '/admin/alunos',
            'alunos' => $this->alunoService->alunos(),
        ], 'admin');
    }

    public function showAluno(): void
    {
        if (!$this->auth->isStaff()) {
            Session::setFlash('flash', 'Faça login como admin, operador ou professor para acessar os alunos.');
            $this->redirect('/admin/login');
        }

        $id = (int) ($this->input('id', 0) ?: ($_GET['id'] ?? 0));
        $aluno = $this->alunoService->findAluno($id);

        if (!$aluno) {
            Session::setFlash('flash', 'Aluno não encontrado.');
            $this->redirect('/admin/alunos');
            return;
        }

        $this->render('pages/admin/alunos/show', [
            'title' => 'Aluno: ' . ($aluno['nome'] ?? ''),
            'currentRoute' => '/admin/alunos/show',
            'aluno' => $aluno,
            'cursos' => $this->alunoService->cursosDoAluno($id),
        ], 'admin');
    }

    public function novoAlunoForm(): void
    {
        if (!$this->auth->isStaff()) {
            Session::setFlash('flash', 'Acesso negado.');
            $this->redirect('/admin/login');
        }

        $this->render('pages/admin/alunos/new', [
            'title' => 'Novo Aluno',
            'currentRoute' => '/admin/alunos/novo',
        ], 'admin');
    }

    public function editarAlunoForm(): void
    {
        if (!$this->auth->isStaff()) {
            Session::setFlash('flash', 'Acesso negado.');
            $this->redirect('/admin/login');
        }

        $id = (int) ($this->input('id', 0) ?: ($_GET['id'] ?? 0));
        if ($id <= 0) {
            Session::setFlash('flash', 'Aluno não encontrado.');
            $this->redirect('/admin/alunos');
            return;
        }

        $aluno = $this->alunoService->findAluno($id);
        if (!$aluno) {
            Session::setFlash('flash', 'Aluno não encontrado.');
            $this->redirect('/admin/alunos');
            return;
        }

        $this->render('pages/admin/alunos/edit', [
            'title' => 'Editar Aluno',
            'currentRoute' => '/admin/alunos/editar',
            'aluno' => $aluno,
        ], 'admin');
    }

    public function createAluno(): void
    {
        if (!$this->auth->isStaff()) {
            Session::setFlash('flash', 'Acesso negado.');
            $this->redirect('/admin/login');
        }

        $nome = trim((string) $this->input('nome', ''));
        $cpf = trim((string) $this->input('cpf', ''));
        $dataNascimento = (string) $this->input('data_nascimento', '');
        $telefone = trim((string) $this->input('telefone', ''));
        $email = trim((string) $this->input('email', ''));
        $ativo = strtoupper(trim((string) $this->input('ativo', '0')));

        if ($alunoId > 0) {
            $this->logService->log('criar', 'aluno', $alunoId, "Aluno criado: $nome");
            Session::setFlash('flash', 'Aluno criado com sucesso.');
            $this->redirect('/admin/alunos');
        } else {
            Session::setFlash('flash', 'Erro ao criar aluno. Tente novamente.');
            $this->redirect('/admin/alunos/novo');
        }
    }

    public function updateAluno(): void
    {
        if (!$this->auth->isStaff()) {
            Session::setFlash('flash', 'Acesso negado.');
            $this->redirect('/admin/login');
        }

        $id = (int) $this->input('id', 0);
        $nome = trim((string) $this->input('nome', ''));
        $cpf = trim((string) $this->input('cpf', ''));
        $dataNascimento = (string) $this->input('data_nascimento', '');
        $telefone = trim((string) $this->input('telefone', ''));
        $email = trim((string) $this->input('email', ''));
        $ativo = strtoupper(trim((string) $this->input('ativo', '0')));

        if ($id <= 0 || $nome === '') {
            Session::setFlash('flash', 'Dados inválidos para atualização.');
            $this->redirect('/admin/alunos/editar?id=' . $id);
            return;
        }

        $this->alunoService->atualizarAluno($id, $nome, $cpf, $dataNascimento, $telefone, $email, $ativo);

        $this->logService->log('atualizar', 'aluno', $id, "Aluno atualizado: $nome");
        Session::setFlash('flash', 'Aluno atualizado com sucesso.');
        $this->redirect('/admin/alunos');
    }

    public function matriculaForm(): void
    {
        if (!$this->auth->isStaff()) {
            Session::setFlash('flash', 'Faça login como admin, operador ou professor para acessar as matrículas.');
            $this->redirect('/admin/login');
        }

        $id = (int) ($this->input('id', 0) ?: ($_GET['id'] ?? 0));
        $aluno = $this->alunoService->findAluno($id);
        if (!$aluno) {
            Session::setFlash('flash', 'Aluno não encontrado.');
            $this->redirect('/admin/alunos');
            return;
        }

        $matriculas = $this->alunoService->matriculasDoAluno($id);
        $turmasMatriculadas = array_map(
            static fn (array $m) => (int) ($m['id_turma'] ?? 0),
            $matriculas
        );

        $this->render('pages/admin/alunos/matricula', [
            'title' => 'Matricular Aluno',
            'currentRoute' => '/admin/alunos/matricula',
            'aluno' => $aluno,
            'turmas' => $this->turmaService->turmas(500),
            'matriculas' => $matriculas,
            'turmasMatriculadas' => $turmasMatriculadas,
        ], 'admin');
    }

    public function createMatricula(): void
    {
        if (!$this->auth->isStaff()) {
            Session::setFlash('flash', 'Acesso negado.');
            $this->redirect('/admin/login');
        }

        $idAluno = (int) $this->input('id_aluno', 0);
        $idTurma = (int) $this->input('id_turma', 0);
        $status = (string) $this->input('status', 'matriculado');

        if ($idAluno <= 0 || $idTurma <= 0) {
            Session::setFlash('flash', 'Selecione o aluno e a turma.');
            $this->redirect('/admin/alunos');
            return;
        }

        $aluno = $this->alunoService->findAluno($idAluno);
        if (!$aluno) {
            Session::setFlash('flash', 'Aluno não encontrado.');
            $this->redirect('/admin/alunos');
            return;
        }

        if ($this->alunoService->matriculaJaExiste($idAluno, $idTurma)) {
            Session::setFlash('flash', 'Aluno já está matriculado nesta turma.');
            $this->redirect('/admin/alunos/matricula?id=' . $idAluno);
            return;
        }

        $matriculaId = $this->alunoService->criarMatricula($idAluno, $idTurma, $status);

        if ($matriculaId > 0) {
            $nomeAluno = (string) ($aluno['nome'] ?? '');
            $this->logService->log('criar', 'matricula', $matriculaId, "Matrícula criada: $nomeAluno");
            Session::setFlash('flash', 'Matrícula realizada com sucesso.');
        } else {
            Session::setFlash('flash', 'Erro ao realizar matrícula. Tente novamente.');
        }

        $this->redirect('/admin/alunos/matricula?id=' . $idAluno);
    }

    public function listaTarefas(): void
    {
        if (!$this->auth->isStaff()) {
            Session::setFlash('flash', 'Faça login como admin, operador ou professor para acessar as tarefas.');
            $this->redirect('/admin/login');
        }

        [$tarefas, $authUser, $isAdmin] = $this->prepareTarefasData();
        $situacaoFiltro = strtolower(trim((string) ($_GET['situacao'] ?? '')));
        $situacoesValidas = array_keys($this->taskSituations());
        if ($situacaoFiltro !== '' && !in_array($situacaoFiltro, $situacoesValidas, true)) {
            $situacaoFiltro = '';
        }

        if ($situacaoFiltro !== '') {
            $tarefas = array_values(array_filter(
                $tarefas,
                static fn (array $tarefa): bool => strtolower((string) ($tarefa['situacao'] ?? '')) === $situacaoFiltro
            ));
        }

        $this->render('pages/admin/tarefas/lista', [
            'title' => 'Lista de Tarefas',
            'currentRoute' => '/admin/tarefas',
            'tarefas' => $tarefas,
            'isAdmin' => $isAdmin,
            'authUser' => $authUser,
            'situacoes' => $this->taskSituations(),
            'filtroSituacao' => $situacaoFiltro,
        ], 'admin');
    }

    public function novaTarefaForm(): void
    {
        if (!$this->auth->isStaff()) {
            Session::setFlash('flash', 'Acesso negado.');
            $this->redirect('/admin/login');
        }

        $this->render('pages/admin/tarefas/novo', [
            'title' => 'Nova Tarefa',
            'currentRoute' => '/admin/tarefas',
            'setores' => $this->tarefaService->setores(),
            'usuarios' => $this->usuarioService->usuarios(1000),
            'situacoes' => $this->taskSituations(),
            'prioridades' => $this->taskPriorities(),
        ], 'admin');
    }

    public function createTarefa(): void
    {
        if (!$this->auth->isStaff()) {
            Session::setFlash('flash', 'Acesso negado.');
            $this->redirect('/admin/login');
        }

        $setorId = (int) $this->input('setor', 0);
        $tarefa = trim((string) $this->input('tarefa', ''));
        $responsavel = (int) $this->input('responsavel', 0);
        $situacao = $this->normalizeTaskSituation((string) $this->input('situacao', 'criada'));
        $prioridade = $this->normalizeTaskPriority((int) $this->input('prioridade', 1));
        $criadoPor = (int) (($this->authUser()['id'] ?? 0));

        if ($setorId <= 0 || $tarefa === '' || $criadoPor <= 0) {
            Session::setFlash('flash', 'Preencha setor e descrição da tarefa.');
            $this->redirect('/admin/tarefas/novo');
            return;
        }

        $tarefaId = $this->tarefaService->criarTarefa($setorId, $tarefa, $criadoPor, $responsavel > 0 ? $responsavel : null, $situacao, $prioridade);
        $this->logService->log('criar', 'tarefa', $tarefaId, 'Tarefa criada: ' . $tarefa);
        Session::setFlash('flash', 'Tarefa criada com sucesso.');
        $this->redirect('/admin/tarefas');
    }

    public function editarTarefaForm(): void
    {
        if (!$this->auth->isStaff()) {
            Session::setFlash('flash', 'Acesso negado.');
            $this->redirect('/admin/login');
        }

        $id = (int) ($this->input('id', 0) ?: ($_GET['id'] ?? 0));
        $tarefa = $this->tarefaService->findTarefa($id);

        if (!$tarefa) {
            Session::setFlash('flash', 'Tarefa não encontrada.');
            $this->redirect('/admin/tarefas');
            return;
        }

        $this->render('pages/admin/tarefas/editar', [
            'title' => 'Editar Tarefa',
            'currentRoute' => '/admin/tarefas',
            'tarefa' => $tarefa,
            'setores' => $this->tarefaService->setores(),
            'usuarios' => $this->usuarioService->usuarios(1000),
            'situacoes' => $this->taskSituations(),
            'prioridades' => $this->taskPriorities(),
        ], 'admin');
    }

    public function updateTarefa(): void
    {
        if (!$this->auth->isStaff()) {
            Session::setFlash('flash', 'Acesso negado.');
            $this->redirect('/admin/login');
        }

        $id = (int) $this->input('id', 0);
        $setorId = (int) $this->input('setor', 0);
        $tarefa = trim((string) $this->input('tarefa', ''));
        $responsavel = (int) $this->input('responsavel', 0);
        $situacao = $this->normalizeTaskSituation((string) $this->input('situacao', 'criada'));
        $prioridade = $this->normalizeTaskPriority((int) $this->input('prioridade', 1));

        if ($id <= 0 || $setorId <= 0 || $tarefa === '') {
            Session::setFlash('flash', 'Preencha setor e descrição da tarefa.');
            $this->redirect('/admin/tarefas/editar?id=' . $id);
            return;
        }

        $existing = $this->tarefaService->findTarefa($id);
        if (!$existing) {
            Session::setFlash('flash', 'Tarefa não encontrada.');
            $this->redirect('/admin/tarefas');
            return;
        }

        $this->tarefaService->atualizarTarefa($id, $setorId, $tarefa, $responsavel > 0 ? $responsavel : null, $situacao, $prioridade);
        $this->logService->log('atualizar', 'tarefa', $id, 'Tarefa atualizada: ' . $tarefa);
        Session::setFlash('flash', 'Tarefa atualizada com sucesso.');
        $this->redirect('/admin/tarefas');
    }

    public function showTarefa(): void
    {
        if (!$this->auth->isStaff()) {
            Session::setFlash('flash', 'Faça login como admin, operador ou professor para acessar as tarefas.');
            $this->redirect('/admin/login');
        }

        $id = (int) ($_GET['id'] ?? 0);
        $tarefa = $this->tarefaService->findTarefa($id);

        if (!$tarefa) {
            Session::setFlash('flash', 'Tarefa não encontrada.');
            $this->redirect('/admin/tarefas');
            return;
        }

        $tarefa['situacao_label'] = $this->taskStatusLabel((string) ($tarefa['situacao'] ?? 'criada'));
        $tarefa['situacao_class'] = $this->taskStatusClass((string) ($tarefa['situacao'] ?? 'criada'));
        $tarefa['prioridade_label'] = $this->taskPriorityLabel((int) ($tarefa['prioridade'] ?? 1));
        $tarefa['prioridade_class'] = $this->taskPriorityClass((int) ($tarefa['prioridade'] ?? 1));
        $tarefa['comentarios_total'] = $this->comments->countFor('tarefas', $id);

        $this->render('pages/admin/tarefas/show', [
            'title' => 'Tarefa #' . $id,
            'currentRoute' => '/admin/tarefas',
            'tarefa' => $tarefa,
            'comentarios' => $this->comments->listFor('tarefas', $id),
        ], 'admin');
    }

    public function createTarefaComment(): void
    {
        if (!$this->auth->isStaff()) {
            Session::setFlash('flash', 'Acesso negado.');
            $this->redirect('/admin/login');
        }

        $tarefaId = (int) $this->input('tarefa_id', 0);
        $comentario = trim((string) $this->input('comentario', ''));

        if ($tarefaId <= 0 || $comentario === '') {
            Session::setFlash('flash', 'Digite um comentário válido.');
            $this->redirect('/admin/tarefas/show?id=' . $tarefaId);
            return;
        }

        $tarefa = $this->tarefaService->findTarefa($tarefaId);
        if (!$tarefa) {
            Session::setFlash('flash', 'Tarefa não encontrada.');
            $this->redirect('/admin/tarefas');
            return;
        }

        $comentarioId = $this->comments->createFor('tarefas', $tarefaId, $comentario);
        $this->logService->log('criar', 'comentario', $comentarioId, 'Comentário adicionado na tarefa #' . $tarefaId);
        Session::setFlash('flash', 'Comentário adicionado com sucesso.');
        $this->redirect('/admin/tarefas/show?id=' . $tarefaId);
    }

    /**
     * @return array{0: array<int, array>, 1: array, 2: bool}
     */
    private function prepareTarefasData(): array
    {
        $tarefas = $this->tarefaService->tarefas();
        $authUser = $this->authUser();
        $isAdmin = (string) ($authUser['role'] ?? $authUser['type'] ?? '') === 'admin';
        $currentUserId = (int) ($authUser['id'] ?? 0);

        if (!$isAdmin && $currentUserId > 0) {
            $tarefas = array_values(array_filter(
                $tarefas,
                static fn (array $tarefa): bool => (int) ($tarefa['responsavel_id'] ?? 0) === $currentUserId
            ));
        }

        $tarefas = array_map(function (array $tarefa): array {
            $situacao = (string) ($tarefa['situacao'] ?? 'criada');
            $prioridade = (int) ($tarefa['prioridade'] ?? 1);
            $tarefa['situacao_label'] = $this->taskStatusLabel($situacao);
            $tarefa['situacao_class'] = $this->taskStatusClass($situacao);
            $tarefa['prioridade_label'] = $this->taskPriorityLabel($prioridade);
            $tarefa['prioridade_class'] = $this->taskPriorityClass($prioridade);
            $tarefa['comentarios_total'] = (int) ($tarefa['comentarios_total'] ?? 0);
            return $tarefa;
        }, $tarefas);

        return [$tarefas, $authUser, $isAdmin];
    }

    // ==================== VISITAS (STAFF) ====================

    public function visitas(): void
    {
        if (!$this->auth->isStaff()) {
            Session::setFlash('flash', 'Faça login como admin, operador ou professor para acessar as visitas.');
            $this->redirect('/admin/login');
        }

        $this->render('pages/admin/visitas/index', [
            'title' => 'Visitas de Páginas',
            'currentRoute' => '/admin/visitas',
            'visits' => $this->visitaService->visits(),
        ], 'admin');
    }

    public function visitasMensal(): void
    {
        if (!$this->auth->isStaff()) {
            Session::setFlash('flash', 'Faça login como admin, operador ou professor para acessar as visitas.');
            $this->redirect('/admin/login');
        }

        $month = isset($_GET['month']) ? (int) $_GET['month'] : null;
        $year = isset($_GET['year']) ? (int) $_GET['year'] : null;

        $this->render('pages/admin/visitas/monthly', [
            'title' => 'Visitas por Mês',
            'currentRoute' => '/admin/visitas',
            'monthly' => $this->visitaService->visitsByMonthDaily($month, $year),
        ], 'admin');
    }

    public function visitasAnalytics(): void
    {
        if (!$this->auth->isStaff()) {
            Session::setFlash('flash', 'Faça login como admin, operador ou professor para acessar as visitas.');
            $this->redirect('/admin/login');
        }

        $month = isset($_GET['month']) ? (int) $_GET['month'] : null;
        $year = isset($_GET['year']) ? (int) $_GET['year'] : null;

        $this->render('pages/admin/visitas/analytics', [
            'title' => 'Analytics de Visitas',
            'currentRoute' => '/admin/visitas',
            'analytics' => $this->visitaService->visitsAnalytics($month, $year),
        ], 'admin');
    }

    public function visitasPaginas(): void
    {
        if (!$this->auth->isStaff()) {
            Session::setFlash('flash', 'Faça login como admin, operador ou professor para acessar as visitas.');
            $this->redirect('/admin/login');
        }

        $month = isset($_GET['month']) ? (int) $_GET['month'] : null;
        $year = isset($_GET['year']) ? (int) $_GET['year'] : null;

        $this->render('pages/admin/visitas/pages', [
            'title' => 'Visitas por Página',
            'currentRoute' => '/admin/visitas',
            'pagesStats' => $this->visitaService->visitsByPage($month, $year),
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
        $record = null;

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
                $recordId = (int) ($_GET['id'] ?? 0);

                if ($recordId > 0) {
                    $viewMode = 'detail';
                    $firstCol = $columns[0]['Field'] ?? 'id';
                    $stmt = $pdo->prepare('SELECT * FROM `' . $table . '` WHERE `' . $firstCol . '` = :id LIMIT 1');
                    $stmt->bindValue(':id', $recordId, PDO::PARAM_INT);
                    $stmt->execute();
                    $record = $stmt->fetch();
                    if (!$record) {
                        $error = 'Registro #' . $recordId . ' nao encontrado na tabela ' . $table;
                    }
                } elseif ($viewMode === 'records') {
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
            'record' => $record,
        ], 'admin');
    }

    private function normalizeAtivo(string $value): int
    {
        $normalized = strtoupper(trim($value));
        return $normalized === 'N' || $normalized === '0' ? 0 : 1;
    }

    private function normalizeConfirmado(string $value): int
    {
        $normalized = strtoupper(trim($value));
        return $normalized === 'S' || $normalized === '1' ? 1 : 0;
    }

    private function normalizeExibirHome(string $value): int
    {
        $normalized = strtoupper(trim($value));
        return $normalized === 'S' || $normalized === '1' ? 1 : 0;
    }

    private function taskSituations(): array
    {
        return [
            'criada' => 'Criada',
            'execucao' => 'Execução',
            'finalizada' => 'Finalizada',
            'revisao' => 'Revisão',
        ];
    }

    private function taskPriorities(): array
    {
        return [
            1 => 'Baixa',
            2 => 'Média',
            3 => 'Alta',
        ];
    }

    private function normalizeTaskSituation(string $value): string
    {
        $value = strtolower(trim($value));
        return array_key_exists($value, $this->taskSituations()) ? $value : 'criada';
    }

    private function normalizeTaskPriority(int $value): int
    {
        return match (true) {
            $value >= 3 => 3,
            $value === 2 => 2,
            default => 1,
        };
    }

    private function taskColumnForStatus(string $status): string
    {
        $status = $this->normalizeTaskSituation($status);

        return match ($status) {
            'finalizada' => 'finalizado',
            'execucao', 'revisao' => 'execucao',
            default => 'tarefa',
        };
    }

    private function taskStatusLabel(string $status): string
    {
        $status = $this->normalizeTaskSituation($status);
        return $this->taskSituations()[$status] ?? 'Criada';
    }

    private function taskStatusClass(string $status): string
    {
        return match ($this->normalizeTaskSituation($status)) {
            'criada' => 'bg-secondary',
            'execucao' => 'bg-primary',
            'finalizada' => 'bg-success',
            'revisao' => 'bg-warning text-dark',
            default => 'bg-secondary',
        };
    }

    private function taskPriorityLabel(int $priority): string
    {
        return $this->taskPriorities()[$this->normalizeTaskPriority($priority)] ?? 'Baixa';
    }

    private function taskPriorityClass(int $priority): string
    {
        return match ($this->normalizeTaskPriority($priority)) {
            3 => 'bg-danger',
            2 => 'bg-warning text-dark',
            default => 'bg-success',
        };
    }

    private function authUser(): array
    {
        $user = \App\Support\Session::get('user');
        return is_array($user) ? $user : [];
    }
}
