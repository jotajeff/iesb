<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Services\CommentService;
use App\Services\AdminService;
use App\Services\AuthService;
use App\Support\Session;
use PDO;

final class AdminController extends Controller
{
    private AuthService $auth;
    private AdminService $admin;
    private CommentService $comments;

    public function __construct()
    {
        $this->auth = new AuthService();
        $this->admin = new AdminService();
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
            'indicators' => $this->admin->indicators(),
            'taskIndicators' => $this->admin->taskIndicators($userId, $isAdmin),
            'isAdmin' => $isAdmin,
        ], 'admin');
    }

    public function logs(): void
    {
        if (!$this->auth->isStaff()) {
            Session::setFlash('flash', 'Faça login como admin, operador ou professor para acessar os logs.');
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
            $this->admin->sincronizarSlugsCursos();
        } catch (\Throwable $e) {
            error_log('[CURSOS] Erro em sincronizarSlugsCursos: ' . $e->getMessage());
        }

        $this->render('pages/admin/cursos/index', [
            'title' => 'Cursos IESB',
            'currentRoute' => '/admin/cursos',
            'courses' => $this->admin->cursos($order, 200, $nivelSelecionado),
            'order' => $order,
            'niveis' => $this->admin->niveis(),
            'nivelSelecionado' => $nivelSelecionado,
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
            'modalidades' => $this->admin->modalidades(),
            'segmentos' => $this->admin->segmentos(),
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

        $cursoId = $this->admin->criarCurso($nome, $dataCurso, $horario, $localCurso, $linkIngresso, $cursoCalendario, $ativo, $exibirHome, $confirmado, '', $modalidadeId, $segmentoId, $nivelId);
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
            'modalidades' => $this->admin->modalidades(),
            'segmentos' => $this->admin->segmentos(),
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
        $cursoCalendario = (string) $this->input('curso_calendario', '');
        $ativo = $this->normalizeAtivo((string) $this->input('ativo', 'S'));
        $exibirHome = $this->normalizeExibirHome((string) $this->input('exibir_home', 'N'));
        $confirmado = $this->normalizeConfirmado((string) $this->input('confirmado', 'N'));
        $modalidadeId = (int) $this->input('modalidade_id', 0);
        $segmentoId = (int) $this->input('segmento_id', 0);
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
        $this->admin->atualizarCurso($id, $nome, $dataCurso, $horario, $localCurso, $linkIngresso, $cursoCalendario, $ativo, $exibirHome, $confirmado, $imagemCard, $modalidadeId, $segmentoId, $nivelId);
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
            Session::setFlash('flash', 'Faça login como admin, operador ou professor para acessar os usuários.');
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

            $tiposValidos = ['admin', 'operador', 'professor'];
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

    public function modalidade(): void
    {
        if (!$this->auth->isStaff()) {
            Session::setFlash('flash', 'Acesso negado.');
            $this->redirect('/admin/login');
        }

        $this->render('pages/admin/modalidade/index', [
            'title' => 'Modalidades',
            'currentRoute' => '/admin/modalidade',
            'modalidades' => $this->admin->modalidades(),
        ], 'admin');
    }

    public function editModalidadeForm(): void
    {
        if (!$this->auth->isStaff()) {
            Session::setFlash('flash', 'Acesso negado.');
            $this->redirect('/admin/login');
        }

        $id = (int) ($this->input('id', 0) ?: ($_GET['id'] ?? 0));
        $modalidade = $id > 0 ? $this->admin->findModalidade($id) : null;

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

        $modalidadeId = $this->admin->saveModalidade($id, $nome, $ativo);
        $acao = $id > 0 ? 'atualizar' : 'criar';
        $descricao = ($id > 0 ? 'Modalidade atualizada: ' : 'Modalidade criada: ') . $nome;
        $this->admin->log($acao, 'modalidade', $modalidadeId, $descricao);

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
            'segmentos' => $this->admin->segmentos(),
        ], 'admin');
    }

    public function editSegmentoForm(): void
    {
        if (!$this->auth->isStaff()) {
            Session::setFlash('flash', 'Acesso negado.');
            $this->redirect('/admin/login');
        }

        $id = (int) ($this->input('id', 0) ?: ($_GET['id'] ?? 0));
        $segmento = $id > 0 ? $this->admin->findSegmento($id) : null;

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
        $ativo = strtoupper(trim((string) $this->input('ativo', 'S')));

        if ($nome === '') {
            Session::setFlash('flash', 'Informe o nome do segmento.');
            $suffix = $id > 0 ? '?id=' . $id : '';
            $this->redirect('/admin/segmento/edit' . $suffix);
            return;
        }

        $segmentoId = $this->admin->saveSegmento($id, $nome, $ativo);
        $acao = $id > 0 ? 'atualizar' : 'criar';
        $descricao = ($id > 0 ? 'Segmento atualizado: ' : 'Segmento criado: ') . $nome;
        $this->admin->log($acao, 'segmento', $segmentoId, $descricao);

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
            'setores' => $this->admin->setores(),
        ], 'admin');
    }

    public function editSetorForm(): void
    {
        if (!$this->auth->isStaff()) {
            Session::setFlash('flash', 'Acesso negado.');
            $this->redirect('/admin/login');
        }

        $id = (int) ($this->input('id', 0) ?: ($_GET['id'] ?? 0));
        $setor = $id > 0 ? $this->admin->findSetor($id) : null;

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

        $setorId = $this->admin->saveSetor($id, $setorNome);
        if ($setorId <= 0) {
            Session::setFlash('flash', 'Nao foi possivel salvar o setor. Verifique a tabela setores e tente novamente.');
            $suffix = $id > 0 ? '?id=' . $id : '';
            $this->redirect('/admin/setor/edit' . $suffix);
            return;
        }

        $acao = $id > 0 ? 'atualizar' : 'criar';
        $descricao = ($id > 0 ? 'Setor atualizado: ' : 'Setor criado: ') . $setorNome;
        $this->admin->log($acao, 'setor', $setorId, $descricao);

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
            'niveis' => $this->admin->niveis(),
        ], 'admin');
    }

    public function editNivelForm(): void
    {
        if (!$this->auth->isStaff()) {
            Session::setFlash('flash', 'Acesso negado.');
            $this->redirect('/admin/login');
        }

        $id = (int) ($this->input('id', 0) ?: ($_GET['id'] ?? 0));
        $nivel = $id > 0 ? $this->admin->findNivel($id) : null;

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

        $nivelId = $this->admin->saveNivel($id, $nome, $ativo, $apresentacao);
        $acao = $id > 0 ? 'atualizar' : 'criar';
        $descricao = ($id > 0 ? 'Nível atualizado: ' : 'Nível criado: ') . $nome;
        $this->admin->log($acao, 'nivel', $nivelId, $descricao);

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
            'setores' => $this->admin->setores(),
            'usuarios' => $this->admin->usuarios(1000),
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

        $turmas = $this->admin->turmas();

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
        $turma = $this->admin->findTurma($id);

        if (!$turma) {
            Session::setFlash('flash', 'Turma não encontrada.');
            $this->redirect('/admin/turmas');
            return;
        }

        $this->render('pages/admin/turmas/show', [
            'title' => 'Turma: ' . ($turma['nome'] ?? ''),
            'currentRoute' => '/admin/turmas/show',
            'turma' => $turma,
            'inscritos' => $this->admin->inscritosPorTurma($id),
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
            'cursos' => $this->admin->cursos('asc', 500),
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

        $turma = $this->admin->findTurma($id);
        if (!$turma) {
            Session::setFlash('flash', 'Turma nao encontrada.');
            $this->redirect('/admin/turmas');
            return;
        }

        $this->render('pages/admin/turmas/edit', [
            'title' => 'Editar Turma',
            'currentRoute' => '/admin/turmas/editar',
            'turma' => $turma,
            'cursos' => $this->admin->cursos('asc', 500),
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
        $ativa = strtoupper(trim((string) $this->input('ativa', 'N')));

        if ($nome === '' || $curso <= 0) {
            Session::setFlash('flash', 'Informe o nome da turma e selecione o curso.');
            $this->redirect('/admin/turmas/novo');
            return;
        }

        $ativa = $ativa === 'S' ? 'S' : 'N';

        $turmaId = $this->admin->criarTurma($nome, $curso, $dataInicio, $ativa);

        if ($turmaId > 0) {
            $this->admin->log('criar', 'turma', $turmaId, "Turma criada: $nome");
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
        $ativa = strtoupper(trim((string) $this->input('ativa', 'N')));

        if ($id <= 0 || $nome === '' || $curso <= 0) {
            Session::setFlash('flash', 'Dados inválidos para atualização.');
            $this->redirect('/admin/turmas/editar?id=' . $id);
            return;
        }

        $ativa = $ativa === 'S' ? 'S' : 'N';

        $this->admin->atualizarTurma($id, $nome, $curso, $dataInicio, $ativa);

        $this->admin->log('atualizar', 'turma', $id, "Turma atualizada: $nome");
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
            'alunos' => $this->admin->alunos(),
        ], 'admin');
    }

    public function showAluno(): void
    {
        if (!$this->auth->isStaff()) {
            Session::setFlash('flash', 'Faça login como admin, operador ou professor para acessar os alunos.');
            $this->redirect('/admin/login');
        }

        $id = (int) ($this->input('id', 0) ?: ($_GET['id'] ?? 0));
        $aluno = $this->admin->findAluno($id);

        if (!$aluno) {
            Session::setFlash('flash', 'Aluno não encontrado.');
            $this->redirect('/admin/alunos');
            return;
        }

        $this->render('pages/admin/alunos/show', [
            'title' => 'Aluno: ' . ($aluno['nome'] ?? ''),
            'currentRoute' => '/admin/alunos/show',
            'aluno' => $aluno,
            'cursos' => $this->admin->cursosDoAluno($id),
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

        $aluno = $this->admin->findAluno($id);
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
        $ativo = strtoupper(trim((string) $this->input('ativo', 'N')));

        if ($nome === '') {
            Session::setFlash('flash', 'Informe o nome do aluno.');
            $this->redirect('/admin/alunos/novo');
            return;
        }

        $alunoId = $this->admin->criarAluno($nome, $cpf, $dataNascimento, $telefone, $email, $ativo);

        if ($alunoId > 0) {
            $this->admin->log('criar', 'aluno', $alunoId, "Aluno criado: $nome");
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
        $ativo = strtoupper(trim((string) $this->input('ativo', 'N')));

        if ($id <= 0 || $nome === '') {
            Session::setFlash('flash', 'Dados inválidos para atualização.');
            $this->redirect('/admin/alunos/editar?id=' . $id);
            return;
        }

        $this->admin->atualizarAluno($id, $nome, $cpf, $dataNascimento, $telefone, $email, $ativo);

        $this->admin->log('atualizar', 'aluno', $id, "Aluno atualizado: $nome");
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
        $aluno = $this->admin->findAluno($id);
        if (!$aluno) {
            Session::setFlash('flash', 'Aluno não encontrado.');
            $this->redirect('/admin/alunos');
            return;
        }

        $matriculas = $this->admin->matriculasDoAluno($id);
        $turmasMatriculadas = array_map(
            static fn (array $m) => (int) ($m['id_turma'] ?? 0),
            $matriculas
        );

        $this->render('pages/admin/alunos/matricula', [
            'title' => 'Matricular Aluno',
            'currentRoute' => '/admin/alunos/matricula',
            'aluno' => $aluno,
            'turmas' => $this->admin->turmas(500),
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

        $aluno = $this->admin->findAluno($idAluno);
        if (!$aluno) {
            Session::setFlash('flash', 'Aluno não encontrado.');
            $this->redirect('/admin/alunos');
            return;
        }

        if ($this->admin->matriculaJaExiste($idAluno, $idTurma)) {
            Session::setFlash('flash', 'Aluno já está matriculado nesta turma.');
            $this->redirect('/admin/alunos/matricula?id=' . $idAluno);
            return;
        }

        $matriculaId = $this->admin->criarMatricula($idAluno, $idTurma, $status);

        if ($matriculaId > 0) {
            $nomeAluno = (string) ($aluno['nome'] ?? '');
            $this->admin->log('criar', 'matricula', $matriculaId, "Matrícula criada: $nomeAluno");
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
            'setores' => $this->admin->setores(),
            'usuarios' => $this->admin->usuarios(1000),
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

        $tarefaId = $this->admin->criarTarefa($setorId, $tarefa, $criadoPor, $responsavel > 0 ? $responsavel : null, $situacao, $prioridade);
        $this->admin->log('criar', 'tarefa', $tarefaId, 'Tarefa criada: ' . $tarefa);
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
        $tarefa = $this->admin->findTarefa($id);

        if (!$tarefa) {
            Session::setFlash('flash', 'Tarefa não encontrada.');
            $this->redirect('/admin/tarefas');
            return;
        }

        $this->render('pages/admin/tarefas/editar', [
            'title' => 'Editar Tarefa',
            'currentRoute' => '/admin/tarefas',
            'tarefa' => $tarefa,
            'setores' => $this->admin->setores(),
            'usuarios' => $this->admin->usuarios(1000),
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

        $existing = $this->admin->findTarefa($id);
        if (!$existing) {
            Session::setFlash('flash', 'Tarefa não encontrada.');
            $this->redirect('/admin/tarefas');
            return;
        }

        $this->admin->atualizarTarefa($id, $setorId, $tarefa, $responsavel > 0 ? $responsavel : null, $situacao, $prioridade);
        $this->admin->log('atualizar', 'tarefa', $id, 'Tarefa atualizada: ' . $tarefa);
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
        $tarefa = $this->admin->findTarefa($id);

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

        $tarefa = $this->admin->findTarefa($tarefaId);
        if (!$tarefa) {
            Session::setFlash('flash', 'Tarefa não encontrada.');
            $this->redirect('/admin/tarefas');
            return;
        }

        $comentarioId = $this->comments->createFor('tarefas', $tarefaId, $comentario);
        $this->admin->log('criar', 'comentario', $comentarioId, 'Comentário adicionado na tarefa #' . $tarefaId);
        Session::setFlash('flash', 'Comentário adicionado com sucesso.');
        $this->redirect('/admin/tarefas/show?id=' . $tarefaId);
    }

    /**
     * @return array{0: array<int, array>, 1: array, 2: bool}
     */
    private function prepareTarefasData(): array
    {
        $tarefas = $this->admin->tarefas();
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
            'visits' => $this->admin->visits(),
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
            'monthly' => $this->admin->visitsByMonthDaily($month, $year),
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
            'analytics' => $this->admin->visitsAnalytics($month, $year),
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

    private function normalizeExibirHome(string $value): string
    {
        $normalized = strtoupper(trim($value));
        return $normalized === 'S' ? 'S' : 'N';
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
