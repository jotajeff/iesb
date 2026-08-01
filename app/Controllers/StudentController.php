<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Services\AlunoService;
use App\Services\CursoService;
use App\Services\LogService;
use App\Services\TurmaService;
use App\Services\AuthService;
use App\Services\CourseService;
use App\Services\EnrollmentService;
use App\Services\IpLocationService;
use App\Services\NoticiaService;
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
                    . ' WHERE (n.tipo_destino = \'aluno\' AND n.id_destino = :aluno_id)'
                    . ' OR (n.tipo_destino = \'turma\' AND n.id_destino IN ('
                    . '   SELECT m.id_turma FROM matriculas m WHERE m.id_aluno = :aluno_id2 AND m.status IN (\'inscrito\',\'matriculado\',\'ativo\')'
                    . ' ))'
                    . ' AND n.id NOT IN (SELECT nl.id_notificacao FROM notificacao_leitura_aluno nl WHERE nl.id_aluno = :aluno_id3)'
                );
                $stmt->bindValue(':aluno_id', $studentId, \PDO::PARAM_INT);
                $stmt->bindValue(':aluno_id2', $studentId, \PDO::PARAM_INT);
                $stmt->bindValue(':aluno_id3', $studentId, \PDO::PARAM_INT);
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

        $this->render('pages/aluno/dashboard', [
            'title' => 'Área do Aluno',
            'currentRoute' => '/area-do-aluno',
            'cursosDisponiveis' => $cursosDisponiveis,
            'matriculasDB' => $this->alunoService->matriculasDoAluno($studentId),
            'notificacaoCount' => $notificacaoCount,
            'temEndereco' => $temEndereco,
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
            'matriculasDB' => $this->alunoService->matriculasDoAluno($studentId),
            'cursosMatriculados' => $this->alunoService->cursosDoAluno($studentId),
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
                    . ' LEFT JOIN cursos c ON t.id_curso = c.id'
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
                    . ' WHERE (n.tipo_destino = \'aluno\' AND n.id_destino = :aluno_id2)'
                    . ' OR (n.tipo_destino = \'turma\' AND n.id_destino IN ('
                    . '   SELECT m.id_turma FROM matriculas m WHERE m.id_aluno = :aluno_id3 AND m.status IN (\'inscrito\',\'matriculado\',\'ativo\')'
                    . ' ))'
                    . ' ORDER BY n.created_at DESC'
                    . ' LIMIT 200'
                );
                $stmt->bindValue(':aluno_id', $studentId, \PDO::PARAM_INT);
                $stmt->bindValue(':aluno_id2', $studentId, \PDO::PARAM_INT);
                $stmt->bindValue(':aluno_id3', $studentId, \PDO::PARAM_INT);
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
                'INSERT IGNORE INTO notificacao_leitura_aluno (id_notificacao, id_aluno) VALUES (:id_notificacao, :id_aluno)'
            );
            $stmt->bindValue(':id_notificacao', $notificacaoId, \PDO::PARAM_INT);
            $stmt->bindValue(':id_aluno', $studentId, \PDO::PARAM_INT);
            $stmt->execute();

            $this->logService->log('ler', 'notificacao', $notificacaoId, 'Notificação marcada como lida');

            $this->json(['sucesso' => true]);
        } catch (\Throwable $e) {
            error_log('[STUDENT MARCAR LIDA] Erro: ' . $e->getMessage());
            $this->json(['erro' => 'Erro ao marcar como lida.'], 500);
        }
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
