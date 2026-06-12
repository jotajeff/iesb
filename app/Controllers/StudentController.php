<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Services\AdminService;
use App\Services\AuthService;
use App\Services\CourseService;
use App\Services\EnrollmentService;
use App\Support\Session;

final class StudentController extends Controller
{
    private AuthService $auth;
    private AdminService $admin;
    private CourseService $courses;
    private EnrollmentService $enrollments;

    public function __construct()
    {
        $this->auth = new AuthService();
        $this->admin = new AdminService();
        $this->courses = new CourseService();
        $this->enrollments = new EnrollmentService();
    }

    public function dashboard(): void
    {
        if (!$this->auth->checkRole('aluno')) {
            Session::setFlash('flash', 'Faça login como aluno para acessar o painel.');
            $this->redirect('/aluno/login');
        }

        $user = Session::get('user');
        $studentId = (int) ($user['id'] ?? 0);

        $cursosAtivos = $this->admin->cursosAtivos();
        $cursosMatriculados = $this->admin->cursosDoAluno($studentId);
        $idsMatriculados = array_map(static fn (array $m): int => (int) ($m['curso_id'] ?? 0), $cursosMatriculados);

        $cursosDisponiveis = array_values(array_filter(
            $cursosAtivos,
            static fn (array $c): bool => !in_array((int) $c['id'], $idsMatriculados, true)
        ));

        $this->render('pages/aluno/dashboard', [
            'title' => 'Área do Aluno',
            'currentRoute' => '/area-do-aluno',
            'cursosDisponiveis' => $cursosDisponiveis,
            'matriculasDB' => $this->admin->matriculasDoAluno($studentId),
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
            'matriculasDB' => $this->admin->matriculasDoAluno($studentId),
            'cursosMatriculados' => $this->admin->cursosDoAluno($studentId),
        ], 'aluno');
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
                    . ' t.id AS turma_id, t.nome AS turma_nome, t.data_inicio, t.data_fim,'
                    . ' c.id AS curso_id, c.nome AS curso_nome, c.local_curso, c.horario, c.imagem_card'
                    . ' FROM matriculas m'
                    . ' JOIN turmas t ON m.id_turma = t.id'
                    . ' LEFT JOIN cursos_iesb c ON t.id_curso = c.id'
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

            try {
                $stmt = $pdo->prepare(
                    'SELECT u.id, u.nome, u.email, u.telefone, u.foto'
                    . ' FROM turma_professor tp'
                    . ' JOIN usuarios u ON tp.id_usuario = u.id'
                    . ' WHERE tp.id_turma = :id_turma AND tp.status = :status'
                );
                $stmt->bindValue(':id_turma', $turmaId, \PDO::PARAM_INT);
                $stmt->bindValue(':status', 'A', \PDO::PARAM_STR);
                $stmt->execute();
                $professores = $stmt->fetchAll() ?: [];
            } catch (\Throwable $e) {
                error_log('[STUDENT SHOW] Erro ao buscar professores: ' . $e->getMessage());
                $professores = [];
            }

            try {
                $stmt = $pdo->prepare(
                    'SELECT id, titulo, link, tipo, criado_em'
                    . ' FROM material'
                    . ' WHERE id_fk = :id_fk AND ativo = :ativo'
                    . ' ORDER BY FIELD(tipo, \'video\', \'PDF\', \'Artigo\', \'Apostila\'), criado_em DESC'
                );
                $stmt->bindValue(':id_fk', $turmaId, \PDO::PARAM_INT);
                $stmt->bindValue(':ativo', 'S', \PDO::PARAM_STR);
                $stmt->execute();
                $materiais = $stmt->fetchAll() ?: [];
            } catch (\Throwable $e) {
                error_log('[STUDENT SHOW] Erro ao buscar materiais: ' . $e->getMessage());
                $materiais = [];
            }
        }

        $this->render('pages/aluno/show', [
            'title' => $matricula['curso_nome'] ?? 'Detalhes do Curso',
            'currentRoute' => '/aluno/cursos',
            'matricula' => $matricula,
            'professores' => $professores,
            'materiais' => $materiais,
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
                    'SELECT id, titulo, link, tipo, criado_em'
                    . ' FROM material WHERE id = :id AND ativo = :ativo'
                );
                $stmt->bindValue(':id', $materialId, \PDO::PARAM_INT);
                $stmt->bindValue(':ativo', 'S', \PDO::PARAM_STR);
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

        $this->render('pages/aluno/video', [
            'title' => $material['titulo'] ?? 'Vídeo',
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
        $curso = $this->admin->findCurso($cursoId);

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

        $cursosMatriculados = $this->admin->cursosDoAluno($studentId);
        $idsMatriculados = array_map(static fn (array $m): int => (int) ($m['curso_id'] ?? 0), $cursosMatriculados);

        if (in_array($cursoId, $idsMatriculados, true)) {
            Session::setFlash('flash', 'Você já está matriculado neste curso.');
            $this->redirect('/aluno');
        }

        $turmas = $this->admin->turmasDoCurso($cursoId);

        if (empty($turmas)) {
            Session::setFlash('flash', 'Não há turmas disponíveis para este curso no momento.');
            $this->redirect('/aluno');
        }

        $this->admin->criarMatricula($studentId, (int) $turmas[0]['id']);
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
        $aluno = $this->admin->findAluno($studentId);

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

        $this->admin->atualizarAluno($studentId, $nome, $cpf, $dataNascimento, $telefone, $email, 'S', $senha ?: null);

        Session::setFlash('flash', 'Perfil atualizado com sucesso.');
        $this->redirect('/aluno/perfil');
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
        Session::setFlash('flash', $result['message']);
        $this->redirect('/aluno');
    }
}
