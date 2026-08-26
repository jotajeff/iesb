<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Database;
use App\Repositories\StorageDriveRepository;
use App\Services\Storage\StorageException;
use App\Services\Storage\StorageService;
use App\Services\UsuarioService;
use App\Services\LogService;
use App\Services\TurmaService;
use App\Services\ImageService;
use App\Repositories\EnderecoRepository;
use App\Support\Session;

final class ProfessorController extends Controller
{
    private UsuarioService $usuarioService;
    private LogService $logService;
    private TurmaService $turmaService;
    private EnderecoRepository $enderecoRepository;
    private ImageService $imageService;

    public function __construct()
    {
        $this->usuarioService = new UsuarioService();
        $this->logService = new LogService();
        $this->turmaService = new TurmaService();
        $this->enderecoRepository = new EnderecoRepository();
        $this->imageService = new ImageService();
    }

    public function index(): void
    {
        if (!$this->isStaff()) {
            Session::setFlash('flash', 'Faça login como admin ou operador para acessar os professores.');
            $this->redirect('/admin/login');
        }

        $perPage = 20;
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $professoresResult = $this->usuarioService->usuariosPorTipoPaginados(
            'professor',
            $perPage,
            ($page - 1) * $perPage
        );
        $professores = $professoresResult['data'] ?? [];
        $totalProfessores = (int) ($professoresResult['total'] ?? 0);
        $totalPages = max(1, (int) ceil($totalProfessores / $perPage));
        if ($page > $totalPages) {
            $page = $totalPages;
            $professoresResult = $this->usuarioService->usuariosPorTipoPaginados(
                'professor',
                $perPage,
                ($page - 1) * $perPage
            );
            $professores = $professoresResult['data'] ?? [];
        }

        $enderecos = [];
        foreach ($professores as $prof) {
            $id = (int) ($prof['id'] ?? 0);
            try {
                $enderecos[$id] = $this->enderecoRepository->findByTipoAndFk('professor', $id);
            } catch (\Throwable) {
                $enderecos[$id] = null;
            }
        }

        $vinculoCounts = [];
        $pdo = \App\Core\Database::connection();
        if ($pdo instanceof \PDO) {
            try {
                $stmt = $pdo->prepare('SELECT id_usuario, COUNT(*) AS total FROM turma_professor WHERE status = :status GROUP BY id_usuario');
                $stmt->bindValue(':status', 'A', \PDO::PARAM_STR);
                $stmt->execute();
                foreach ($stmt->fetchAll() as $row) {
                    $vinculoCounts[(int) $row['id_usuario']] = (int) $row['total'];
                }
            } catch (\Throwable) {
                $vinculoCounts = [];
            }
        }

        $fotos = [];
        foreach ($professores as $prof) {
            $id = (int) ($prof['id'] ?? 0);
            try {
                $imgs = $this->imageService->listarPorFk('usuarios', $id);
                $fotos[$id] = !empty($imgs) ? $imgs[0]['path'] : null;
            } catch (\Throwable) {
                $fotos[$id] = null;
            }
        }

        $curriculos = [];
        $pdoCurriculo = Database::connection();
        if ($pdoCurriculo instanceof \PDO) {
            foreach ($professores as $prof) {
                $id = (int) ($prof['id'] ?? 0);
                try {
                    $stmt = $pdoCurriculo->prepare('SELECT id, resumo, conteudo FROM curriculo WHERE tipo = :tipo AND id_fk = :id_fk AND ativo = :ativo LIMIT 1');
                    $stmt->bindValue(':tipo', 'professor', \PDO::PARAM_STR);
                    $stmt->bindValue(':id_fk', $id, \PDO::PARAM_INT);
                    $stmt->bindValue(':ativo', 1, \PDO::PARAM_INT);
                    $stmt->execute();
                    $row = $stmt->fetch();
                    $curriculos[$id] = $row ?: null;
                } catch (\Throwable) {
                    $curriculos[$id] = null;
                }
            }
        }

        $this->render('pages/admin/professores/index', [
            'title' => 'Professores',
            'currentRoute' => '/admin/professores',
            'professores' => $professores,
            'enderecos' => $enderecos,
            'vinculoCounts' => $vinculoCounts,
            'fotos' => $fotos,
            'curriculos' => $curriculos,
            'pagination' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => $totalProfessores,
                'total_pages' => $totalPages,
            ],
        ], 'admin');
    }

    public function novo(): void
    {
        if (!$this->isStaff()) {
            Session::setFlash('flash', 'Acesso negado.');
            $this->redirect('/admin/login');
        }

        $this->render('pages/admin/professores/novo', [
            'title' => 'Novo Professor',
            'currentRoute' => '/admin/professores/novo',
        ], 'admin');
    }

    public function salvar(): void
    {
        if (!$this->isStaff()) {
            Session::setFlash('flash', 'Acesso negado.');
            $this->redirect('/admin/login');
        }

        $nome = trim((string) $this->input('nome', ''));
        $email = trim((string) $this->input('email', ''));
        $senha = (string) $this->input('senha', '');
        $telefone = trim((string) $this->input('telefone', ''));
        $titulacao = trim((string) $this->input('titulacao', ''));
        $ativo = (string) $this->input('ativo', '1');

        if ($nome === '' || $email === '' || $senha === '') {
            Session::setFlash('flash', 'Preencha nome, email e senha.');
            $this->redirect('/admin/professores/novo');
            return;
        }

        $usuarioId = $this->usuarioService->criarUsuario($nome, $email, $senha, 'professor', $ativo, $telefone, $titulacao);
        $this->logService->log('criar', 'usuario', $usuarioId, "Professor criado: $nome");
        Session::setFlash('flash', 'Professor criado com sucesso.');
        $this->redirect('/admin/professores');
    }

    public function editar(): void
    {
        if (!$this->isStaff()) {
            Session::setFlash('flash', 'Acesso negado.');
            $this->redirect('/admin/login');
        }

        $authUser = Session::get('user');
        $isProfessor = ((string) ($authUser['role'] ?? $authUser['tipo'] ?? '')) === 'professor';

        $id = (int) ($this->input('id', 0) ?: ($_GET['id'] ?? 0));
        $professor = $this->usuarioService->findUsuario($id);

        if (!$professor || ((string) ($professor['tipo'] ?? '')) !== 'professor') {
            Session::setFlash('flash', 'Professor não encontrado.');
            $this->redirect('/admin/professores');
            return;
        }

        $this->render('pages/admin/professores/edit', [
            'title' => 'Editar Professor',
            'currentRoute' => '/admin/professores/editar',
            'professor' => $professor,
            'backRoute' => $isProfessor ? '/admin/professores/perfil' : '/admin/professores',
        ], 'admin');
    }

    public function atualizar(): void
    {
        if (!$this->isStaff()) {
            Session::setFlash('flash', 'Acesso negado.');
            $this->redirect('/admin/login');
        }

        $authUser = Session::get('user');
        $isProfessor = ((string) ($authUser['role'] ?? $authUser['tipo'] ?? '')) === 'professor';
        $redirectBase = $isProfessor ? '/admin/professores/perfil' : '/admin/professores';

        $id = (int) $this->input('id', 0);
        $nome = trim((string) $this->input('nome', ''));
        $telefone = trim((string) $this->input('telefone', ''));
        $titulacao = trim((string) $this->input('titulacao', ''));
        $ativo = (string) $this->input('ativo', '1');

        if ($id <= 0 || $nome === '') {
            Session::setFlash('flash', 'Dados inválidos.');
            $this->redirect($isProfessor ? '/admin/professores/editar?id=' . $id : $redirectBase);
            return;
        }

        $professor = $this->usuarioService->findUsuario($id);
        if (!$professor) {
            Session::setFlash('flash', 'Professor não encontrado.');
            $this->redirect($redirectBase);
            return;
        }

        $this->usuarioService->atualizarUsuario($id, '', $ativo, $nome, '', '', $telefone, $titulacao);
        $this->logService->log('atualizar', 'usuario', $id, "Professor atualizado: $nome");
        Session::setFlash('flash', 'Professor atualizado com sucesso.');
        $this->redirect($redirectBase);
    }

    public function endereco(): void
    {
        if (isset($_GET['ajax']) && $_GET['ajax'] === '1' && isset($_GET['cep'])) {
            $this->buscarCep();
            return;
        }

        if (!$this->isStaff()) {
            Session::setFlash('flash', 'Acesso negado.');
            $this->redirect('/admin/login');
        }

        $authUser = Session::get('user');
        $isProfessor = ((string) ($authUser['role'] ?? $authUser['tipo'] ?? '')) === 'professor';

        $id = (int) ($this->input('id', 0) ?: ($_GET['id'] ?? 0));
        $professor = $this->usuarioService->findUsuario($id);

        if (!$professor || ((string) ($professor['tipo'] ?? '')) !== 'professor') {
            Session::setFlash('flash', 'Professor não encontrado.');
            $this->redirect('/admin/professores');
            return;
        }

        try {
            $endereco = $this->enderecoRepository->findByTipoAndFk('professor', $id);
        } catch (\Throwable) {
            $endereco = null;
        }

        $this->render('pages/admin/professores/endereco', [
            'title' => 'Endereço do Professor',
            'currentRoute' => '/admin/professores/endereco',
            'professor' => $professor,
            'endereco' => $endereco,
            'backRoute' => $isProfessor ? '/admin/professores/perfil' : '/admin/professores',
        ], 'admin');
    }

    public function salvarEndereco(): void
    {
        if (!$this->isStaff()) {
            Session::setFlash('flash', 'Acesso negado.');
            $this->redirect('/admin/login');
        }

        $authUser = Session::get('user');
        $isProfessor = ((string) ($authUser['role'] ?? $authUser['tipo'] ?? '')) === 'professor';
        $redirectBase = $isProfessor ? '/admin/professores/perfil' : '/admin/professores';

        $id = (int) $this->input('id', 0);
        $professor = $this->usuarioService->findUsuario($id);
        if (!$professor) {
            Session::setFlash('flash', 'Professor não encontrado.');
            $this->redirect($redirectBase);
            return;
        }

        $cep = trim((string) $this->input('cep', ''));
        $logradouro = trim((string) $this->input('logradouro', ''));
        $numero = trim((string) $this->input('numero', ''));
        $cidade = trim((string) $this->input('cidade', ''));
        $uf = strtoupper(trim((string) $this->input('uf', '')));

        if ($cep === '' || $logradouro === '' || $cidade === '' || $uf === '') {
            Session::setFlash('flash', 'Preencha CEP, logradouro, cidade e UF.');
            $this->redirect('/admin/professores/endereco?id=' . $id);
            return;
        }

        $data = [
            'tipo' => 'professor',
            'id_fk' => $id,
            'cep' => $cep,
            'logradouro' => $logradouro,
            'numero' => $numero ?: null,
            'cidade' => $cidade,
            'uf' => $uf,
        ];

        try {
            $existente = $this->enderecoRepository->findByTipoAndFk('professor', $id);
        } catch (\Throwable) {
            $existente = null;
        }
        try {
            if ($existente) {
                $this->enderecoRepository->update((int) $existente['id'], $data);
                $this->logService->log('atualizar', 'endereco', (int) $existente['id'], "Endereço atualizado: $logradouro");
            } else {
                $this->enderecoRepository->create($data);
                $this->logService->log('criar', 'endereco', $id, "Endereço criado: $logradouro");
            }
            Session::setFlash('flash', 'Endereço salvo com sucesso.');
        } catch (\Throwable) {
            Session::setFlash('flash', 'Erro ao salvar endereço. A tabela endereco pode não existir no banco.');
        }
        $this->redirect($redirectBase);
    }

    public function vincular(): void
    {
        if (!$this->isStaff()) {
            Session::setFlash('flash', 'Acesso negado.');
            $this->redirect('/admin/login');
        }

        $id = (int) ($this->input('id', 0) ?: ($_GET['id'] ?? 0));
        $professor = $this->usuarioService->findUsuario($id);

        if (!$professor || ((string) ($professor['tipo'] ?? '')) !== 'professor') {
            Session::setFlash('flash', 'Professor não encontrado.');
            $this->redirect('/admin/professores');
            return;
        }

        $turmas = $this->turmaService->turmas();

        $vinculos = [];
        $pdo = Database::connection();
        if ($pdo instanceof \PDO) {
            $stmt = $pdo->prepare('SELECT id_turma FROM turma_professor WHERE id_usuario = :id AND status = :status');
            $stmt->bindValue(':id', $id, \PDO::PARAM_INT);
            $stmt->bindValue(':status', 'A', \PDO::PARAM_STR);
            $stmt->execute();
            foreach ($stmt->fetchAll() as $row) {
                $vinculos[(int) $row['id_turma']] = true;
            }
        }

        $this->render('pages/admin/professores/vincular', [
            'title' => 'Vincular Professor a Turmas',
            'currentRoute' => '/admin/professores/vincular',
            'professor' => $professor,
            'turmas' => $turmas,
            'vinculos' => $vinculos,
        ], 'admin');
    }

    public function salvarVinculo(): void
    {
        if (!$this->isStaff()) {
            Session::setFlash('flash', 'Acesso negado.');
            $this->redirect('/admin/login');
        }

        $id = (int) $this->input('id', 0);
        $professor = $this->usuarioService->findUsuario($id);
        if (!$professor) {
            Session::setFlash('flash', 'Professor não encontrado.');
            $this->redirect('/admin/professores');
            return;
        }

        $turmaIds = array_filter(array_map('intval', (array) ($this->input('turmas', []) ?: [])), fn(int $v) => $v > 0);

        $pdo = Database::connection();
        if (!$pdo instanceof \PDO) {
            Session::setFlash('flash', 'Erro de conexão com o banco.');
            $this->redirect('/admin/professores/vincular?id=' . $id);
            return;
        }

        try {
            $pdo->beginTransaction();

            $stmtDel = $pdo->prepare('DELETE FROM turma_professor WHERE id_usuario = :id');
            $stmtDel->bindValue(':id', $id, \PDO::PARAM_INT);
            $stmtDel->execute();

            if (!empty($turmaIds)) {
                $stmtIns = $pdo->prepare('INSERT INTO turma_professor (id_turma, id_usuario, status) VALUES (:id_turma, :id_usuario, :status)');
                foreach ($turmaIds as $idTurma) {
                    $stmtIns->bindValue(':id_turma', $idTurma, \PDO::PARAM_INT);
                    $stmtIns->bindValue(':id_usuario', $id, \PDO::PARAM_INT);
                    $stmtIns->bindValue(':status', 'A', \PDO::PARAM_STR);
                    $stmtIns->execute();
                }
            }

            $pdo->commit();

            $this->logService->log('vincular', 'turma_professor', $id, sprintf(
                'Professor ID %d vinculado a %d turma(s)', $id, count($turmaIds)
            ));
            Session::setFlash('flash', count($turmaIds) > 0
                ? 'Professor vinculado a ' . count($turmaIds) . ' turma(s) com sucesso.'
                : 'Vínculos removidos com sucesso.');
        } catch (\Throwable $e) {
            $pdo->rollBack();
            error_log('[VINCULAR] Erro: ' . $e->getMessage());
            Session::setFlash('flash', 'Erro ao salvar vínculo.');
        }

        $this->redirect('/admin/professores');
    }

    public function perfil(): void
    {
        $authUser = Session::get('user');
        $userId = (int) ($authUser['id'] ?? 0);

        if (!$this->isStaff() || $userId <= 0) {
            Session::setFlash('flash', 'Acesso negado.');
            $this->redirect('/admin/login');
        }

        $usuario = $this->usuarioService->findUsuario($userId);

        if (!$usuario || ((string) ($usuario['tipo'] ?? '')) !== 'professor') {
            Session::setFlash('flash', 'Acesso negado.');
            $this->redirect('/admin');
        }

        try {
            $endereco = $this->enderecoRepository->findByTipoAndFk('professor', $userId);
        } catch (\Throwable) {
            $endereco = null;
        }

        $social = [];
        try {
            $pdo = Database::connection();
            if ($pdo instanceof \PDO) {
                $stmt = $pdo->prepare('SELECT id, tipo, rede, link_perfil FROM social WHERE tipo = :tipo AND id_fk = :id_fk ORDER BY rede ASC');
                $stmt->bindValue(':tipo', 'professor', \PDO::PARAM_STR);
                $stmt->bindValue(':id_fk', $userId, \PDO::PARAM_INT);
                $stmt->execute();
                $social = $stmt->fetchAll() ?: [];
            }
        } catch (\Throwable) {
            $social = [];
        }

        $curriculo = null;
        try {
            $pdo = Database::connection();
            if ($pdo instanceof \PDO) {
                $stmt = $pdo->prepare('SELECT id, resumo, conteudo FROM curriculo WHERE tipo = :tipo AND id_fk = :id_fk AND ativo = :ativo LIMIT 1');
                $stmt->bindValue(':tipo', 'professor', \PDO::PARAM_STR);
                $stmt->bindValue(':id_fk', $userId, \PDO::PARAM_INT);
                $stmt->bindValue(':ativo', 1, \PDO::PARAM_INT);
                $stmt->execute();
                $row = $stmt->fetch();
                $curriculo = $row ?: null;
            }
        } catch (\Throwable) {
            $curriculo = null;
        }

        $this->render('pages/admin/professores/perfil', [
            'title' => 'Meu Perfil',
            'currentRoute' => '/admin/professores/perfil',
            'usuario' => $usuario,
            'professor' => $usuario,
            'endereco' => $endereco,
            'social' => $social,
            'curriculo' => $curriculo,
        ], 'admin');
    }

    public function turmas(): void
    {
        $authUser = Session::get('user');
        $userId = (int) ($authUser['id'] ?? 0);

        if (!$this->isStaff() || $userId <= 0) {
            Session::setFlash('flash', 'Acesso negado.');
            $this->redirect('/admin/login');
        }

        // Inclui vínculo geral da turma e vínculo específico por disciplina
        // (turma_disciplina_professor), usado em graduação e pós-graduação.
        $turmas = $this->turmaService->turmasDoProfessor($userId, 200, null);

        $this->render('pages/admin/professores/turmas', [
            'title' => 'Minhas Turmas',
            'currentRoute' => '/admin/professores/turmas',
            'turmas' => $turmas,
        ], 'admin');
    }

    public function social(): void
    {
        $authUser = Session::get('user');
        $userId = (int) ($authUser['id'] ?? 0);

        if (!$this->isStaff() || $userId <= 0) {
            Session::setFlash('flash', 'Acesso negado.');
            $this->redirect('/admin/login');
        }

        $usuario = $this->usuarioService->findUsuario($userId);
        if (!$usuario || ((string) ($usuario['tipo'] ?? '')) !== 'professor') {
            Session::setFlash('flash', 'Acesso negado.');
            $this->redirect('/admin');
        }

        $social = [];
        try {
            $pdo = Database::connection();
            if ($pdo instanceof \PDO) {
                $stmt = $pdo->prepare('SELECT id, rede, link_perfil FROM social WHERE tipo = :tipo AND id_fk = :id_fk ORDER BY rede ASC');
                $stmt->bindValue(':tipo', 'professor', \PDO::PARAM_STR);
                $stmt->bindValue(':id_fk', $userId, \PDO::PARAM_INT);
                $stmt->execute();
                $social = $stmt->fetchAll() ?: [];
            }
        } catch (\Throwable) {
            $social = [];
        }

        $this->render('pages/admin/professores/social', [
            'title' => 'Redes Sociais',
            'currentRoute' => '/admin/professores/social',
            'social' => $social,
            'usuario' => $usuario,
        ], 'admin');
    }

    public function salvarSocial(): void
    {
        $authUser = Session::get('user');
        $userId = (int) ($authUser['id'] ?? 0);

        if (!$this->isStaff() || $userId <= 0) {
            Session::setFlash('flash', 'Acesso negado.');
            $this->redirect('/admin/login');
        }

        $rede = trim((string) $this->input('rede', ''));
        $link = trim((string) $this->input('link_perfil', ''));

        if ($rede === '' || $link === '') {
            Session::setFlash('flash', 'Preencha a rede e o link.');
            $this->redirect('/admin/professores/social');
            return;
        }

        try {
            $pdo = Database::connection();
            if ($pdo instanceof \PDO) {
                $stmt = $pdo->prepare('INSERT INTO social (id_fk, tipo, rede, link_perfil) VALUES (:id_fk, :tipo, :rede, :link_perfil)');
                $stmt->bindValue(':id_fk', $userId, \PDO::PARAM_INT);
                $stmt->bindValue(':tipo', 'professor', \PDO::PARAM_STR);
                $stmt->bindValue(':rede', $rede, \PDO::PARAM_STR);
                $stmt->bindValue(':link_perfil', $link, \PDO::PARAM_STR);
                $stmt->execute();

                $this->logService->log('criar', 'social', (int) $pdo->lastInsertId(), "Rede social adicionada: $rede");
            }

            $action = (string) $this->input('action', '');
            if ($action === 'add_another') {
                Session::setFlash('flash', 'Rede social cadastrada. Adicione outra.');
                $this->redirect('/admin/professores/social');
            } else {
                Session::setFlash('flash', 'Rede social cadastrada com sucesso.');
                $this->redirect('/admin/professores/perfil');
            }
        } catch (\Throwable $e) {
            error_log('[SOCIAL] Erro ao salvar: ' . $e->getMessage());
            Session::setFlash('flash', 'Erro ao cadastrar rede social.');
            $this->redirect('/admin/professores/social');
        }
    }

    public function deletarSocial(): void
    {
        $authUser = Session::get('user');
        $userId = (int) ($authUser['id'] ?? 0);

        if (!$this->isStaff() || $userId <= 0) {
            http_response_code(403);
            echo json_encode(['erro' => 'Acesso negado.']);
            return;
        }

        $id = (int) $this->input('id', 0);
        if ($id <= 0) {
            http_response_code(400);
            echo json_encode(['erro' => 'ID inválido.']);
            return;
        }

        try {
            $pdo = Database::connection();
            if ($pdo instanceof \PDO) {
                $stmt = $pdo->prepare('DELETE FROM social WHERE id = :id AND id_fk = :id_fk AND tipo = :tipo');
                $stmt->bindValue(':id', $id, \PDO::PARAM_INT);
                $stmt->bindValue(':id_fk', $userId, \PDO::PARAM_INT);
                $stmt->bindValue(':tipo', 'professor', \PDO::PARAM_STR);
                $stmt->execute();

                if ($stmt->rowCount() > 0) {
                    $this->logService->log('excluir', 'social', $id, "Rede social excluída");
                    echo json_encode(['sucesso' => true]);
                } else {
                    http_response_code(404);
                    echo json_encode(['erro' => 'Registro não encontrado.']);
                }
            }
        } catch (\Throwable $e) {
            error_log('[SOCIAL] Erro ao excluir: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['erro' => 'Erro ao excluir.']);
        }
    }

    public function curriculo(): void
    {
        if (!$this->isStaff()) {
            Session::setFlash('flash', 'Acesso negado.');
            $this->redirect('/admin/login');
        }

        $authUser = Session::get('user');
        $authRole = (string) ($authUser['role'] ?? '');
        $isSelf = ((string) ($authUser['tipo'] ?? $authUser['role'] ?? '')) === 'professor';

        $userId = (int) ($this->input('id', 0) ?: ($_GET['id'] ?? 0));
        if ($userId <= 0 || ($isSelf && $authRole !== 'admin' && $authRole !== 'operador')) {
            $userId = (int) ($authUser['id'] ?? 0);
        }

        if ($userId <= 0) {
            Session::setFlash('flash', 'Acesso negado.');
            $this->redirect('/admin/login');
        }

        $usuario = $this->usuarioService->findUsuario($userId);
        if (!$usuario || ((string) ($usuario['tipo'] ?? '')) !== 'professor') {
            Session::setFlash('flash', 'Professor não encontrado.');
            $this->redirect('/admin/professores');
        }

        $curriculo = null;
        try {
            $pdo = Database::connection();
            if ($pdo instanceof \PDO) {
                $stmt = $pdo->prepare('SELECT id, resumo, conteudo FROM curriculo WHERE tipo = :tipo AND id_fk = :id_fk AND ativo = :ativo LIMIT 1');
                $stmt->bindValue(':tipo', 'professor', \PDO::PARAM_STR);
                $stmt->bindValue(':id_fk', $userId, \PDO::PARAM_INT);
                $stmt->bindValue(':ativo', 1, \PDO::PARAM_INT);
                $stmt->execute();
                $row = $stmt->fetch();
                $curriculo = $row ?: null;
            }
        } catch (\Throwable) {
            $curriculo = null;
        }

        $backRoute = $authRole === 'admin' || $authRole === 'operador' ? '/admin/professores' : '/admin/professores/perfil';

        $this->render('pages/admin/professores/curriculo', [
            'title' => 'Currículo — ' . ($usuario['nome'] ?? ''),
            'currentRoute' => '/admin/professores/curriculo',
            'curriculo' => $curriculo,
            'usuario' => $usuario,
            'professorId' => $userId,
            'backRoute' => $backRoute,
        ], 'admin');
    }

    public function salvarCurriculo(): void
    {
        if (!$this->isStaff()) {
            Session::setFlash('flash', 'Acesso negado.');
            $this->redirect('/admin/login');
        }

        $authUser = Session::get('user');
        $authRole = (string) ($authUser['role'] ?? '');
        $isSelf = ((string) ($authUser['tipo'] ?? $authUser['role'] ?? '')) === 'professor';

        $userId = (int) $this->input('id', 0);
        if ($userId <= 0 || ($isSelf && $authRole !== 'admin' && $authRole !== 'operador')) {
            $userId = (int) ($authUser['id'] ?? 0);
        }

        if ($userId <= 0) {
            Session::setFlash('flash', 'Acesso negado.');
            $this->redirect('/admin/login');
        }

        $conteudo = (string) $this->input('conteudo', '');
        $resumo = trim((string) $this->input('resumo', ''));

        if ($conteudo === '') {
            Session::setFlash('flash', 'O conteúdo do currículo não pode ficar vazio.');
            $this->redirect('/admin/professores/curriculo?id=' . $userId);
            return;
        }

        try {
            $pdo = Database::connection();
            if ($pdo instanceof \PDO) {
                $stmt = $pdo->prepare('SELECT id FROM curriculo WHERE tipo = :tipo AND id_fk = :id_fk AND ativo = :ativo LIMIT 1');
                $stmt->bindValue(':tipo', 'professor', \PDO::PARAM_STR);
                $stmt->bindValue(':id_fk', $userId, \PDO::PARAM_INT);
                $stmt->bindValue(':ativo', 1, \PDO::PARAM_INT);
                $stmt->execute();
                $existing = $stmt->fetch();

                if ($existing) {
                    $upd = $pdo->prepare('UPDATE curriculo SET resumo = :resumo, conteudo = :conteudo WHERE id = :id');
                    $upd->bindValue(':resumo', $resumo, \PDO::PARAM_STR);
                    $upd->bindValue(':conteudo', $conteudo, \PDO::PARAM_STR);
                    $upd->bindValue(':id', (int) $existing['id'], \PDO::PARAM_INT);
                    $upd->execute();
                    $this->logService->log('atualizar', 'curriculo', (int) $existing['id'], 'Currículo atualizado');
                } else {
                    $ins = $pdo->prepare('INSERT INTO curriculo (id_fk, tipo, resumo, conteudo, ativo) VALUES (:id_fk, :tipo, :resumo, :conteudo, :ativo)');
                    $ins->bindValue(':id_fk', $userId, \PDO::PARAM_INT);
                    $ins->bindValue(':tipo', 'professor', \PDO::PARAM_STR);
                    $ins->bindValue(':resumo', $resumo, \PDO::PARAM_STR);
                    $ins->bindValue(':conteudo', $conteudo, \PDO::PARAM_STR);
                    $ins->bindValue(':ativo', 1, \PDO::PARAM_INT);
                    $ins->execute();
                    $this->logService->log('criar', 'curriculo', (int) $pdo->lastInsertId(), 'Currículo criado');
                }
            }

            Session::setFlash('flash', 'Currículo salvo com sucesso.');
            $backRoute = $authRole === 'admin' || $authRole === 'operador' ? '/admin/professores' : '/admin/professores/perfil';
            $this->redirect($backRoute);
        } catch (\Throwable $e) {
            error_log('[CURRICULO] Erro: ' . $e->getMessage());
            Session::setFlash('flash', 'Erro ao salvar currículo.');
            $this->redirect('/admin/professores/curriculo?id=' . $userId);
        }
    }

    public function videos(): void
    {
        if (!$this->isStaff()) {
            Session::setFlash('flash', 'Acesso negado.');
            $this->redirect('/admin/login');
        }

        $turmaId = (int) ($this->input('turma_id', 0) ?: ($_GET['turma_id'] ?? 0));

        $pdo = Database::connection();
        $turma = null;
        $videos = [];
        if ($pdo instanceof \PDO) {
            try {
                $stmt = $pdo->prepare('SELECT t.*, c.nome AS curso_nome FROM turmas t LEFT JOIN cursos c ON t.id_curso = c.id WHERE t.id = :id');
                $stmt->bindValue(':id', $turmaId, \PDO::PARAM_INT);
                $stmt->execute();
                $turma = $stmt->fetch() ?: null;
            } catch (\Throwable $e) {
                error_log('[VIDEOS] Erro ao buscar turma: ' . $e->getMessage());
            }

            if (!$turma) {
                Session::setFlash('flash', 'Turma não encontrada.');
                $this->redirect('/admin/professores/turmas');
                return;
            }

            try {
                $stmtMat = $pdo->prepare("SELECT m.id, m.titulo, m.link, m.created_at, t.nome AS turma_nome"
                    . " FROM material m"
                    . " JOIN turmas t ON m.id_fk = t.id"
                    . " WHERE m.tipo = ? AND m.id_fk = ?"
                    . " ORDER BY m.created_at DESC");
                $stmtMat->bindValue(1, 'video', \PDO::PARAM_STR);
                $stmtMat->bindValue(2, $turmaId, \PDO::PARAM_INT);
                $stmtMat->execute();
                $videos = $stmtMat->fetchAll() ?: [];
            } catch (\Throwable $e) {
                error_log('[VIDEOS] Erro ao listar vídeos: ' . $e->getMessage());
                $videos = [];
            }
        }

        $this->render('pages/admin/professores/videos', [
            'title' => 'Vídeos - ' . ($turma['nome'] ?? 'Turma'),
            'currentRoute' => '/admin/professores/videos',
            'turma' => $turma,
            'materiais' => $videos,
        ], 'admin');
    }

    public function salvarVideo(): void
    {
        if (!$this->isStaff()) {
            Session::setFlash('flash', 'Acesso negado.');
            $this->redirect('/admin/login');
        }

        $idTurma = (int) $this->input('id_fk', 0);
        $tipo = trim((string) $this->input('tipo', ''));
        $link = trim((string) $this->input('link', ''));
        $titulo = trim((string) $this->input('titulo', ''));

        if ($idTurma <= 0 || $tipo === '' || $link === '' || $titulo === '') {
            Session::setFlash('flash', 'Preencha todos os campos.');
            $this->redirect('/admin/professores/videos?turma_id=' . $idTurma);
            return;
        }

        try {
            $pdo = Database::connection();
            if ($pdo instanceof \PDO) {
                $stmt = $pdo->prepare('INSERT INTO material (tipo, link, id_fk, titulo) VALUES (:tipo, :link, :id_fk, :titulo)');
                $stmt->bindValue(':tipo', $tipo, \PDO::PARAM_STR);
                $stmt->bindValue(':link', $link, \PDO::PARAM_STR);
                $stmt->bindValue(':id_fk', $idTurma, \PDO::PARAM_INT);
                $stmt->bindValue(':titulo', $titulo, \PDO::PARAM_STR);
                $stmt->execute();

                $this->logService->log('criar', 'video', (int) $pdo->lastInsertId(), "Vídeo adicionado à turma $idTurma");
            }
            Session::setFlash('flash', 'Vídeo adicionado com sucesso.');
        } catch (\Throwable $e) {
            error_log('[VIDEOS] Erro ao salvar: ' . $e->getMessage());
            Session::setFlash('flash', 'Erro ao salvar vídeo.');
        }

        $this->redirect('/admin/professores/videos?turma_id=' . $idTurma);
    }

    public function deletarVideo(): void
    {
        if (!$this->isStaff()) {
            http_response_code(403);
            echo json_encode(['erro' => 'Acesso negado.']);
            return;
        }

        $id = (int) $this->input('id', 0);
        if ($id <= 0) {
            http_response_code(400);
            echo json_encode(['erro' => 'ID inválido.']);
            return;
        }

        try {
            $pdo = Database::connection();
            if ($pdo instanceof \PDO) {
                $stmt = $pdo->prepare('DELETE FROM material WHERE id = :id AND tipo = :tipo');
                $stmt->bindValue(':id', $id, \PDO::PARAM_INT);
                $stmt->bindValue(':tipo', 'video', \PDO::PARAM_STR);
                $stmt->execute();

                if ($stmt->rowCount() > 0) {
                    $this->logService->log('excluir', 'video', $id, 'Vídeo excluído');
                    echo json_encode(['sucesso' => true]);
                } else {
                    http_response_code(404);
                    echo json_encode(['erro' => 'Vídeo não encontrado.']);
                }
            }
        } catch (\Throwable $e) {
            error_log('[VIDEOS] Erro ao excluir: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['erro' => 'Erro ao excluir vídeo.']);
        }
    }

    public function drive(): void
    {
        if (!$this->isStaff()) {
            Session::setFlash('flash', 'Acesso negado.');
            $this->redirect('/admin/login');
        }

        $turmaId = (int) ($this->input('turma_id', 0) ?: ($_GET['turma_id'] ?? 0));

        $pdo = Database::connection();
        $turma = null;
        $arquivos = [];
        $driveArquivos = [];
        $storageConectado = false;
        $pastaDrive = null;

        if ($pdo instanceof \PDO) {
            try {
                $stmt = $pdo->prepare('SELECT t.*, c.nome AS curso_nome FROM turmas t LEFT JOIN cursos c ON t.id_curso = c.id WHERE t.id = :id');
                $stmt->bindValue(':id', $turmaId, \PDO::PARAM_INT);
                $stmt->execute();
                $turma = $stmt->fetch() ?: null;
            } catch (\Throwable $e) {
                error_log('[DRIVE] Erro ao buscar turma: ' . $e->getMessage());
            }

            if (!$turma) {
                Session::setFlash('flash', 'Turma não encontrada.');
                $this->redirect('/admin/professores/turmas');
                return;
            }

            try {
                $stmtMat = $pdo->prepare("SELECT m.id, m.titulo, m.link, m.created_at, t.nome AS turma_nome"
                    . " FROM material m"
                    . " JOIN turmas t ON m.id_fk = t.id"
                    . " WHERE m.tipo = ? AND m.id_fk = ?"
                    . " ORDER BY m.created_at DESC");
                $stmtMat->bindValue(1, 'drive', \PDO::PARAM_STR);
                $stmtMat->bindValue(2, $turmaId, \PDO::PARAM_INT);
                $stmtMat->execute();
                $arquivos = $stmtMat->fetchAll() ?: [];
            } catch (\Throwable $e) {
                error_log('[DRIVE] Erro ao listar arquivos: ' . $e->getMessage());
                $arquivos = [];
            }
        }

        $authUser = Session::get('user');
        $professorId = (int) ($authUser['id'] ?? 0);
        $storage = new StorageService();
        $storageConectado = $storage->isConnected();

        if ($storageConectado) {
            try {
                $storageDriveRepo = new StorageDriveRepository();
                $pastaDrive = $storageDriveRepo->findByRegistro(StorageService::GROUP_PROFESSORES, $professorId);

                $folderId = $pastaDrive['folder_id'] ?? '';
                if ($folderId === '') {
                    $folderId = $storage->ensureRegistroFolder(
                        StorageService::GROUP_PROFESSORES,
                        (string) $professorId,
                        (string) ($authUser['name'] ?? '')
                    );
                }

                if ($folderId !== '') {
                    $driveArquivos = $storage->listFolder($folderId);
                }
            } catch (\Throwable $e) {
                error_log('[DRIVE] Erro ao listar Google Drive: ' . $e->getMessage());
                $driveArquivos = [];
            }
        }

        $this->render('pages/admin/professores/drive', [
            'title' => 'Google Drive - ' . ($turma['nome'] ?? 'Turma'),
            'currentRoute' => '/admin/professores/drive',
            'turma' => $turma,
            'materiais' => $arquivos,
            'driveArquivos' => $driveArquivos,
            'storageConectado' => $storageConectado,
            'pastaDrive' => $pastaDrive,
        ], 'admin');
    }

    public function salvarDrive(): void
    {
        if (!$this->isStaff()) {
            Session::setFlash('flash', 'Acesso negado.');
            $this->redirect('/admin/login');
        }

        $idTurma = (int) $this->input('id_fk', 0);
        if ($idTurma <= 0) {
            $idTurma = (int) ($_GET['turma_id'] ?? 0);
        }

        $titulo = trim((string) $this->input('titulo', ''));
        $file = $_FILES['arquivo'] ?? null;

        if ($idTurma <= 0) {
            Session::setFlash('flash', 'Turma inválida.');
            $this->redirect('/admin/professores/turmas');
            return;
        }

        if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
            Session::setFlash('flash', 'Selecione um arquivo PDF para enviar.');
            $this->redirect('/admin/professores/drive?turma_id=' . $idTurma);
            return;
        }

        $originalName = (string) ($file['name'] ?? '');
        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

        if ($extension !== 'pdf') {
            Session::setFlash('flash', 'Apenas arquivos PDF são permitidos.');
            $this->redirect('/admin/professores/drive?turma_id=' . $idTurma);
            return;
        }

        if ((int) ($file['size'] ?? 0) > 20 * 1024 * 1024) {
            Session::setFlash('flash', 'O arquivo deve ter no máximo 20MB.');
            $this->redirect('/admin/professores/drive?turma_id=' . $idTurma);
            return;
        }

        if ($titulo === '') {
            $titulo = pathinfo($originalName, PATHINFO_FILENAME);
        }

        $authUser = Session::get('user');
        $professorId = (int) ($authUser['id'] ?? 0);

        try {
            $storage = new StorageService();
            if (!$storage->isConnected()) {
                Session::setFlash('flash', 'Storage não conectado. Conecte em /admin/storage.');
                $this->redirect('/admin/professores/drive?turma_id=' . $idTurma);
                return;
            }

            $storageDriveRepo = new StorageDriveRepository();

            $estrutura = $storage->ensureStructure();
            $folderId = (string) ($estrutura['materiais'] ?? '');

            if ($folderId !== '') {
                $pastaMateriais = $storageDriveRepo->findByGrupo(StorageService::GROUP_MATERIAIS);
                if ($pastaMateriais === null) {
                    $storageDriveRepo->create([
                        'id_grupo' => StorageService::GROUP_MATERIAIS,
                        'id_registro' => 0,
                        'folder_id' => $folderId,
                        'folder_name' => 'Materiais',
                        'folder_link' => $storage->generateViewLinkByFileId($folderId),
                        'tipo' => 'grupo',
                        'nivel' => 1,
                    ]);
                } elseif ((string) ($pastaMateriais['folder_id'] ?? '') !== $folderId) {
                    $storageDriveRepo->updateFolderId((int) $pastaMateriais['id'], $folderId);
                }
            }

            if ($folderId === '') {
                Session::setFlash('flash', 'Pasta de Materiais no Drive não encontrada.');
                $this->redirect('/admin/professores/drive?turma_id=' . $idTurma);
                return;
            }

            $timestamp = date('YmdHis');
            $nomeDrive = sprintf('MAT_%s_%s_%s.pdf', $idTurma, $professorId, $timestamp);
            $resultado = $storage->uploadFile($file, $folderId, $nomeDrive);

            $pdo = Database::connection();
            if ($pdo instanceof \PDO) {
                $stmt = $pdo->prepare('INSERT INTO material (tipo, link, id_fk, titulo) VALUES (:tipo, :link, :id_fk, :titulo)');
                $stmt->bindValue(':tipo', 'drive', \PDO::PARAM_STR);
                $stmt->bindValue(':link', $resultado['link'], \PDO::PARAM_STR);
                $stmt->bindValue(':id_fk', $idTurma, \PDO::PARAM_INT);
                $stmt->bindValue(':titulo', $titulo, \PDO::PARAM_STR);
                $stmt->execute();

                $this->logService->log('criar', 'drive', (int) $pdo->lastInsertId(), "Material enviado à turma $idTurma: $titulo");
            }

            Session::setFlash('flash', 'Material enviado com sucesso.');
        } catch (\Throwable $e) {
            error_log('[DRIVE] Erro ao salvar material (turma ' . $idTurma . '): ' . $e->getMessage() . ' @ ' . $e->getFile() . ':' . $e->getLine());
            Session::setFlash('flash', 'Erro ao salvar material: ' . $e->getMessage());
        }

        $this->redirect('/admin/professores/drive?turma_id=' . $idTurma);
    }

    public function fotos(): void
    {
        if (!$this->isStaff()) {
            Session::setFlash('flash', 'Acesso negado.');
            $this->redirect('/admin/login');
        }

        $id = (int) ($this->input('id', 0) ?: ($_GET['id'] ?? 0));
        $professor = $this->usuarioService->findUsuario($id);

        if (!$professor || ((string) ($professor['tipo'] ?? '')) !== 'professor') {
            Session::setFlash('flash', 'Professor não encontrado.');
            $this->redirect('/admin/professores');
            return;
        }

        $imagens = $this->imageService->listarPorFk('usuarios', $id);

        $this->render('pages/admin/professores/fotos', [
            'title' => 'Fotos — ' . ($professor['nome'] ?? ''),
            'currentRoute' => '/admin/professores/fotos',
            'professor' => $professor,
            'idFk' => $id,
            'tabelaFk' => 'usuarios',
            'imagens' => $imagens,
        ], 'admin');
    }

    public function uploadFoto(): void
    {
        if (!$this->isStaff()) {
            Session::setFlash('flash', 'Acesso negado.');
            $this->redirect('/admin/login');
        }

        $idFk = (int) $this->input('id_fk', 0);
        $tabelaFk = trim((string) $this->input('tabela_fk', ''));
        $legenda = trim((string) $this->input('legenda', ''));

        if ($idFk <= 0 || $tabelaFk === '') {
            Session::setFlash('flash', 'Parâmetros inválidos.');
            $this->redirect('/admin/professores');
        }

        $path = '';
        $file = $_FILES['imagem'] ?? null;

        if ($file && $file['error'] === UPLOAD_ERR_OK) {
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            if (in_array($ext, $allowed, true)) {
                $filename = 'professor-' . $idFk . '-' . time() . '-' . mt_rand(100, 999) . '.' . $ext;
                $destDir = dirname(__DIR__, 3) . '/public/assets/img/professor';
                if (!is_dir($destDir)) {
                    mkdir($destDir, 0755, true);
                }
                $destPath = $destDir . '/' . $filename;
                if (move_uploaded_file($file['tmp_name'], $destPath)) {
                    $path = 'assets/img/professor/' . $filename;
                }
            }
        }

        if ($path === '') {
            Session::setFlash('flash', 'Erro ao fazer upload da foto. Verifique o formato e tamanho.');
            $this->redirect('/admin/professores/fotos?id=' . $idFk);
            return;
        }

        $this->imageService->salvar($tabelaFk, $idFk, $path, $legenda ?: null);
        $this->logService->log('criar', 'imagem', 0, 'Foto adicionada ao professor ID ' . $idFk);

        Session::setFlash('flash', 'Foto salva com sucesso.');
        $this->redirect('/admin/professores/fotos?id=' . $idFk);
    }

    public function deletarFoto(): void
    {
        if (!$this->isStaff()) {
            http_response_code(403);
            echo json_encode(['erro' => 'Acesso negado.']);
            return;
        }

        $id = (int) ($this->input('id', 0) ?: ($_POST['id'] ?? 0));
        $idFk = (int) ($this->input('id_fk', 0) ?: ($_POST['id_fk'] ?? 0));
        $tabelaFk = trim((string) ($this->input('tabela_fk', '') ?: ($_POST['tabela_fk'] ?? '')));

        if ($id <= 0 || $idFk <= 0 || $tabelaFk === '') {
            http_response_code(400);
            echo json_encode(['erro' => 'Parâmetros inválidos.']);
            return;
        }

        $this->imageService->deletar($id);
        $this->logService->log('deletar', 'imagem', $id, 'Foto removida do professor ID ' . $idFk);
        echo json_encode(['sucesso' => true]);
    }

    public function detalhe(): void
    {
        if (!$this->isStaff()) {
            Session::setFlash('flash', 'Acesso negado.');
            $this->redirect('/admin/login');
        }

        $id = (int) ($this->input('id', 0) ?: ($_GET['id'] ?? 0));
        $professor = $this->usuarioService->findUsuario($id);

        if (!$professor || ((string) ($professor['tipo'] ?? '')) !== 'professor') {
            Session::setFlash('flash', 'Professor não encontrado.');
            $this->redirect('/admin/professores');
            return;
        }

        try {
            $endereco = $this->enderecoRepository->findByTipoAndFk('professor', $id);
        } catch (\Throwable) {
            $endereco = null;
        }

        $social = [];
        try {
            $pdo = Database::connection();
            if ($pdo instanceof \PDO) {
                $stmt = $pdo->prepare('SELECT id, rede, link_perfil FROM social WHERE tipo = :tipo AND id_fk = :id_fk ORDER BY rede ASC');
                $stmt->bindValue(':tipo', 'professor', \PDO::PARAM_STR);
                $stmt->bindValue(':id_fk', $id, \PDO::PARAM_INT);
                $stmt->execute();
                $social = $stmt->fetchAll() ?: [];
            }
        } catch (\Throwable) {
            $social = [];
        }

        $curriculo = null;
        try {
            $pdo = Database::connection();
            if ($pdo instanceof \PDO) {
                $stmt = $pdo->prepare('SELECT id, resumo, conteudo FROM curriculo WHERE tipo = :tipo AND id_fk = :id_fk AND ativo = :ativo LIMIT 1');
                $stmt->bindValue(':tipo', 'professor', \PDO::PARAM_STR);
                $stmt->bindValue(':id_fk', $id, \PDO::PARAM_INT);
                $stmt->bindValue(':ativo', 1, \PDO::PARAM_INT);
                $stmt->execute();
                $row = $stmt->fetch();
                $curriculo = $row ?: null;
            }
        } catch (\Throwable) {
            $curriculo = null;
        }

        $imagens = $this->imageService->listarPorFk('usuarios', $id);

        $turmas = [];
        try {
            $pdo = Database::connection();
            if ($pdo instanceof \PDO) {
                $stmt = $pdo->prepare(
                    'SELECT t.id, t.nome, t.data_inicio, t.data_fim, t.ativo, c.nome AS curso_nome'
                    . ' FROM turma_professor tp'
                    . ' JOIN turmas t ON tp.id_turma = t.id'
                    . ' LEFT JOIN cursos c ON t.id_curso = c.id'
                    . ' WHERE tp.id_usuario = :id AND tp.status = :status'
                    . ' ORDER BY t.nome ASC'
                );
                $stmt->bindValue(':id', $id, \PDO::PARAM_INT);
                $stmt->bindValue(':status', 'A', \PDO::PARAM_STR);
                $stmt->execute();
                $turmas = $stmt->fetchAll() ?: [];
            }
        } catch (\Throwable) {
            $turmas = [];
        }

        $documentos = [];
        try {
            $pdo = Database::connection();
            if ($pdo instanceof \PDO) {
                $grupoProfessores = \App\Services\Storage\StorageService::GROUP_PROFESSORES;
                $stmt = $pdo->prepare(
                    'SELECT t.id AS tipo_id, t.descricao AS tipo_descricao, t.obrigatorio, t.ordem,'
                    . ' d.id AS documento_id, d.nome_original, d.nome_drive, d.mime_type, d.tamanho, d.versao, d.created_at, d.file_id, d.status, d.observacao'
                    . ' FROM documento_tipo t'
                    . ' LEFT JOIN documento d ON d.id = ('
                    . '   SELECT d2.id FROM documento d2'
                    . '   WHERE d2.id_tipo = t.id AND d2.id_registro = :id_registro AND d2.ativo = 1'
                    . '   ORDER BY d2.versao DESC, d2.id DESC LIMIT 1'
                    . ' )'
                    . ' WHERE t.id_grupo = :id_grupo AND t.ativo = 1'
                    . ' ORDER BY t.ordem ASC, t.descricao ASC'
                );
                $stmt->bindValue(':id_registro', $id, \PDO::PARAM_INT);
                $stmt->bindValue(':id_grupo', $grupoProfessores, \PDO::PARAM_INT);
                $stmt->execute();
                $rows = $stmt->fetchAll();
                $documentos = is_array($rows) ? $rows : [];
            }
        } catch (\Throwable $e) {
            error_log('[PROFESSOR DETALHE DOCUMENTOS] Erro: ' . $e->getMessage());
            $documentos = [];
        }

        $this->render('pages/admin/professores/detalhe', [
            'title' => 'Detalhes do Professor — ' . ($professor['nome'] ?? ''),
            'currentRoute' => '/admin/professores/detalhe',
            'professor' => $professor,
            'endereco' => $endereco,
            'social' => $social,
            'curriculo' => $curriculo,
            'imagens' => $imagens,
            'turmas' => $turmas,
            'documentos' => $documentos,
        ], 'admin');
    }

    public function buscarCep(): void
    {
        ini_set('display_errors', '0');
        error_reporting(0);

        error_log('[buscarCep] Iniciando busca de CEP');

        $cep = preg_replace('/[^0-9]/', '', (string) ($_GET['cep'] ?? ''));

        if (strlen($cep) !== 8) {
            http_response_code(400);
            header('Content-Type: application/json');
            echo json_encode(['erro' => 'CEP inválido']);
            return;
        }

        $url = "https://viacep.com.br/ws/{$cep}/json/";

        error_log('[buscarCep] URL: ' . $url);

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 5,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTPHEADER => ['Accept: application/json'],
            CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
        $error = curl_error($ch);
        $errno = curl_errno($ch);
        curl_close($ch);

        error_log('[buscarCep] HTTP Code: ' . $httpCode);
        error_log('[buscarCep] Content-Type: ' . ($contentType ?? 'null'));
        error_log('[buscarCep] Error: ' . $error);
        error_log('[buscarCep] Response: ' . substr($response, 0, 500));

        if ($response === false || $httpCode !== 200) {
            http_response_code(502);
            header('Content-Type: application/json');
            echo json_encode([
                'erro' => 'Falha ao consultar ViaCEP',
                'detalhe' => $error ?: "HTTP {$httpCode}",
                'errno' => $errno,
                'url' => $url,
                'curl_enabled' => function_exists('curl_init')
            ]);
            return;
        }

        if (!$response || $response === '' || $response[0] !== '{') {
            http_response_code(502);
            header('Content-Type: application/json');
            echo json_encode([
                'erro' => 'Resposta inesperada da ViaCEP',
                'content_type' => $contentType,
                'prefix' => substr($response, 0, 200),
                'http_code' => $httpCode,
            ]);
            return;
        }

        $data = json_decode($response, true);

        if (!is_array($data) || isset($data['erro'])) {
            http_response_code(404);
            header('Content-Type: application/json');
            echo json_encode(['erro' => 'CEP não encontrado']);
            return;
        }

        header('Content-Type: application/json');
        echo json_encode([
            'logradouro' => $data['logradouro'] ?? '',
            'bairro' => $data['bairro'] ?? '',
            'cidade' => $data['localidade'] ?? '',
            'uf' => $data['uf'] ?? '',
        ]);
    }

    public function documentos(): void
    {
        $authUser = Session::get('user');
        $userId = (int) ($authUser['id'] ?? 0);

        if (!$this->isStaff() || $userId <= 0) {
            Session::setFlash('flash', 'Acesso negado.');
            $this->redirect('/admin/login');
        }

        $usuario = $this->usuarioService->findUsuario($userId);
        if (!$usuario || ((string) ($usuario['tipo'] ?? '')) !== 'professor') {
            Session::setFlash('flash', 'Acesso negado.');
            $this->redirect('/admin');
        }

        $grupoProfessores = StorageService::GROUP_PROFESSORES;
        $pdo = Database::connection();
        $nomeProfessor = (string) ($usuario['nome'] ?? '');

        $storage = new StorageService();
        $pasta = null;

        if ($pdo instanceof \PDO) {
            $storageDriveRepo = new StorageDriveRepository();
            $pasta = $storageDriveRepo->findByRegistro($grupoProfessores, $userId);

            if ($pasta === null && $storage->isConnected()) {
                try {
                    $folderId = $storage->ensureRegistroFolder($grupoProfessores, (string) $userId, $nomeProfessor);
                    if ($folderId !== '') {
                        $storageDriveRepo->create([
                            'id_grupo' => $grupoProfessores,
                            'id_registro' => $userId,
                            'folder_id' => $folderId,
                            'folder_name' => sprintf('%06d-%s', $userId, $nomeProfessor),
                            'folder_link' => $storage->generateViewLinkByFileId($folderId),
                            'tipo' => 'registro',
                            'nivel' => 2,
                        ]);
                        $pasta = $storageDriveRepo->findByRegistro($grupoProfessores, $userId);
                    }
                } catch (StorageException $e) {
                    error_log('[PROFESSOR DOCUMENTOS] Erro ao criar pasta: ' . $e->getMessage());
                } catch (\Throwable $e) {
                    error_log('[PROFESSOR DOCUMENTOS] Erro ao criar pasta: ' . $e->getMessage());
                }
            }
        }

        $documentos = [];
        if ($pdo instanceof \PDO) {
            try {
                $stmt = $pdo->prepare(
                    'SELECT t.id AS tipo_id, t.descricao AS tipo_descricao, t.obrigatorio, t.ordem,'
                    . ' d.id AS documento_id, d.nome_original, d.nome_drive, d.mime_type, d.tamanho, d.versao, d.created_at, d.file_id, d.status, d.observacao'
                    . ' FROM documento_tipo t'
                    . ' LEFT JOIN documento d ON d.id = ('
                    . '   SELECT d2.id FROM documento d2'
                    . '   WHERE d2.id_tipo = t.id AND d2.id_registro = :id_registro AND d2.ativo = 1'
                    . '   ORDER BY d2.versao DESC, d2.id DESC LIMIT 1'
                    . ' )'
                    . ' WHERE t.id_grupo = :id_grupo AND t.ativo = 1'
                    . ' ORDER BY t.ordem ASC, t.descricao ASC'
                );
                $stmt->bindValue(':id_registro', $userId, \PDO::PARAM_INT);
                $stmt->bindValue(':id_grupo', $grupoProfessores, \PDO::PARAM_INT);
                $stmt->execute();
                $rows = $stmt->fetchAll();
                $documentos = is_array($rows) ? $rows : [];
            } catch (\Throwable $e) {
                error_log('[PROFESSOR DOCUMENTOS] Erro: ' . $e->getMessage());
                $documentos = [];
            }
        }

        $this->render('pages/admin/professores/documentos', [
            'title' => 'Meus Documentos',
            'currentRoute' => '/admin/professores/documentos',
            'documentos' => $documentos,
            'pasta' => $pasta,
            'storageConectado' => $storage->isConnected(),
            'storageErro' => $storage->isConnected() ? null : 'Storage não conectado.',
        ], 'admin');
    }

    public function uploadDocumento(): void
    {
        $authUser = Session::get('user');
        $userId = (int) ($authUser['id'] ?? 0);

        if (!$this->isStaff() || $userId <= 0) {
            Session::setFlash('flash', 'Acesso negado.');
            $this->redirect('/admin/login');
        }

        $grupoProfessores = StorageService::GROUP_PROFESSORES;
        $tipoId = (int) $this->input('id_tipo', 0);
        $file = $_FILES['arquivo'] ?? null;

        if ($tipoId <= 0 || !$file) {
            Session::setFlash('flash', 'Selecione o tipo de documento e o arquivo.');
            $this->redirect('/admin/professores/documentos');
            return;
        }

        if ($file['error'] !== UPLOAD_ERR_OK) {
            Session::setFlash('flash', 'Erro no upload do arquivo.');
            $this->redirect('/admin/professores/documentos');
            return;
        }

        $originalName = (string) ($file['name'] ?? '');
        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        $allowed = ['pdf', 'png', 'jpg', 'jpeg'];

        if (!in_array($extension, $allowed, true)) {
            Session::setFlash('flash', 'Formato não permitido. Use PDF, PNG, JPG ou JPEG.');
            $this->redirect('/admin/professores/documentos');
            return;
        }

        if ((int) ($file['size'] ?? 0) > 20 * 1024 * 1024) {
            Session::setFlash('flash', 'O arquivo deve ter no máximo 20MB.');
            $this->redirect('/admin/professores/documentos');
            return;
        }

        $pdo = Database::connection();
        if (!$pdo instanceof \PDO) {
            Session::setFlash('flash', 'Erro de conexão com o banco de dados.');
            $this->redirect('/admin/professores/documentos');
            return;
        }

        $tipo = null;
        try {
            $stmt = $pdo->prepare('SELECT id, descricao FROM documento_tipo WHERE id = :id AND id_grupo = :id_grupo AND ativo = 1 LIMIT 1');
            $stmt->bindValue(':id', $tipoId, \PDO::PARAM_INT);
            $stmt->bindValue(':id_grupo', $grupoProfessores, \PDO::PARAM_INT);
            $stmt->execute();
            $tipo = $stmt->fetch() ?: null;
        } catch (\Throwable $e) {
            error_log('[PROFESSOR UPLOAD DOC] Erro: ' . $e->getMessage());
        }

        if (!$tipo) {
            Session::setFlash('flash', 'Tipo de documento inválido.');
            $this->redirect('/admin/professores/documentos');
            return;
        }

        $storageDriveRepo = new StorageDriveRepository();
        $pasta = $storageDriveRepo->findByRegistro($grupoProfessores, $userId);

        if ($pasta === null) {
            Session::setFlash('flash', 'Pasta do professor no Drive não encontrada. Fale com a secretaria.');
            $this->redirect('/admin/professores/documentos');
            return;
        }

        $storage = new StorageService();
        if (!$storage->isConnected()) {
            Session::setFlash('flash', 'Storage não conectado. Tente novamente mais tarde.');
            $this->redirect('/admin/professores/documentos');
            return;
        }

        $documentoAtual = null;
        try {
            $stmt = $pdo->prepare('SELECT id, versao, status FROM documento WHERE id_tipo = :id_tipo AND id_registro = :id_registro AND ativo = 1 ORDER BY versao DESC, id DESC LIMIT 1');
            $stmt->bindValue(':id_tipo', $tipoId, \PDO::PARAM_INT);
            $stmt->bindValue(':id_registro', $userId, \PDO::PARAM_INT);
            $stmt->execute();
            $documentoAtual = $stmt->fetch() ?: null;
        } catch (\Throwable $e) {
            error_log('[PROFESSOR UPLOAD DOC] Erro: ' . $e->getMessage());
        }

        if ($documentoAtual !== null) {
            $statusAtual = (string) ($documentoAtual['status'] ?? '');
            if ($statusAtual === 'em_analise' || $statusAtual === 'aprovado') {
                Session::setFlash('flash', 'Este documento está em análise/aprovado e não pode ser substituído.');
                $this->redirect('/admin/professores/documentos');
                return;
            }
        }

        $versao = $documentoAtual !== null ? ((int) ($documentoAtual['versao'] ?? 1)) + 1 : 1;
        $timestamp = date('YmdHis');
        $tipoSigla = $this->tipoSigla((string) $tipo['descricao']);
        $nomeDrive = sprintf('%s_%s.%s', $tipoSigla, $timestamp, $extension);

        try {
            if ($documentoAtual !== null) {
                $documentoRepository = new \App\Repositories\DocumentoRepository();
                $documentoRepository->markSubstituido((int) $documentoAtual['id']);
            }

            $result = $storage->upload(
                $file,
                $grupoProfessores,
                $userId,
                $tipoId,
                (string) ($pasta['folder_id'] ?? ''),
                $nomeDrive,
                'enviado'
            );

            $this->logService->log('upload', 'documento', (int) $result['id'], "Professor enviou documento {$tipo['descricao']} (v{$versao})");
            Session::setFlash('flash', 'Documento enviado com sucesso.');
        } catch (StorageException $e) {
            error_log('[PROFESSOR UPLOAD DOC] Storage: ' . $e->getMessage());
            Session::setFlash('flash', 'Erro ao enviar o documento: ' . $e->getMessage());
        } catch (\Throwable $e) {
            error_log('[PROFESSOR UPLOAD DOC] Erro: ' . $e->getMessage());
            Session::setFlash('flash', 'Erro ao enviar o documento.');
        }

        $this->redirect('/admin/professores/documentos');
    }

    public function visualizarDocumento(): void
    {
        $authUser = Session::get('user');
        $userId = (int) ($authUser['id'] ?? 0);

        if (!$this->isStaff() || $userId <= 0) {
            Session::setFlash('flash', 'Acesso negado.');
            $this->redirect('/admin/login');
        }

        $documentoId = (int) ($_GET['id'] ?? 0);

        $pdo = Database::connection();
        $documento = null;
        if ($pdo instanceof \PDO) {
            try {
                $stmt = $pdo->prepare('SELECT id, id_registro, nome_original FROM documento WHERE id = :id AND ativo = 1 LIMIT 1');
                $stmt->bindValue(':id', $documentoId, \PDO::PARAM_INT);
                $stmt->execute();
                $documento = $stmt->fetch() ?: null;
            } catch (\Throwable $e) {
                error_log('[PROFESSOR VIEW DOC] Erro: ' . $e->getMessage());
            }
        }

        if ($documento === null || (int) ($documento['id_registro'] ?? 0) !== $userId) {
            Session::setFlash('flash', 'Documento não encontrado.');
            $this->redirect('/admin/professores/documentos');
            return;
        }

        try {
            $storage = new StorageService();
            $link = $storage->generateViewLink($documentoId);
            $this->logService->log('visualizar', 'documento', $documentoId, "Professor visualizou documento: {$documento['nome_original']}");
            $this->redirect($link);
        } catch (\Throwable $e) {
            Session::setFlash('flash', 'Erro ao visualizar o documento.');
            $this->redirect('/admin/professores/documentos');
        }
    }

    public function baixarDocumento(): void
    {
        $authUser = Session::get('user');
        $userId = (int) ($authUser['id'] ?? 0);

        if (!$this->isStaff() || $userId <= 0) {
            Session::setFlash('flash', 'Acesso negado.');
            $this->redirect('/admin/login');
        }

        $documentoId = (int) ($_GET['id'] ?? 0);

        $pdo = Database::connection();
        $documento = null;
        if ($pdo instanceof \PDO) {
            try {
                $stmt = $pdo->prepare('SELECT id, id_registro, nome_original, mime_type FROM documento WHERE id = :id AND ativo = 1 LIMIT 1');
                $stmt->bindValue(':id', $documentoId, \PDO::PARAM_INT);
                $stmt->execute();
                $documento = $stmt->fetch() ?: null;
            } catch (\Throwable $e) {
                error_log('[PROFESSOR DOWNLOAD DOC] Erro: ' . $e->getMessage());
            }
        }

        if ($documento === null || (int) ($documento['id_registro'] ?? 0) !== $userId) {
            Session::setFlash('flash', 'Documento não encontrado.');
            $this->redirect('/admin/professores/documentos');
            return;
        }

        try {
            $storage = new StorageService();
            $conteudo = $storage->download($documentoId);

            $mime = (string) ($documento['mime_type'] ?? 'application/octet-stream');
            $nome = (string) ($documento['nome_original'] ?? 'documento');

            header('Content-Type: ' . $mime);
            header('Content-Disposition: attachment; filename="' . $this->safeFilename($nome) . '"');
            header('Content-Length: ' . strlen($conteudo));
            echo $conteudo;
            exit;
        } catch (\Throwable $e) {
            Session::setFlash('flash', 'Erro ao baixar o documento.');
            $this->redirect('/admin/professores/documentos');
        }
    }

    public function excluirDocumento(): void
    {
        $authUser = Session::get('user');
        $userId = (int) ($authUser['id'] ?? 0);

        if (!$this->isStaff() || $userId <= 0) {
            Session::setFlash('flash', 'Acesso negado.');
            $this->redirect('/admin/login');
        }

        $documentoId = (int) $this->input('id', 0);

        $pdo = Database::connection();
        $documento = null;
        if ($pdo instanceof \PDO) {
            try {
                $stmt = $pdo->prepare('SELECT id, id_registro, status, nome_original FROM documento WHERE id = :id AND ativo = 1 LIMIT 1');
                $stmt->bindValue(':id', $documentoId, \PDO::PARAM_INT);
                $stmt->execute();
                $documento = $stmt->fetch() ?: null;
            } catch (\Throwable $e) {
                error_log('[PROFESSOR DELETE DOC] Erro: ' . $e->getMessage());
            }
        }

        if ($documento === null || (int) ($documento['id_registro'] ?? 0) !== $userId) {
            Session::setFlash('flash', 'Documento não encontrado.');
            $this->redirect('/admin/professores/documentos');
            return;
        }

        $status = (string) ($documento['status'] ?? '');
        if (in_array($status, ['em_analise', 'aprovado', 'rejeitado'], true)) {
            Session::setFlash('flash', 'Este documento já foi analisado e não pode ser excluído.');
            $this->redirect('/admin/professores/documentos');
            return;
        }

        try {
            $storage = new StorageService();
            $storage->delete($documentoId);
            $this->logService->log('excluir', 'documento', $documentoId, "Professor excluiu documento: {$documento['nome_original']}");
            Session::setFlash('flash', 'Documento excluído com sucesso.');
        } catch (\Throwable $e) {
            Session::setFlash('flash', 'Erro ao excluir o documento.');
        }

        $this->redirect('/admin/professores/documentos');
    }

    private function tipoSigla(string $descricao): string
    {
        $sigla = preg_replace('/[^A-Za-z0-9]/', '', $descricao);
        $sigla = strtoupper((string) $sigla);
        return $sigla !== '' ? $sigla : 'DOC';
    }

    private function safeFilename(string $name): string
    {
        $name = str_replace('"', '', $name);
        $name = preg_replace('/[^\w\.\- ]+/u', '_', $name);
        return trim((string) $name);
    }

    private function isStaff(): bool
    {
        return (new \App\Services\AuthService())->isStaff();
    }
}
