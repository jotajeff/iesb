<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Database;
use App\Services\TurmaService;
use App\Services\CursoService;
use App\Services\LogService;
use App\Support\Session;

final class TurmaController extends Controller
{
    private TurmaService $turmaService;
    private CursoService $cursoService;
    private LogService $logService;

    public function __construct()
    {
        $this->turmaService = new TurmaService();
        $this->cursoService = new CursoService();
        $this->logService = new LogService();
    }

    public function index(): void
    {
        if (!$this->isStaff()) {
            Session::setFlash('flash', 'Faça login como admin, operador ou professor para acessar as turmas.');
            $this->redirect('/admin/login');
        }

        $filtroAtivo = (int) ($_GET['ativo'] ?? 1);
        $ativo = $filtroAtivo >= 0 ? $filtroAtivo : null;
        $turmas = $this->turmaService->turmas(200, $ativo);

        $this->render('pages/admin/turmas/index', [
            'title' => 'Turmas',
            'currentRoute' => '/admin/turmas',
            'turmas' => $turmas,
            'filtroAtivo' => $filtroAtivo,
        ], 'admin');
    }

    public function show(): void
    {
        if (!$this->isStaff()) {
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
            'professores' => $this->professoresDaTurma($id),
            'materiais' => $this->materiaisDaTurma($id),
        ], 'admin');
    }

    private function professoresDaTurma(int $idTurma): array
    {
        $pdo = Database::connection();
        if (!$pdo instanceof \PDO) {
            return [];
        }

        try {
            $stmt = $pdo->prepare(
                'SELECT u.id, u.nome, u.email'
                . ' FROM turma_professor tp'
                . ' JOIN usuarios u ON tp.id_usuario = u.id'
                . ' WHERE tp.id_turma = :id_turma AND tp.status = :status'
                . ' ORDER BY u.nome ASC'
            );
            $stmt->bindValue(':id_turma', $idTurma, \PDO::PARAM_INT);
            $stmt->bindValue(':status', 'A', \PDO::PARAM_STR);
            $stmt->execute();
            $rows = $stmt->fetchAll();
            return is_array($rows) ? $rows : [];
        } catch (\Throwable) {
            return [];
        }
    }

    public function novo(): void
    {
        if (!$this->isStaff()) {
            Session::setFlash('flash', 'Acesso negado.');
            $this->redirect('/admin/login');
        }

        $this->render('pages/admin/turmas/new', [
            'title' => 'Nova Turma',
            'currentRoute' => '/admin/turmas/novo',
            'cursos' => $this->cursoService->cursos('asc', 500),
        ], 'admin');
    }

    public function editar(): void
    {
        if (!$this->isStaff()) {
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

    public function salvar(): void
    {
        if (!$this->isStaff()) {
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

    public function atualizar(): void
    {
        if (!$this->isStaff()) {
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

    public function verVideo(): void
    {
        if (!$this->isStaff()) {
            Session::setFlash('flash', 'Faça login como admin, operador ou professor.');
            $this->redirect('/admin/login');
        }

        $materialId = (int) ($_GET['id'] ?? 0);
        $material = $this->buscarMaterial($materialId, 'video');

        if (!$material) {
            Session::setFlash('flash', 'Vídeo não encontrado.');
            $this->redirect('/admin/turmas');
            return;
        }

        $this->render('pages/admin/turmas/ver_video', [
            'title' => $material['titulo'] ?? 'Vídeo',
            'currentRoute' => '/admin/turmas/show',
            'material' => $material,
        ], 'admin');
    }

    public function verDrive(): void
    {
        if (!$this->isStaff()) {
            Session::setFlash('flash', 'Faça login como admin, operador ou professor.');
            $this->redirect('/admin/login');
        }

        $materialId = (int) ($_GET['id'] ?? 0);
        $material = $this->buscarMaterial($materialId, 'drive');

        if (!$material) {
            Session::setFlash('flash', 'Documento não encontrado.');
            $this->redirect('/admin/turmas');
            return;
        }

        $this->render('pages/admin/turmas/ver_drive', [
            'title' => $material['titulo'] ?? 'Drive',
            'currentRoute' => '/admin/turmas/show',
            'material' => $material,
        ], 'admin');
    }

    private function buscarMaterial(int $id, string $tipo): ?array
    {
        $pdo = Database::connection();
        if (!$pdo instanceof \PDO) {
            return null;
        }

        try {
            $stmt = $pdo->prepare(
                'SELECT id, titulo, link, tipo, id_fk, created_at'
                . ' FROM material WHERE id = :id AND tipo = :tipo AND ativo = :ativo'
            );
            $stmt->bindValue(':id', $id, \PDO::PARAM_INT);
            $stmt->bindValue(':tipo', $tipo, \PDO::PARAM_STR);
            $stmt->bindValue(':ativo', 1, \PDO::PARAM_INT);
            $stmt->execute();
            $row = $stmt->fetch();
            return $row ?: null;
        } catch (\Throwable) {
            return null;
        }
    }

    private function materiaisDaTurma(int $idTurma): array
    {
        $pdo = Database::connection();
        if (!$pdo instanceof \PDO) {
            return [];
        }

        try {
            $stmt = $pdo->prepare(
                'SELECT id, tipo, titulo, link, created_at'
                . ' FROM material'
                . ' WHERE id_fk = :id_turma AND ativo = :ativo'
                . ' ORDER BY tipo ASC, titulo ASC'
            );
            $stmt->bindValue(':id_turma', $idTurma, \PDO::PARAM_INT);
            $stmt->bindValue(':ativo', 1, \PDO::PARAM_INT);
            $stmt->execute();
            $rows = $stmt->fetchAll();
            return is_array($rows) ? $rows : [];
        } catch (\Throwable) {
            return [];
        }
    }

    private function isStaff(): bool
    {
        return (new \App\Services\AuthService())->isStaff();
    }
}
