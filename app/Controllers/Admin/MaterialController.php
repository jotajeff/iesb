<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Database;
use App\Repositories\StorageDriveRepository;
use App\Services\LogService;
use App\Services\Storage\StorageService;
use App\Support\Session;
use PDO;

final class MaterialController extends Controller
{
    private LogService $logService;

    public function __construct()
    {
        $this->logService = new LogService();
    }

    public function index(): void
    {
        if (!$this->podeGerenciar()) {
            Session::setFlash('flash', 'Acesso negado.');
            $this->redirect('/admin/login');
        }

        $turmas = $this->turmas();
        $idTurma = (int) ($_GET['id_turma'] ?? 0);
        if ($idTurma > 0 && !in_array($idTurma, array_column($turmas, 'id'), true)) {
            $idTurma = 0;
        }

        $materiais = [];
        $pdo = Database::connection();
        if ($pdo instanceof PDO) {
            try {
                $sql = 'SELECT m.id, m.tipo, m.titulo, m.link, m.id_fk, m.id_disciplina, m.created_at, t.nome AS turma_nome, d.nome AS disciplina_nome'
                    . ' FROM material m'
                    . ' JOIN turmas t ON t.id = m.id_fk AND t.ativo = 1'
                    . ' LEFT JOIN disciplina d ON d.id = m.id_disciplina';
                if ($idTurma > 0) {
                    $sql .= ' WHERE m.ativo = 1 AND m.id_fk = :id_turma';
                } else {
                    $sql .= ' WHERE m.ativo = 1';
                }
                $sql .= ' ORDER BY m.created_at DESC, m.id DESC';

                $stmt = $pdo->prepare($sql);
                if ($idTurma > 0) {
                    $stmt->bindValue(':id_turma', $idTurma, PDO::PARAM_INT);
                }
                $stmt->execute();
                $materiais = $stmt->fetchAll() ?: [];
            } catch (\Throwable $e) {
                error_log('[MATERIAL] Erro em index: ' . $e->getMessage());
                $materiais = [];
            }
        }

        $this->render('pages/admin/material/index', [
            'title' => 'Materiais',
            'currentRoute' => '/admin/material',
            'materiais' => $materiais,
            'turmas' => $turmas,
            'idTurma' => $idTurma,
        ], 'admin');
    }

    public function novo(): void
    {
        if (!$this->podeGerenciar()) {
            Session::setFlash('flash', 'Acesso negado.');
            $this->redirect('/admin/login');
        }

        $tipo = trim((string) ($_GET['tipo'] ?? ''));
        if ($tipo !== '' && !in_array($tipo, ['video', 'pdf'], true)) {
            $tipo = '';
        }

        $this->render('pages/admin/material/novo', [
            'title' => 'Novo Material',
            'currentRoute' => '/admin/material',
            'tipo' => $tipo,
            'turmas' => $this->turmas(),
        ], 'admin');
    }

    public function salvar(): void
    {
        if (!$this->podeGerenciar()) {
            Session::setFlash('flash', 'Acesso negado.');
            $this->redirect('/admin/login');
        }

        $tipo = trim((string) $this->input('tipo', ''));
        $idTurma = (int) $this->input('id_fk', 0);
        $idDisciplina = (int) $this->input('id_disciplina', 0);
        $titulo = trim((string) $this->input('titulo', ''));

        if (!in_array($tipo, ['video', 'pdf'], true) || $idTurma <= 0 || $titulo === '') {
            Session::setFlash('flash', 'Selecione o tipo, a turma e informe o título.');
            $this->redirect('/admin/material/novo');
            return;
        }

        $idDisciplina = $idDisciplina > 0 ? $idDisciplina : 0;

        try {
            if ($tipo === 'video') {
                $link = trim((string) $this->input('link', ''));
                if ($link === '') {
                    Session::setFlash('flash', 'Informe o link do vídeo.');
                    $this->redirect('/admin/material/novo?tipo=video');
                    return;
                }

                $id = $this->inserirMaterial('video', $link, $idTurma, $titulo, $idDisciplina);
                if ($id > 0) {
                    $this->logService->log('criar', 'video', $id, "Vídeo publicado na turma $idTurma: $titulo");
                    Session::setFlash('flash', 'Vídeo publicado com sucesso.');
                } else {
                    Session::setFlash('flash', 'Erro ao publicar o vídeo.');
                }
                $this->redirect('/admin/material');
                return;
            }

            $file = $_FILES['arquivo'] ?? null;
            if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
                Session::setFlash('flash', 'Selecione um arquivo PDF para enviar.');
                $this->redirect('/admin/material/novo?tipo=pdf');
                return;
            }

            $originalName = (string) ($file['name'] ?? '');
            $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
            if ($extension !== 'pdf') {
                Session::setFlash('flash', 'Apenas arquivos PDF são permitidos.');
                $this->redirect('/admin/material/novo?tipo=pdf');
                return;
            }
            if ((int) ($file['size'] ?? 0) > 20 * 1024 * 1024) {
                Session::setFlash('flash', 'O arquivo deve ter no máximo 20MB.');
                $this->redirect('/admin/material/novo?tipo=pdf');
                return;
            }

            $link = $this->uploadPdfDrive($file, $originalName, $idTurma);
            if ($link === '') {
                $this->redirect('/admin/material/novo?tipo=pdf');
                return;
            }

            $id = $this->inserirMaterial('drive', $link, $idTurma, $titulo, $idDisciplina);
            if ($id > 0) {
                $this->logService->log('criar', 'drive', $id, "Material PDF publicado na turma $idTurma: $titulo");
                Session::setFlash('flash', 'Material publicado com sucesso.');
            } else {
                Session::setFlash('flash', 'Erro ao publicar o material.');
            }
            $this->redirect('/admin/material');
        } catch (\Throwable $e) {
            error_log('[MATERIAL] Erro em salvar: ' . $e->getMessage());
            Session::setFlash('flash', 'Erro ao publicar o material.');
            $this->redirect('/admin/material/novo');
        }
    }

    public function deletar(): void
    {
        if (!$this->podeGerenciar()) {
            Session::setFlash('flash', 'Acesso negado.');
            $this->redirect('/admin/login');
        }

        $id = (int) $this->input('id', 0);
        if ($id <= 0) {
            Session::setFlash('flash', 'Material inválido.');
            $this->redirect('/admin/material');
            return;
        }

        $pdo = Database::connection();
        if ($pdo instanceof PDO) {
            try {
                $stmt = $pdo->prepare('UPDATE material SET ativo = 0 WHERE id = :id');
                $stmt->bindValue(':id', $id, PDO::PARAM_INT);
                $stmt->execute();
                $this->logService->log('desativar', 'material', $id, 'Material desativado');
                Session::setFlash('flash', 'Material removido.');
            } catch (\Throwable $e) {
                error_log('[MATERIAL] Erro em deletar: ' . $e->getMessage());
                Session::setFlash('flash', 'Erro ao remover o material.');
            }
        }

        $this->redirect('/admin/material');
    }

    private function inserirMaterial(string $tipo, string $link, int $idTurma, string $titulo, int $idDisciplina = 0): int
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return 0;
        }

        try {
            $stmt = $pdo->prepare(
                'INSERT INTO material (tipo, link, id_fk, id_disciplina, titulo) VALUES (:tipo, :link, :id_fk, :id_disciplina, :titulo)'
            );
            $stmt->bindValue(':tipo', $tipo, PDO::PARAM_STR);
            $stmt->bindValue(':link', $link, PDO::PARAM_STR);
            $stmt->bindValue(':id_fk', $idTurma, PDO::PARAM_INT);
            $stmt->bindValue(':id_disciplina', $idDisciplina, PDO::PARAM_INT);
            $stmt->bindValue(':titulo', $titulo, PDO::PARAM_STR);
            $stmt->execute();

            return (int) $pdo->lastInsertId();
        } catch (\Throwable $e) {
            error_log('[MATERIAL] Erro ao inserir: ' . $e->getMessage());
            return 0;
        }
    }

    public function editar(): void
    {
        if (!$this->podeGerenciar()) {
            Session::setFlash('flash', 'Acesso negado.');
            $this->redirect('/admin/login');
        }

        $id = (int) ($_GET['id'] ?? 0);
        $material = $id > 0 ? $this->buscarMaterial($id) : null;

        if (!$material) {
            Session::setFlash('flash', 'Material não encontrado.');
            $this->redirect('/admin/material');
            return;
        }

        $this->render('pages/admin/material/editar', [
            'title' => 'Editar Material',
            'currentRoute' => '/admin/material',
            'material' => $material,
            'turmas' => $this->turmas(),
            'disciplinas' => $this->disciplinasDaTurma((int) ($material['id_fk'] ?? 0)),
        ], 'admin');
    }

    public function atualizar(): void
    {
        if (!$this->podeGerenciar()) {
            Session::setFlash('flash', 'Acesso negado.');
            $this->redirect('/admin/login');
        }

        $id = (int) $this->input('id', 0);
        $idTurma = (int) $this->input('id_fk', 0);
        $idDisciplina = (int) $this->input('id_disciplina', 0);
        $titulo = trim((string) $this->input('titulo', ''));

        if ($id <= 0 || $idTurma <= 0 || $titulo === '') {
            Session::setFlash('flash', 'Dados inválidos.');
            $this->redirect('/admin/material/editar?id=' . $id);
            return;
        }

        $material = $this->buscarMaterial($id);
        if (!$material) {
            Session::setFlash('flash', 'Material não encontrado.');
            $this->redirect('/admin/material');
            return;
        }

        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            Session::setFlash('flash', 'Sem conexão com o banco de dados.');
            $this->redirect('/admin/material');
            return;
        }

        try {
            $sql = 'UPDATE material SET titulo = :titulo, id_fk = :id_fk, id_disciplina = :id_disciplina, updated_at = NOW()';
            if ((string) ($material['tipo'] ?? '') === 'video') {
                $sql .= ', link = :link';
            }
            $sql .= ' WHERE id = :id';

            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':titulo', $titulo, PDO::PARAM_STR);
            $stmt->bindValue(':id_fk', $idTurma, PDO::PARAM_INT);
            $stmt->bindValue(':id_disciplina', $idDisciplina > 0 ? $idDisciplina : 0, PDO::PARAM_INT);
            if ((string) ($material['tipo'] ?? '') === 'video') {
                $stmt->bindValue(':link', trim((string) $this->input('link', '')), PDO::PARAM_STR);
            }
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            $this->logService->log('atualizar', 'material', $id, "Material atualizado: $titulo");
            Session::setFlash('flash', 'Material atualizado com sucesso.');
        } catch (\Throwable $e) {
            error_log('[MATERIAL] Erro em atualizar: ' . $e->getMessage());
            Session::setFlash('flash', 'Erro ao atualizar o material.');
        }

        $this->redirect('/admin/material');
    }

    public function ajaxDisciplinas(): void
    {
        if (!$this->podeGerenciar()) {
            $this->json(['erro' => 'Acesso negado.'], 403);
            return;
        }

        $idTurma = (int) ($_GET['id_turma'] ?? 0);
        $this->json($this->disciplinasDaTurma($idTurma));
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function disciplinasDaTurma(int $idTurma): array
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO || $idTurma <= 0) {
            return [];
        }

        try {
            $stmt = $pdo->prepare(
                'SELECT DISTINCT d.id, d.nome AS disciplina_nome'
                . ' FROM turma_disciplina td'
                . ' INNER JOIN disciplina d ON d.id = td.id_disciplina AND d.ativo = 1'
                . ' WHERE td.id_turma = :id_turma AND td.ativo = 1'
                . ' ORDER BY d.nome ASC'
            );
            $stmt->bindValue(':id_turma', $idTurma, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll() ?: [];
        } catch (\Throwable $e) {
            error_log('[MATERIAL] Erro em disciplinasDaTurma: ' . $e->getMessage());
            return [];
        }
    }

    private function buscarMaterial(int $id): ?array
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO || $id <= 0) {
            return null;
        }

        try {
            $stmt = $pdo->prepare('SELECT * FROM material WHERE id = :id AND ativo = 1 LIMIT 1');
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $row = $stmt->fetch();
            return is_array($row) ? $row : null;
        } catch (\Throwable $e) {
            error_log('[MATERIAL] Erro em buscarMaterial: ' . $e->getMessage());
            return null;
        }
    }

    private function uploadPdfDrive(array $file, string $originalName, int $idTurma): string
    {
        try {
            $storage = new StorageService();
            if (!$storage->isConnected()) {
                Session::setFlash('flash', 'Storage não conectado. Conecte em /admin/storage.');
                return '';
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
                return '';
            }

            $user = Session::get('user');
            $usuarioId = (int) ($user['id'] ?? 0);
            $timestamp = date('YmdHis');
            $nomeDrive = sprintf('MAT_%s_%s_%s.pdf', $idTurma, $usuarioId, $timestamp);

            $resultado = $storage->uploadFile($file, $folderId, $nomeDrive);

            return (string) ($resultado['link'] ?? '');
        } catch (\Throwable $e) {
            error_log('[MATERIAL] Erro no upload do PDF: ' . $e->getMessage());
            Session::setFlash('flash', 'Erro ao enviar o PDF para o Drive.');
            return '';
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function turmas(): array
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return [];
        }

        try {
            $stmt = $pdo->query(
                'SELECT t.id, t.nome AS turma_nome, c.nome AS curso_nome'
                . ' FROM turmas t'
                . ' LEFT JOIN cursos c ON c.id = t.id_curso'
                . ' WHERE t.ativo = 1'
                . ' ORDER BY c.nome ASC, t.nome ASC'
            );
            $rows = $stmt->fetchAll();
            return is_array($rows) ? $rows : [];
        } catch (\Throwable $e) {
            error_log('[MATERIAL] Erro em turmas: ' . $e->getMessage());
            return [];
        }
    }

    private function podeGerenciar(): bool
    {
        $user = Session::get('user');
        $role = (string) ($user['role'] ?? $user['tipo'] ?? '');
        return in_array($role, ['admin', 'operador'], true);
    }
}