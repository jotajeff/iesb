<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Services\CursoPagamentoService;
use App\Services\CursoService;
use App\Services\ConfigService;
use App\Services\ImageService;
use App\Services\LogService;
use App\Support\Session;

final class CursoController extends Controller
{
    private CursoService $cursoService;
    private ConfigService $configService;
    private LogService $logService;
    private CursoPagamentoService $pagamentoService;
    private ImageService $imageService;

    public function __construct()
    {
        $this->cursoService = new CursoService();
        $this->configService = new ConfigService();
        $this->logService = new LogService();
        $this->pagamentoService = new CursoPagamentoService();
        $this->imageService = new ImageService();
    }

    public function index(): void
    {
        if (!$this->isStaff()) {
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
            error_log('[CURSOS] Erro em sincronizarSlugs: ' . $e->getMessage());
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

    public function novo(): void
    {
        if (!$this->isStaff()) {
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

    public function salvar(): void
    {
        if (!$this->isStaff()) {
            Session::setFlash('flash', 'Acesso negado.');
            $this->redirect('/admin/login');
        }

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
        $cargaHoraria = (int) $this->input('carga_horaria', 0);
        $publicoAlvo = (string) $this->input('publico_alvo', '');

        if ($nome === '' || $localCurso === '') {
            Session::setFlash('flash', 'Preencha ao menos nome e local do curso.');
            $this->redirect('/admin/cursos/novo');
            return;
        }

        $cursoId = $this->cursoService->criarCurso($nome, $dataCurso, $horario, $localCurso, $linkIngresso, $cursoCalendario, $ativo, $exibirHome, $confirmado, '', $modalidadeId, $segmentoId, $nivelId, $cargaHoraria, $publicoAlvo);
        $this->logService->log('criar', 'curso', $cursoId, "Curso criado: $nome");
        Session::setFlash('flash', 'Curso criado com sucesso.');
        $this->redirect('/admin/cursos');
    }

    public function editar(): void
    {
        if (!$this->isStaff()) {
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

    public function atualizar(): void
    {
        if (!$this->isStaff()) {
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
        $cargaHoraria = (int) $this->input('carga_horaria', 0);
        $publicoAlvo = (string) $this->input('publico_alvo', '');

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
        $this->cursoService->atualizarCurso($id, $nome, $dataCurso, $horario, $localCurso, $linkIngresso, $cursoCalendario, $ativo, $exibirHome, $confirmado, $imagemCard, $modalidadeId, $segmentoId, $nivelId, $cargaHoraria, $publicoAlvo);
        $this->logService->log('atualizar', 'curso', $id, "Curso atualizado: $nome");
        Session::setFlash('flash', 'Curso atualizado com sucesso.');
        $this->redirect('/admin/cursos');
    }

    public function show(): void
    {
        if (!$this->isStaff()) {
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

        $disciplinas = [];
        try {
            $pdo = \App\Core\Database::connection();
            if ($pdo instanceof \PDO) {
                $stmt = $pdo->prepare('SELECT d.id, d.nome, d.carga_horaria, d.ativo, d.created_at, (SELECT COUNT(*) FROM ementa e WHERE e.id_disciplina = d.id AND e.ativo = 1) AS tem_ementa FROM disciplina d WHERE d.id_curso = :id_curso ORDER BY d.nome ASC');
                $stmt->bindValue(':id_curso', $id, \PDO::PARAM_INT);
                $stmt->execute();
                $disciplinas = $stmt->fetchAll() ?: [];
            }
        } catch (\Throwable) {
            $disciplinas = [];
        }

        $corpoDocente = [];
        try {
            $pdo = \App\Core\Database::connection();
            if ($pdo instanceof \PDO) {
                $stmt = $pdo->prepare(
                    'SELECT cd.id, cd.id_funcao, cd.ativo AS vinculo_ativo,'
                    . ' u.id AS usuario_id, u.nome AS usuario_nome,'
                    . ' f.nome AS funcao_nome'
                    . ' FROM corpo_docente cd'
                    . ' JOIN usuarios u ON cd.id_usuario = u.id'
                    . ' JOIN funcoes_docente f ON cd.id_funcao = f.id'
                    . ' WHERE cd.id_curso = :id_curso'
                    . ' ORDER BY u.nome ASC'
                );
                $stmt->bindValue(':id_curso', $id, \PDO::PARAM_INT);
                $stmt->execute();
                $corpoDocente = $stmt->fetchAll() ?: [];
            }
        } catch (\Throwable) {
            $corpoDocente = [];
        }

        $galeriaImagens = $this->imageService->listarPorFk('cursos_iesb', $id);

        $this->render('pages/admin/cursos/show', [
            'title' => $course['nome'] ?? 'Curso',
            'currentRoute' => '/admin/cursos/show',
            'course' => $course,
            'detalhe' => $this->cursoService->findDetalheByCurso($id),
            'pagamentos' => $this->pagamentoService->listarPorCurso($id),
            'disciplinas' => $disciplinas,
            'corpoDocente' => $corpoDocente,
            'galeriaImagens' => $galeriaImagens,
        ], 'admin');
    }

    public function disciplinas(): void
    {
        if (!$this->isStaff()) {
            Session::setFlash('flash', 'Acesso negado.');
            $this->redirect('/admin/login');
        }

        $cursoId = (int) ($_GET['id_curso'] ?? 0);
        $disciplinaId = (int) ($_GET['id'] ?? 0);

        $course = $this->cursoService->findCurso($cursoId);
        if (!$course) {
            Session::setFlash('flash', 'Curso não encontrado.');
            $this->redirect('/admin/cursos');
            return;
        }

        $disciplina = null;
        if ($disciplinaId > 0) {
            try {
                $pdo = \App\Core\Database::connection();
                if ($pdo instanceof \PDO) {
                    $stmt = $pdo->prepare('SELECT id, nome, carga_horaria, ativo FROM disciplina WHERE id = :id AND id_curso = :id_curso LIMIT 1');
                    $stmt->bindValue(':id', $disciplinaId, \PDO::PARAM_INT);
                    $stmt->bindValue(':id_curso', $cursoId, \PDO::PARAM_INT);
                    $stmt->execute();
                    $disciplina = $stmt->fetch() ?: null;
                }
            } catch (\Throwable) {
                $disciplina = null;
            }
        }

        $this->render('pages/admin/cursos/disciplinas', [
            'title' => 'Disciplinas — ' . ($course['nome'] ?? ''),
            'currentRoute' => '/admin/cursos/disciplinas',
            'course' => $course,
            'disciplina' => $disciplina,
        ], 'admin');
    }

    public function salvarDisciplina(): void
    {
        if (!$this->isStaff()) {
            Session::setFlash('flash', 'Acesso negado.');
            $this->redirect('/admin/login');
        }

        $cursoId = (int) $this->input('id_curso', 0);
        $disciplinaId = (int) $this->input('id', 0);
        $nome = trim((string) $this->input('nome', ''));
        $cargaHoraria = (int) $this->input('carga_horaria', 0);
        $ativo = (string) $this->input('ativo', '1');

        if ($cursoId <= 0 || $nome === '') {
            Session::setFlash('flash', 'Preencha o nome da disciplina.');
            $this->redirect('/admin/cursos/disciplinas?id_curso=' . $cursoId);
            return;
        }

        try {
            $pdo = \App\Core\Database::connection();
            if ($pdo instanceof \PDO) {
                if ($disciplinaId > 0) {
                    $stmt = $pdo->prepare('UPDATE disciplina SET nome = :nome, carga_horaria = :carga_horaria, ativo = :ativo WHERE id = :id AND id_curso = :id_curso');
                    $stmt->bindValue(':nome', $nome, \PDO::PARAM_STR);
                    $stmt->bindValue(':carga_horaria', $cargaHoraria, \PDO::PARAM_INT);
                    $stmt->bindValue(':ativo', $ativo, \PDO::PARAM_STR);
                    $stmt->bindValue(':id', $disciplinaId, \PDO::PARAM_INT);
                    $stmt->bindValue(':id_curso', $cursoId, \PDO::PARAM_INT);
                    $stmt->execute();
                    $this->logService->log('atualizar', 'disciplina', $disciplinaId, "Disciplina atualizada: $nome");
                } else {
                    $stmt = $pdo->prepare('INSERT INTO disciplina (id_curso, nome, carga_horaria, ativo) VALUES (:id_curso, :nome, :carga_horaria, :ativo)');
                    $stmt->bindValue(':id_curso', $cursoId, \PDO::PARAM_INT);
                    $stmt->bindValue(':nome', $nome, \PDO::PARAM_STR);
                    $stmt->bindValue(':carga_horaria', $cargaHoraria, \PDO::PARAM_INT);
                    $stmt->bindValue(':ativo', $ativo, \PDO::PARAM_STR);
                    $stmt->execute();
                    $this->logService->log('criar', 'disciplina', (int) $pdo->lastInsertId(), "Disciplina criada: $nome");
                }
            }
            Session::setFlash('flash', 'Disciplina salva com sucesso.');
        } catch (\Throwable $e) {
            error_log('[DISCIPLINA] Erro: ' . $e->getMessage());
            Session::setFlash('flash', 'Erro ao salvar disciplina.');
        }

        $this->redirect('/admin/cursos/disciplinas?id_curso=' . $cursoId);
    }

    public function corpoDocente(): void
    {
        if (!$this->isStaff()) {
            Session::setFlash('flash', 'Acesso negado.');
            $this->redirect('/admin/login');
        }

        $cursoId = (int) ($_GET['id_curso'] ?? 0);
        $course = $this->cursoService->findCurso($cursoId);
        if (!$course) {
            Session::setFlash('flash', 'Curso não encontrado.');
            $this->redirect('/admin/cursos');
            return;
        }

        $professores = [];
        $funcoes = [];
        $vinculados = [];

        try {
            $pdo = \App\Core\Database::connection();
            if ($pdo instanceof \PDO) {
                $stmt = $pdo->prepare('SELECT id, nome FROM usuarios WHERE tipo = :tipo ORDER BY nome ASC');
                $stmt->bindValue(':tipo', 'professor', \PDO::PARAM_STR);
                $stmt->execute();
                $professores = $stmt->fetchAll() ?: [];

                $stmt = $pdo->prepare('SELECT id, nome FROM funcoes_docente WHERE ativo = :ativo ORDER BY nome ASC');
                $stmt->bindValue(':ativo', 1, \PDO::PARAM_INT);
                $stmt->execute();
                $funcoes = $stmt->fetchAll() ?: [];

                $stmt = $pdo->prepare('SELECT id_usuario FROM corpo_docente WHERE id_curso = :id_curso AND ativo = :ativo');
                $stmt->bindValue(':id_curso', $cursoId, \PDO::PARAM_INT);
                $stmt->bindValue(':ativo', 1, \PDO::PARAM_INT);
                $stmt->execute();
                foreach ($stmt->fetchAll() as $row) {
                    $vinculados[(int) $row['id_usuario']] = true;
                }
            }
        } catch (\Throwable) {
        }

        $this->render('pages/admin/cursos/corpo-docente', [
            'title' => 'Corpo Docente — ' . ($course['nome'] ?? ''),
            'currentRoute' => '/admin/cursos/corpo-docente',
            'course' => $course,
            'professores' => $professores,
            'funcoes' => $funcoes,
            'vinculados' => $vinculados,
        ], 'admin');
    }

    public function salvarCorpoDocente(): void
    {
        if (!$this->isStaff()) {
            Session::setFlash('flash', 'Acesso negado.');
            $this->redirect('/admin/login');
        }

        $cursoId = (int) $this->input('id_curso', 0);
        $usuarios = (array) $this->input('usuarios', []);
        $funcao = (int) $this->input('id_funcao', 0);

        if ($cursoId <= 0 || empty($usuarios) || $funcao <= 0) {
            Session::setFlash('flash', 'Selecione ao menos um professor e uma função.');
            $this->redirect('/admin/cursos/corpo-docente?id_curso=' . $cursoId);
            return;
        }

        try {
            $pdo = \App\Core\Database::connection();
            if ($pdo instanceof \PDO) {
                $stmt = $pdo->prepare('INSERT INTO corpo_docente (id_curso, id_usuario, id_funcao) VALUES (:id_curso, :id_usuario, :id_funcao)');
                foreach ($usuarios as $userId) {
                    $userId = (int) $userId;
                    if ($userId <= 0) continue;
                    $stmt->bindValue(':id_curso', $cursoId, \PDO::PARAM_INT);
                    $stmt->bindValue(':id_usuario', $userId, \PDO::PARAM_INT);
                    $stmt->bindValue(':id_funcao', $funcao, \PDO::PARAM_INT);
                    $stmt->execute();
                }
                $this->logService->log('criar', 'corpo_docente', $cursoId, count($usuarios) . ' docente(s) vinculado(s) ao curso ' . $cursoId);
            }
            Session::setFlash('flash', 'Docente(s) vinculado(s) com sucesso.');
        } catch (\Throwable $e) {
            error_log('[CORPO DOCENTE] Erro: ' . $e->getMessage());
            Session::setFlash('flash', 'Erro ao vincular docente(s).');
        }

        $this->redirect('/admin/cursos/show?id=' . $cursoId);
    }

    public function removerCorpoDocente(): void
    {
        if (!$this->isStaff()) {
            http_response_code(403);
            echo json_encode(['erro' => 'Acesso negado.']);
            return;
        }

        $id = (int) ($this->input('id', 0) ?: ($_POST['id'] ?? 0));

        if ($id <= 0) {
            http_response_code(400);
            echo json_encode(['erro' => 'ID inválido.']);
            return;
        }

        try {
            $pdo = \App\Core\Database::connection();
            if ($pdo instanceof \PDO) {
                $stmt = $pdo->prepare('DELETE FROM corpo_docente WHERE id = :id');
                $stmt->bindValue(':id', $id, \PDO::PARAM_INT);
                $stmt->execute();
                $this->logService->log('excluir', 'corpo_docente', $id, 'Vínculo docente removido');
            }
            echo json_encode(['sucesso' => true]);
        } catch (\Throwable $e) {
            error_log('[CORPO DOCENTE] Erro ao remover: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['erro' => 'Erro ao remover vínculo.']);
        }
    }

    public function galeria(): void
    {
        if (!$this->isStaff()) {
            Session::setFlash('flash', 'Acesso negado.');
            $this->redirect('/admin/login');
        }

        $cursoId = (int) ($_GET['id'] ?? 0);
        $course = $this->cursoService->findCurso($cursoId);

        if (!$course) {
            Session::setFlash('flash', 'Curso não encontrado.');
            $this->redirect('/admin/cursos');
            return;
        }

        $imagens = $this->imageService->listarPorFk('cursos_iesb', $cursoId);

        $this->render('pages/admin/cursos/galeria', [
            'title' => 'Galeria — ' . ($course['nome'] ?? ''),
            'currentRoute' => '/admin/cursos/galeria',
            'course' => $course,
            'imagens' => $imagens,
        ], 'admin');
    }

    public function uploadGaleria(): void
    {
        if (!$this->isStaff()) {
            Session::setFlash('flash', 'Acesso negado.');
            $this->redirect('/admin/login');
        }

        $cursoId = (int) $this->input('id_curso', 0);
        $legenda = trim((string) $this->input('legenda', ''));

        if ($cursoId <= 0) {
            Session::setFlash('flash', 'Parâmetros inválidos.');
            $this->redirect('/admin/cursos');
            return;
        }

        $path = '';
        $file = $_FILES['imagem'] ?? null;

        if ($file && $file['error'] === UPLOAD_ERR_OK) {
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            if (in_array($ext, $allowed, true)) {
                $filename = 'curso-' . $cursoId . '-' . time() . '-' . mt_rand(100, 999) . '.' . $ext;
                $destDir = dirname(__DIR__, 3) . '/public/assets/img/cursos';
                if (!is_dir($destDir)) {
                    mkdir($destDir, 0755, true);
                }
                $destPath = $destDir . '/' . $filename;
                if (move_uploaded_file($file['tmp_name'], $destPath)) {
                    $path = 'assets/img/cursos/' . $filename;
                }
            }
        }

        if ($path === '') {
            Session::setFlash('flash', 'Erro ao fazer upload. Verifique o formato e tamanho.');
            $this->redirect('/admin/cursos/galeria?id=' . $cursoId);
            return;
        }

        $this->imageService->salvar('cursos_iesb', $cursoId, $path, $legenda ?: null);
        $this->logService->log('criar', 'imagem', 0, 'Imagem adicionada à galeria do curso ' . $cursoId);

        Session::setFlash('flash', 'Imagem salva com sucesso.');
        $this->redirect('/admin/cursos/galeria?id=' . $cursoId);
    }

    public function deletarGaleria(): void
    {
        if (!$this->isStaff()) {
            http_response_code(403);
            echo json_encode(['erro' => 'Acesso negado.']);
            return;
        }

        $id = (int) ($this->input('id', 0) ?: ($_POST['id'] ?? 0));

        if ($id <= 0) {
            http_response_code(400);
            echo json_encode(['erro' => 'ID inválido.']);
            return;
        }

        $this->imageService->deletar($id);
        $this->logService->log('deletar', 'imagem', $id, 'Imagem removida da galeria de curso');
        echo json_encode(['sucesso' => true]);
    }

    public function ementa(): void
    {
        if (!$this->isStaff()) {
            Session::setFlash('flash', 'Acesso negado.');
            $this->redirect('/admin/login');
        }

        $disciplinaId = (int) ($_GET['id_disciplina'] ?? 0);

        $disciplina = null;
        $ementa = null;

        try {
            $pdo = \App\Core\Database::connection();
            if ($pdo instanceof \PDO) {
                $stmt = $pdo->prepare('SELECT d.id, d.nome, d.id_curso FROM disciplina d WHERE d.id = :id LIMIT 1');
                $stmt->bindValue(':id', $disciplinaId, \PDO::PARAM_INT);
                $stmt->execute();
                $disciplina = $stmt->fetch() ?: null;

                if ($disciplina) {
                    $stmt = $pdo->prepare('SELECT id, ementa FROM ementa WHERE id_disciplina = :id_disciplina AND ativo = :ativo LIMIT 1');
                    $stmt->bindValue(':id_disciplina', $disciplinaId, \PDO::PARAM_INT);
                    $stmt->bindValue(':ativo', 1, \PDO::PARAM_INT);
                    $stmt->execute();
                    $ementa = $stmt->fetch() ?: null;
                }
            }
        } catch (\Throwable $e) {
            error_log('[EMENTA] Erro: ' . $e->getMessage());
        }

        if (!$disciplina) {
            Session::setFlash('flash', 'Disciplina não encontrada.');
            $this->redirect('/admin/cursos');
            return;
        }

        $this->render('pages/admin/cursos/ementa', [
            'title' => 'Ementa — ' . ($disciplina['nome'] ?? ''),
            'currentRoute' => '/admin/cursos/ementa',
            'disciplina' => $disciplina,
            'ementa' => $ementa,
        ], 'admin');
    }

    public function salvarEmenta(): void
    {
        if (!$this->isStaff()) {
            Session::setFlash('flash', 'Acesso negado.');
            $this->redirect('/admin/login');
        }

        $disciplinaId = (int) $this->input('id_disciplina', 0);
        $ementaId = (int) $this->input('id', 0);
        $conteudo = (string) $this->input('ementa', '');

        if ($disciplinaId <= 0) {
            Session::setFlash('flash', 'Disciplina inválida.');
            $this->redirect('/admin/cursos');
            return;
        }

        try {
            $pdo = \App\Core\Database::connection();
            if ($pdo instanceof \PDO) {
                if ($ementaId > 0) {
                    $stmt = $pdo->prepare('UPDATE ementa SET ementa = :ementa WHERE id = :id');
                    $stmt->bindValue(':ementa', $conteudo, \PDO::PARAM_STR);
                    $stmt->bindValue(':id', $ementaId, \PDO::PARAM_INT);
                    $stmt->execute();
                    $this->logService->log('atualizar', 'ementa', $ementaId, 'Ementa atualizada');
                } else {
                    $stmt = $pdo->prepare('INSERT INTO ementa (id_disciplina, ementa, created_at) VALUES (:id_disciplina, :ementa, :created_at)');
                    $stmt->bindValue(':id_disciplina', $disciplinaId, \PDO::PARAM_INT);
                    $stmt->bindValue(':ementa', $conteudo, \PDO::PARAM_STR);
                    $stmt->bindValue(':created_at', time(), \PDO::PARAM_INT);
                    $stmt->execute();
                    $this->logService->log('criar', 'ementa', (int) $pdo->lastInsertId(), 'Ementa criada');
                }
            }
            Session::setFlash('flash', 'Ementa salva com sucesso.');
        } catch (\Throwable $e) {
            error_log('[EMENTA] Erro salvar: ' . $e->getMessage());
            Session::setFlash('flash', 'Erro ao salvar ementa.');
        }

        $this->redirect('/admin/cursos/ementa?id_disciplina=' . $disciplinaId);
    }

    public function detalhes(): void
    {
        if (!$this->isStaff()) {
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

    public function salvarDetalhe(): void
    {
        if (!$this->isStaff()) {
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

        if ($detalheId > 0) {
            $this->cursoService->atualizarDetalhe($detalheId, $payload);
            $this->logService->log('atualizar', 'detalhe', $detalheId, "Detalhe atualizado para o curso #$cursoId");
            Session::setFlash('flash', 'Detalhe atualizado com sucesso.');
        } else {
            $novoId = $this->cursoService->salvarDetalhe($payload);
            $this->logService->log('criar', 'detalhe', $novoId, "Detalhe criado para o curso #$cursoId");
            Session::setFlash('flash', 'Detalhe criado com sucesso.');
        }

        $this->redirect('/admin/cursos');
    }

    public function uploadForm(): void
    {
        if (!$this->isStaff()) {
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

    public function uploadImagem(): void
    {
        if (!$this->isStaff()) {
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

        $destDir = dirname(__DIR__, 3) . '/public/assets/img/cursos';
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

    public function definirValor(): void
    {
        if (!$this->isStaff()) {
            Session::setFlash('flash', 'Acesso negado.');
            $this->redirect('/admin/login');
        }

        $idCurso = (int) ($_GET['id'] ?? 0);
        $curso = $this->cursoService->findCurso($idCurso);

        if (!$curso) {
            Session::setFlash('flash', 'Curso não encontrado.');
            $this->redirect('/admin/cursos');
        }

        $pagamentos = $this->pagamentoService->listarPorCurso($idCurso);

        $this->render('pages/admin/cursos/definir_valor', [
            'title' => 'Pagamento — ' . ($curso['nome'] ?? ''),
            'currentRoute' => '/admin/cursos',
            'curso' => $curso,
            'pagamentos' => $pagamentos,
        ], 'admin');
    }

    public function salvarPagamento(): void
    {
        if (!$this->isStaff()) {
            Session::setFlash('flash', 'Acesso negado.');
            $this->redirect('/admin/login');
        }

        $idCurso = (int) $this->input('id_curso', 0);
        $curso = $this->cursoService->findCurso($idCurso);

        if (!$curso) {
            Session::setFlash('flash', 'Curso não encontrado.');
            $this->redirect('/admin/cursos');
        }

        $idPagamento = (int) $this->input('id', 0);
        $descricao = trim((string) $this->input('descricao', ''));
        $tipo = (string) $this->input('tipo', '');
        $parcelas = max(1, (int) $this->input('parcelas', 1));
        $valor = (float) str_replace(',', '.', (string) $this->input('valor', '0'));
        $ativo = (string) $this->input('ativo', '1');

        if ($descricao === '') {
            Session::setFlash('flash', 'Informe a descrição.');
            $this->redirect('/admin/cursos/definir-valor?id=' . $idCurso);
        }

        $result = $this->pagamentoService->salvar([
            'id' => $idPagamento,
            'id_curso' => $idCurso,
            'descricao' => $descricao,
            'tipo' => $tipo,
            'parcelas' => $parcelas,
            'valor' => $valor,
            'ativo' => $ativo,
        ]);

        if ($result <= 0) {
            Session::setFlash('flash', 'Erro ao salvar forma de pagamento.');
            $this->redirect('/admin/cursos/definir-valor?id=' . $idCurso);
        }

        $this->logService->log($idPagamento > 0 ? 'atualizar' : 'criar', 'cursos_iesb_pagamento', $result, ($idPagamento > 0 ? 'Pagamento atualizado' : 'Pagamento criado') . ' para o curso #' . $idCurso);
        Session::setFlash('flash', 'Forma de pagamento salva com sucesso.');
        $this->redirect('/admin/cursos/definir-valor?id=' . $idCurso);
    }

    public function cursosTurma(): void
    {
        if (!$this->isStaff()) {
            Session::setFlash('flash', 'Acesso negado.');
            $this->redirect('/admin/login');
        }

        $cursosTurmas = $this->cursoService->listarCursosTurmas();

        $this->render('pages/admin/cursos/cursos_turma', [
            'title' => 'Cursos-turma',
            'currentRoute' => '/admin/cursos/cursos-turma',
            'cursosTurmas' => $cursosTurmas,
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

    private function isStaff(): bool
    {
        return (new \App\Services\AuthService())->isStaff();
    }
}
