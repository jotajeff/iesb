<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Database;
use App\Services\AlunoService;
use App\Services\CursoParcelaService;
use App\Services\TurmaService;
use App\Services\CursoService;
use App\Services\CursoPagamentoService;
use App\Services\AsaasService;
use App\Services\LogService;
use App\Services\NotificacaoMatriculaService;
use App\Services\AuthService;
use App\Services\AcordoPagamentoService;
use App\Services\EmailService;
use App\Services\PlanilhaService;
use App\Services\IpLocationService;
use App\Support\Session;

final class AlunoController extends Controller
{
    private AlunoService $alunoService;
    private TurmaService $turmaService;
    private CursoService $cursoService;
    private LogService $logService;
    private CursoParcelaService $parcelaService;
    private CursoPagamentoService $pagamentoService;
    private NotificacaoMatriculaService $notificacaoMatriculaService;
    private AcordoPagamentoService $acordoService;

    public function __construct()
    {
        $this->alunoService = new AlunoService();
        $this->turmaService = new TurmaService();
        $this->cursoService = new CursoService();
        $this->logService = new LogService();
        $this->parcelaService = new CursoParcelaService();
        $this->pagamentoService = new CursoPagamentoService();
        $this->notificacaoMatriculaService = new NotificacaoMatriculaService();
        $this->acordoService = new AcordoPagamentoService();
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

        $perPage = 25;
        $total = $this->alunoService->contarAlunos($filtroAtivo);
        $totalPages = (int) ceil($total / $perPage);
        if ($totalPages < 1) {
            $totalPages = 1;
        }
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $page = min($page, $totalPages);
        $offset = ($page - 1) * $perPage;

        $this->render('pages/admin/alunos/index', [
            'title' => 'Alunos',
            'currentRoute' => '/admin/alunos',
            'alunos' => $this->alunoService->alunos($perPage, $filtroAtivo, $offset),
            'filtroAtivo' => $filtroAtivo,
            'page' => $page,
            'totalPages' => $totalPages,
            'total' => $total,
            'perPage' => $perPage,
        ], 'admin');
    }

    public function lote(): void
    {
        if (!$this->isStaff()) {
            Session::setFlash('flash', 'Acesso negado.');
            $this->redirect('/admin/login');
        }

        $this->render('pages/admin/alunos/lote', [
            'title' => 'Importação em Lote',
            'currentRoute' => '/admin/alunos/lote',
        ], 'admin');
    }

    public function importarLote(): void
    {
        if (!$this->isStaff()) {
            Session::setFlash('flash', 'Acesso negado.');
            $this->redirect('/admin/login');
        }

        $resultado = [
            'total' => 0,
            'importados' => 0,
            'ignorados' => 0,
            'erros' => [],
        ];

        $arquivo = $_FILES['planilha'] ?? null;
        if (!$arquivo || !is_array($arquivo) || (int) ($arquivo['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            $resultado['erros'][] = 'Selecione um arquivo .xlsx ou .csv para importar.';
            $this->renderLote($resultado);
            return;
        }

        $extensao = strtolower(pathinfo((string) ($arquivo['name'] ?? ''), PATHINFO_EXTENSION));

        try {
            $planilhaService = new PlanilhaService();
            $linhas = $planilhaService->ler((string) $arquivo['tmp_name'], $extensao);
        } catch (\Throwable $e) {
            $resultado['erros'][] = $e->getMessage();
            $this->renderLote($resultado);
            return;
        }

        if (empty($linhas)) {
            $resultado['erros'][] = 'Nenhuma linha com dados encontrada na planilha.';
            $this->renderLote($resultado);
            return;
        }

        $resultado['total'] = count($linhas);
        $emailsVistos = [];

        foreach ($linhas as $linha) {
            $nome = trim((string) ($linha['nome'] ?? ''));
            $telefone = trim((string) ($linha['telefone'] ?? ''));
            $email = strtolower(trim((string) ($linha['email'] ?? '')));

            if ($nome === '' || $email === '') {
                $resultado['ignorados']++;
                $resultado['erros'][] = "Linha ignorada (sem nome ou email): \"$nome\" <$email>";
                continue;
            }

            if (isset($emailsVistos[$email]) || $this->alunoService->existeEmail($email)) {
                $resultado['ignorados']++;
                $resultado['erros'][] = "Email já cadastrado: $email";
                continue;
            }
            $emailsVistos[$email] = true;

            $alunoId = $this->alunoService->criarAluno($nome, '', '', $telefone, $email, 1);

            if ($alunoId > 0) {
                $resultado['importados']++;
                $this->logService->log('criar', 'aluno', $alunoId, "Aluno criado via lote: $nome");
            } else {
                $resultado['ignorados']++;
                $resultado['erros'][] = "Falha ao cadastrar: $nome <$email>";
            }
        }

        $this->renderLote($resultado);
    }

    private function renderLote(array $resultado): void
    {
        $this->render('pages/admin/alunos/lote', [
            'title' => 'Importação em Lote',
            'currentRoute' => '/admin/alunos/lote',
            'resultado' => $resultado,
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
            'turmas' => $this->turmaService->turmasAtivas(500),
            'matricula' => $matricula,
            'turmasMatriculadas' => $turmasMatriculadas,
            'planos' => $this->planosDasTurmas($this->turmaService->turmasAtivas(500)),
            'linkFinanceiroMatricula' => Session::get('link_financeiro_matricula', ''),
        ], 'admin');
        Session::forget('link_financeiro_matricula');
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
        $idPlano = (int) $this->input('id_curso_pagamento', 0);
        $valorPrimeira = $this->valorBrasileiro((string) $this->input('valor_primeira', '0'));
        $totalParcelas = max(1, min(120, (int) $this->input('total_parcelas', 1)));
        $valorDemais = $this->valorBrasileiro((string) $this->input('valor_demais', '0'));
        $vencimento = trim((string) $this->input('data_vencimento', ''));
        $tipoFinanceiro = (int) $this->input('tipo_financeiro', 1);
        if (!in_array($tipoFinanceiro, [1, 2, 3], true)) {
            $tipoFinanceiro = 1;
        }

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

        if (preg_replace('/\D/', '', (string) ($aluno['cpf'] ?? '')) === '') {
            Session::setFlash('flash', 'CPF faltando');
            $this->redirect('/admin/alunos/editar?id=' . $idAluno);
            return;
        }

        $turma = $this->turmaService->findTurma($idTurma);
        $plano = $idPlano > 0 ? $this->pagamentoService->find($idPlano) : null;
        if (!$turma || (int) ($turma['ativo'] ?? 0) !== 1 || !$plano || (int) ($plano['id_curso'] ?? 0) !== (int) ($turma['id_curso'] ?? 0)) {
            Session::setFlash('flash', 'Selecione uma turma e um plano de pagamento válidos.');
            $this->redirect('/admin/alunos/matricula?id=' . $idAluno);
            return;
        }
        $totalParcelas = $totalParcelas > 1 ? $totalParcelas : max(1, (int) ($plano['parcelas'] ?? 1));
        $valorPrimeira = $valorPrimeira > 0 ? $valorPrimeira : round((float) ($plano['valor'] ?? 0) / $totalParcelas, 2);
        $valorDemais = $valorDemais > 0 ? $valorDemais : $valorPrimeira;
        if ($valorPrimeira <= 0 || ($totalParcelas > 1 && $valorDemais <= 0)) {
            Session::setFlash('flash', 'Informe valores válidos para as parcelas.');
            $this->redirect('/admin/alunos/matricula?id=' . $idAluno);
            return;
        }
        if ($vencimento === '' || strtotime($vencimento) === false) $vencimento = date('Y-m-d');

        if ($this->alunoService->matriculaJaExiste($idAluno, $idTurma)) {
            Session::setFlash('flash', 'Aluno já está matriculado nesta turma.');
            $this->redirect('/admin/alunos/matricula?id=' . $idAluno);
            return;
        }

        $parcelaId = $this->parcelaService->criar([
            'id_curso' => (int) $turma['id_curso'], 'id_pagamento' => $idPlano, 'id_turma' => $idTurma,
            'numero_parcela' => 1, 'total_parcelas' => $totalParcelas,
            'descricao_pagamento' => (string) ($plano['descricao'] ?? 'Matrícula'),
            'nome' => (string) ($aluno['nome'] ?? ''), 'cpf' => (string) ($aluno['cpf'] ?? ''),
            'email' => (string) ($aluno['email'] ?? ''), 'telefone' => (string) ($aluno['telefone'] ?? ''),
            'valor' => $valorPrimeira, 'data_vencimento' => $vencimento,
        ]);
        if ($parcelaId <= 0) {
            Session::setFlash('flash', 'Não foi possível registrar a primeira parcela.');
            $this->redirect('/admin/alunos/matricula?id=' . $idAluno);
            return;
        }
        $matriculaId = $this->alunoService->criarMatricula($idAluno, $idTurma, $status, $parcelaId);

        if ($matriculaId > 0) {
            $this->parcelaService->atualizarStatus($parcelaId, 'CONFIRMADO', $idAluno, $matriculaId);
            $this->notificacaoMatriculaService->enviar(
                $idAluno,
                (int) ($turma['id_curso'] ?? 0),
                (string) ($aluno['nome'] ?? ''),
                (string) ($aluno['email'] ?? ''),
                (string) ($aluno['cpf'] ?? ''),
                $matriculaId
            );
            $financeiro = ' Primeira parcela registrada como paga.';
            if ($totalParcelas > 1) {
                $origem = $this->parcelaService->buscar($parcelaId) ?? [];
                $origem['id_aluno'] = $idAluno; $origem['id_matricula'] = $matriculaId; $origem['valor'] = $valorDemais;

                if ($tipoFinanceiro === 3) {
                    $this->parcelaService->gerarParcelasRestantesPorPlano($origem);
                    $financeiro .= ' As demais parcelas foram registradas como acordo externo/manual; nenhuma cobrança foi criada automaticamente no Asaas.';
                } elseif ($tipoFinanceiro === 2) {
                    $usuarioSessao = Session::get('user');
                    $acordoId = $this->acordoService->salvar([
                        'tipo' => 5,
                        'id_pre_inscricao' => 0,
                        'id_curso_pagamento' => $idPlano,
                        'id_curso_parcela_origem' => $parcelaId,
                        'id_usuario_autorizacao' => is_array($usuarioSessao) ? (int) ($usuarioSessao['id'] ?? 0) : 0,
                        'cpf' => preg_replace('/\D/', '', (string) ($aluno['cpf'] ?? '')),
                        'token' => $this->acordoService->gerarToken(),
                        'valor_entrada' => $valorPrimeira,
                        'data_vencimento_entrada' => $vencimento,
                        'total_parcelas' => $totalParcelas,
                        'valor_demais_parcelas' => $valorDemais,
                        'utilizado' => 0,
                        'ativo' => 1,
                    ]);
                    $acordo = $acordoId > 0 ? $this->acordoService->findById($acordoId) : null;
                    if ($acordoId > 0 && is_array($acordo)) {
                        $this->parcelaService->vincularAcordo($parcelaId, $acordoId);
                        $origem['id_acordo_pagamento'] = $acordoId;
                        $this->parcelaService->gerarParcelasRestantes($origem, $acordo);
                    }
                    $token = is_array($acordo) ? (string) ($acordo['token'] ?? '') : '';
                    $link = $token !== '' ? rtrim((string) (getenv('APP_URL') ?: 'https://inteligenciaeducacionalsouzabrazil.com'), '/') . '/financeiro/' . $token : '';
                    if ($link !== '') {
                        Session::set('link_financeiro_matricula', $link);
                    }
                    if ($link !== '') {
                        $emailService = new EmailService();
                        $emailService->enviarHtml(
                            (string) ($aluno['email'] ?? ''),
                            (string) ($aluno['nome'] ?? ''),
                            'Escolha a recorrência das suas parcelas',
                            '<p>Olá, ' . htmlspecialchars((string) ($aluno['nome'] ?? ''), ENT_QUOTES, 'UTF-8') . '.</p><p>A primeira parcela da sua matrícula já foi registrada. Acesse o link abaixo para escolher cartão de crédito e recorrência para as demais parcelas:</p><p><a href="' . htmlspecialchars($link, ENT_QUOTES, 'UTF-8') . '">Acessar financeiro</a></p>',
                            "Olá, " . (string) ($aluno['nome'] ?? '') . ".\n\nA primeira parcela já foi registrada. Acesse o financeiro para escolher cartão e recorrência:\n" . $link
                        );
                        $financeiro .= ' Link para escolha de cartão e recorrência enviado ao aluno.';
                    } else {
                        $financeiro .= ' Não foi possível gerar o link do financeiro.';
                    }
                } else {
                    $this->parcelaService->gerarParcelasRestantesPorPlano($origem);
                    $asaas = new AsaasService();
                    $cliente = $asaas->criarCliente(['nome' => $aluno['nome'] ?? '', 'cpf' => $aluno['cpf'] ?? '', 'email' => $aluno['email'] ?? '', 'telefone' => $aluno['telefone'] ?? '']);
                    if ($cliente) {
                        $parcelas = $this->parcelaService->listarPorInscricao($idAluno, $idPlano, (int) $turma['id_curso']);
                        $cobrancasCriadas = 0;
                        $cobrancasFalhas = 0;
                        foreach ($parcelas as $parcela) {
                            $numero = (int) ($parcela['numero_parcela'] ?? 0);
                            if ($numero < 2) continue;
                            $cobranca = $asaas->criarCobranca([
                                'customer_id' => (string) $cliente['id'],
                                'billing_type' => 'UNDEFINED',
                                'value' => (float) ($parcela['valor'] ?? $valorDemais),
                                'due_date' => (string) ($parcela['data_vencimento'] ?? date('Y-m-d')),
                                'description' => (string) ($plano['descricao'] ?? 'Parcelas') . ' - ' . $numero . 'ª parcela',
                                'external_reference' => (string) ($parcela['id'] ?? 0),
                            ]);
                            if (!$cobranca) { $cobrancasFalhas++; continue; }
                            $this->parcelaService->atualizarAsaasInfo((int) $parcela['id'], [
                                'asaas_customer' => (string) $cliente['id'], 'asaas_payment' => (string) ($cobranca['id'] ?? ''),
                                'invoice_url' => (string) ($cobranca['invoiceUrl'] ?? $cobranca['paymentLink'] ?? $cobranca['bankSlipUrl'] ?? ''),
                                'bank_slip_url' => ($cobranca['bankSlipUrl'] ?? '') !== '' ? (string) $cobranca['bankSlipUrl'] : null,
                                'status' => (string) ($cobranca['status'] ?? 'PENDING'),
                            ]);
                            $cobrancasCriadas++;
                        }
                        $financeiro .= ' ' . $cobrancasCriadas . ' cobrança(s) restante(s) gerada(s) no Asaas; o aluno escolherá a forma de pagamento.';
                        if ($cobrancasFalhas > 0) $financeiro .= ' Falhas: ' . $cobrancasFalhas . '. ' . ($asaas->getLastError() ?? '');
                    } else $financeiro .= ' Demais parcelas criadas, mas não foi possível criar o cliente no Asaas: ' . ($asaas->getLastError() ?? 'erro desconhecido');
                }
            }
            $nomeAluno = (string) ($aluno['nome'] ?? '');
            $this->logService->log('criar', 'matricula', $matriculaId, "Matrícula criada: $nomeAluno");
            Session::setFlash('flash', 'Matrícula realizada com sucesso.' . $financeiro);
        } else {
            Session::setFlash('flash', 'Erro ao realizar matrícula. Tente novamente.');
        }

        $this->redirect('/admin/alunos/matricula?id=' . $idAluno);
    }

    public function cancelarMatricula(): void
    {
        if (!$this->isStaff()) {
            Session::setFlash('flash', 'Acesso negado.');
            $this->redirect('/admin/login');
            return;
        }

        $idAluno = (int) $this->input('id_aluno', 0);
        $idMatricula = (int) $this->input('id_matricula', 0);
        $senha = (string) $this->input('senha_confirmacao', '');

        if ($idAluno <= 0 || $idMatricula <= 0) {
            Session::setFlash('flash', 'Matrícula inválida.');
            $this->redirect('/admin/alunos');
            return;
        }

        $matricula = $this->alunoService->findMatriculaById($idMatricula);
        if (!$matricula || (int) ($matricula['id_aluno'] ?? 0) !== $idAluno) {
            Session::setFlash('flash', 'A matrícula não pertence ao aluno informado.');
            $this->redirect('/admin/alunos/matricula?id=' . $idAluno);
            return;
        }

        if (!(new AuthService())->reautenticarSessao($senha)) {
            Session::setFlash('flash', 'Senha de confirmação inválida. A matrícula não foi alterada.');
            $this->redirect('/admin/alunos/matricula?id=' . $idAluno);
            return;
        }

        if (!$this->alunoService->cancelarMatricula($idMatricula)) {
            Session::setFlash('flash', 'Não foi possível cancelar a matrícula. Ela pode já estar cancelada.');
            $this->redirect('/admin/alunos/matricula?id=' . $idAluno);
            return;
        }

        $this->logService->log(
            'excluir',
            'matricula',
            $idMatricula,
            'Matrícula cancelada pelo usuário autenticado; parcelas vinculadas inativadas.'
        );
        Session::setFlash('flash', 'Matrícula cancelada com segurança. O cadastro do aluno e o histórico foram preservados.');
        $this->redirect('/admin/alunos/matricula?id=' . $idAluno);
    }

    public function lancarParcelaPaga(): void
    {
        if (!$this->isStaff()) {
            Session::setFlash('flash', 'Acesso negado.');
            $this->redirect('/admin/login');
            return;
        }

        $idAluno = (int) $this->input('id_aluno', 0);
        $idParcela = (int) $this->input('id_parcela', 0);
        $senha = (string) $this->input('senha_confirmacao', '');
        $redirect = '/admin/alunos/show?id=' . $idAluno;

        $parcela = $idParcela > 0 ? $this->parcelaService->buscar($idParcela) : null;
        if ($idAluno <= 0 || !is_array($parcela) || (int) ($parcela['id_aluno'] ?? 0) !== $idAluno) {
            Session::setFlash('flash', 'Parcela inválida ou não pertencente a este aluno.');
            $this->redirect($redirect);
            return;
        }

        if (in_array((string) ($parcela['status'] ?? ''), ['RECEBIDO', 'CONFIRMADO'], true)) {
            Session::setFlash('flash', 'Esta parcela já está marcada como paga.');
            $this->redirect($redirect);
            return;
        }

        if (!(new AuthService())->reautenticarSessao($senha)) {
            Session::setFlash('flash', 'Senha de confirmação inválida. A parcela não foi alterada.');
            $this->redirect($redirect);
            return;
        }

        $idMatricula = (int) ($parcela['id_matricula'] ?? 0);
        if (!$this->parcelaService->atualizarStatus($idParcela, 'CONFIRMADO', $idAluno, $idMatricula > 0 ? $idMatricula : null)) {
            Session::setFlash('flash', 'Não foi possível lançar a parcela como paga.');
            $this->redirect($redirect);
            return;
        }

        $this->logService->log('atualizar', 'curso_parcela', $idParcela, 'Parcela lançada como paga manualmente pelo administrador.');
        Session::setFlash('flash', 'Parcela lançada como paga com sucesso.');
        $this->redirect($redirect);
    }

    private function valorBrasileiro(string $valor): float
    {
        return $valor === '' ? 0.0 : (float) str_replace(',', '.', str_replace('.', '', trim($valor)));
    }

    private function planosDasTurmas(array $turmas): array
    {
        $ids = [];
        foreach ($turmas as $turma) $ids[(int) ($turma['id_curso'] ?? 0)] = true;
        $planos = [];
        foreach (array_keys($ids) as $idCurso) foreach ($this->pagamentoService->listarPorCurso((int) $idCurso) as $plano) if ((int) ($plano['ativo'] ?? 0) === 1) $planos[] = $plano;
        return $planos;
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

    public function uploadDocumento(): void
    {
        if (!$this->isStaff()) {
            Session::setFlash('flash', 'Acesso negado.');
            $this->redirect('/admin/login');
        }

        $alunoId = (int) $this->input('aluno_id', 0);
        $tipoId = (int) $this->input('id_tipo', 0);
        $file = $_FILES['arquivo'] ?? null;

        if ($alunoId <= 0 || $tipoId <= 0 || !$file) {
            Session::setFlash('flash', 'Selecione o tipo de documento e o arquivo.');
            $this->redirect('/admin/alunos/show?id=' . $alunoId);
            return;
        }

        if ($file['error'] !== UPLOAD_ERR_OK) {
            Session::setFlash('flash', 'Erro no upload do arquivo.');
            $this->redirect('/admin/alunos/show?id=' . $alunoId);
            return;
        }

        $originalName = (string) ($file['name'] ?? '');
        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        $allowed = ['pdf', 'png', 'jpg', 'jpeg'];

        if (!in_array($extension, $allowed, true)) {
            Session::setFlash('flash', 'Formato não permitido. Use PDF, PNG, JPG ou JPEG.');
            $this->redirect('/admin/alunos/show?id=' . $alunoId);
            return;
        }

        if ((int) ($file['size'] ?? 0) > 20 * 1024 * 1024) {
            Session::setFlash('flash', 'O arquivo deve ter no máximo 20MB.');
            $this->redirect('/admin/alunos/show?id=' . $alunoId);
            return;
        }

        $aluno = $this->alunoService->findAluno($alunoId);
        if (!$aluno) {
            Session::setFlash('flash', 'Aluno não encontrado.');
            $this->redirect('/admin/alunos');
            return;
        }

        $pdo = Database::connection();
        if (!$pdo instanceof \PDO) {
            Session::setFlash('flash', 'Erro de conexão com o banco de dados.');
            $this->redirect('/admin/alunos/show?id=' . $alunoId);
            return;
        }

        $grupoAlunos = \App\Services\Storage\StorageService::GROUP_ALUNOS;

        $tipo = null;
        try {
            $stmt = $pdo->prepare('SELECT id, descricao FROM documento_tipo WHERE id = :id AND id_grupo = :id_grupo AND ativo = 1 LIMIT 1');
            $stmt->bindValue(':id', $tipoId, \PDO::PARAM_INT);
            $stmt->bindValue(':id_grupo', $grupoAlunos, \PDO::PARAM_INT);
            $stmt->execute();
            $tipo = $stmt->fetch() ?: null;
        } catch (\Throwable $e) {
            error_log('[ALUNO UPLOAD DOC] Erro: ' . $e->getMessage());
        }

        if (!$tipo) {
            Session::setFlash('flash', 'Tipo de documento inválido.');
            $this->redirect('/admin/alunos/show?id=' . $alunoId);
            return;
        }

        $storageDriveRepo = new \App\Repositories\StorageDriveRepository();
        $pasta = $storageDriveRepo->findByRegistro($grupoAlunos, $alunoId);

        $storage = new \App\Services\Storage\StorageService();

        if ($pasta === null) {
            if (!$storage->isConnected()) {
                Session::setFlash('flash', 'Storage não conectado. Tente novamente mais tarde.');
                $this->redirect('/admin/alunos/show?id=' . $alunoId);
                return;
            }
            try {
                $nomeAluno = (string) ($aluno['nome'] ?? '');
                $folderId = $storage->ensureRegistroFolder($grupoAlunos, (string) $alunoId, $nomeAluno);
                if ($folderId === '') {
                    throw new \RuntimeException('Pasta não criada no Drive.');
                }
                $storageDriveRepo->create([
                    'id_grupo' => $grupoAlunos,
                    'id_registro' => $alunoId,
                    'folder_id' => $folderId,
                    'folder_name' => sprintf('%06d-%s', $alunoId, $nomeAluno),
                    'folder_link' => $storage->generateViewLinkByFileId($folderId),
                    'tipo' => 'registro',
                    'nivel' => 2,
                ]);
                $pasta = $storageDriveRepo->findByRegistro($grupoAlunos, $alunoId);
            } catch (\Throwable $e) {
                error_log('[ALUNO UPLOAD DOC] Erro ao criar pasta: ' . $e->getMessage());
                Session::setFlash('flash', 'Pasta do aluno no Drive não encontrada. Verifique a conexão do Storage.');
                $this->redirect('/admin/alunos/show?id=' . $alunoId);
                return;
            }
        }

        if (!$storage->isConnected()) {
            Session::setFlash('flash', 'Storage não conectado. Tente novamente mais tarde.');
            $this->redirect('/admin/alunos/show?id=' . $alunoId);
            return;
        }

        $documentoAtual = null;
        try {
            $stmt = $pdo->prepare('SELECT id, versao, status FROM documento WHERE id_tipo = :id_tipo AND id_registro = :id_registro AND ativo = 1 ORDER BY versao DESC, id DESC LIMIT 1');
            $stmt->bindValue(':id_tipo', $tipoId, \PDO::PARAM_INT);
            $stmt->bindValue(':id_registro', $alunoId, \PDO::PARAM_INT);
            $stmt->execute();
            $documentoAtual = $stmt->fetch() ?: null;
        } catch (\Throwable $e) {
            error_log('[ALUNO UPLOAD DOC] Erro: ' . $e->getMessage());
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
                $alunoId,
                $tipoId,
                (string) ($pasta['folder_id'] ?? ''),
                $nomeDrive,
                'aprovado'
            );

            $this->logService->log('upload', 'documento', (int) $result['id'], "Secretaria registrou documento {$tipo['descricao']} para o aluno {$aluno['nome']} (v{$versao})");
            Session::setFlash('flash', 'Documento registrado com sucesso.');
        } catch (\App\Services\Storage\StorageException $e) {
            error_log('[ALUNO UPLOAD DOC] Storage: ' . $e->getMessage());
            Session::setFlash('flash', 'Erro ao registrar o documento: ' . $e->getMessage());
        } catch (\Throwable $e) {
            error_log('[ALUNO UPLOAD DOC] Erro: ' . $e->getMessage());
            Session::setFlash('flash', 'Erro ao registrar o documento.');
        }

        $this->redirect('/admin/alunos/show?id=' . $alunoId);
    }

    private function tipoSigla(string $descricao): string
    {
        $sigla = preg_replace('/[^A-Za-z0-9]/', '', $descricao);
        $sigla = strtoupper((string) $sigla);
        return $sigla !== '' ? $sigla : 'DOC';
    }

    private function isStaff(): bool
    {
        return (new \App\Services\AuthService())->isStaff();
    }
}
