<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Database;
use App\Services\AlunoService;
use App\Services\CursoParcelaService;
use App\Services\TurmaService;
use App\Services\CursoService;
use App\Services\LogService;
use App\Services\IpLocationService;
use App\Support\Session;

final class AlunoController extends Controller
{
    private AlunoService $alunoService;
    private TurmaService $turmaService;
    private CursoService $cursoService;
    private LogService $logService;
    private CursoParcelaService $parcelaService;

    public function __construct()
    {
        $this->alunoService = new AlunoService();
        $this->turmaService = new TurmaService();
        $this->cursoService = new CursoService();
        $this->logService = new LogService();
        $this->parcelaService = new CursoParcelaService();
    }

    public function index(): void
    {
        if (!$this->isStaff()) {
            Session::setFlash('flash', 'Faça login como admin, operador ou professor para acessar os alunos.');
            $this->redirect('/admin/login');
        }

        $filtroAtivo = (int) ($_GET['ativo'] ?? 1);
        if ($filtroAtivo !== 0 && $filtroAtivo !== 1) {
            $filtroAtivo = 1;
        }

        $this->render('pages/admin/alunos/index', [
            'title' => 'Alunos',
            'currentRoute' => '/admin/alunos',
            'alunos' => $this->alunoService->alunos(200, $filtroAtivo),
            'filtroAtivo' => $filtroAtivo,
        ], 'admin');
    }

    public function show(): void
    {
        if (!$this->isStaff()) {
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

        $logs = [];
        $documentos = [];
        $pdo = Database::connection();
        if ($pdo instanceof \PDO) {
            try {
                $stmt = $pdo->prepare(
                    'SELECT l.id, l.acao, l.entidade, l.descricao, l.ip, l.created_at, a.nome AS aluno_nome'
                    . ' FROM logs_auditoria l'
                    . ' LEFT JOIN alunos a ON a.id = l.usuario_id'
                    . ' WHERE l.usuario_id = :usuario_id AND l.perfil = :perfil'
                    . ' ORDER BY l.id DESC'
                    . ' LIMIT 50'
                );
                $stmt->bindValue(':usuario_id', $id, \PDO::PARAM_INT);
                $stmt->bindValue(':perfil', 'aluno', \PDO::PARAM_STR);
                $stmt->execute();
                $rows = $stmt->fetchAll();
                if (is_array($rows)) {
                    $geo = new IpLocationService();
                    foreach ($rows as &$log) {
                        $ip = (string) ($log['ip'] ?? '127.0.0.1');
                        $log['location'] = $geo->resolve($ip);
                    }
                    unset($log);
                    $logs = $rows;
                }
            } catch (\Throwable $e) {
                error_log('[ALUNO LOGS] Erro: ' . $e->getMessage());
            }

            try {
                $grupoAlunos = \App\Services\Storage\StorageService::GROUP_ALUNOS;
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
                $stmt->bindValue(':id_grupo', $grupoAlunos, \PDO::PARAM_INT);
                $stmt->execute();
                $rows = $stmt->fetchAll();
                $documentos = is_array($rows) ? $rows : [];
            } catch (\Throwable $e) {
                error_log('[ALUNO DOCUMENTOS] Erro: ' . $e->getMessage());
                $documentos = [];
            }
        }

        $this->render('pages/admin/alunos/show', [
            'title' => 'Aluno: ' . ($aluno['nome'] ?? ''),
            'currentRoute' => '/admin/alunos/show',
            'aluno' => $aluno,
            'cursos' => $this->alunoService->cursosDoAluno($id),
            'logsAluno' => $logs,
            'documentos' => $documentos,
            'parcelasFinanceiro' => $this->parcelaService->listarPorAluno($id),
        ], 'admin');
    }

    public function novo(): void
    {
        if (!$this->isStaff()) {
            Session::setFlash('flash', 'Acesso negado.');
            $this->redirect('/admin/login');
        }

        $this->render('pages/admin/alunos/new', [
            'title' => 'Novo Aluno',
            'currentRoute' => '/admin/alunos/novo',
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

    public function salvar(): void
    {
        if (!$this->isStaff()) {
            Session::setFlash('flash', 'Acesso negado.');
            $this->redirect('/admin/login');
        }

        $nome = trim((string) $this->input('nome', ''));
        $cpf = trim((string) $this->input('cpf', ''));
        $dataNascimento = (string) $this->input('data_nascimento', '');
        $telefone = trim((string) $this->input('telefone', ''));
        $email = trim((string) $this->input('email', ''));
        $ativo = strtoupper(trim((string) $this->input('ativo', '0')));
        $ativo = in_array($ativo, ['1', 'S', 'Y', 'TRUE'], true) ? 1 : 0;

        if ($nome === '') {
            Session::setFlash('flash', 'Informe o nome do aluno.');
            $this->redirect('/admin/alunos/novo');
            return;
        }

        $alunoId = $this->alunoService->criarAluno($nome, $cpf, $dataNascimento, $telefone, $email, $ativo);

        if ($alunoId > 0) {
            $this->logService->log('criar', 'aluno', $alunoId, "Aluno criado: $nome");
            Session::setFlash('flash', 'Aluno criado com sucesso.');
            $this->redirect('/admin/alunos');
        } else {
            Session::setFlash('flash', 'Erro ao criar aluno. Tente novamente.');
            $this->redirect('/admin/alunos/novo');
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
        $cpf = trim((string) $this->input('cpf', ''));
        $dataNascimento = (string) $this->input('data_nascimento', '');
        $telefone = trim((string) $this->input('telefone', ''));
        $email = trim((string) $this->input('email', ''));
        $ativo = strtoupper(trim((string) $this->input('ativo', '0')));
        $ativo = in_array($ativo, ['1', 'S', 'Y', 'TRUE'], true) ? 1 : 0;

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

    public function matricula(): void
    {
        if (!$this->isStaff()) {
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

        $matricula = $this->alunoService->matriculaDoAluno($id);
        $turmasMatriculadas = array_map(
            static fn (array $m) => (int) ($m['id_turma'] ?? 0),
            $matricula
        );

        $this->render('pages/admin/alunos/matricula', [
            'title' => 'Matricular Aluno',
            'currentRoute' => '/admin/alunos/matricula',
            'aluno' => $aluno,
            'turmas' => $this->turmaService->turmas(500),
            'matricula' => $matricula,
            'turmasMatriculadas' => $turmasMatriculadas,
        ], 'admin');
    }

    public function matricular(): void
    {
        if (!$this->isStaff()) {
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

    public function trocaHistorico(): void
    {
        if (!$this->isStaff()) {
            Session::setFlash('flash', 'Faça login como admin, operador ou professor para acessar o histórico.');
            $this->redirect('/admin/login');
        }

        $trocas = $this->turmaService->trocaHistorico();

        $this->render('pages/admin/alunos/troca_historico', [
            'title' => 'Histórico de Trocas',
            'currentRoute' => '/admin/alunos/troca-historico',
            'trocas' => $trocas,
        ], 'admin');
    }

    public function troca(): void
    {
        if (!$this->isStaff()) {
            Session::setFlash('flash', 'Faça login como admin, operador ou professor para acessar a troca de turma.');
            $this->redirect('/admin/login');
        }

        $idAluno = (int) ($this->input('id', 0) ?: ($_GET['id'] ?? 0));
        $idMatricula = (int) ($this->input('matricula_id', 0) ?: ($_GET['matricula_id'] ?? 0));

        if ($idAluno <= 0 || $idMatricula <= 0) {
            Session::setFlash('flash', 'Parâmetros inválidos.');
            $this->redirect('/admin/alunos');
            return;
        }

        $aluno = $this->alunoService->findAluno($idAluno);
        if (!$aluno) {
            Session::setFlash('flash', 'Aluno não encontrado.');
            $this->redirect('/admin/alunos');
            return;
        }

        $matricula = $this->alunoService->findMatriculaById($idMatricula);
        if (!$matricula) {
            Session::setFlash('flash', 'Matrícula não encontrada.');
            $this->redirect('/admin/alunos/show?id=' . $idAluno);
            return;
        }

        $todas = $this->turmaService->turmas(500);
        $turmasAtivas = array_values(
            array_filter($todas, static fn (array $t): bool => (intval($t['ativo'] ?? 0) === 1))
        );

        $this->render('pages/admin/alunos/troca', [
            'title' => 'Trocar Turma',
            'currentRoute' => '/admin/alunos/troca',
            'aluno' => $aluno,
            'matricula' => $matricula,
            'turmasAtivas' => $turmasAtivas,
        ], 'admin');
    }

    public function trocar(): void
    {
        if (!$this->isStaff()) {
            Session::setFlash('flash', 'Acesso negado.');
            $this->redirect('/admin/login');
        }

        $idAluno = (int) $this->input('id_aluno', 0);
        $idMatricula = (int) $this->input('id_matricula', 0);
        $idTurmaDestino = (int) $this->input('id_turma_destino', 0);
        $motivo = trim((string) $this->input('motivo', ''));

        if ($idAluno <= 0 || $idMatricula <= 0 || $idTurmaDestino <= 0) {
            Session::setFlash('flash', 'Parâmetros inválidos.');
            $this->redirect('/admin/alunos');
            return;
        }

        if ($motivo === '') {
            Session::setFlash('flash', 'Informe o motivo da troca.');
            $this->redirect('/admin/alunos/troca?id=' . $idAluno . '&matricula_id=' . $idMatricula);
            return;
        }

        $matricula = $this->alunoService->findMatriculaById($idMatricula);
        if (!$matricula) {
            Session::setFlash('flash', 'Matrícula não encontrada.');
            $this->redirect('/admin/alunos');
            return;
        }

        $idTurmaOrigem = (int) ($matricula['id_turma'] ?? 0);

        if ($idTurmaOrigem === $idTurmaDestino) {
            Session::setFlash('flash', 'A turma de destino deve ser diferente da turma atual.');
            $this->redirect('/admin/alunos/troca?id=' . $idAluno . '&matricula_id=' . $idMatricula);
            return;
        }

        if ($this->alunoService->matriculaJaExiste($idAluno, $idTurmaDestino)) {
            Session::setFlash('flash', 'Aluno já está matriculado na turma de destino.');
            $this->redirect('/admin/alunos/troca?id=' . $idAluno . '&matricula_id=' . $idMatricula);
            return;
        }

        $atualizou = $this->alunoService->atualizarMatriculaTurma($idMatricula, $idTurmaDestino);

        if ($atualizou) {
            $trocaId = $this->alunoService->registrarTroca($idTurmaOrigem, $idTurmaDestino, $idAluno, $motivo);
            $nomeAluno = (string) ($matricula['turma_nome'] ?? '');
            $this->logService->log('atualizar', 'matricula', $idMatricula, "Troca de turma do aluno ID $idAluno: origem $idTurmaOrigem -> destino $idTurmaDestino");
            Session::setFlash('flash', 'Troca de turma realizada com sucesso.');
        } else {
            Session::setFlash('flash', 'Erro ao realizar troca de turma. Tente novamente.');
        }

        $this->redirect('/admin/alunos/show?id=' . $idAluno);
    }

    public function restaurarSenha(): void
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

        $aluno = $this->alunoService->findAluno($id);
        if (!$aluno) {
            http_response_code(404);
            echo json_encode(['erro' => 'Aluno não encontrado.']);
            return;
        }

        $email = strtolower(trim((string) ($aluno['email'] ?? '')));
        if ($email === '') {
            http_response_code(400);
            echo json_encode(['erro' => 'Aluno não possui email cadastrado.']);
            return;
        }

        $senha = explode('@', $email)[0] . '#' . date('Y');
        $this->alunoService->atualizarSenha($id, $senha);
        $this->logService->log('atualizar', 'aluno', $id, "Senha do aluno restaurada");

        echo json_encode(['sucesso' => true, 'senha' => $senha]);
    }

    public function liberarDocumentosPublicos(): void
    {
        if (!$this->isStaff()) {
            Session::setFlash('flash', 'Acesso negado.');
            $this->redirect('/admin/login');
        }

        $alunoId = (int) $this->input('aluno_id', 0);
        if ($alunoId <= 0) {
            Session::setFlash('flash', 'Parâmetros inválidos.');
            $this->redirect('/admin/alunos');
            return;
        }

        $pdo = Database::connection();
        $documentos = [];
        if ($pdo instanceof \PDO) {
            try {
                $stmt = $pdo->prepare('SELECT id, file_id, nome_original FROM documento WHERE id_registro = :id_registro AND ativo = 1 ORDER BY id DESC');
                $stmt->bindValue(':id_registro', $alunoId, \PDO::PARAM_INT);
                $stmt->execute();
                $rows = $stmt->fetchAll();
                $documentos = is_array($rows) ? $rows : [];
            } catch (\Throwable $e) {
                error_log('[ALUNO LIBERAR DOCS] Erro: ' . $e->getMessage());
            }
        }

        $storage = new \App\Services\Storage\StorageService();
        $liberados = 0;
        $erros = 0;

        foreach ($documentos as $documento) {
            $fileId = (string) ($documento['file_id'] ?? '');
            if ($fileId === '') {
                continue;
            }
            try {
                $storage->sharePublicByFileId($fileId);
                $liberados++;
            } catch (\Throwable $e) {
                $erros++;
                error_log('[ALUNO LIBERAR DOCS] Storage: ' . $e->getMessage());
            }
        }

        $this->logService->log('compartilhar', 'documento', $alunoId, "Documentos do aluno liberados publicamente ({$liberados} ok, {$erros} erro)");

        if ($liberados > 0 || $erros > 0) {
            Session::setFlash('flash', "Documentos liberados: {$liberados} ok, {$erros} com erro.");
        } else {
            Session::setFlash('flash', 'Nenhum documento encontrado para liberar.');
        }

        $this->redirect('/admin/alunos/show?id=' . $alunoId);
    }

    public function compartilharDocumento(): void
    {
        if (!$this->isStaff()) {
            Session::setFlash('flash', 'Acesso negado.');
            $this->redirect('/admin/login');
        }

        $documentoId = (int) $this->input('id', 0);
        $alunoId = (int) $this->input('aluno_id', 0);

        if ($documentoId <= 0 || $alunoId <= 0) {
            Session::setFlash('flash', 'Parâmetros inválidos.');
            $this->redirect('/admin/alunos/show?id=' . $alunoId);
            return;
        }

        $documento = null;
        $pdo = Database::connection();
        if ($pdo instanceof \PDO) {
            try {
                $stmt = $pdo->prepare('SELECT id, id_registro, file_id, nome_original FROM documento WHERE id = :id AND ativo = 1 LIMIT 1');
                $stmt->bindValue(':id', $documentoId, \PDO::PARAM_INT);
                $stmt->execute();
                $documento = $stmt->fetch() ?: null;
            } catch (\Throwable $e) {
                error_log('[ALUNO COMPARTILHAR DOC] Erro: ' . $e->getMessage());
            }
        }

        if ($documento === null || (int) ($documento['id_registro'] ?? 0) !== $alunoId) {
            Session::setFlash('flash', 'Documento não encontrado para este aluno.');
            $this->redirect('/admin/alunos/show?id=' . $alunoId);
            return;
        }

        try {
            $storage = new \App\Services\Storage\StorageService();
            $storage->sharePublic($documentoId);
            $this->logService->log('compartilhar', 'documento', $documentoId, "Documento liberado publicamente: {$documento['nome_original']}");
            Session::setFlash('flash', 'Documento liberado para download público. Qualquer pessoa com o link pode baixar.');
        } catch (\Throwable $e) {
            error_log('[ALUNO COMPARTILHAR DOC] Storage: ' . $e->getMessage());
            Session::setFlash('flash', 'Erro ao liberar o documento para download público.');
        }

        $this->redirect('/admin/alunos/show?id=' . $alunoId);
    }

    private function isStaff(): bool
    {
        return (new \App\Services\AuthService())->isStaff();
    }
}
