<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Database;
use App\Repositories\LinkRepository;
use App\Repositories\StorageDriveRepository;
use App\Services\LogService;
use App\Services\AuthService;
use App\Services\Storage\StorageService;
use App\Support\Session;
use PDO;

final class MaterialController extends Controller
{
    private const TIPOS = ['video', 'pdf', 'excel', 'doc', 'link'];

    private const EXTENSOES_ARQUIVO = [
        'pdf' => ['pdf'],
        'excel' => ['xls', 'xlsx', 'csv'],
        'doc' => ['doc', 'docx'],
    ];

    private LogService $logService;

    /** @var array<string, mixed>|null */
    private ?array $escopoAcessoCache = null;

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

        $escopo = $this->escopoAcesso();
        $turmas = $this->turmas();
        $idTurma = (int) ($_GET['id_turma'] ?? 0);
        $turmasPermitidas = array_map(static fn (array $turma): int => (int) ($turma['id'] ?? 0), $turmas);
        if ($idTurma > 0 && !in_array($idTurma, $turmasPermitidas, true)) {
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

        $materiais = array_values(array_filter(
            $materiais,
            fn (array $material): bool => $this->podeAcessarMaterial($escopo, $material)
        ));

        $links = array_values(array_filter(
            (new LinkRepository())->listarAdmin($idTurma > 0 ? $idTurma : null),
            fn (array $link): bool => $this->podeAcessarMaterial($escopo, $link)
        ));
        if ($links !== []) {
            $materiais = array_merge($materiais, $links);
            usort($materiais, static function (array $a, array $b): int {
                $cmp = strcmp((string) ($b['created_at'] ?? ''), (string) ($a['created_at'] ?? ''));
                return $cmp !== 0 ? $cmp : ((int) ($b['id'] ?? 0) <=> (int) ($a['id'] ?? 0));
            });
        }

        $this->render('pages/admin/material/index', [
            'title' => 'Materiais',
            'currentRoute' => '/admin/material',
            'materiais' => $materiais,
            'turmas' => $turmas,
            'idTurma' => $idTurma,
            'restricaoTurmas' => !$escopo['irrestrito'],
        ], 'admin');
    }

    public function novo(): void
    {
        if (!$this->podeGerenciar()) {
            Session::setFlash('flash', 'Acesso negado.');
            $this->redirect('/admin/login');
        }

        $tipo = trim((string) ($_GET['tipo'] ?? ''));
        if ($tipo !== '' && !in_array($tipo, self::TIPOS, true)) {
            $tipo = '';
        }

        $this->render('pages/admin/material/novo', [
            'title' => 'Novo Material',
            'currentRoute' => '/admin/material',
            'tipo' => $tipo,
            'turmas' => $this->turmas(),
            'permiteSecretaria' => $this->permiteMaterialGeral(),
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
        $extra = (int) $this->input('extra', 0) === 1 ? 1 : 0;

        if (!in_array($tipo, self::TIPOS, true) || $idTurma <= 0 || $titulo === '') {
            Session::setFlash('flash', 'Selecione o tipo, a turma e informe o título.');
            $this->redirect('/admin/material/novo');
            return;
        }

        $idDisciplina = $idDisciplina > 0 ? $idDisciplina : 0;

        if (!$this->podeUsarTurmaDisciplina($idTurma, $idDisciplina)) {
            Session::setFlash('flash', 'Você não tem permissão para publicar material nesta turma ou disciplina.');
            $this->redirect('/admin/material/novo');
            return;
        }

        try {
            if ($tipo === 'link') {
                $url = trim((string) $this->input('link', ''));
                if ($url === '') {
                    Session::setFlash('flash', 'Informe o link (URL).');
                    $this->redirect('/admin/material/novo?tipo=link');
                    return;
                }

                $id = (new LinkRepository())->insert($titulo, $url, $idTurma, $idDisciplina, $extra);
                if ($id > 0) {
                    $this->logService->log('criar', 'link', $id, "Link publicado na turma $idTurma: $titulo");
                    Session::setFlash('flash', 'Link publicado com sucesso.');
                } else {
                    Session::setFlash('flash', 'Erro ao publicar o link.');
                }
                $this->redirect('/admin/material');
                return;
            }

            if ($tipo === 'video') {
                $link = trim((string) $this->input('link', ''));
                if ($link === '') {
                    Session::setFlash('flash', 'Informe o link do vídeo.');
                    $this->redirect('/admin/material/novo?tipo=video');
                    return;
                }

                $id = $this->inserirMaterial('video', $link, $idTurma, $titulo, $idDisciplina, $extra);
                if ($id > 0) {
                    $this->logService->log('criar', 'video', $id, "Vídeo publicado na turma $idTurma: $titulo");
                    Session::setFlash('flash', 'Vídeo publicado com sucesso.');
                } else {
                    Session::setFlash('flash', 'Erro ao publicar o vídeo.');
                }
                $this->redirect('/admin/material');
                return;
            }

            $extensoes = self::EXTENSOES_ARQUIVO[$tipo] ?? [];
            $rotuloTipo = $this->rotuloArquivo($tipo);

            $file = $_FILES['arquivo'] ?? null;
            if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
                Session::setFlash('flash', "Selecione um arquivo {$rotuloTipo} para enviar.");
                $this->redirect('/admin/material/novo?tipo=' . $tipo);
                return;
            }

            $originalName = (string) ($file['name'] ?? '');
            $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
            if (!in_array($extension, $extensoes, true)) {
                Session::setFlash('flash', 'Formato inválido. Envie: ' . implode(', ', array_map('strtoupper', $extensoes)) . '.');
                $this->redirect('/admin/material/novo?tipo=' . $tipo);
                return;
            }
            if ((int) ($file['size'] ?? 0) > 20 * 1024 * 1024) {
                Session::setFlash('flash', 'O arquivo deve ter no máximo 20MB.');
                $this->redirect('/admin/material/novo?tipo=' . $tipo);
                return;
            }

            $link = $this->uploadArquivoDrive($file, $originalName, $idTurma, $tipo);
            if ($link === '') {
                $this->redirect('/admin/material/novo?tipo=' . $tipo);
                return;
            }

            $tipoBanco = $tipo === 'pdf' ? 'drive' : $tipo;
            $id = $this->inserirMaterial($tipoBanco, $link, $idTurma, $titulo, $idDisciplina, $extra);
            if ($id > 0) {
                $this->logService->log('criar', $tipoBanco, $id, "Material {$rotuloTipo} publicado na turma $idTurma: $titulo");
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
        $tabela = ((string) $this->input('tabela', '')) === 'link' ? 'link' : 'material';
        if ($id <= 0) {
            Session::setFlash('flash', 'Material inválido.');
            $this->redirect('/admin/material');
            return;
        }

        $material = $tabela === 'link' ? (new LinkRepository())->buscarPorId($id) : $this->buscarMaterial($id);
        if (!$material) {
            Session::setFlash('flash', 'Material não encontrado.');
            $this->redirect('/admin/material');
            return;
        }

        if (!$this->podeUsarTurmaDisciplina(
            (int) ($material['id_fk'] ?? 0),
            (int) ($material['id_disciplina'] ?? 0)
        )) {
            Session::setFlash('flash', 'Você não tem permissão para remover este material.');
            $this->redirect('/admin/material');
            return;
        }

        if ($tabela === 'link') {
            if ((new LinkRepository())->softDelete($id)) {
                $this->logService->log('desativar', 'link', $id, 'Link desativado');
                Session::setFlash('flash', 'Link removido.');
            } else {
                Session::setFlash('flash', 'Erro ao remover o link.');
            }
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

    private function inserirMaterial(string $tipo, string $link, int $idTurma, string $titulo, int $idDisciplina = 0, int $extra = 0): int
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return 0;
        }

        try {
            $stmt = $pdo->prepare(
                'INSERT INTO material (tipo, extra, link, id_fk, id_disciplina, titulo) VALUES (:tipo, :extra, :link, :id_fk, :id_disciplina, :titulo)'
            );
            $stmt->bindValue(':tipo', $tipo, PDO::PARAM_STR);
            $stmt->bindValue(':extra', $extra === 1 ? 1 : 0, PDO::PARAM_INT);
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
        $tabela = ((string) ($_GET['tabela'] ?? '')) === 'link' ? 'link' : 'material';
        $material = $id > 0
            ? ($tabela === 'link' ? (new LinkRepository())->buscarPorId($id) : $this->buscarMaterial($id))
            : null;

        if (!$material) {
            Session::setFlash('flash', 'Material não encontrado.');
            $this->redirect('/admin/material');
            return;
        }

        $idTurmaMaterial = (int) ($material['id_fk'] ?? 0);
        $idDisciplinaMaterial = (int) ($material['id_disciplina'] ?? 0);
        if (!$this->podeUsarTurmaDisciplina($idTurmaMaterial, $idDisciplinaMaterial)) {
            Session::setFlash('flash', 'Você não tem permissão para editar este material.');
            $this->redirect('/admin/material');
            return;
        }

        if ($tabela === 'link') {
            $material['tipo'] = 'link';
        }

        $this->render('pages/admin/material/editar', [
            'title' => 'Editar Material',
            'currentRoute' => '/admin/material',
            'material' => $material,
            'tabela' => $tabela,
            'turmas' => $this->turmas(),
            'disciplinas' => $this->disciplinasDaTurma($idTurmaMaterial),
            'permiteSecretaria' => $this->podeUsarTurmaDisciplina($idTurmaMaterial, 0),
        ], 'admin');
    }

    public function atualizar(): void
    {
        if (!$this->podeGerenciar()) {
            Session::setFlash('flash', 'Acesso negado.');
            $this->redirect('/admin/login');
        }

        $id = (int) $this->input('id', 0);
        $tabela = ((string) $this->input('tabela', '')) === 'link' ? 'link' : 'material';
        $idTurma = (int) $this->input('id_fk', 0);
        $idDisciplina = (int) $this->input('id_disciplina', 0);
        $titulo = trim((string) $this->input('titulo', ''));

        if ($id <= 0 || $idTurma <= 0 || $titulo === '') {
            Session::setFlash('flash', 'Dados inválidos.');
            $this->redirect('/admin/material/editar?id=' . $id);
            return;
        }

        $material = $tabela === 'link' ? (new LinkRepository())->buscarPorId($id) : $this->buscarMaterial($id);
        if (!$material) {
            Session::setFlash('flash', 'Material não encontrado.');
            $this->redirect('/admin/material');
            return;
        }

        $idTurmaAtual = (int) ($material['id_fk'] ?? 0);
        $idDisciplinaAtual = (int) ($material['id_disciplina'] ?? 0);
        if (!$this->podeUsarTurmaDisciplina($idTurmaAtual, $idDisciplinaAtual)
            || !$this->podeUsarTurmaDisciplina($idTurma, $idDisciplina)) {
            Session::setFlash('flash', 'Você não tem permissão para editar material nesta turma ou disciplina.');
            $this->redirect('/admin/material');
            return;
        }

        if ($tabela === 'link') {
            $linkRepo = new LinkRepository();
            $url = trim((string) $this->input('link', ''));
            if ($url === '') {
                Session::setFlash('flash', 'Informe o link (URL).');
                $this->redirect('/admin/material/editar?id=' . $id . '&tabela=link');
                return;
            }

            if ($linkRepo->atualizar($id, $titulo, $url, $idTurma, $idDisciplina > 0 ? $idDisciplina : 0)) {
                $this->logService->log('atualizar', 'link', $id, "Link atualizado: $titulo");
                Session::setFlash('flash', 'Link atualizado com sucesso.');
            } else {
                Session::setFlash('flash', 'Erro ao atualizar o link.');
            }
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
        $escopo = $this->escopoAcesso();
        if (!$pdo instanceof PDO || $idTurma <= 0 || !$this->podeAcessarTurma($escopo, $idTurma)) {
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
            $rows = $stmt->fetchAll() ?: [];

            if ($escopo['irrestrito'] || isset($escopo['turmas_coordenadas'][$idTurma])) {
                return $rows;
            }

            $permitidas = $escopo['disciplinas_professor'][$idTurma] ?? [];
            return array_values(array_filter(
                $rows,
                static fn (array $disciplina): bool => isset($permitidas[(int) ($disciplina['id'] ?? 0)])
            ));
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

    private function uploadArquivoDrive(array $file, string $originalName, int $idTurma, string $tipo): string
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
            $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
            $nomeDrive = sprintf('MAT_%s_%s_%s_%s.%s', strtoupper($tipo), $idTurma, $usuarioId, $timestamp, $extension);

            $resultado = $storage->uploadFile($file, $folderId, $nomeDrive);

            return (string) ($resultado['link'] ?? '');
        } catch (\Throwable $e) {
            error_log('[MATERIAL] Erro no upload de arquivo: ' . $e->getMessage());
            Session::setFlash('flash', 'Erro ao enviar o arquivo para o Drive.');
            return '';
        }
    }

    private function rotuloArquivo(string $tipo): string
    {
        return match ($tipo) {
            'excel' => 'Excel',
            'doc' => 'DOC',
            default => 'PDF',
        };
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
                'SELECT DISTINCT t.id, t.nome AS turma_nome, c.nome AS curso_nome'
                . ' FROM turmas t'
                . ' LEFT JOIN cursos c ON c.id = t.id_curso'
                . ' WHERE t.ativo = 1 ORDER BY c.nome ASC, t.nome ASC'
            );
            $rows = $stmt->fetchAll();
            if (!is_array($rows)) {
                return [];
            }

            $escopo = $this->escopoAcesso();
            if (!$escopo['irrestrito']) {
                $rows = array_values(array_filter(
                    $rows,
                    fn (array $turma): bool => $this->podeAcessarTurma($escopo, (int) ($turma['id'] ?? 0))
                ));
            }

            foreach ($rows as &$turma) {
                $idTurma = (int) ($turma['id'] ?? 0);
                $turma['permite_secretaria'] = $this->podeAcessar($escopo, $idTurma, 0);
            }
            unset($turma);

            return $rows;
        } catch (\Throwable $e) {
            error_log('[MATERIAL] Erro em turmas: ' . $e->getMessage());
            return [];
        }
    }

    private function podeUsarTurmaDisciplina(int $idTurma, int $idDisciplina): bool
    {
        return $this->podeAcessar($this->escopoAcesso(), $idTurma, $idDisciplina);
    }

    private function podeAcessarMaterial(array $escopo, array $material): bool
    {
        return $this->podeAcessar(
            $escopo,
            (int) ($material['id_fk'] ?? 0),
            (int) ($material['id_disciplina'] ?? 0)
        );
    }

    private function podeAcessar(array $escopo, int $idTurma, int $idDisciplina): bool
    {
        if ($idTurma <= 0) {
            return false;
        }

        if ($escopo['irrestrito'] || isset($escopo['turmas_coordenadas'][$idTurma])) {
            return true;
        }

        return $idDisciplina > 0
            && isset($escopo['disciplinas_professor'][$idTurma][$idDisciplina]);
    }

    private function podeAcessarTurma(array $escopo, int $idTurma): bool
    {
        if ($idTurma <= 0) {
            return false;
        }

        if ($escopo['irrestrito'] || isset($escopo['turmas_coordenadas'][$idTurma])) {
            return true;
        }

        return !empty($escopo['disciplinas_professor'][$idTurma]);
    }

    private function permiteMaterialGeral(): bool
    {
        $escopo = $this->escopoAcesso();
        return $escopo['irrestrito'] || $escopo['coordenador'];
    }

    /**
     * @return array{irrestrito: bool, coordenador: bool, professor: bool, turmas_coordenadas: array<int, bool>, disciplinas_professor: array<int, array<int, bool>>}
     */
    private function escopoAcesso(): array
    {
        if ($this->escopoAcessoCache !== null) {
            return $this->escopoAcessoCache;
        }

        $auth = new AuthService();
        $user = Session::get('user');
        $idUsuario = is_array($user) ? (int) ($user['id'] ?? 0) : 0;
        $isAdmin = $auth->isAdmin();
        $isCoordenador = !$isAdmin && $auth->checkRole('coordenador');
        $isProfessor = !$isAdmin && $auth->isProfessor();

        $escopo = [
            'irrestrito' => !$isCoordenador && !$isProfessor,
            'coordenador' => $isCoordenador,
            'professor' => $isProfessor,
            'turmas_coordenadas' => [],
            'disciplinas_professor' => [],
        ];

        if ($escopo['irrestrito'] || $idUsuario <= 0) {
            return $this->escopoAcessoCache = $escopo;
        }

        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return $this->escopoAcessoCache = $escopo;
        }

        if ($isCoordenador) {
            try {
                $stmt = $pdo->prepare(
                    'SELECT DISTINCT tc.id_turma FROM turma_coordenador tc'
                    . ' INNER JOIN turmas t ON t.id = tc.id_turma AND t.ativo = 1'
                    . ' WHERE tc.id_usuario = :id_usuario'
                );
                $stmt->bindValue(':id_usuario', $idUsuario, PDO::PARAM_INT);
                $stmt->execute();
                foreach ($stmt->fetchAll() ?: [] as $row) {
                    $idTurma = (int) ($row['id_turma'] ?? 0);
                    if ($idTurma > 0) {
                        $escopo['turmas_coordenadas'][$idTurma] = true;
                    }
                }
            } catch (\Throwable $e) {
                error_log('[MATERIAL] Erro ao carregar turmas do coordenador: ' . $e->getMessage());
            }
        }

        if ($isProfessor) {
            $escopo['disciplinas_professor'] = $this->carregarDisciplinasProfessor($pdo, $idUsuario);
        }

        return $this->escopoAcessoCache = $escopo;
    }

    /**
     * @return array<int, array<int, bool>>
     */
    private function carregarDisciplinasProfessor(PDO $pdo, int $idUsuario): array
    {
        $pares = [];

        try {
            $stmt = $pdo->prepare(
                'SELECT DISTINCT td.id_turma, td.id_disciplina'
                . ' FROM turma_disciplina td'
                . ' WHERE td.id_usuario_professor = :id_usuario AND td.ativo = 1'
            );
            $stmt->bindValue(':id_usuario', $idUsuario, PDO::PARAM_INT);
            $stmt->execute();
            foreach ($stmt->fetchAll() ?: [] as $row) {
                $this->adicionarParDisciplina($pares, $row);
            }
        } catch (\Throwable $e) {
            error_log('[MATERIAL] Erro ao carregar disciplinas legadas do professor: ' . $e->getMessage());
        }

        try {
            $colunas = $pdo->query('SHOW COLUMNS FROM turma_disciplina_professor')->fetchAll(PDO::FETCH_COLUMN);
            $modoTurma = in_array('id_turma', $colunas, true);

            if ($modoTurma && in_array('id_disciplina', $colunas, true)) {
                $stmt = $pdo->prepare(
                    'SELECT DISTINCT id_turma, id_disciplina'
                    . ' FROM turma_disciplina_professor'
                    . ' WHERE id_usuario_professor = :id_usuario AND ativo = 1 AND id_disciplina > 0'
                );
            } elseif ($modoTurma) {
                $stmt = $pdo->prepare(
                    'SELECT DISTINCT td.id_turma, td.id_disciplina'
                    . ' FROM turma_disciplina_professor tdp'
                    . ' INNER JOIN turma_disciplina td ON td.id_turma = tdp.id_turma AND td.ativo = 1'
                    . ' WHERE tdp.id_usuario_professor = :id_usuario AND tdp.ativo = 1'
                );
            } else {
                $stmt = $pdo->prepare(
                    'SELECT DISTINCT td.id_turma, td.id_disciplina'
                    . ' FROM turma_disciplina_professor tdp'
                    . ' INNER JOIN turma_disciplina td ON td.id = tdp.id_turma_disciplina AND td.ativo = 1'
                    . ' WHERE tdp.id_usuario_professor = :id_usuario AND tdp.ativo = 1'
                );
            }

            $stmt->bindValue(':id_usuario', $idUsuario, PDO::PARAM_INT);
            $stmt->execute();
            foreach ($stmt->fetchAll() ?: [] as $row) {
                $this->adicionarParDisciplina($pares, $row);
            }
        } catch (\Throwable $e) {
            error_log('[MATERIAL] Erro ao carregar disciplinas do professor: ' . $e->getMessage());
        }

        return $pares;
    }

    /**
     * @param array<int, array<int, bool>> $pares
     */
    private function adicionarParDisciplina(array &$pares, array $row): void
    {
        $idTurma = (int) ($row['id_turma'] ?? 0);
        $idDisciplina = (int) ($row['id_disciplina'] ?? 0);
        if ($idTurma > 0 && $idDisciplina > 0) {
            $pares[$idTurma][$idDisciplina] = true;
        }
    }

    private function podeGerenciar(): bool
    {
        return (new \App\Services\AuthService())->hasPermission('/admin/material', 'editar');
    }
}
