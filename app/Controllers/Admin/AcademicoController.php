<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Services\AuthService;
use App\Services\CursoService;
use App\Services\EstruturaCurricularService;
use App\Services\LogService;
use App\Services\TurmaService;
use App\Support\Session;

final class AcademicoController extends Controller
{
    private EstruturaCurricularService $estruturaService;
    private LogService $logService;
    private CursoService $cursoService;
    private TurmaService $turmaService;

    public function __construct()
    {
        $this->estruturaService = new EstruturaCurricularService();
        $this->logService = new LogService();
        $this->cursoService = new CursoService();
        $this->turmaService = new TurmaService();
    }

    // ==================== MATRIZ CURRICULAR ====================

    public function matrizes(): void
    {
        if (!$this->isStaff()) {
            Session::setFlash('flash', 'Acesso negado.');
            $this->redirect('/admin/login');
        }

        $idCurso = (int) ($_GET['id_curso'] ?? 0);
        $status = (string) ($_GET['status'] ?? '');

        $matrizes = $this->estruturaService->listarMatrizes(
            $idCurso > 0 ? $idCurso : null,
            $status === 'ativo' ? 1 : ($status === 'inativo' ? 0 : null)
        );

        $this->render('pages/admin/academico/matrizes', [
            'title' => 'Matrizes Curriculares',
            'currentRoute' => '/admin/academico/matrizes',
            'matrizes' => $matrizes,
            'cursos' => $this->estruturaService->listarCursosParaSeletor(),
            'idCursoFiltro' => $idCurso,
            'statusFiltro' => $status,
        ], 'admin');
    }

    public function matrizForm(): void
    {
        if (!$this->isStaff()) {
            Session::setFlash('flash', 'Acesso negado.');
            $this->redirect('/admin/login');
        }

        $id = (int) ($_GET['id'] ?? 0);
        $matriz = $id > 0 ? $this->estruturaService->findMatriz($id) : null;

        if ($id > 0 && $matriz === null) {
            Session::setFlash('flash', 'Matriz não encontrada.');
            $this->redirect('/admin/academico/matrizes');
        }

        $this->render('pages/admin/academico/matriz_form', [
            'title' => ($id > 0 ? 'Editar' : 'Nova') . ' Matriz Curricular',
            'currentRoute' => '/admin/academico/matrizes',
            'matriz' => $matriz,
            'cursos' => $this->estruturaService->listarCursosParaSeletor(),
        ], 'admin');
    }

    public function salvarMatriz(): void
    {
        if (!$this->isStaff()) {
            Session::setFlash('flash', 'Acesso negado.');
            $this->redirect('/admin/login');
        }

        $id = (int) $this->input('id', 0);
        $idCurso = (int) $this->input('id_curso', 0);
        $nome = trim((string) $this->input('nome', ''));
        $descricao = trim((string) $this->input('descricao', ''));
        $cargaHoraria = (int) $this->input('carga_horaria', 0);
        $versao = trim((string) $this->input('versao', '1.0'));
        $ativo = (int) $this->input('ativo', 1);

        if ($idCurso <= 0 || $nome === '') {
            Session::setFlash('flash', 'Informe o curso e o nome da matriz.');
            $this->redirect('/admin/academico/matrizes/form' . ($id > 0 ? '?id=' . $id : ''));
        }

        $result = $this->estruturaService->salvarMatriz([
            'id' => $id,
            'id_curso' => $idCurso,
            'nome' => $nome,
            'descricao' => $descricao,
            'carga_horaria' => $cargaHoraria,
            'versao' => $versao !== '' ? $versao : '1.0',
            'ativo' => $ativo,
        ]);

        if ($result <= 0) {
            Session::setFlash('flash', 'Erro ao salvar a matriz curricular.');
            $this->redirect('/admin/academico/matrizes/form' . ($id > 0 ? '?id=' . $id : ''));
        }

        $this->logService->log($id > 0 ? 'atualizar' : 'criar', 'estrutura_curricular', $result, ($id > 0 ? 'Matriz atualizada' : 'Matriz criada') . ': ' . $nome);
        Session::setFlash('flash', 'Matriz curricular salva com sucesso.');
        $this->redirect('/admin/academico/matrizes/detalhe?id=' . $result);
    }

    public function matrizDetalhe(): void
    {
        if (!$this->isStaff()) {
            Session::setFlash('flash', 'Acesso negado.');
            $this->redirect('/admin/login');
        }

        $id = (int) ($_GET['id'] ?? 0);
        $matriz = $this->estruturaService->findMatriz($id);

        if ($matriz === null) {
            Session::setFlash('flash', 'Matriz não encontrada.');
            $this->redirect('/admin/academico/matrizes');
        }

        $modulos = $this->estruturaService->listarModulos($id);

        foreach ($modulos as &$modulo) {
            $modulo['disciplinas'] = $this->estruturaService->listarDisciplinasDoModulo((int) ($modulo['id'] ?? 0));
        }
        unset($modulo);

        $this->render('pages/admin/academico/matriz_detalhe', [
            'title' => 'Matriz — ' . ($matriz['nome'] ?? ''),
            'currentRoute' => '/admin/academico/matrizes',
            'matriz' => $matriz,
            'modulos' => $modulos,
            'disciplinasDoCurso' => $this->estruturaService->listarDisciplinasDoCurso((int) ($matriz['id_curso'] ?? 0)),
        ], 'admin');
    }

    public function desativarMatriz(): void
    {
        if (!$this->isStaff()) {
            $this->json(['erro' => 'Acesso negado.'], 403);
        }

        $id = (int) $this->input('id', 0);
        if ($id < 1) {
            $this->json(['erro' => 'ID inválido.'], 400);
        }

        $this->estruturaService->desativarMatriz($id);
        $this->logService->log('desativar', 'estrutura_curricular', $id, 'Matriz desativada');
        $this->json(['sucesso' => true]);
    }

    // ==================== MÓDULOS ====================

    public function salvarModulo(): void
    {
        if (!$this->isStaff()) {
            $this->json(['erro' => 'Acesso negado.'], 403);
        }

        $id = (int) $this->input('id', 0);
        $idEstrutura = (int) $this->input('id_estrutura', 0);
        $nome = trim((string) $this->input('nome', ''));
        $descricao = trim((string) $this->input('descricao', ''));
        $ordem = (int) $this->input('ordem', 0);
        $cargaHoraria = (int) $this->input('carga_horaria', 0);
        $ativo = (int) $this->input('ativo', 1);

        if ($idEstrutura <= 0 || $nome === '') {
            $this->json(['erro' => 'Informe a matriz e o nome do módulo.'], 400);
        }

        $result = $this->estruturaService->salvarModulo([
            'id' => $id,
            'id_estrutura' => $idEstrutura,
            'nome' => $nome,
            'descricao' => $descricao,
            'ordem' => $ordem,
            'carga_horaria' => $cargaHoraria,
            'ativo' => $ativo,
        ]);

        if ($result <= 0) {
            $this->json(['erro' => 'Erro ao salvar o módulo.'], 500);
        }

        $this->logService->log($id > 0 ? 'atualizar' : 'criar', 'estrutura_modulo', $result, ($id > 0 ? 'Módulo atualizado' : 'Módulo criado') . ': ' . $nome);
        $this->json(['sucesso' => true, 'id' => $result]);
    }

    public function desativarModulo(): void
    {
        if (!$this->isStaff()) {
            $this->json(['erro' => 'Acesso negado.'], 403);
        }

        $id = (int) $this->input('id', 0);
        if ($id < 1) {
            $this->json(['erro' => 'ID inválido.'], 400);
        }

        $this->estruturaService->desativarModulo($id);
        $this->logService->log('desativar', 'estrutura_modulo', $id, 'Módulo desativado');
        $this->json(['sucesso' => true]);
    }

    // ==================== DISCIPLINAS DA MATRIZ ====================

    public function salvarDisciplinaDaMatriz(): void
    {
        if (!$this->isStaff()) {
            $this->json(['erro' => 'Acesso negado.'], 403);
        }

        $id = (int) $this->input('id', 0);
        $idModulo = (int) $this->input('id_modulo', 0);
        $idDisciplina = (int) $this->input('id_disciplina', 0);
        $ordem = (int) $this->input('ordem', 0);
        $obrigatoria = (int) $this->input('obrigatoria', 1);
        $ativo = (int) $this->input('ativo', 1);

        $modulo = $this->estruturaService->findModulo($idModulo);
        if ($modulo === null) {
            $this->json(['erro' => 'Módulo não encontrado.'], 400);
        }

        if ($idDisciplina <= 0) {
            $this->json(['erro' => 'Selecione uma disciplina.'], 400);
        }

        $result = $this->estruturaService->salvarDisciplinaDaMatriz([
            'id' => $id,
            'id_modulo' => $idModulo,
            'id_disciplina' => $idDisciplina,
            'ordem' => $ordem,
            'obrigatoria' => $obrigatoria,
            'ativo' => $ativo,
        ]);

        if ($result === -1) {
            $this->json(['erro' => 'Esta disciplina já está vinculada a este módulo.'], 400);
        }

        if ($result <= 0) {
            $this->json(['erro' => 'Erro ao salvar a disciplina.'], 500);
        }

        $this->logService->log($id > 0 ? 'atualizar' : 'criar', 'estrutura_disciplina', $result, ($id > 0 ? 'Disciplina da matriz atualizada' : 'Disciplina da matriz criada') . ' no módulo #' . $idModulo);
        $this->json(['sucesso' => true, 'id' => $result]);
    }

    public function desativarDisciplinaDaMatriz(): void
    {
        if (!$this->isStaff()) {
            $this->json(['erro' => 'Acesso negado.'], 403);
        }

        $id = (int) $this->input('id', 0);
        if ($id < 1) {
            $this->json(['erro' => 'ID inválido.'], 400);
        }

        $this->estruturaService->desativarDisciplinaDaMatriz($id);
        $this->logService->log('desativar', 'estrutura_disciplina', $id, 'Disciplina da matriz desativada');
        $this->json(['sucesso' => true]);
    }

    // ==================== SITUAÇÃO ACADÊMICA ====================

    public function situacaoAcademica(): void
    {
        if (!$this->isStaff()) {
            Session::setFlash('flash', 'Acesso negado.');
            $this->redirect('/admin/login');
        }

        $termo = trim((string) ($_GET['termo'] ?? ''));
        $idCurso = (int) ($_GET['id_curso'] ?? 0);
        $idTurma = (int) ($_GET['id_turma'] ?? 0);

        $matriculas = $this->estruturaService->buscarMatriculasSituacao(
            $termo !== '' ? $termo : null,
            $idCurso > 0 ? $idCurso : null,
            $idTurma > 0 ? $idTurma : null
        );

        $matriculaId = (int) ($_GET['matricula'] ?? 0);
        $matriculaDetalhe = null;
        $disciplinasMatricula = [];

        if ($matriculaId > 0) {
            $matriculaDetalhe = $this->estruturaService->findMatriculaParaSituacao($matriculaId);
            if ($matriculaDetalhe !== null) {
                $disciplinasMatricula = $this->estruturaService->listarDisciplinasDaMatricula($matriculaId);
            } else {
                $matriculaId = 0;
            }
        }

        $this->render('pages/admin/academico/situacao_academica', [
            'title' => 'Situação Acadêmica',
            'currentRoute' => '/admin/academico/situacao',
            'cursos' => $this->cursoService->cursos(),
            'turmas' => $this->turmaService->turmas(500),
            'matriculas' => $matriculas,
            'termo' => $termo,
            'idCurso' => $idCurso,
            'idTurma' => $idTurma,
            'matriculaDetalhe' => $matriculaDetalhe,
            'matriculaId' => $matriculaId,
            'disciplinasMatricula' => $disciplinasMatricula,
        ], 'admin');
    }

    public function salvarSituacaoDisciplina(): void
    {
        if (!$this->isStaff()) {
            $this->json(['erro' => 'Acesso negado.'], 403);
        }

        $id = (int) $this->input('id', 0);
        $idMatricula = (int) $this->input('id_matricula', 0);
        $situacao = trim((string) $this->input('situacao', 'MATRICULADO'));
        $nota = trim((string) $this->input('nota', ''));
        $frequencia = trim((string) $this->input('frequencia', ''));
        $dataConclusao = trim((string) $this->input('data_conclusao', ''));
        $observacao = trim((string) $this->input('observacao', ''));

        if ($id < 1 || $idMatricula < 1) {
            $this->json(['erro' => 'Parâmetros inválidos.'], 400);
        }

        $situacoesValidas = ['MATRICULADO', 'CURSANDO', 'APROVADO', 'REPROVADO', 'DISPENSADO', 'TRANCADO', 'CANCELADO'];
        if (!in_array($situacao, $situacoesValidas, true)) {
            $this->json(['erro' => 'Situação inválida.'], 400);
        }

        if ($nota !== '' && ((float) $nota < 0 || (float) $nota > 10)) {
            $this->json(['erro' => 'Nota deve estar entre 0 e 10.'], 400);
        }

        if ($frequencia !== '' && ((float) $frequencia < 0 || (float) $frequencia > 100)) {
            $this->json(['erro' => 'Frequência deve estar entre 0 e 100.'], 400);
        }

        if ($dataConclusao !== '' && \DateTime::createFromFormat('Y-m-d', $dataConclusao) === false) {
            $dataConclusao = '';
        }

        $resultado = $this->estruturaService->atualizarDisciplinaDaMatricula($id, [
            'situacao' => $situacao,
            'nota' => $nota,
            'frequencia' => $frequencia,
            'data_conclusao' => $dataConclusao,
            'observacao' => $observacao,
        ]);

        if (!$resultado) {
            $this->json(['erro' => 'Erro ao atualizar a situação acadêmica.'], 500);
        }

        $this->logService->log('atualizar', 'matricula_disciplina', $id, 'Situação acadêmica atualizada na matrícula #' . $idMatricula . ' (' . $situacao . ')');
        $this->json(['sucesso' => true]);
    }

    private function isStaff(): bool
    {
        return (new AuthService())->isStaff();
    }
}
