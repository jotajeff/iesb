<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Database;
use App\Repositories\StorageDriveRepository;
use App\Services\AuthService;
use App\Services\LogService;
use App\Services\Storage\StorageException;
use App\Services\Storage\StorageService;
use App\Support\Session;
use PDO;

final class DocumentoController extends Controller
{
    private const GRUPO_SECRETARIA = 7;

    private LogService $logService;

    public function __construct()
    {
        $this->logService = new LogService();
    }

    public function index(): void
    {
        if (!$this->isStaff()) {
            Session::setFlash('flash', 'Acesso negado.');
            $this->redirect('/admin/login');
        }

        $turmas = $this->turmas();
        $idTurma = (int) ($_GET['id_turma'] ?? 0);
        if ($idTurma > 0 && !in_array($idTurma, array_column($turmas, 'id'), true)) {
            $idTurma = 0;
        }

        $documentos = [];
        $pdo = Database::connection();
        if ($pdo instanceof PDO) {
            try {
                $sql = 'SELECT d.id, d.id_registro AS id_aluno, d.id_tipo, d.nome_original, d.nome_drive,'
                    . ' d.mime_type, d.tamanho, d.versao, d.status, d.created_at, d.file_id,'
                    . ' t.descricao AS tipo_descricao, a.nome AS aluno_nome'
                    . ' FROM documento d'
                    . ' LEFT JOIN documento_tipo t ON t.id = d.id_tipo'
                    . ' LEFT JOIN alunos a ON a.id = d.id_registro'
                    . ' WHERE d.id_grupo = :id_grupo AND d.ativo = 1';
                if ($idTurma > 0) {
                    $sql .= ' AND EXISTS (SELECT 1 FROM matricula m WHERE m.id_aluno = d.id_registro AND m.id_turma = :id_turma AND m.ativo = 1)';
                }
                $sql .= ' ORDER BY d.created_at DESC, d.id DESC';

                $stmt = $pdo->prepare($sql);
                $stmt->bindValue(':id_grupo', self::GRUPO_SECRETARIA, PDO::PARAM_INT);
                if ($idTurma > 0) {
                    $stmt->bindValue(':id_turma', $idTurma, PDO::PARAM_INT);
                }
                $stmt->execute();
                $documentos = $stmt->fetchAll() ?: [];
            } catch (\Throwable $e) {
                error_log('[ADMIN DOC] Erro em index: ' . $e->getMessage());
                $documentos = [];
            }
        }

        $this->render('pages/admin/documentos/index', [
            'title' => 'Documentos da Secretaria',
            'currentRoute' => '/admin/documentos',
            'documentos' => $documentos,
            'turmas' => $turmas,
            'idTurma' => $idTurma,
        ], 'admin');
    }

    public function novo(): void
    {
        if (!$this->isStaff()) {
            Session::setFlash('flash', 'Acesso negado.');
            $this->redirect('/admin/login');
        }

        $storage = new StorageService();

        $this->render('pages/admin/documentos/novo', [
            'title' => 'Novo Documento',
            'currentRoute' => '/admin/documentos',
            'turmas' => $this->turmas(),
            'tipos' => $this->tipos(),
            'storageConectado' => $storage->isConnected(),
        ], 'admin');
    }

    public function ajaxAlunos(): void
    {
        if (!$this->isStaff()) {
            $this->json(['erro' => 'Acesso negado.'], 403);
            return;
        }

        $idTurma = (int) ($_GET['id_turma'] ?? 0);
        $this->json($this->alunosDaTurma($idTurma));
    }

    public function upload(): void
    {
        if (!$this->isStaff()) {
            Session::setFlash('flash', 'Acesso negado.');
            $this->redirect('/admin/login');
        }

        $idAluno = (int) $this->input('id_aluno', 0);
        $idTipo = (int) $this->input('id_tipo', 0);
        $file = $_FILES['arquivo'] ?? null;

        if ($idAluno <= 0 || $idTipo <= 0 || !$file || (int) ($file['error'] ?? 1) !== UPLOAD_ERR_OK) {
            Session::setFlash('flash', 'Selecione o aluno, o tipo de documento e o arquivo.');
            $this->redirect('/admin/documentos/novo');
            return;
        }

        $tipo = $this->buscarTipo($idTipo);
        if (!$tipo) {
            Session::setFlash('flash', 'Tipo de documento inválido para a Secretaria.');
            $this->redirect('/admin/documentos/novo');
            return;
        }

        $aluno = $this->buscarAluno($idAluno);
        if (!$aluno) {
            Session::setFlash('flash', 'Aluno não encontrado.');
            $this->redirect('/admin/documentos/novo');
            return;
        }

        $originalName = (string) ($file['name'] ?? '');
        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        $allowed = ['pdf', 'png', 'jpg', 'jpeg'];

        if (!in_array($extension, $allowed, true)) {
            Session::setFlash('flash', 'Formato não permitido. Use PDF, PNG, JPG ou JPEG.');
            $this->redirect('/admin/documentos/novo');
            return;
        }

        if ((int) ($file['size'] ?? 0) > 20 * 1024 * 1024) {
            Session::setFlash('flash', 'O arquivo deve ter no máximo 20MB.');
            $this->redirect('/admin/documentos/novo');
            return;
        }

        $storage = new StorageService();
        if (!$storage->isConnected()) {
            Session::setFlash('flash', 'Storage não conectado. Conecte em /admin/storage.');
            $this->redirect('/admin/documentos/novo');
            return;
        }

        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            Session::setFlash('flash', 'Erro de conexão com o banco de dados.');
            $this->redirect('/admin/documentos/novo');
            return;
        }

        try {
            $storageDriveRepo = new StorageDriveRepository();
            $pasta = $storageDriveRepo->findByRegistro(StorageService::GROUP_ALUNOS, $idAluno);

            if ($pasta === null) {
                $folderId = $storage->ensureRegistroFolder(StorageService::GROUP_ALUNOS, (string) $idAluno, (string) ($aluno['nome'] ?? ''));
                if ($folderId !== '') {
                    $storageDriveRepo->create([
                        'id_grupo' => StorageService::GROUP_ALUNOS,
                        'id_registro' => $idAluno,
                        'folder_id' => $folderId,
                        'folder_name' => sprintf('%06d-%s', $idAluno, (string) ($aluno['nome'] ?? '')),
                        'folder_link' => $storage->generateViewLinkByFileId($folderId),
                        'tipo' => 'registro',
                        'nivel' => 2,
                    ]);
                    $pasta = $storageDriveRepo->findByRegistro(StorageService::GROUP_ALUNOS, $idAluno);
                }
            }

            if ($pasta === null) {
                Session::setFlash('flash', 'Não foi possível preparar a pasta do aluno no Drive.');
                $this->redirect('/admin/documentos/novo');
                return;
            }

            $timestamp = date('YmdHis');
            $sigla = $this->tipoSigla((string) ($tipo['descricao'] ?? ''));
            $nomeDrive = sprintf('%s_%s.%s', $sigla, $timestamp, $extension);

            $result = $storage->upload(
                $file,
                self::GRUPO_SECRETARIA,
                $idAluno,
                $idTipo,
                (string) ($pasta['folder_id'] ?? ''),
                $nomeDrive,
                'enviado'
            );

            $this->logService->log('upload', 'documento', (int) ($result['id'] ?? 0), 'Secretaria enviou documento "' . ($tipo['descricao'] ?? '') . '" para o aluno #' . $idAluno);
            Session::setFlash('flash', 'Documento enviado com sucesso.');
        } catch (StorageException $e) {
            error_log('[ADMIN DOC] Storage: ' . $e->getMessage());
            Session::setFlash('flash', 'Erro ao enviar o documento: ' . $e->getMessage());
        } catch (\Throwable $e) {
            error_log('[ADMIN DOC] Erro: ' . $e->getMessage());
            Session::setFlash('flash', 'Erro ao enviar o documento.');
        }

        $this->redirect('/admin/documentos');
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
            error_log('[ADMIN DOC] Erro em turmas: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function tipos(): array
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return [];
        }

        try {
            $stmt = $pdo->prepare(
                'SELECT id, descricao, obrigatorio, ordem'
                . ' FROM documento_tipo'
                . ' WHERE id_grupo = :id_grupo AND ativo = 1'
                . ' ORDER BY ordem ASC, descricao ASC'
            );
            $stmt->bindValue(':id_grupo', self::GRUPO_SECRETARIA, PDO::PARAM_INT);
            $stmt->execute();
            $rows = $stmt->fetchAll();
            return is_array($rows) ? $rows : [];
        } catch (\Throwable $e) {
            error_log('[ADMIN DOC] Erro em tipos: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function alunosDaTurma(int $idTurma): array
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO || $idTurma <= 0) {
            return [];
        }

        try {
            $stmt = $pdo->prepare(
                'SELECT a.id, a.nome'
                . ' FROM matricula m'
                . ' INNER JOIN alunos a ON a.id = m.id_aluno'
                . ' WHERE m.id_turma = :id_turma AND m.ativo = 1'
                . ' ORDER BY a.nome ASC'
            );
            $stmt->bindValue(':id_turma', $idTurma, PDO::PARAM_INT);
            $stmt->execute();
            $rows = $stmt->fetchAll();
            return is_array($rows) ? $rows : [];
        } catch (\Throwable $e) {
            error_log('[ADMIN DOC] Erro em alunosDaTurma: ' . $e->getMessage());
            return [];
        }
    }

    private function buscarTipo(int $id): ?array
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO || $id <= 0) {
            return null;
        }

        try {
            $stmt = $pdo->prepare('SELECT id, descricao FROM documento_tipo WHERE id = :id AND id_grupo = :id_grupo AND ativo = 1 LIMIT 1');
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->bindValue(':id_grupo', self::GRUPO_SECRETARIA, PDO::PARAM_INT);
            $stmt->execute();
            $row = $stmt->fetch();
            return is_array($row) ? $row : null;
        } catch (\Throwable $e) {
            error_log('[ADMIN DOC] Erro em buscarTipo: ' . $e->getMessage());
            return null;
        }
    }

    private function buscarAluno(int $id): ?array
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO || $id <= 0) {
            return null;
        }

        try {
            $stmt = $pdo->prepare('SELECT id, nome FROM alunos WHERE id = :id LIMIT 1');
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $row = $stmt->fetch();
            return is_array($row) ? $row : null;
        } catch (\Throwable $e) {
            error_log('[ADMIN DOC] Erro em buscarAluno: ' . $e->getMessage());
            return null;
        }
    }

    private function tipoSigla(string $descricao): string
    {
        $sigla = preg_replace('/[^A-Za-z0-9]/', '', $descricao);
        $sigla = strtoupper((string) $sigla);
        return $sigla !== '' ? $sigla : 'DOC';
    }

    private function isStaff(): bool
    {
        return (new AuthService())->isStaff();
    }
}