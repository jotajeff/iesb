<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Database;
use App\Services\UsuarioService;
use App\Services\LogService;
use App\Services\TurmaService;
use App\Repositories\EnderecoRepository;
use App\Support\Session;

final class ProfessorController extends Controller
{
    private UsuarioService $usuarioService;
    private LogService $logService;
    private TurmaService $turmaService;
    private EnderecoRepository $enderecoRepository;

    public function __construct()
    {
        $this->usuarioService = new UsuarioService();
        $this->logService = new LogService();
        $this->turmaService = new TurmaService();
        $this->enderecoRepository = new EnderecoRepository();
    }

    public function index(): void
    {
        if (!$this->isStaff()) {
            Session::setFlash('flash', 'Faça login como admin ou operador para acessar os professores.');
            $this->redirect('/admin/login');
        }

        $professores = $this->usuarioService->usuariosPorTipo('professor');

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

        $this->render('pages/admin/professores/index', [
            'title' => 'Professores',
            'currentRoute' => '/admin/professores',
            'professores' => $professores,
            'enderecos' => $enderecos,
            'vinculoCounts' => $vinculoCounts,
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
        $ativo = (string) $this->input('ativo', '1');

        if ($nome === '' || $email === '' || $senha === '') {
            Session::setFlash('flash', 'Preencha nome, email e senha.');
            $this->redirect('/admin/professores/novo');
            return;
        }

        $usuarioId = $this->usuarioService->criarUsuario($nome, $email, $senha, 'professor', $ativo, $telefone);
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

        $this->usuarioService->atualizarUsuario($id, '', $ativo, $nome, '', '', $telefone);
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
                $stmt = $pdo->prepare('SELECT id, conteudo FROM curriculo WHERE tipo = :tipo AND id_fk = :id_fk AND ativo = :ativo LIMIT 1');
                $stmt->bindValue(':tipo', 'professor', \PDO::PARAM_STR);
                $stmt->bindValue(':id_fk', $userId, \PDO::PARAM_INT);
                $stmt->bindValue(':ativo', 'S', \PDO::PARAM_STR);
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

        $pdo = Database::connection();
        $turmas = [];
        if ($pdo instanceof \PDO) {
            try {
                $sql = 'SELECT t.id, t.nome, t.data_inicio, t.data_fim, t.ativa,'
                     . ' c.nome AS curso_nome, n.nome AS nivel_nome,'
                     . ' (SELECT COUNT(*) FROM matriculas WHERE id_turma = t.id) AS total_inscritos'
                     . ' FROM turma_professor tp'
                     . ' JOIN turmas t ON tp.id_turma = t.id'
                     . ' LEFT JOIN cursos_iesb c ON t.id_curso = c.id'
                     . ' LEFT JOIN nivel n ON c.nivel = n.id'
                     . ' WHERE tp.id_usuario = :id_usuario AND tp.status = :status'
                     . ' ORDER BY t.nome ASC';

                $stmt = $pdo->prepare($sql);
                $stmt->bindValue(':id_usuario', $userId, \PDO::PARAM_INT);
                $stmt->bindValue(':status', 'A', \PDO::PARAM_STR);
                $stmt->execute();
                $turmas = $stmt->fetchAll() ?: [];
            } catch (\Throwable $e) {
                error_log('[PROFESSOR TURMAS] Erro: ' . $e->getMessage());
                $turmas = [];
            }
        }

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

        $curriculo = null;
        try {
            $pdo = Database::connection();
            if ($pdo instanceof \PDO) {
                $stmt = $pdo->prepare('SELECT id, conteudo FROM curriculo WHERE tipo = :tipo AND id_fk = :id_fk AND ativo = :ativo LIMIT 1');
                $stmt->bindValue(':tipo', 'professor', \PDO::PARAM_STR);
                $stmt->bindValue(':id_fk', $userId, \PDO::PARAM_INT);
                $stmt->bindValue(':ativo', 'S', \PDO::PARAM_STR);
                $stmt->execute();
                $row = $stmt->fetch();
                $curriculo = $row ?: null;
            }
        } catch (\Throwable) {
            $curriculo = null;
        }

        $this->render('pages/admin/professores/curriculo', [
            'title' => 'Currículo',
            'currentRoute' => '/admin/professores/curriculo',
            'curriculo' => $curriculo,
            'usuario' => $usuario,
        ], 'admin');
    }

    public function salvarCurriculo(): void
    {
        $authUser = Session::get('user');
        $userId = (int) ($authUser['id'] ?? 0);

        if (!$this->isStaff() || $userId <= 0) {
            Session::setFlash('flash', 'Acesso negado.');
            $this->redirect('/admin/login');
        }

        $conteudo = (string) $this->input('conteudo', '');

        if ($conteudo === '') {
            Session::setFlash('flash', 'O conteúdo do currículo não pode ficar vazio.');
            $this->redirect('/admin/professores/curriculo');
            return;
        }

        try {
            $pdo = Database::connection();
            if ($pdo instanceof \PDO) {
                $stmt = $pdo->prepare('SELECT id FROM curriculo WHERE tipo = :tipo AND id_fk = :id_fk AND ativo = :ativo LIMIT 1');
                $stmt->bindValue(':tipo', 'professor', \PDO::PARAM_STR);
                $stmt->bindValue(':id_fk', $userId, \PDO::PARAM_INT);
                $stmt->bindValue(':ativo', 'S', \PDO::PARAM_STR);
                $stmt->execute();
                $existing = $stmt->fetch();

                if ($existing) {
                    $upd = $pdo->prepare('UPDATE curriculo SET conteudo = :conteudo WHERE id = :id');
                    $upd->bindValue(':conteudo', $conteudo, \PDO::PARAM_STR);
                    $upd->bindValue(':id', (int) $existing['id'], \PDO::PARAM_INT);
                    $upd->execute();
                    $this->logService->log('atualizar', 'curriculo', (int) $existing['id'], 'Currículo atualizado');
                } else {
                    $ins = $pdo->prepare('INSERT INTO curriculo (id_fk, tipo, conteudo, ativo) VALUES (:id_fk, :tipo, :conteudo, :ativo)');
                    $ins->bindValue(':id_fk', $userId, \PDO::PARAM_INT);
                    $ins->bindValue(':tipo', 'professor', \PDO::PARAM_STR);
                    $ins->bindValue(':conteudo', $conteudo, \PDO::PARAM_STR);
                    $ins->bindValue(':ativo', 'S', \PDO::PARAM_STR);
                    $ins->execute();
                    $this->logService->log('criar', 'curriculo', (int) $pdo->lastInsertId(), 'Currículo criado');
                }
            }

            Session::setFlash('flash', 'Currículo salvo com sucesso.');
            $this->redirect('/admin/professores/perfil');
        } catch (\Throwable $e) {
            error_log('[CURRICULO] Erro: ' . $e->getMessage());
            Session::setFlash('flash', 'Erro ao salvar currículo.');
            $this->redirect('/admin/professores/curriculo');
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
                $stmt = $pdo->prepare('SELECT t.*, c.nome AS curso_nome FROM turmas t LEFT JOIN cursos_iesb c ON t.id_curso = c.id WHERE t.id = :id');
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
                $stmtMat = $pdo->prepare("SELECT m.id, m.titulo, m.link, m.criado_em, t.nome AS turma_nome"
                    . " FROM material m"
                    . " JOIN turmas t ON m.id_fk = t.id"
                    . " WHERE m.tipo = ? AND m.id_fk = ?"
                    . " ORDER BY m.criado_em DESC");
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
        if ($pdo instanceof \PDO) {
            try {
                $stmt = $pdo->prepare('SELECT t.*, c.nome AS curso_nome FROM turmas t LEFT JOIN cursos_iesb c ON t.id_curso = c.id WHERE t.id = :id');
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
                $stmtMat = $pdo->prepare("SELECT m.id, m.titulo, m.link, m.criado_em, t.nome AS turma_nome"
                    . " FROM material m"
                    . " JOIN turmas t ON m.id_fk = t.id"
                    . " WHERE m.tipo = ? AND m.id_fk = ?"
                    . " ORDER BY m.criado_em DESC");
                $stmtMat->bindValue(1, 'drive', \PDO::PARAM_STR);
                $stmtMat->bindValue(2, $turmaId, \PDO::PARAM_INT);
                $stmtMat->execute();
                $arquivos = $stmtMat->fetchAll() ?: [];
            } catch (\Throwable $e) {
                error_log('[DRIVE] Erro ao listar arquivos: ' . $e->getMessage());
                $arquivos = [];
            }
        }

        $this->render('pages/admin/professores/drive', [
            'title' => 'Google Drive - ' . ($turma['nome'] ?? 'Turma'),
            'currentRoute' => '/admin/professores/drive',
            'turma' => $turma,
            'materiais' => $arquivos,
        ], 'admin');
    }

    public function salvarDrive(): void
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
            $this->redirect('/admin/professores/drive?turma_id=' . $idTurma);
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

                $this->logService->log('criar', 'drive', (int) $pdo->lastInsertId(), "Arquivo do Drive adicionado à turma $idTurma");
            }
            Session::setFlash('flash', 'Arquivo adicionado com sucesso.');
        } catch (\Throwable $e) {
            error_log('[DRIVE] Erro ao salvar: ' . $e->getMessage());
            Session::setFlash('flash', 'Erro ao salvar arquivo.');
        }

        $this->redirect('/admin/professores/drive?turma_id=' . $idTurma);
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

    private function isStaff(): bool
    {
        return (new \App\Services\AuthService())->isStaff();
    }
}
