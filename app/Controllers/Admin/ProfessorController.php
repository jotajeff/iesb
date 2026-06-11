<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Database;
use App\Services\UsuarioService;
use App\Services\AdminService;
use App\Services\TurmaService;
use App\Repositories\EnderecoRepository;
use App\Support\Session;

final class ProfessorController extends Controller
{
    private UsuarioService $usuarioService;
    private AdminService $adminService;
    private TurmaService $turmaService;
    private EnderecoRepository $enderecoRepository;

    public function __construct()
    {
        $this->usuarioService = new UsuarioService();
        $this->adminService = new AdminService();
        $this->turmaService = new TurmaService();
        $this->enderecoRepository = new EnderecoRepository();
    }

    public function index(): void
    {
        if (!$this->isStaff()) {
            Session::setFlash('flash', 'Faça login como admin ou operador para acessar os professores.');
            $this->redirect('/admin/login');
        }

        $professores = $this->usuarioService->usuariosPorTipo('professor');

        $enderecos = [];
        foreach ($professores as $prof) {
            $id = (int) ($prof['id'] ?? 0);
            try {
                $enderecos[$id] = $this->enderecoRepository->findByTipoAndFk('professor', $id);
            } catch (\Throwable) {
                $enderecos[$id] = null;
            }
        }

        $vinculoCounts = [];
        $pdo = \App\Core\Database::connection();
        if ($pdo instanceof \PDO) {
            try {
                $stmt = $pdo->prepare('SELECT id_usuario, COUNT(*) AS total FROM turma_professor WHERE status = :status GROUP BY id_usuario');
                $stmt->bindValue(':status', 'A', \PDO::PARAM_STR);
                $stmt->execute();
                foreach ($stmt->fetchAll() as $row) {
                    $vinculoCounts[(int) $row['id_usuario']] = (int) $row['total'];
                }
            } catch (\Throwable) {
                $vinculoCounts = [];
            }
        }

        $this->render('pages/admin/professores/index', [
            'title' => 'Professores',
            'currentRoute' => '/admin/professores',
            'professores' => $professores,
            'enderecos' => $enderecos,
            'vinculoCounts' => $vinculoCounts,
        ], 'admin');
    }

    public function novo(): void
    {
        if (!$this->isStaff()) {
            Session::setFlash('flash', 'Acesso negado.');
            $this->redirect('/admin/login');
        }

        $this->render('pages/admin/professores/novo', [
            'title' => 'Novo Professor',
            'currentRoute' => '/admin/professores/novo',
        ], 'admin');
    }

    public function salvar(): void
    {
        if (!$this->isStaff()) {
            Session::setFlash('flash', 'Acesso negado.');
            $this->redirect('/admin/login');
        }

        $nome = trim((string) $this->input('nome', ''));
        $email = trim((string) $this->input('email', ''));
        $senha = (string) $this->input('senha', '');
        $telefone = trim((string) $this->input('telefone', ''));
        $ativo = (string) $this->input('ativo', '1');

        if ($nome === '' || $email === '' || $senha === '') {
            Session::setFlash('flash', 'Preencha nome, email e senha.');
            $this->redirect('/admin/professores/novo');
            return;
        }

        $usuarioId = $this->usuarioService->criarUsuario($nome, $email, $senha, 'professor', $ativo, $telefone);
        $this->adminService->log('criar', 'usuario', $usuarioId, "Professor criado: $nome");
        Session::setFlash('flash', 'Professor criado com sucesso.');
        $this->redirect('/admin/professores');
    }

    public function editar(): void
    {
        if (!$this->isStaff()) {
            Session::setFlash('flash', 'Acesso negado.');
            $this->redirect('/admin/login');
        }

        $id = (int) ($this->input('id', 0) ?: ($_GET['id'] ?? 0));
        $professor = $this->usuarioService->findUsuario($id);

        if (!$professor || ((string) ($professor['tipo'] ?? '')) !== 'professor') {
            Session::setFlash('flash', 'Professor não encontrado.');
            $this->redirect('/admin/professores');
            return;
        }

        $this->render('pages/admin/professores/edit', [
            'title' => 'Editar Professor',
            'currentRoute' => '/admin/professores/editar',
            'professor' => $professor,
        ], 'admin');
    }

    public function atualizar(): void
    {
        if (!$this->isStaff()) {
            Session::setFlash('flash', 'Acesso negado.');
            $this->redirect('/admin/login');
        }

        $id = (int) $this->input('id', 0);
        $nome = trim((string) $this->input('nome', ''));
        $telefone = trim((string) $this->input('telefone', ''));
        $ativo = (string) $this->input('ativo', '1');

        if ($id <= 0 || $nome === '') {
            Session::setFlash('flash', 'Dados inválidos.');
            $this->redirect('/admin/professores');
            return;
        }

        $professor = $this->usuarioService->findUsuario($id);
        if (!$professor) {
            Session::setFlash('flash', 'Professor não encontrado.');
            $this->redirect('/admin/professores');
            return;
        }

        $this->usuarioService->atualizarUsuario($id, '', $ativo, $nome, '', '', $telefone);
        $this->adminService->log('atualizar', 'usuario', $id, "Professor atualizado: $nome");
        Session::setFlash('flash', 'Professor atualizado com sucesso.');
        $this->redirect('/admin/professores');
    }

    public function endereco(): void
    {
        if (isset($_GET['ajax']) && $_GET['ajax'] === '1' && isset($_GET['cep'])) {
            $this->buscarCep();
            return;
        }

        if (!$this->isStaff()) {
            Session::setFlash('flash', 'Acesso negado.');
            $this->redirect('/admin/login');
        }

        $id = (int) ($this->input('id', 0) ?: ($_GET['id'] ?? 0));
        $professor = $this->usuarioService->findUsuario($id);

        if (!$professor || ((string) ($professor['tipo'] ?? '')) !== 'professor') {
            Session::setFlash('flash', 'Professor não encontrado.');
            $this->redirect('/admin/professores');
            return;
        }

        try {
            $endereco = $this->enderecoRepository->findByTipoAndFk('professor', $id);
        } catch (\Throwable) {
            $endereco = null;
        }

        $this->render('pages/admin/professores/endereco', [
            'title' => 'Endereço do Professor',
            'currentRoute' => '/admin/professores/endereco',
            'professor' => $professor,
            'endereco' => $endereco,
        ], 'admin');
    }

    public function salvarEndereco(): void
    {
        if (!$this->isStaff()) {
            Session::setFlash('flash', 'Acesso negado.');
            $this->redirect('/admin/login');
        }

        $id = (int) $this->input('id', 0);
        $professor = $this->usuarioService->findUsuario($id);
        if (!$professor) {
            Session::setFlash('flash', 'Professor não encontrado.');
            $this->redirect('/admin/professores');
            return;
        }

        $cep = trim((string) $this->input('cep', ''));
        $logradouro = trim((string) $this->input('logradouro', ''));
        $numero = trim((string) $this->input('numero', ''));
        $cidade = trim((string) $this->input('cidade', ''));
        $uf = strtoupper(trim((string) $this->input('uf', '')));

        if ($cep === '' || $logradouro === '' || $cidade === '' || $uf === '') {
            Session::setFlash('flash', 'Preencha CEP, logradouro, cidade e UF.');
            $this->redirect('/admin/professores/endereco?id=' . $id);
            return;
        }

        $data = [
            'tipo' => 'professor',
            'id_fk' => $id,
            'cep' => $cep,
            'logradouro' => $logradouro,
            'numero' => $numero ?: null,
            'cidade' => $cidade,
            'uf' => $uf,
        ];

        try {
            $existente = $this->enderecoRepository->findByTipoAndFk('professor', $id);
        } catch (\Throwable) {
            $existente = null;
        }
        try {
            if ($existente) {
                $this->enderecoRepository->update((int) $existente['id'], $data);
                $this->adminService->log('atualizar', 'endereco', (int) $existente['id'], "Endereço atualizado: $logradouro");
            } else {
                $this->enderecoRepository->create($data);
                $this->adminService->log('criar', 'endereco', $id, "Endereço criado: $logradouro");
            }
            Session::setFlash('flash', 'Endereço salvo com sucesso.');
        } catch (\Throwable) {
            Session::setFlash('flash', 'Erro ao salvar endereço. A tabela endereco pode não existir no banco.');
        }
        $this->redirect('/admin/professores');
    }

    public function vincular(): void
    {
        if (!$this->isStaff()) {
            Session::setFlash('flash', 'Acesso negado.');
            $this->redirect('/admin/login');
        }

        $id = (int) ($this->input('id', 0) ?: ($_GET['id'] ?? 0));
        $professor = $this->usuarioService->findUsuario($id);

        if (!$professor || ((string) ($professor['tipo'] ?? '')) !== 'professor') {
            Session::setFlash('flash', 'Professor não encontrado.');
            $this->redirect('/admin/professores');
            return;
        }

        $turmas = $this->turmaService->turmas();

        $vinculos = [];
        $pdo = Database::connection();
        if ($pdo instanceof \PDO) {
            $stmt = $pdo->prepare('SELECT id_turma FROM turma_professor WHERE id_usuario = :id AND status = :status');
            $stmt->bindValue(':id', $id, \PDO::PARAM_INT);
            $stmt->bindValue(':status', 'A', \PDO::PARAM_STR);
            $stmt->execute();
            foreach ($stmt->fetchAll() as $row) {
                $vinculos[(int) $row['id_turma']] = true;
            }
        }

        $this->render('pages/admin/professores/vincular', [
            'title' => 'Vincular Professor a Turmas',
            'currentRoute' => '/admin/professores/vincular',
            'professor' => $professor,
            'turmas' => $turmas,
            'vinculos' => $vinculos,
        ], 'admin');
    }

    public function salvarVinculo(): void
    {
        if (!$this->isStaff()) {
            Session::setFlash('flash', 'Acesso negado.');
            $this->redirect('/admin/login');
        }

        $id = (int) $this->input('id', 0);
        $professor = $this->usuarioService->findUsuario($id);
        if (!$professor) {
            Session::setFlash('flash', 'Professor não encontrado.');
            $this->redirect('/admin/professores');
            return;
        }

        $turmaIds = array_filter(array_map('intval', (array) ($this->input('turmas', []) ?: [])), fn(int $v) => $v > 0);

        $pdo = Database::connection();
        if (!$pdo instanceof \PDO) {
            Session::setFlash('flash', 'Erro de conexão com o banco.');
            $this->redirect('/admin/professores/vincular?id=' . $id);
            return;
        }

        try {
            $pdo->beginTransaction();

            $stmtDel = $pdo->prepare('DELETE FROM turma_professor WHERE id_usuario = :id');
            $stmtDel->bindValue(':id', $id, \PDO::PARAM_INT);
            $stmtDel->execute();

            if (!empty($turmaIds)) {
                $stmtIns = $pdo->prepare('INSERT INTO turma_professor (id_turma, id_usuario, status) VALUES (:id_turma, :id_usuario, :status)');
                foreach ($turmaIds as $idTurma) {
                    $stmtIns->bindValue(':id_turma', $idTurma, \PDO::PARAM_INT);
                    $stmtIns->bindValue(':id_usuario', $id, \PDO::PARAM_INT);
                    $stmtIns->bindValue(':status', 'A', \PDO::PARAM_STR);
                    $stmtIns->execute();
                }
            }

            $pdo->commit();

            $this->adminService->log('vincular', 'turma_professor', $id, sprintf(
                'Professor ID %d vinculado a %d turma(s)', $id, count($turmaIds)
            ));
            Session::setFlash('flash', count($turmaIds) > 0
                ? 'Professor vinculado a ' . count($turmaIds) . ' turma(s) com sucesso.'
                : 'Vínculos removidos com sucesso.');
        } catch (\Throwable $e) {
            $pdo->rollBack();
            error_log('[VINCULAR] Erro: ' . $e->getMessage());
            Session::setFlash('flash', 'Erro ao salvar vínculo.');
        }

        $this->redirect('/admin/professores');
    }

    public function buscarCep(): void
    {
        ini_set('display_errors', '0');
        error_reporting(0);

        error_log('[buscarCep] Iniciando busca de CEP');

        $cep = preg_replace('/[^0-9]/', '', (string) ($_GET['cep'] ?? ''));

        if (strlen($cep) !== 8) {
            http_response_code(400);
            header('Content-Type: application/json');
            echo json_encode(['erro' => 'CEP inválido']);
            return;
        }

        $url = "https://viacep.com.br/ws/{$cep}/json/";

        error_log('[buscarCep] URL: ' . $url);

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
        $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
        $error = curl_error($ch);
        $errno = curl_errno($ch);
        curl_close($ch);

        error_log('[buscarCep] HTTP Code: ' . $httpCode);
        error_log('[buscarCep] Content-Type: ' . ($contentType ?? 'null'));
        error_log('[buscarCep] Error: ' . $error);
        error_log('[buscarCep] Response: ' . substr($response, 0, 500));

        if ($response === false || $httpCode !== 200) {
            http_response_code(502);
            header('Content-Type: application/json');
            echo json_encode([
                'erro' => 'Falha ao consultar ViaCEP',
                'detalhe' => $error ?: "HTTP {$httpCode}",
                'errno' => $errno,
                'url' => $url,
                'curl_enabled' => function_exists('curl_init')
            ]);
            return;
        }

        if (!$response || $response === '' || $response[0] !== '{') {
            http_response_code(502);
            header('Content-Type: application/json');
            echo json_encode([
                'erro' => 'Resposta inesperada da ViaCEP',
                'content_type' => $contentType,
                'prefix' => substr($response, 0, 200),
                'http_code' => $httpCode,
            ]);
            return;
        }

        $data = json_decode($response, true);

        if (!is_array($data) || isset($data['erro'])) {
            http_response_code(404);
            header('Content-Type: application/json');
            echo json_encode(['erro' => 'CEP não encontrado']);
            return;
        }

        header('Content-Type: application/json');
        echo json_encode([
            'logradouro' => $data['logradouro'] ?? '',
            'bairro' => $data['bairro'] ?? '',
            'cidade' => $data['localidade'] ?? '',
            'uf' => $data['uf'] ?? '',
        ]);
    }

    private function isStaff(): bool
    {
        return (new \App\Services\AuthService())->isStaff();
    }
}
