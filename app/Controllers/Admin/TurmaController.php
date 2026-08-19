<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Database;
use App\Services\TurmaService;
use App\Services\CursoService;
use App\Services\EstruturaCurricularService;
use App\Services\LogService;
use App\Support\Session;

final class TurmaController extends Controller
{
    private TurmaService $turmaService;
    private CursoService $cursoService;
    private EstruturaCurricularService $estruturaService;
    private LogService $logService;

    public function __construct()
    {
        $this->turmaService = new TurmaService();
        $this->cursoService = new CursoService();
        $this->estruturaService = new EstruturaCurricularService();
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

    public function geracao(): void
    {
        if (!$this->isStaff()) {
            Session::setFlash('flash', 'Faça login como admin, operador ou professor para acessar as turmas.');
            $this->redirect('/admin/login');
        }

        $cursos = [];
        $pdo = Database::connection();
        if ($pdo instanceof \PDO) {
            try {
                $stmt = $pdo->prepare(
                    'SELECT c.id, c.nome, c.tipo_curso AS nivel_id,'
                    . ' COALESCE(n.nome, \'Outros\') AS nivel_nome, n.slug AS nivel_slug'
                    . ' FROM cursos c'
                    . ' LEFT JOIN tipo_curso n ON n.id = c.tipo_curso'
                    . ' WHERE c.ativo = 1'
                    . ' ORDER BY c.tipo_curso ASC, c.nome ASC'
                );
                $stmt->execute();
                $rows = $stmt->fetchAll();
                $cursos = is_array($rows) ? $rows : [];
            } catch (\Throwable $e) {
                error_log('[TURMA GERACAO] Erro: ' . $e->getMessage());
                $cursos = [];
            }
        }

        $cursosPorTipo = [];
        $pdo2 = Database::connection();
        $turmasPorCurso = [];
        if ($pdo2 instanceof \PDO) {
            try {
                $stmt = $pdo2->query('SELECT id_curso, nome FROM turmas WHERE ativo = 1 ORDER BY nome ASC');
                $rows = $stmt->fetchAll();
                if (is_array($rows)) {
                    foreach ($rows as $row) {
                        $idCurso = (int) ($row['id_curso'] ?? 0);
                        if (!isset($turmasPorCurso[$idCurso])) {
                            $turmasPorCurso[$idCurso] = [];
                        }
                        $turmasPorCurso[$idCurso][] = (string) ($row['nome'] ?? '');
                    }
                }
            } catch (\Throwable $e) {
                error_log('[TURMA GERACAO] Erro ao buscar turmas: ' . $e->getMessage());
            }
        }

        foreach ($cursos as $curso) {
            $tipoId = (int) ($curso['nivel_id'] ?? 0);
            $tipoNome = trim((string) ($curso['nivel_nome'] ?? ''));
            if ($tipoId <= 0) {
                $tipoId = 0;
                $tipoNome = $tipoNome !== '' ? $tipoNome : 'Outros';
            }
            if (!isset($cursosPorTipo[$tipoId])) {
                $cursosPorTipo[$tipoId] = ['nome' => $tipoNome, 'cursos' => []];
            }
            $idCurso = (int) ($curso['id'] ?? 0);
            $cursosPorTipo[$tipoId]['cursos'][] = [
                'id' => $idCurso,
                'nome' => (string) ($curso['nome'] ?? ''),
                'nome_turma' => $this->nomeTurmaGerada((string) ($curso['nome'] ?? '')),
                'tem_turma' => !empty($turmasPorCurso[$idCurso]),
                'turmas_existentes' => $turmasPorCurso[$idCurso] ?? [],
            ];
        }

        $this->render('pages/admin/turmas/geracao', [
            'title' => 'Geração de Turmas',
            'currentRoute' => '/admin/turmas',
            'cursosPorTipo' => $cursosPorTipo,
        ], 'admin');
    }

    public function geracaoConfirmar(): void
    {
        if (!$this->isStaff()) {
            Session::setFlash('flash', 'Faça login como admin, operador ou professor para acessar as turmas.');
            $this->redirect('/admin/login');
        }

        $cursoId = (int) $this->input('curso_id', 0);
        $nome = trim((string) $this->input('nome', ''));

        if ($cursoId <= 0 || $nome === '') {
            Session::setFlash('flash', 'Selecione um curso e informe o nome da turma.');
            $this->redirect('/admin/turmas/geracao');
            return;
        }

        $pdo = Database::connection();
        $curso = null;
        if ($pdo instanceof \PDO) {
            try {
                $stmt = $pdo->prepare(
                    'SELECT c.id, c.nome, c.tipo_curso AS nivel_id, n.slug AS nivel_slug'
                    . ' FROM cursos c'
                    . ' LEFT JOIN tipo_curso n ON n.id = c.tipo_curso'
                    . ' WHERE c.id = :id AND c.ativo = 1'
                    . ' LIMIT 1'
                );
                $stmt->bindValue(':id', $cursoId, \PDO::PARAM_INT);
                $stmt->execute();
                $curso = $stmt->fetch() ?: null;
            } catch (\Throwable $e) {
                error_log('[TURMA GERACAO CONFIRMAR] Erro ao buscar curso: ' . $e->getMessage());
            }
        }

        if (!$curso) {
            Session::setFlash('flash', 'Curso não encontrado ou inativo.');
            $this->redirect('/admin/turmas/geracao');
            return;
        }

        $existe = false;
        if ($pdo instanceof \PDO) {
            try {
                $stmt = $pdo->prepare('SELECT id FROM turmas WHERE id_curso = :id_curso AND nome = :nome AND ativo = 1 LIMIT 1');
                $stmt->bindValue(':id_curso', $cursoId, \PDO::PARAM_INT);
                $stmt->bindValue(':nome', $nome, \PDO::PARAM_STR);
                $stmt->execute();
                $existe = (bool) $stmt->fetch();
            } catch (\Throwable $e) {
                error_log('[TURMA GERACAO CONFIRMAR] Erro ao verificar duplicidade: ' . $e->getMessage());
            }
        }

        if ($existe) {
            Session::setFlash('flash', 'Já existe uma turma ativa com este nome para este curso.');
            $this->redirect('/admin/turmas/geracao');
            return;
        }

        $idEstrutura = null;
        if (strtolower(trim((string) ($curso['nivel_slug'] ?? ''))) !== 'cursos-livres') {
            $matrizes = $this->estruturaService->listarMatrizes($cursoId, 1);
            if (!empty($matrizes)) {
                $idEstrutura = (int) ($matrizes[0]['id'] ?? 0);
            }
        }

        $turmaId = $this->turmaService->criarTurma($nome, $cursoId, '', 1, $idEstrutura > 0 ? $idEstrutura : null);

        if ($turmaId > 0) {
            $this->logService->log('criar', 'turma', $turmaId, "Turma gerada: $nome (curso {$curso['nome']})");
            Session::setFlash('flash', "Turma \"$nome\" criada com sucesso.");
            $this->redirect('/admin/turmas');
        } else {
            Session::setFlash('flash', 'Erro ao criar a turma. Tente novamente.');
            $this->redirect('/admin/turmas/geracao');
        }
    }

    public function matriculas(): void
    {
        if (!$this->isStaff()) {
            Session::setFlash('flash', 'Faça login como admin, operador ou professor para acessar as matrículas.');
            $this->redirect('/admin/login');
            return;
        }

        $this->render('pages/admin/matriculas/index', [
            'title' => 'Matrículas',
            'currentRoute' => '/admin/matriculas',
            'matriculas' => $this->turmaService->matriculasAtivas(),
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

        $inscritos = $this->turmaService->inscritosPorTurma($id);

        $disciplinasMatriculadas = [];
        foreach ($inscritos as &$inscrito) {
            $idMatricula = (int) ($inscrito['id_matricula'] ?? 0);
            $disciplinasMatriculadas[$idMatricula] = $this->estruturaService->listarIdsDisciplinasDaMatricula($idMatricula);
        }
        unset($inscrito);

        $disciplinasTurma = $this->estruturaService->listarDisciplinasDaTurma($id);

        $disciplinasDaTurmaPorDisciplina = [];
        foreach ($disciplinasTurma as $disciplinaDaTurma) {
            $disciplinasDaTurmaPorDisciplina[(int) ($disciplinaDaTurma['id_disciplina'] ?? 0)] = $disciplinaDaTurma;
        }

        $modulosDaTurma = $this->estruturaService->listarModulosDaTurma($id);

        $disciplinasDosModulos = [];
        foreach ($modulosDaTurma as $modulo) {
            $disciplinas = $this->estruturaService->listarDisciplinasDoModulo((int) ($modulo['id'] ?? 0));
            foreach ($disciplinas as &$disciplina) {
                $idDisciplina = (int) ($disciplina['id_disciplina'] ?? 0);
                $vinculo = $disciplinasDaTurmaPorDisciplina[$idDisciplina] ?? null;
                $disciplina['professor_nome'] = $vinculo['professor_nome'] ?? null;
                $disciplina['vinculada'] = $vinculo !== null;
                $disciplina['turma_disciplina_id'] = (int) ($vinculo['id'] ?? 0);
                $disciplina['professor_id'] = (int) ($vinculo['id_usuario_professor'] ?? 0);
                $disciplina['data_inicio_turma'] = (string) ($vinculo['data_inicio'] ?? '');
                $disciplina['data_fim_turma'] = (string) ($vinculo['data_fim'] ?? '');
                $disciplina['status_turma'] = (string) ($vinculo['status'] ?? 'PLANEJADA');
                $disciplina['ativo_turma'] = (int) ($vinculo['ativo'] ?? 1);
            }
            unset($disciplina);
            $disciplinasDosModulos[] = [
                'modulo' => $modulo,
                'disciplinas' => $disciplinas,
            ];
        }

        $this->render('pages/admin/turmas/show', [
            'title' => 'Turma: ' . ($turma['nome'] ?? ''),
            'currentRoute' => '/admin/turmas/show',
            'turma' => $turma,
            'inscritos' => $inscritos,
            'professores' => $this->professoresDaTurma($id),
            'materiais' => $this->materiaisDaTurma($id),
            'disciplinasTurma' => $disciplinasTurma,
            'modulosDaTurma' => $modulosDaTurma,
            'disciplinasDosModulos' => $disciplinasDosModulos,
            'disciplinasDoCurso' => (int) ($turma['id_estrutura'] ?? 0) > 0
                ? $this->estruturaService->listarDisciplinasDaMatriz((int) $turma['id_estrutura'])
                : $this->estruturaService->listarDisciplinasDoCurso((int) ($turma['id_curso'] ?? 0)),
            'professoresDaTurma' => $this->estruturaService->listarProfessoresDaTurma($id),
            'disciplinasMatriculadas' => $disciplinasMatriculadas,
        ], 'admin');
    }

    public function salvarDisciplinasMatricula(): void
    {
        if (!$this->isStaff()) {
            $this->json(['erro' => 'Acesso negado.'], 403);
        }

        $idMatricula = (int) $this->input('id_matricula', 0);
        $idTurma = (int) $this->input('id_turma', 0);
        $ids = array_values(array_filter(array_map('intval', (array) ($this->input('disciplinas', []) ?: [])), static fn (int $v): bool => $v > 0));

        if ($idMatricula <= 0 || $idTurma <= 0) {
            $this->json(['erro' => 'Parâmetros inválidos.'], 400);
        }

        // Valida a matrícula e que ela pertence à turma.
        $matricula = $this->turmaService->findMatricula($idMatricula);
        if ($matricula === null || (int) ($matricula['id_turma'] ?? 0) !== $idTurma) {
            $this->json(['erro' => 'Matrícula não encontrada ou não pertence a esta turma.'], 400);
        }

        // Disciplinas da turma
        $disciplinasTurma = $this->estruturaService->listarDisciplinasDaTurma($idTurma);
        $idsValidos = array_map(static fn (array $d): int => (int) ($d['id'] ?? 0), $disciplinasTurma);
        $idsValidos = array_flip($idsValidos);

        $atuais = $this->estruturaService->listarIdsDisciplinasDaMatricula($idMatricula);
        $novoConjunto = [];
        foreach ($ids as $id) {
            if (isset($idsValidos[$id])) {
                $novoConjunto[] = $id;
            }
        }

        // Remove os que não estão mais selecionados
        foreach ($atuais as $idAtual) {
            if (!in_array($idAtual, $novoConjunto, true)) {
                $this->estruturaService->desvincularDisciplinaDaMatricula($idMatricula, $idAtual);
            }
        }

        // Adiciona os novos
        foreach ($novoConjunto as $idNovo) {
            if (!in_array($idNovo, $atuais, true)) {
                $this->estruturaService->vincularDisciplinaDaMatricula($idMatricula, $idNovo);
            }
        }

        $this->logService->log('atualizar', 'matricula_disciplina', $idMatricula, 'Disciplinas da matrícula #' . $idMatricula . ' atualizadas (' . count($novoConjunto) . ' disciplina(s))');
        $this->json(['sucesso' => true]);
    }

    public function salvarDisciplinaTurma(): void
    {
        if (!$this->isStaff()) {
            $this->json(['erro' => 'Acesso negado.'], 403);
        }

        $id = (int) $this->input('id', 0);
        $idTurma = (int) $this->input('id_turma', 0);
        $idDisciplina = (int) $this->input('id_disciplina', 0);
        $idProfessor = (int) $this->input('id_usuario_professor', 0);
        $dataInicio = trim((string) $this->input('data_inicio', ''));
        $dataFim = trim((string) $this->input('data_fim', ''));
        $status = trim((string) $this->input('status', 'PLANEJADA'));
        $ativo = (int) $this->input('ativo', 1);

        $turma = $this->turmaService->findTurma($idTurma);
        if ($turma === null) {
            $this->json(['erro' => 'Turma não encontrada.'], 400);
        }

        if ($idDisciplina <= 0) {
            $this->json(['erro' => 'Selecione uma disciplina.'], 400);
        }

        if ($idProfessor > 0) {
            $professoresDaTurma = $this->estruturaService->listarProfessoresDaTurma($idTurma);
            $professorVinculado = false;
            foreach ($professoresDaTurma as $professor) {
                if ((int) ($professor['id'] ?? 0) === $idProfessor) {
                    $professorVinculado = true;
                    break;
                }
            }

            if (!$professorVinculado) {
                $this->json(['erro' => 'O professor selecionado não está vinculado a esta turma.'], 400);
            }
        }

        $idEstruturaTurma = (int) ($turma['id_estrutura'] ?? 0);
        if ($idEstruturaTurma > 0) {
            $disciplinasDaMatriz = $this->estruturaService->listarDisciplinasDaMatriz($idEstruturaTurma);
            $idsDaMatriz = array_map(static fn (array $disciplina): int => (int) ($disciplina['id'] ?? 0), $disciplinasDaMatriz);
            if (!in_array($idDisciplina, $idsDaMatriz, true)) {
                $this->json(['erro' => 'A disciplina selecionada não pertence à matriz desta turma.'], 400);
            }
        }

        if ($dataInicio !== '' && \DateTime::createFromFormat('Y-m-d', $dataInicio) === false) {
            $dataInicio = '';
        }
        if ($dataFim !== '' && \DateTime::createFromFormat('Y-m-d', $dataFim) === false) {
            $dataFim = '';
        }

        $result = $this->estruturaService->salvarDisciplinaDaTurma([
            'id' => $id,
            'id_turma' => $idTurma,
            'id_disciplina' => $idDisciplina,
            'id_usuario_professor' => $idProfessor > 0 ? $idProfessor : null,
            'data_inicio' => $dataInicio,
            'data_fim' => $dataFim,
            'status' => $status,
            'ativo' => $ativo,
        ]);

        if ($result === -1) {
            $this->json(['erro' => 'Esta disciplina já está vinculada a esta turma.'], 400);
        }

        if ($result <= 0) {
            $this->json(['erro' => 'Erro ao salvar a disciplina da turma.'], 500);
        }

        $this->logService->log($id > 0 ? 'atualizar' : 'criar', 'turma_disciplina', $result, ($id > 0 ? 'Disciplina da turma atualizada' : 'Disciplina da turma criada') . ' na turma #' . $idTurma);
        $this->json(['sucesso' => true, 'id' => $result]);
    }

    public function desativarDisciplinaTurma(): void
    {
        if (!$this->isStaff()) {
            $this->json(['erro' => 'Acesso negado.'], 403);
        }

        $id = (int) $this->input('id', 0);
        if ($id < 1) {
            $this->json(['erro' => 'ID inválido.'], 400);
        }

        $this->estruturaService->desativarDisciplinaDaTurma($id);
        $this->logService->log('desativar', 'turma_disciplina', $id, 'Disciplina da turma desativada');
        $this->json(['sucesso' => true]);
    }

    public function vincularProfessorModulo(): void
    {
        if (!$this->isStaff()) {
            $this->json(['erro' => 'Acesso negado.'], 403);
        }

        $idTurma = (int) $this->input('id_turma', 0);
        $idModulo = (int) $this->input('id_modulo', 0);
        $idProfessor = (int) $this->input('id_usuario_professor', 0);

        if ($idTurma <= 0 || $idModulo <= 0) {
            $this->json(['erro' => 'Parâmetros inválidos.'], 400);
        }

        $turma = $this->turmaService->findTurma($idTurma);
        if ($turma === null) {
            $this->json(['erro' => 'Turma não encontrada.'], 400);
        }

        $modulo = $this->estruturaService->findModulo($idModulo);
        if ($modulo === null || (int) ($modulo['id_estrutura'] ?? 0) !== (int) ($turma['id_estrutura'] ?? 0)) {
            $this->json(['erro' => 'O módulo selecionado não pertence à matriz desta turma.'], 400);
        }

        $total = $this->estruturaService->vincularProfessorDoModulo($idTurma, $idModulo, $idProfessor > 0 ? $idProfessor : null);
        if ($total <= 0) {
            $this->json(['erro' => 'Nenhuma disciplina do módulo foi vinculada ao professor.'], 400);
        }

        $this->logService->log('atualizar', 'turma_disciplina', $idTurma, 'Professor vinculado em lote a ' . $total . ' disciplina(s) do módulo #' . $idModulo . ' da turma #' . $idTurma);
        $this->json(['sucesso' => true, 'total' => $total]);
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
            'matrizes' => $this->estruturaService->listarMatrizes(null, 1),
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

        $matrizes = $this->estruturaService->listarMatrizes(null, 1);
        $idEstruturaAtual = (int) ($turma['id_estrutura'] ?? 0);
        if ($idEstruturaAtual > 0) {
            $presente = false;
            foreach ($matrizes as $matriz) {
                if ((int) ($matriz['id'] ?? 0) === $idEstruturaAtual) {
                    $presente = true;
                    break;
                }
            }
            if (!$presente) {
                $matrizAtual = $this->estruturaService->findMatriz($idEstruturaAtual);
                if ($matrizAtual !== null) {
                    $matrizes[] = $matrizAtual;
                }
            }
        }

        $this->render('pages/admin/turmas/edit', [
            'title' => 'Editar Turma',
            'currentRoute' => '/admin/turmas/editar',
            'turma' => $turma,
            'cursos' => $this->cursoService->cursos('asc', 500),
            'matrizes' => $matrizes,
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
        $idEstrutura = (int) $this->input('id_estrutura', 0);
        $dataInicio = (string) $this->input('data_inicio', '');
        $ativo = intval($this->input('ativo', 0));

        if ($nome === '' || $curso <= 0 || $idEstrutura <= 0) {
            Session::setFlash('flash', 'Informe o nome, o curso e a matriz curricular da turma.');
            $this->redirect('/admin/turmas/novo');
            return;
        }

        if (!$this->matrizPertenceAoCurso($idEstrutura, $curso)) {
            Session::setFlash('flash', 'A matriz selecionada não pertence ao curso informado.');
            $this->redirect('/admin/turmas/novo');
            return;
        }

        $ativo = intval($ativo) ? 1 : 0;

        $turmaId = $this->turmaService->criarTurma($nome, $curso, $dataInicio, $ativo, $idEstrutura > 0 ? $idEstrutura : null);

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
        $idEstrutura = (int) $this->input('id_estrutura', 0);
        $dataInicio = (string) $this->input('data_inicio', '');
        $ativo = intval($this->input('ativo', 0));

        if ($id <= 0 || $nome === '' || $curso <= 0) {
            Session::setFlash('flash', 'Dados inválidos para atualização.');
            $this->redirect('/admin/turmas/editar?id=' . $id);
            return;
        }

        if (!$this->matrizPertenceAoCurso($idEstrutura, $curso)) {
            Session::setFlash('flash', 'A matriz selecionada não pertence ao curso informado.');
            $this->redirect('/admin/turmas/editar?id=' . $id);
            return;
        }

        $ativo = intval($ativo) ? 1 : 0;

        $this->turmaService->atualizarTurma($id, $nome, $curso, $dataInicio, $ativo, $idEstrutura > 0 ? $idEstrutura : null);

        $this->logService->log('atualizar', 'turma', $id, "Turma atualizada: $nome");
        Session::setFlash('flash', 'Turma atualizada com sucesso.');
        $this->redirect('/admin/turmas');
    }

    private function matrizPertenceAoCurso(int $idEstrutura, int $idCurso): bool
    {
        if ($idEstrutura <= 0) {
            return true;
        }

        return $this->estruturaService->validarMatrizParaCurso($idEstrutura, $idCurso);
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

    private function nomeTurmaGerada(string $nomeCurso): string
    {
        $palavras = array_values(array_filter(
            preg_split('/\s+/', trim($nomeCurso)) ?: [],
            static fn (string $palavra): bool => $palavra !== ''
        ));
        return 'T. ' . implode(' ', array_slice($palavras, 0, 5));
    }

    private function isStaff(): bool
    {
        return (new \App\Services\AuthService())->isStaff();
    }
}
