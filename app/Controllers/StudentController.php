<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Repositories\StorageDriveRepository;
use App\Services\AlunoService;
use App\Services\CursoService;
use App\Services\LogService;
use App\Services\TurmaService;
use App\Services\AuthService;
use App\Services\AcordoPagamentoService;
use App\Services\CourseService;
use App\Services\CursoParcelaService;
use App\Services\EnrollmentService;
use App\Services\IpLocationService;
use App\Services\MatriculaService;
use App\Services\NoticiaService;
use App\Services\Storage\StorageException;
use App\Services\Storage\StorageService;
use App\Support\Session;

final class StudentController extends Controller
{
    private AuthService $auth;
    private AlunoService $alunoService;
    private CursoService $cursoService;
    private LogService $logService;
    private TurmaService $turmaService;
    private CourseService $courses;
    private EnrollmentService $enrollments;
    private NoticiaService $noticiaService;
    private CursoParcelaService $parcelaService;
    private AcordoPagamentoService $acordoService;
    private MatriculaService $matriculaService;

    public function __construct()
    {
        $this->auth = new AuthService();
        $this->alunoService = new AlunoService();
        $this->cursoService = new CursoService();
        $this->logService = new LogService();
        $this->turmaService = new TurmaService();
        $this->courses = new CourseService();
        $this->enrollments = new EnrollmentService();
        $this->noticiaService = new NoticiaService();
        $this->parcelaService = new CursoParcelaService();
        $this->acordoService = new AcordoPagamentoService();
        $this->matriculaService = new MatriculaService();
    }

    public function dashboard(): void
    {
        if (!$this->auth->checkRole('aluno')) {
            Session::setFlash('flash', 'Faça login como aluno para acessar o painel.');
            $this->redirect('/aluno/login');
        }

        $user = Session::get('user');
        $studentId = (int) ($user['id'] ?? 0);

        $cursosAtivos = $this->cursoService->cursosAtivos();
        $cursosMatriculados = $this->alunoService->cursosDoAluno($studentId);
        $idsMatriculados = array_map(static fn (array $m): int => (int) ($m['curso_id'] ?? 0), $cursosMatriculados);

        $cursosDisponiveis = array_values(array_filter(
            $cursosAtivos,
            static fn (array $c): bool => !in_array((int) $c['id'], $idsMatriculados, true)
        ));

        $notificacaoCount = 0;
        $pdo = Database::connection();
        if ($pdo instanceof \PDO) {
            try {
                $stmt = $pdo->prepare(
                    'SELECT COUNT(*) AS total FROM notificacao n'
                    . ' WHERE ('
                    . ' (n.tipo_destino = \'aluno\' AND n.id_destino = :aluno_id)'
                    . ' OR (n.tipo_destino = \'turma\' AND n.id_destino IN ('
                    . '   SELECT m.id_turma FROM matricula m WHERE m.id_aluno = :aluno_id2 AND m.status NOT IN (\'cancelado\',\'concluido\')'
                    . ' ))'
                    . ' )'
                    . ' AND n.ativo = :ativo'
                    . ' AND n.id NOT IN (SELECT nl.id_notificacao FROM notificacao_leitura_aluno nl WHERE nl.id_aluno = :aluno_id3)'
                );
                $stmt->bindValue(':aluno_id', $studentId, \PDO::PARAM_INT);
                $stmt->bindValue(':aluno_id2', $studentId, \PDO::PARAM_INT);
                $stmt->bindValue(':aluno_id3', $studentId, \PDO::PARAM_INT);
                $stmt->bindValue(':ativo', 1, \PDO::PARAM_INT);
                $stmt->execute();
                $notificacaoCount = (int) ($stmt->fetch()['total'] ?? 0);
            } catch (\Throwable) {
                $notificacaoCount = 0;
            }
        }

        $temEndereco = false;
        if ($pdo instanceof \PDO) {
            try {
                $stmt = $pdo->prepare('SELECT 1 FROM endereco WHERE tipo = :tipo AND id_fk = :id_fk LIMIT 1');
                $stmt->bindValue(':tipo', 'aluno', \PDO::PARAM_STR);
                $stmt->bindValue(':id_fk', $studentId, \PDO::PARAM_INT);
                $stmt->execute();
                $temEndereco = (bool) $stmt->fetch();
            } catch (\Throwable) {
                $temEndereco = false;
            }
        }

        $documentosPendentes = [];
        if ($pdo instanceof \PDO) {
            try {
                $stmt = $pdo->prepare(
                    'SELECT t.id, t.descricao, d.id AS documento_id, d.status'
                    . ' FROM documento_tipo t'
                    . ' LEFT JOIN documento d ON d.id_tipo = t.id'
                    . ' AND d.id_grupo = :documento_grupo'
                    . ' AND d.id_registro = :documento_aluno'
                    . ' AND d.ativo = 1'
                    . ' LEFT JOIN documento d_novo ON d_novo.id_tipo = d.id_tipo'
                    . ' AND d_novo.id_grupo = :novo_grupo'
                    . ' AND d_novo.id_registro = :novo_aluno'
                    . ' AND d_novo.ativo = 1'
                    . ' AND (d_novo.versao > d.versao OR (d_novo.versao = d.versao AND d_novo.id > d.id))'
                    . ' WHERE t.id_grupo = :tipo_grupo'
                    . ' AND t.obrigatorio = 1'
                    . ' AND t.ativo = 1'
                    . ' AND d_novo.id IS NULL'
                    . ' ORDER BY t.ordem ASC, t.descricao ASC'
                );
                $stmt->bindValue(':documento_grupo', StorageService::GROUP_ALUNOS, \PDO::PARAM_INT);
                $stmt->bindValue(':documento_aluno', $studentId, \PDO::PARAM_INT);
                $stmt->bindValue(':novo_grupo', StorageService::GROUP_ALUNOS, \PDO::PARAM_INT);
                $stmt->bindValue(':novo_aluno', $studentId, \PDO::PARAM_INT);
                $stmt->bindValue(':tipo_grupo', StorageService::GROUP_ALUNOS, \PDO::PARAM_INT);
                $stmt->execute();

                foreach ($stmt->fetchAll() ?: [] as $documento) {
                    $status = (string) ($documento['status'] ?? '');
                    if ((int) ($documento['documento_id'] ?? 0) === 0 || in_array($status, ['rejeitado', 'substituido', 'nao_enviado'], true)) {
                        $documentosPendentes[] = (string) ($documento['descricao'] ?? 'Documento');
                    }
                }
            } catch (\Throwable $e) {
                error_log('[STUDENT DASHBOARD DOCS] Erro: ' . $e->getMessage());
                $documentosPendentes = [];
            }
        }

        $this->render('pages/aluno/dashboard', [
            'title' => 'Área do Aluno',
            'currentRoute' => '/area-do-aluno',
            'cursosDisponiveis' => $cursosDisponiveis,
            'cursosMatriculados' => $cursosMatriculados,
            'matriculaDB' => $this->alunoService->matriculaDoAluno($studentId),
            'notificacaoCount' => $notificacaoCount,
            'temEndereco' => $temEndereco,
            'documentosPendentes' => $documentosPendentes,
            'noticias' => $this->noticiaService->listPublicados(),
        ], 'aluno');
    }

    public function noticia(): void
    {
        if (!$this->auth->checkRole('aluno')) {
            Session::setFlash('flash', 'Faça login como aluno para acessar as notícias.');
            $this->redirect('/aluno/login');
        }

        $slug = trim((string) ($_GET['slug'] ?? ''));
        $noticia = $slug !== '' ? $this->noticiaService->findBySlug($slug) : null;

        if ($noticia === null) {
            Session::setFlash('flash', 'Notícia não encontrada.');
            $this->redirect('/aluno');
            return;
        }

        $this->render('pages/aluno/noticia', [
            'title' => (string) ($noticia['titulo'] ?? 'Notícia'),
            'currentRoute' => '/aluno/noticia',
            'noticia' => $noticia,
            'noticias' => $this->noticiaService->listPublicados(),
        ], 'aluno');
    }

    public function cursos(): void
    {
        if (!$this->auth->checkRole('aluno')) {
            Session::setFlash('flash', 'Faça login como aluno para acessar seus cursos.');
            $this->redirect('/aluno/login');
        }

        $user = Session::get('user');
        $studentId = (int) ($user['id'] ?? 0);

        $this->render('pages/aluno/cursos', [
            'title' => 'Meus Cursos',
            'currentRoute' => '/aluno/cursos',
            'matriculaDB' => $this->alunoService->matriculaDoAluno($studentId),
            'cursosMatriculados' => $this->alunoService->cursosDoAluno($studentId),
        ], 'aluno');
    }

    public function financeiro(): void
    {
        if (!$this->auth->checkRole('aluno')) {
            Session::setFlash('flash', 'Faça login como aluno para acessar o financeiro.');
            $this->redirect('/aluno/login');
        }

        $user = Session::get('user');
        $studentId = (int) ($user['id'] ?? 0);

        $parcelas = $this->parcelaService->listarPorAluno($studentId);

        $parcelas = $this->garantirParcelasRestantes($parcelas);

        $parcelas = $this->marcarRecorrencia($parcelas);

        $this->render('pages/aluno/financeiro', [
            'title' => 'Financeiro',
            'currentRoute' => '/aluno/financeiro',
            'parcelas' => $parcelas,
            'cursosMatriculados' => $this->alunoService->cursosDoAluno($studentId),
        ], 'aluno');
    }

    /**
     * Garante a geração das parcelas restantes (2..N) quando a primeira
     * parcela do curso já estiver paga. Idempotente.
     *
     * @param array<int, array<string, mixed>> $parcelas
     * @return array<int, array<string, mixed>>
     */
    private function garantirParcelasRestantes(array $parcelas): array
    {
        $pdo = Database::connection();
        if (!$pdo instanceof \PDO) {
            return $parcelas;
        }

        $user = Session::get('user');
        $studentId = (int) ($user['id'] ?? 0);

        $processados = [];
        foreach ($parcelas as $parcela) {
            if ((int) ($parcela['numero_parcela'] ?? 0) !== 1) {
                continue;
            }
            if (!in_array((string) ($parcela['status'] ?? ''), ['RECEBIDO', 'CONFIRMADO'], true)) {
                continue;
            }

            // Reconciliação: garante a matrícula quando o webhook foi interrompido.
            if ((int) ($parcela['id_matricula'] ?? 0) <= 0) {
                try {
                    $this->matriculaService->reprocessarParcela($parcela);
                } catch (\Throwable $e) {
                    error_log('[STUDENT FINANCEIRO] Erro ao reprocessar parcela #' . (int) ($parcela['id'] ?? 0) . ': ' . $e->getMessage());
                }
            }

            $idAcordo = (int) ($parcela['id_acordo_pagamento'] ?? 0);
            if ($idAcordo > 0) {
                if (isset($processados['acordo_' . $idAcordo])) {
                    continue;
                }
                $processados['acordo_' . $idAcordo] = true;

                $acordo = $this->acordoService->findById($idAcordo);
                if ($acordo === null) {
                    continue;
                }

                // Backfill: propaga id_aluno/id_matricula para parcelas irmãs
                // geradas antes da correção (com id_aluno = 0).
                $this->parcelaService->vincularAlunoPorAcordo(
                    $idAcordo,
                    (int) ($parcela['id_aluno'] ?? 0),
                    (int) ($parcela['id_matricula'] ?? 0)
                );

                $this->parcelaService->gerarParcelasRestantes($parcela, $acordo);
                continue;
            }

            // Inscrição feita pelo site (sem acordo): usa o plano de pagamento.
            $idPagamento = (int) ($parcela['id_pagamento'] ?? 0);
            $idCurso = (int) ($parcela['id_curso'] ?? 0);
            if ($idPagamento > 0 && $idCurso > 0) {
                $chave = 'plano_' . $idPagamento . '_' . $idCurso;
                if (isset($processados[$chave])) {
                    continue;
                }
                $processados[$chave] = true;

                $this->parcelaService->gerarParcelasRestantesPorPlano($parcela);
            }
        }

        return $this->parcelaService->listarPorAluno($studentId);
    }

    /**
     * Enriquece cada parcela com o status da recorrência do acordo vinculado,
     * para o painel exibir "Cobrança automática ativa" quando aplicável.
     *
     * @param array<int, array<string, mixed>> $parcelas
     * @return array<int, array<string, mixed>>
     */
    private function marcarRecorrencia(array $parcelas): array
    {
        $cache = [];
        $origens = [];

        foreach ($parcelas as &$parcela) {
            $parcela['recorrencia_ativa'] = false;
            $parcela['status_recorrencia'] = '';

            $idAcordo = (int) ($parcela['id_acordo_pagamento'] ?? 0);
            if ($idAcordo > 0) {
                if (!array_key_exists($idAcordo, $cache)) {
                    $acordo = $this->acordoService->findById($idAcordo);
                    $cache[$idAcordo] = is_array($acordo) ? $acordo : false;
                }

                $acordo = $cache[$idAcordo];
                if (!is_array($acordo)) {
                    continue;
                }

                $status = strtoupper(trim((string) ($acordo['status_recorrencia'] ?? '')));
                $parcela['status_recorrencia'] = $status;
                $parcela['recorrencia_ativa'] = $status === 'ATIVA';
                continue;
            }

            // Inscrição direta (sem acordo): a parcela 1 é a dona da assinatura.
            $status = strtoupper(trim((string) ($parcela['status_recorrencia'] ?? '')));
            if ($status === '' && trim((string) ($parcela['asaas_subscription'] ?? '')) !== '') {
                $status = 'ATIVA';
            }
            $parcela['status_recorrencia'] = $status;
            $parcela['recorrencia_ativa'] = $status === 'ATIVA';

            if ($parcela['recorrencia_ativa']) {
                $origens[$this->chaveInscricao($parcela)] = true;
            }
        }
        unset($parcela);

        // Propaga a recorrência ativa para as demais parcelas da mesma inscrição.
        foreach ($parcelas as &$parcela) {
            if (!empty($parcela['recorrencia_ativa'])) {
                continue;
            }
            if ((int) ($parcela['id_acordo_pagamento'] ?? 0) > 0) {
                continue;
            }
            if (isset($origens[$this->chaveInscricao($parcela)])) {
                $parcela['recorrencia_ativa'] = true;
                $parcela['status_recorrencia'] = 'ATIVA';
            }
        }
        unset($parcela);

        return $parcelas;
    }

    /**
     * Chave que identifica uma inscrição direta (sem acordo).
     *
     * @param array<string, mixed> $parcela
     */
    private function chaveInscricao(array $parcela): string
    {
        return (int) ($parcela['id_pagamento'] ?? 0)
            . ':' . (int) ($parcela['id_curso'] ?? 0)
            . ':' . (int) ($parcela['id_aluno'] ?? 0);
    }

    public function show(): void
    {
        if (!$this->auth->checkRole('aluno')) {
            Session::setFlash('flash', 'Faça login como aluno.');
            $this->redirect('/aluno/login');
        }

        $user = Session::get('user');
        $studentId = (int) ($user['id'] ?? 0);

        $matriculaId = (int) ($_GET['matricula_id'] ?? 0);

        $pdo = Database::connection();
        $matricula = null;
        $professores = [];
        $materiais = [];

        if ($pdo instanceof \PDO) {
            try {
                $stmt = $pdo->prepare(
                    'SELECT m.id AS matricula_id, m.status, m.data_matricula,'
                    . ' t.id AS turma_id, t.nome AS turma_nome, t.data_inicio, t.data_fim, t.id_estrutura AS estrutura_id,'
                    . ' c.id AS curso_id, c.nome AS curso_nome, c.local_curso, c.horario, c.imagem_card, c.tipo_curso AS curso_tipo,'
                    . ' ec.nome AS estrutura_nome'
                    . ' FROM matricula m'
                    . ' JOIN turmas t ON m.id_turma = t.id'
                    . ' LEFT JOIN cursos c ON t.id_curso = c.id'
                    . ' LEFT JOIN estrutura_curricular ec ON ec.id = t.id_estrutura'
                    . ' WHERE m.id = :id AND m.id_aluno = :id_aluno'
                );
                $stmt->bindValue(':id', $matriculaId, \PDO::PARAM_INT);
                $stmt->bindValue(':id_aluno', $studentId, \PDO::PARAM_INT);
                $stmt->execute();
                $matricula = $stmt->fetch() ?: null;
            } catch (\Throwable $e) {
                error_log('[STUDENT SHOW] Erro ao buscar matrícula: ' . $e->getMessage());
            }

            if (!$matricula) {
                Session::setFlash('flash', 'Matrícula não encontrada.');
                $this->redirect('/aluno/cursos');
                return;
            }

            $turmaId = (int) ($matricula['turma_id'] ?? 0);
            $cursoId = (int) ($matricula['curso_id'] ?? 0);
            $cursoTipo = (int) ($matricula['curso_tipo'] ?? 0);

            if ($cursoTipo === 3) {
                try {
                    $stmt = $pdo->prepare(
                        'SELECT u.id, u.nome, u.email, u.telefone, u.foto, cr.id AS curriculo_id'
                        . ' FROM corpo_docente cd'
                        . ' JOIN usuarios u ON cd.id_usuario = u.id'
                        . ' LEFT JOIN curriculo cr ON cr.id_fk = u.id AND cr.tipo = :tipo_curriculo AND cr.ativo = :ativo_curriculo'
                        . ' WHERE cd.id_curso = :id_curso AND cd.ativo = :ativo'
                        . ' ORDER BY u.nome ASC'
                    );
                    $stmt->bindValue(':id_curso', $cursoId, \PDO::PARAM_INT);
                    $stmt->bindValue(':ativo', 1, \PDO::PARAM_INT);
                    $stmt->bindValue(':tipo_curriculo', 'professor', \PDO::PARAM_STR);
                    $stmt->bindValue(':ativo_curriculo', 1, \PDO::PARAM_INT);
                    $stmt->execute();
                    $professores = $stmt->fetchAll() ?: [];
                } catch (\Throwable $e) {
                    error_log('[STUDENT SHOW] Erro ao buscar professores (corpo_docente): ' . $e->getMessage());
                    $professores = [];
                }
            } else {
                try {
                    $stmt = $pdo->prepare(
                        'SELECT u.id, u.nome, u.email, u.telefone, u.foto, cr.id AS curriculo_id'
                        . ' FROM turma_professor tp'
                        . ' JOIN usuarios u ON tp.id_usuario = u.id'
                        . ' LEFT JOIN curriculo cr ON cr.id_fk = u.id AND cr.tipo = :tipo_curriculo AND cr.ativo = :ativo_curriculo'
                        . ' WHERE tp.id_turma = :id_turma AND tp.status = :status'
                    );
                    $stmt->bindValue(':id_turma', $turmaId, \PDO::PARAM_INT);
                    $stmt->bindValue(':status', 'A', \PDO::PARAM_STR);
                    $stmt->bindValue(':tipo_curriculo', 'professor', \PDO::PARAM_STR);
                    $stmt->bindValue(':ativo_curriculo', 1, \PDO::PARAM_INT);
                    $stmt->execute();
                    $professores = $stmt->fetchAll() ?: [];
                } catch (\Throwable $e) {
                    error_log('[STUDENT SHOW] Erro ao buscar professores: ' . $e->getMessage());
                    $professores = [];
                }
            }

            try {
                $stmt = $pdo->prepare(
                    'SELECT id, titulo, link, tipo, created_at'
                    . ' FROM material'
                    . ' WHERE id_fk = :id_fk AND ativo = :ativo'
                    . ' ORDER BY FIELD(tipo, \'video\', \'PDF\', \'Artigo\', \'Apostila\'), created_at DESC'
                );
                $stmt->bindValue(':id_fk', $turmaId, \PDO::PARAM_INT);
                $stmt->bindValue(':ativo', 1, \PDO::PARAM_INT);
                $stmt->execute();
                $materiais = $stmt->fetchAll() ?: [];
            } catch (\Throwable $e) {
                error_log('[STUDENT SHOW] Erro ao buscar materiais: ' . $e->getMessage());
                $materiais = [];
            }

            $idEstrutura = (int) ($matricula['estrutura_id'] ?? 0);
            $disciplinas = [];
            $modulosDisciplinas = [];

            if ($idEstrutura > 0) {
                $modulos = [];
                try {
                    $stmt = $pdo->prepare(
                        'SELECT em.id, em.nome, em.descricao, em.ordem, em.carga_horaria'
                        . ' FROM estrutura_modulo em'
                        . ' WHERE em.id_estrutura = :id_estrutura AND em.ativo = :modulo_ativo'
                        . ' ORDER BY em.ordem ASC, em.id ASC'
                    );
                    $stmt->bindValue(':id_estrutura', $idEstrutura, \PDO::PARAM_INT);
                    $stmt->bindValue(':modulo_ativo', 1, \PDO::PARAM_INT);
                    $stmt->execute();
                    $modulos = $stmt->fetchAll() ?: [];
                } catch (\Throwable $e) {
                    error_log('[STUDENT SHOW] Erro ao buscar módulos: ' . $e->getMessage());
                    $modulos = [];
                }

                foreach ($modulos as $modulo) {
                    $disciplinasDoModulo = [];
                    try {
                        $stmt = $pdo->prepare(
                            'SELECT d.id, d.nome, d.carga_horaria, ed.ordem,'
                            . ' e.ementa, u.nome AS professor_nome'
                            . ' FROM estrutura_disciplina ed'
                            . ' INNER JOIN disciplina d ON d.id = ed.id_disciplina AND d.ativo = :disciplina_ativo'
                            . ' LEFT JOIN ementa e ON e.id_disciplina = d.id AND e.ativo = :ementa_ativo'
                            . ' LEFT JOIN turma_disciplina td ON td.id_turma = :id_turma AND td.id_disciplina = d.id AND td.ativo = :td_ativo'
                            . ' LEFT JOIN usuarios u ON u.id = td.id_usuario_professor'
                            . ' WHERE ed.id_modulo = :id_modulo AND ed.ativo = :ed_ativo'
                            . ' ORDER BY ed.ordem ASC, d.nome ASC'
                        );
                        $stmt->bindValue(':id_modulo', (int) ($modulo['id'] ?? 0), \PDO::PARAM_INT);
                        $stmt->bindValue(':id_turma', $turmaId, \PDO::PARAM_INT);
                        $stmt->bindValue(':disciplina_ativo', 1, \PDO::PARAM_INT);
                        $stmt->bindValue(':ementa_ativo', 1, \PDO::PARAM_INT);
                        $stmt->bindValue(':td_ativo', 1, \PDO::PARAM_INT);
                        $stmt->bindValue(':ed_ativo', 1, \PDO::PARAM_INT);
                        $stmt->execute();
                        $disciplinasDoModulo = $stmt->fetchAll() ?: [];
                    } catch (\Throwable $e) {
                        error_log('[STUDENT SHOW] Erro ao buscar disciplinas do módulo: ' . $e->getMessage());
                        $disciplinasDoModulo = [];
                    }
                    $modulosDisciplinas[] = [
                        'modulo' => $modulo,
                        'disciplinas' => $disciplinasDoModulo,
                    ];
                }
            } elseif ($cursoId > 0) {
                try {
                    $stmt = $pdo->prepare(
                        'SELECT d.id, d.nome, d.carga_horaria, d.ordem,'
                        . ' e.id AS ementa_id, e.ementa'
                        . ' FROM disciplina d'
                        . ' LEFT JOIN ementa e ON e.id_disciplina = d.id AND e.ativo = :ementa_ativo'
                        . ' WHERE d.id_curso = :id_curso AND d.ativo = :disciplina_ativo'
                        . ' ORDER BY d.ordem ASC, d.nome ASC'
                    );
                    $stmt->bindValue(':id_curso', $cursoId, \PDO::PARAM_INT);
                    $stmt->bindValue(':disciplina_ativo', 1, \PDO::PARAM_INT);
                    $stmt->bindValue(':ementa_ativo', 1, \PDO::PARAM_INT);
                    $stmt->execute();
                    $disciplinas = $stmt->fetchAll() ?: [];
                } catch (\Throwable $e) {
                    error_log('[STUDENT SHOW] Erro ao buscar disciplinas: ' . $e->getMessage());
                    $disciplinas = [];
                }
            }
        }

        $this->render('pages/aluno/show', [
            'title' => $matricula['curso_nome'] ?? 'Detalhes do Curso',
            'currentRoute' => '/aluno/cursos',
            'matricula' => $matricula,
            'professores' => $professores,
            'materiais' => $materiais,
            'disciplinas' => $disciplinas,
            'modulosDisciplinas' => $modulosDisciplinas,
        ], 'aluno');
    }

    public function professor(): void
    {
        if (!$this->auth->checkRole('aluno')) {
            Session::setFlash('flash', 'Faça login como aluno.');
            $this->redirect('/aluno/login');
        }

        $professorId = (int) ($_GET['id'] ?? 0);

        $pdo = Database::connection();
        $professor = null;
        $curriculo = null;

        if ($professorId > 0 && $pdo instanceof \PDO) {
            try {
                $stmt = $pdo->prepare(
                    'SELECT u.id, u.nome, u.email, u.telefone, u.foto,'
                    . ' cr.id AS curriculo_id, cr.resumo AS curriculo_resumo, cr.conteudo AS curriculo_conteudo'
                    . ' FROM usuarios u'
                    . ' LEFT JOIN curriculo cr ON cr.id_fk = u.id AND cr.tipo = :tipo_curriculo AND cr.ativo = :ativo_curriculo'
                    . ' WHERE u.id = :id AND u.tipo = :tipo_usuario AND u.ativo = :ativo_usuario'
                );
                $stmt->bindValue(':id', $professorId, \PDO::PARAM_INT);
                $stmt->bindValue(':tipo_usuario', 'professor', \PDO::PARAM_STR);
                $stmt->bindValue(':ativo_usuario', 1, \PDO::PARAM_INT);
                $stmt->bindValue(':tipo_curriculo', 'professor', \PDO::PARAM_STR);
                $stmt->bindValue(':ativo_curriculo', 1, \PDO::PARAM_INT);
                $stmt->execute();
                $professor = $stmt->fetch() ?: null;

                if ($professor && !empty($professor['curriculo_id'])) {
                    $curriculo = [
                        'resumo' => (string) ($professor['curriculo_resumo'] ?? ''),
                        'conteudo' => (string) ($professor['curriculo_conteudo'] ?? ''),
                    ];
                }
            } catch (\Throwable $e) {
                error_log('[STUDENT PROFESSOR] Erro ao buscar professor: ' . $e->getMessage());
                $professor = null;
            }
        }

        if (!$professor) {
            Session::setFlash('flash', 'Professor não encontrado.');
            $this->redirect('/aluno/cursos');
            return;
        }

        $this->render('pages/aluno/professor', [
            'title' => $professor['nome'] ?? 'Professor',
            'currentRoute' => '/aluno/cursos',
            'professor' => $professor,
            'curriculo' => $curriculo,
        ], 'aluno');
    }

    public function video(): void
    {
        if (!$this->auth->checkRole('aluno')) {
            Session::setFlash('flash', 'Faça login como aluno.');
            $this->redirect('/aluno/login');
        }

        $materialId = (int) ($_GET['id'] ?? 0);

        $pdo = Database::connection();
        $material = null;

        if ($pdo instanceof \PDO) {
            try {
                $stmt = $pdo->prepare(
                    'SELECT id, titulo, link, tipo, created_at'
                    . ' FROM material WHERE id = :id AND ativo = :ativo'
                );
                $stmt->bindValue(':id', $materialId, \PDO::PARAM_INT);
                $stmt->bindValue(':ativo', 1, \PDO::PARAM_INT);
                $stmt->execute();
                $material = $stmt->fetch() ?: null;
            } catch (\Throwable $e) {
                error_log('[STUDENT VIDEO] Erro: ' . $e->getMessage());
            }
        }

        if (!$material) {
            Session::setFlash('flash', 'Vídeo não encontrado.');
            $this->redirect('/aluno/cursos');
            return;
        }

        $this->logService->log('visualizar', 'video', $materialId, "Aluno visualizou vídeo: {$material['titulo']}");

        $this->render('pages/aluno/video', [
            'title' => $material['titulo'] ?? 'Vídeo',
            'currentRoute' => '/aluno/cursos',
            'material' => $material,
        ], 'aluno');
    }

    public function drive(): void
    {
        if (!$this->auth->checkRole('aluno')) {
            Session::setFlash('flash', 'Faça login como aluno.');
            $this->redirect('/aluno/login');
        }

        $materialId = (int) ($_GET['id'] ?? 0);

        $pdo = Database::connection();
        $material = null;

        if ($pdo instanceof \PDO) {
            try {
                $stmt = $pdo->prepare(
                    'SELECT id, titulo, link, tipo, created_at'
                    . ' FROM material WHERE id = :id AND ativo = :ativo'
                );
                $stmt->bindValue(':id', $materialId, \PDO::PARAM_INT);
                $stmt->bindValue(':ativo', 1, \PDO::PARAM_INT);
                $stmt->execute();
                $material = $stmt->fetch() ?: null;
            } catch (\Throwable $e) {
                error_log('[STUDENT DRIVE] Erro: ' . $e->getMessage());
            }
        }

        if (!$material) {
            Session::setFlash('flash', 'Documento não encontrado.');
            $this->redirect('/aluno/cursos');
            return;
        }

        $this->logService->log('visualizar', 'drive', $materialId, "Aluno visualizou documento do Drive: {$material['titulo']}");

        $this->render('pages/aluno/drive', [
            'title' => $material['titulo'] ?? 'Drive',
            'currentRoute' => '/aluno/cursos',
            'material' => $material,
        ], 'aluno');
    }

    public function detalhes(): void
    {
        if (!$this->auth->checkRole('aluno')) {
            Session::setFlash('flash', 'Faça login como aluno.');
            $this->redirect('/aluno/login');
        }

        $cursoId = (int) ($_GET['id'] ?? 0);
        $curso = $this->cursoService->findCurso($cursoId);

        if ($curso === null) {
            Session::setFlash('flash', 'Curso não encontrado.');
            $this->redirect('/aluno');
        }

        $this->render('pages/aluno/detalhes', [
            'title' => $curso['nome'] ?? 'Detalhes do Curso',
            'currentRoute' => '/aluno/detalhes',
            'curso' => $curso,
        ], 'aluno');
    }

    public function enrollCurso(): void
    {
        if (!$this->auth->checkRole('aluno')) {
            Session::setFlash('flash', 'Faça login como aluno para se matricular.');
            $this->redirect('/aluno/login');
        }

        $user = Session::get('user');
        $studentId = (int) ($user['id'] ?? 0);
        $cursoId = (int) $this->input('curso_id', 0);

        if ($cursoId <= 0) {
            Session::setFlash('flash', 'Curso inválido.');
            $this->redirect('/aluno');
        }

        $cursosMatriculados = $this->alunoService->cursosDoAluno($studentId);
        $idsMatriculados = array_map(static fn (array $m): int => (int) ($m['curso_id'] ?? 0), $cursosMatriculados);

        if (in_array($cursoId, $idsMatriculados, true)) {
            Session::setFlash('flash', 'Você já está matriculado neste curso.');
            $this->redirect('/aluno');
        }

        $turmas = $this->turmaService->turmasDoCurso($cursoId);

        if (empty($turmas)) {
            Session::setFlash('flash', 'Não há turmas disponíveis para este curso no momento.');
            $this->redirect('/aluno');
        }

        $matriculaId = $this->alunoService->criarMatricula($studentId, (int) $turmas[0]['id']);
        $this->logService->log('criar', 'matricula', $matriculaId, "Aluno matriculou-se no curso $cursoId");
        Session::setFlash('flash', 'Matrícula realizada com sucesso!');
        $this->redirect('/aluno');
    }

    public function perfil(): void
    {
        if (!$this->auth->checkRole('aluno')) {
            Session::setFlash('flash', 'Faça login como aluno para acessar o perfil.');
            $this->redirect('/aluno/login');
        }

        $user = Session::get('user');
        $studentId = (int) ($user['id'] ?? 0);
        $aluno = $this->alunoService->findAluno($studentId);

        $this->render('pages/aluno/perfil', [
            'title' => 'Meu Perfil',
            'currentRoute' => '/aluno/perfil',
            'aluno' => $aluno,
        ], 'aluno');
    }

    public function atualizarPerfil(): void
    {
        if (!$this->auth->checkRole('aluno')) {
            Session::setFlash('flash', 'Faça login como aluno.');
            $this->redirect('/aluno/login');
        }

        $user = Session::get('user');
        $studentId = (int) ($user['id'] ?? 0);

        $nome = trim((string) $this->input('nome', ''));
        $cpf = trim((string) $this->input('cpf', ''));
        $dataNascimento = (string) $this->input('data_nascimento', '');
        $telefone = trim((string) $this->input('telefone', ''));
        $email = trim((string) $this->input('email', ''));
        $senha = (string) $this->input('senha', '');

        $this->alunoService->atualizarAluno($studentId, $nome, $cpf, $dataNascimento, $telefone, $email, 1, $senha ?: null);
        $this->logService->log('atualizar', 'aluno', $studentId, "Aluno atualizou o próprio perfil");

        Session::setFlash('flash', 'Perfil atualizado com sucesso.');
        $this->redirect('/aluno/perfil');
    }

    public function endereco(): void
    {
        if (!$this->auth->checkRole('aluno')) {
            Session::setFlash('flash', 'Faça login como aluno para acessar o endereço.');
            $this->redirect('/aluno/login');
        }

        $user = Session::get('user');
        $studentId = (int) ($user['id'] ?? 0);

        $endereco = null;
        $pdo = Database::connection();
        if ($pdo instanceof \PDO) {
            try {
                $stmt = $pdo->prepare('SELECT id, cep, logradouro, numero, cidade, uf FROM endereco WHERE tipo = :tipo AND id_fk = :id_fk LIMIT 1');
                $stmt->bindValue(':tipo', 'aluno', \PDO::PARAM_STR);
                $stmt->bindValue(':id_fk', $studentId, \PDO::PARAM_INT);
                $stmt->execute();
                $endereco = $stmt->fetch() ?: null;
            } catch (\Throwable $e) {
                error_log('[STUDENT ENDERECO] Erro: ' . $e->getMessage());
            }
        }

        $this->render('pages/aluno/endereco', [
            'title' => 'Meu Endereço',
            'currentRoute' => '/aluno/endereco',
            'endereco' => $endereco,
        ], 'aluno');
    }

    public function atualizarEndereco(): void
    {
        if (!$this->auth->checkRole('aluno')) {
            Session::setFlash('flash', 'Faça login como aluno.');
            $this->redirect('/aluno/login');
        }

        $user = Session::get('user');
        $studentId = (int) ($user['id'] ?? 0);

        $cep = preg_replace('/\D/', '', (string) $this->input('cep', ''));
        $logradouro = trim((string) $this->input('logradouro', ''));
        $numero = trim((string) $this->input('numero', ''));
        $cidade = trim((string) $this->input('cidade', ''));
        $uf = strtoupper(substr(trim((string) $this->input('uf', '')), 0, 2));

        if ($cep === '' || $logradouro === '' || $cidade === '' || $uf === '') {
            Session::setFlash('flash', 'Preencha CEP, logradouro, cidade e UF.');
            $this->redirect('/aluno/endereco');
            return;
        }

        $pdo = Database::connection();
        if (!$pdo instanceof \PDO) {
            Session::setFlash('flash', 'Erro de conexão com o banco de dados.');
            $this->redirect('/aluno/endereco');
            return;
        }

        try {
            $stmt = $pdo->prepare('SELECT id FROM endereco WHERE tipo = :tipo AND id_fk = :id_fk LIMIT 1');
            $stmt->bindValue(':tipo', 'aluno', \PDO::PARAM_STR);
            $stmt->bindValue(':id_fk', $studentId, \PDO::PARAM_INT);
            $stmt->execute();
            $existente = $stmt->fetch();

            if ($existente) {
                $update = $pdo->prepare('UPDATE endereco SET cep = :cep, logradouro = :logradouro, numero = :numero, cidade = :cidade, uf = :uf WHERE id = :id');
                $update->bindValue(':cep', $cep, \PDO::PARAM_STR);
                $update->bindValue(':logradouro', $logradouro, \PDO::PARAM_STR);
                $update->bindValue(':numero', $numero, \PDO::PARAM_STR);
                $update->bindValue(':cidade', $cidade, \PDO::PARAM_STR);
                $update->bindValue(':uf', $uf, \PDO::PARAM_STR);
                $update->bindValue(':id', (int) $existente['id'], \PDO::PARAM_INT);
                $update->execute();
                $this->logService->log('atualizar', 'endereco', (int) $existente['id'], "Aluno atualizou o endereço");
            } else {
                $insert = $pdo->prepare('INSERT INTO endereco (tipo, id_fk, cep, logradouro, numero, cidade, uf) VALUES (:tipo, :id_fk, :cep, :logradouro, :numero, :cidade, :uf)');
                $insert->bindValue(':tipo', 'aluno', \PDO::PARAM_STR);
                $insert->bindValue(':id_fk', $studentId, \PDO::PARAM_INT);
                $insert->bindValue(':cep', $cep, \PDO::PARAM_STR);
                $insert->bindValue(':logradouro', $logradouro, \PDO::PARAM_STR);
                $insert->bindValue(':numero', $numero, \PDO::PARAM_STR);
                $insert->bindValue(':cidade', $cidade, \PDO::PARAM_STR);
                $insert->bindValue(':uf', $uf, \PDO::PARAM_STR);
                $insert->execute();
                $this->logService->log('criar', 'endereco', (int) $pdo->lastInsertId(), "Aluno cadastrou o endereço");
            }
        } catch (\Throwable $e) {
            error_log('[STUDENT ENDERECO] Erro ao salvar: ' . $e->getMessage());
            Session::setFlash('flash', 'Erro ao salvar o endereço.');
            $this->redirect('/aluno/endereco');
            return;
        }

        Session::setFlash('flash', 'Endereço salvo com sucesso.');
        $this->redirect('/aluno/endereco');
    }

    public function buscarCep(): void
    {
        ini_set('display_errors', '0');
        error_reporting(0);

        if (!$this->auth->checkRole('aluno')) {
            http_response_code(403);
            header('Content-Type: application/json');
            echo json_encode(['erro' => 'Acesso negado.']);
            return;
        }

        $cep = preg_replace('/[^0-9]/', '', (string) ($_GET['cep'] ?? ''));

        if (strlen($cep) !== 8) {
            http_response_code(400);
            header('Content-Type: application/json');
            echo json_encode(['erro' => 'CEP inválido']);
            return;
        }

        $url = "https://viacep.com.br/ws/{$cep}/json/";

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
        curl_close($ch);

        if ($response === false || $httpCode !== 200 || $response === '' || $response[0] !== '{') {
            http_response_code(502);
            header('Content-Type: application/json');
            echo json_encode(['erro' => 'Falha ao consultar ViaCEP']);
            return;
        }

        $data = json_decode($response, true);

        if (!is_array($data) || isset($data['erro'])) {
            http_response_code(404);
            header('Content-Type: application/json');
            echo json_encode(['erro' => 'CEP não encontrado']);
            return;
        }

        $user = Session::get('user');
        $studentId = (int) ($user['id'] ?? 0);
        $this->logService->log('consultar_cep', 'endereco', $studentId, "Consulta de CEP: $cep");

        header('Content-Type: application/json');
        echo json_encode([
            'logradouro' => $data['logradouro'] ?? '',
            'bairro' => $data['bairro'] ?? '',
            'cidade' => $data['localidade'] ?? '',
            'uf' => $data['uf'] ?? '',
        ]);
    }

    public function foto(): void
    {
        if (!$this->auth->checkRole('aluno')) {
            Session::setFlash('flash', 'Faça login como aluno.');
            $this->redirect('/aluno/login');
        }

        $user = Session::get('user');
        $studentId = (int) ($user['id'] ?? 0);

        $file = $_FILES['foto'] ?? null;

        if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
            Session::setFlash('flash', 'Erro ao enviar a foto.');
            $this->redirect('/aluno/perfil');
            return;
        }

        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png'];

        if (!in_array($ext, $allowed, true)) {
            Session::setFlash('flash', 'Formato não permitido. Use jpg, jpeg ou png.');
            $this->redirect('/aluno/perfil');
            return;
        }

        if ($file['size'] > 1048576) {
            Session::setFlash('flash', 'A foto deve ter no máximo 1MB.');
            $this->redirect('/aluno/perfil');
            return;
        }

        $filename = 'aluno_' . $studentId . '_' . time() . '.' . $ext;
        $destDir = dirname(__DIR__, 2) . '/public/assets/img/alunos';
        if (!is_dir($destDir)) {
            mkdir($destDir, 0755, true);
        }

        $destPath = $destDir . '/' . $filename;

        if (!move_uploaded_file($file['tmp_name'], $destPath)) {
            Session::setFlash('flash', 'Falha ao salvar a foto.');
            $this->redirect('/aluno/perfil');
            return;
        }

        $filePath = 'assets/img/alunos/' . $filename;
        $this->alunoService->atualizarFotoAluno($studentId, $filePath);
        $this->logService->log('atualizar', 'aluno', $studentId, "Aluno atualizou a própria foto");

        Session::setFlash('flash', 'Foto atualizada com sucesso.');
        $this->redirect('/aluno/perfil');
    }

    public function logs(): void
    {
        if (!$this->auth->checkRole('aluno')) {
            Session::setFlash('flash', 'Faça login como aluno.');
            $this->redirect('/aluno/login');
        }

        $user = Session::get('user');
        $studentId = (int) ($user['id'] ?? 0);

        $pdo = Database::connection();
        $entries = [];

        if ($pdo instanceof \PDO) {
            try {
                $stmt = $pdo->prepare(
                    'SELECT l.id, l.acao, l.entidade, l.descricao, l.ip, l.created_at, a.nome AS aluno_nome'
                    . ' FROM logs_auditoria l'
                    . ' LEFT JOIN alunos a ON a.id = l.usuario_id'
                    . ' WHERE l.usuario_id = :usuario_id AND l.perfil = :perfil'
                    . ' ORDER BY l.id DESC'
                    . ' LIMIT 100'
                );
                $stmt->bindValue(':usuario_id', $studentId, \PDO::PARAM_INT);
                $stmt->bindValue(':perfil', 'aluno', \PDO::PARAM_STR);
                $stmt->execute();
                $entries = $stmt->fetchAll() ?: [];
            } catch (\Throwable $e) {
                error_log('[STUDENT LOGS] Erro: ' . $e->getMessage());
            }
        }

        $geo = new IpLocationService();
        $ipCache = [];
        foreach ($entries as &$log) {
            $ip = (string) ($log['ip'] ?? '127.0.0.1');
            if (!array_key_exists($ip, $ipCache)) {
                $ipCache[$ip] = $geo->resolve($ip);
            }
            $log['location'] = $ipCache[$ip];
        }
        unset($log);

        $this->render('pages/aluno/logs', [
            'title' => 'Meus Logs',
            'currentRoute' => '/aluno/logs',
            'entries' => $entries,
        ], 'aluno');
    }

    public function notificacoes(): void
    {
        if (!$this->auth->checkRole('aluno')) {
            Session::setFlash('flash', 'Faça login como aluno.');
            $this->redirect('/aluno/login');
        }

        $user = Session::get('user');
        $studentId = (int) ($user['id'] ?? 0);

        $notificacoes = [];
        $pdo = Database::connection();
        if ($pdo instanceof \PDO) {
            try {
                $stmt = $pdo->prepare(
                    'SELECT n.*, u.nome AS origem_nome,'
                    . ' t.nome AS destino_turma_nome,'
                    . ' nl.id IS NOT NULL AS lida'
                    . ' FROM notificacao n'
                    . ' LEFT JOIN usuarios u ON n.id_usuario_origem = u.id'
                    . ' LEFT JOIN turmas t ON n.tipo_destino = \'turma\' AND n.id_destino = t.id'
                    . ' LEFT JOIN notificacao_leitura_aluno nl ON nl.id_notificacao = n.id AND nl.id_aluno = :aluno_id'
                    . ' WHERE ('
                    . ' (n.tipo_destino = \'aluno\' AND n.id_destino = :aluno_id2)'
                    . ' OR (n.tipo_destino = \'turma\' AND n.id_destino IN ('
                    . '   SELECT m.id_turma FROM matricula m WHERE m.id_aluno = :aluno_id3 AND m.status NOT IN (\'cancelado\',\'concluido\')'
                    . ' ))'
                    . ' )'
                    . ' AND n.ativo = :ativo'
                    . ' ORDER BY n.created_at DESC'
                    . ' LIMIT 200'
                );
                $stmt->bindValue(':aluno_id', $studentId, \PDO::PARAM_INT);
                $stmt->bindValue(':aluno_id2', $studentId, \PDO::PARAM_INT);
                $stmt->bindValue(':aluno_id3', $studentId, \PDO::PARAM_INT);
                $stmt->bindValue(':ativo', 1, \PDO::PARAM_INT);
                $stmt->execute();
                $notificacoes = $stmt->fetchAll() ?: [];
            } catch (\Throwable $e) {
                error_log('[STUDENT NOTIFICACOES] Erro: ' . $e->getMessage());
                $notificacoes = [];
            }
        }

        $this->render('pages/aluno/notificacoes', [
            'title' => 'Notificações',
            'currentRoute' => '/aluno/notificacoes',
            'notificacoes' => $notificacoes,
        ], 'aluno');
    }

    public function marcarLida(): void
    {
        if (!$this->auth->checkRole('aluno')) {
            $this->json(['erro' => 'Acesso negado.'], 403);
        }

        $user = Session::get('user');
        $studentId = (int) ($user['id'] ?? 0);
        $notificacaoId = (int) $this->input('id', 0);

        if ($notificacaoId <= 0) {
            $this->json(['erro' => 'ID inválido.'], 400);
        }

        $pdo = Database::connection();
        if (!$pdo instanceof \PDO) {
            $this->json(['erro' => 'Erro de conexão.'], 500);
        }

        try {
            $stmt = $pdo->prepare(
                'SELECT n.id FROM notificacao n'
                . ' WHERE n.ativo = :ativo AND ('
                . ' (n.tipo_destino = \'aluno\' AND n.id_destino = :aluno_id)'
                . ' OR (n.tipo_destino = \'turma\' AND n.id_destino IN ('
                . '   SELECT m.id_turma FROM matricula m WHERE m.id_aluno = :aluno_id2 AND m.status NOT IN (\'cancelado\',\'concluido\')'
                . ' ))'
                . ' )'
                . ' AND n.id = :id'
            );
            $stmt->bindValue(':id', $notificacaoId, \PDO::PARAM_INT);
            $stmt->bindValue(':ativo', 1, \PDO::PARAM_INT);
            $stmt->bindValue(':aluno_id', $studentId, \PDO::PARAM_INT);
            $stmt->bindValue(':aluno_id2', $studentId, \PDO::PARAM_INT);
            $stmt->execute();

            if (!$stmt->fetch()) {
                $this->json(['erro' => 'Notificação não encontrada para este aluno.'], 404);
            }

            $stmt = $pdo->prepare(
                'INSERT IGNORE INTO notificacao_leitura_aluno (id_notificacao, id_aluno) VALUES (:id_notificacao, :id_aluno)'
            );
            $stmt->bindValue(':id_notificacao', $notificacaoId, \PDO::PARAM_INT);
            $stmt->bindValue(':id_aluno', $studentId, \PDO::PARAM_INT);
            $stmt->execute();

            $this->logService->log('ler', 'notificacao', $notificacaoId, 'Notificação marcada como lida pelo aluno');

            $this->json(['sucesso' => true]);
        } catch (\Throwable $e) {
            error_log('[STUDENT MARCAR LIDA] Erro: ' . $e->getMessage());
            $this->json(['erro' => 'Erro ao marcar como lida.'], 500);
        }
    }

    public function documentos(): void
    {
        if (!$this->auth->checkRole('aluno')) {
            Session::setFlash('flash', 'Faça login como aluno para acessar os documentos.');
            $this->redirect('/aluno/login');
        }

        $user = Session::get('user');
        $studentId = (int) ($user['id'] ?? 0);

        $grupoAlunos = StorageService::GROUP_ALUNOS;
        $pdo = Database::connection();

        $aluno = $this->alunoService->findAluno($studentId);
        $nomeAluno = (string) ($aluno['nome'] ?? '');

        $storage = new StorageService();
        $pasta = null;

        if ($pdo instanceof \PDO) {
            $storageDriveRepo = new StorageDriveRepository();
            $pasta = $storageDriveRepo->findByRegistro($grupoAlunos, $studentId);

            if ($pasta === null && $storage->isConnected()) {
                try {
                    $folderId = $storage->ensureRegistroFolder($grupoAlunos, (string) $studentId, $nomeAluno);
                    if ($folderId !== '') {
                        $storageDriveRepo->create([
                            'id_grupo' => $grupoAlunos,
                            'id_registro' => $studentId,
                            'folder_id' => $folderId,
                            'folder_name' => sprintf('%06d-%s', $studentId, $nomeAluno),
                            'folder_link' => $storage->generateViewLinkByFileId($folderId),
                            'tipo' => 'registro',
                            'nivel' => 2,
                        ]);
                        $pasta = $storageDriveRepo->findByRegistro($grupoAlunos, $studentId);
                    }
                } catch (StorageException $e) {
                    error_log('[STUDENT DOCUMENTOS] Erro ao criar pasta: ' . $e->getMessage());
                } catch (\Throwable $e) {
                    error_log('[STUDENT DOCUMENTOS] Erro ao criar pasta: ' . $e->getMessage());
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
                $stmt->bindValue(':id_registro', $studentId, \PDO::PARAM_INT);
                $stmt->bindValue(':id_grupo', $grupoAlunos, \PDO::PARAM_INT);
                $stmt->execute();
                $documentos = $stmt->fetchAll() ?: [];
            } catch (\Throwable $e) {
                error_log('[STUDENT DOCUMENTOS] Erro: ' . $e->getMessage());
                $documentos = [];
            }
        }

        $this->render('pages/aluno/documentos', [
            'title' => 'Meus Documentos',
            'currentRoute' => '/aluno/documentos',
            'documentos' => $documentos,
            'pasta' => $pasta,
            'storageConectado' => $storage->isConnected(),
            'storageErro' => $storage->isConnected() ? null : 'Storage não conectado.',
        ], 'aluno');
    }

    public function uploadDocumento(): void
    {
        if (!$this->auth->checkRole('aluno')) {
            Session::setFlash('flash', 'Faça login como aluno.');
            $this->redirect('/aluno/login');
        }

        $user = Session::get('user');
        $studentId = (int) ($user['id'] ?? 0);
        $grupoAlunos = StorageService::GROUP_ALUNOS;

        $tipoId = (int) $this->input('id_tipo', 0);
        $file = $_FILES['arquivo'] ?? null;

        if ($tipoId <= 0 || !$file) {
            Session::setFlash('flash', 'Selecione o tipo de documento e o arquivo.');
            $this->redirect('/aluno/documentos');
            return;
        }

        if ($file['error'] !== UPLOAD_ERR_OK) {
            Session::setFlash('flash', 'Erro no upload do arquivo.');
            $this->redirect('/aluno/documentos');
            return;
        }

        $originalName = (string) ($file['name'] ?? '');
        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        $allowed = ['pdf', 'png', 'jpg', 'jpeg'];

        if (!in_array($extension, $allowed, true)) {
            Session::setFlash('flash', 'Formato não permitido. Use PDF, PNG, JPG ou JPEG.');
            $this->redirect('/aluno/documentos');
            return;
        }

        if ((int) ($file['size'] ?? 0) > 20 * 1024 * 1024) {
            Session::setFlash('flash', 'O arquivo deve ter no máximo 20MB.');
            $this->redirect('/aluno/documentos');
            return;
        }

        $pdo = Database::connection();
        if (!$pdo instanceof \PDO) {
            Session::setFlash('flash', 'Erro de conexão com o banco de dados.');
            $this->redirect('/aluno/documentos');
            return;
        }

        $tipo = null;
        try {
            $stmt = $pdo->prepare('SELECT id, descricao FROM documento_tipo WHERE id = :id AND id_grupo = :id_grupo AND ativo = 1 LIMIT 1');
            $stmt->bindValue(':id', $tipoId, \PDO::PARAM_INT);
            $stmt->bindValue(':id_grupo', $grupoAlunos, \PDO::PARAM_INT);
            $stmt->execute();
            $tipo = $stmt->fetch() ?: null;
        } catch (\Throwable $e) {
            error_log('[STUDENT UPLOAD DOC] Erro: ' . $e->getMessage());
        }

        if (!$tipo) {
            Session::setFlash('flash', 'Tipo de documento inválido.');
            $this->redirect('/aluno/documentos');
            return;
        }

        $storageDriveRepo = new StorageDriveRepository();
        $pasta = $storageDriveRepo->findByRegistro($grupoAlunos, $studentId);

        if ($pasta === null) {
            Session::setFlash('flash', 'Pasta do aluno no Drive não encontrada. Fale com a secretaria.');
            $this->redirect('/aluno/documentos');
            return;
        }

        $storage = new StorageService();
        if (!$storage->isConnected()) {
            Session::setFlash('flash', 'Storage não conectado. Tente novamente mais tarde.');
            $this->redirect('/aluno/documentos');
            return;
        }

        $documentoAtual = null;
        try {
            $stmt = $pdo->prepare('SELECT id, versao, status FROM documento WHERE id_tipo = :id_tipo AND id_registro = :id_registro AND ativo = 1 ORDER BY versao DESC, id DESC LIMIT 1');
            $stmt->bindValue(':id_tipo', $tipoId, \PDO::PARAM_INT);
            $stmt->bindValue(':id_registro', $studentId, \PDO::PARAM_INT);
            $stmt->execute();
            $documentoAtual = $stmt->fetch() ?: null;
        } catch (\Throwable $e) {
            error_log('[STUDENT UPLOAD DOC] Erro: ' . $e->getMessage());
        }

        if ($documentoAtual !== null) {
            $statusAtual = (string) ($documentoAtual['status'] ?? '');
            if ($statusAtual === 'em_analise' || $statusAtual === 'aprovado') {
                Session::setFlash('flash', 'Este documento está em análise/aprovado e não pode ser substituído.');
                $this->redirect('/aluno/documentos');
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
                $grupoAlunos,
                $studentId,
                $tipoId,
                (string) ($pasta['folder_id'] ?? ''),
                $nomeDrive,
                'enviado'
            );

            $this->logService->log('upload', 'documento', (int) $result['id'], "Aluno enviou documento {$tipo['descricao']} (v{$versao})");
            Session::setFlash('flash', 'Documento enviado com sucesso.');
        } catch (StorageException $e) {
            error_log('[STUDENT UPLOAD DOC] Storage: ' . $e->getMessage());
            Session::setFlash('flash', 'Erro ao enviar o documento: ' . $e->getMessage());
        } catch (\Throwable $e) {
            error_log('[STUDENT UPLOAD DOC] Erro: ' . $e->getMessage());
            Session::setFlash('flash', 'Erro ao enviar o documento.');
        }

        $this->redirect('/aluno/documentos');
    }

    public function visualizarDocumento(): void
    {
        if (!$this->auth->checkRole('aluno')) {
            Session::setFlash('flash', 'Faça login como aluno.');
            $this->redirect('/aluno/login');
        }

        $user = Session::get('user');
        $studentId = (int) ($user['id'] ?? 0);
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
                error_log('[STUDENT VIEW DOC] Erro: ' . $e->getMessage());
            }
        }

        if ($documento === null || (int) ($documento['id_registro'] ?? 0) !== $studentId) {
            Session::setFlash('flash', 'Documento não encontrado.');
            $this->redirect('/aluno/documentos');
            return;
        }

        try {
            $storage = new StorageService();
            $link = $storage->generateViewLink($documentoId);
            $this->logService->log('visualizar', 'documento', $documentoId, "Aluno visualizou documento: {$documento['nome_original']}");
            $this->redirect($link);
        } catch (StorageException $e) {
            Session::setFlash('flash', 'Erro ao visualizar o documento.');
            $this->redirect('/aluno/documentos');
        } catch (\Throwable $e) {
            Session::setFlash('flash', 'Erro ao visualizar o documento.');
            $this->redirect('/aluno/documentos');
        }
    }

    public function baixarDocumento(): void
    {
        if (!$this->auth->checkRole('aluno')) {
            Session::setFlash('flash', 'Faça login como aluno.');
            $this->redirect('/aluno/login');
        }

        $user = Session::get('user');
        $studentId = (int) ($user['id'] ?? 0);
        $documentoId = (int) ($_GET['id'] ?? 0);

        $pdo = Database::connection();
        $documento = null;
        if ($pdo instanceof \PDO) {
            try {
                $stmt = $pdo->prepare('SELECT id, id_registro, nome_original, mime_type, tamanho FROM documento WHERE id = :id AND ativo = 1 LIMIT 1');
                $stmt->bindValue(':id', $documentoId, \PDO::PARAM_INT);
                $stmt->execute();
                $documento = $stmt->fetch() ?: null;
            } catch (\Throwable $e) {
                error_log('[STUDENT DOWNLOAD DOC] Erro: ' . $e->getMessage());
            }
        }

        if ($documento === null || (int) ($documento['id_registro'] ?? 0) !== $studentId) {
            Session::setFlash('flash', 'Documento não encontrado.');
            $this->redirect('/aluno/documentos');
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
        } catch (StorageException $e) {
            Session::setFlash('flash', 'Erro ao baixar o documento.');
            $this->redirect('/aluno/documentos');
        } catch (\Throwable $e) {
            Session::setFlash('flash', 'Erro ao baixar o documento.');
            $this->redirect('/aluno/documentos');
        }
    }

    public function excluirDocumento(): void
    {
        if (!$this->auth->checkRole('aluno')) {
            Session::setFlash('flash', 'Faça login como aluno.');
            $this->redirect('/aluno/login');
        }

        $user = Session::get('user');
        $studentId = (int) ($user['id'] ?? 0);
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
                error_log('[STUDENT DELETE DOC] Erro: ' . $e->getMessage());
            }
        }

        if ($documento === null || (int) ($documento['id_registro'] ?? 0) !== $studentId) {
            Session::setFlash('flash', 'Documento não encontrado.');
            $this->redirect('/aluno/documentos');
            return;
        }

        $status = (string) ($documento['status'] ?? '');
        if (in_array($status, ['em_analise', 'aprovado', 'rejeitado'], true)) {
            Session::setFlash('flash', 'Este documento já foi analisado e não pode ser excluído.');
            $this->redirect('/aluno/documentos');
            return;
        }

        try {
            $storage = new StorageService();
            $storage->delete($documentoId);
            $this->logService->log('excluir', 'documento', $documentoId, "Aluno excluiu documento: {$documento['nome_original']}");
            Session::setFlash('flash', 'Documento excluído com sucesso.');
        } catch (StorageException $e) {
            Session::setFlash('flash', 'Erro ao excluir o documento.');
        } catch (\Throwable $e) {
            Session::setFlash('flash', 'Erro ao excluir o documento.');
        }

        $this->redirect('/aluno/documentos');
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

    public function enroll(): void
    {
        if (!$this->auth->checkRole('aluno')) {
            Session::setFlash('flash', 'Faça login como aluno para se matricular.');
            $this->redirect('/aluno/login');
        }

        $user = Session::get('user');
        $studentId = (int) ($user['id'] ?? 0);
        $courseId = (int) $this->input('course_id', 0);

        $result = $this->enrollments->enroll($studentId, $courseId);
        if (($result['ok'] ?? false)) {
            $this->logService->log('criar', 'matricula', 0, "Aluno matriculou-se no curso $courseId");
        }
        Session::setFlash('flash', $result['message']);
        $this->redirect('/aluno');
    }
}
