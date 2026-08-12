<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Services\AcordoPagamentoService;
use App\Services\AsaasService;
use App\Services\AuthService;
use App\Services\CursoParcelaService;
use App\Services\CursoPagamentoService;
use App\Services\CursoService;
use App\Support\Session;

final class FinanceiroController extends Controller
{
    private AcordoPagamentoService $acordoService;
    private CursoPagamentoService $pagamentoService;
    private CursoService $cursoService;
    private CursoParcelaService $parcelaService;
    private AuthService $auth;

    public function __construct()
    {
        $this->acordoService = new AcordoPagamentoService();
        $this->pagamentoService = new CursoPagamentoService();
        $this->cursoService = new CursoService();
        $this->parcelaService = new CursoParcelaService();
        $this->auth = new AuthService();
    }

    public function portal(): void
    {
        $token = trim((string) ($_GET['token'] ?? ''));
        $acordo = $token !== '' ? $this->acordoService->findByToken($token) : null;

        if ($acordo === null || (int) ($acordo['ativo'] ?? 0) !== 1) {
            http_response_code(404);
            $this->render('pages/404', ['title' => 'Acordo não encontrado', 'currentRoute' => '/financeiro']);
            return;
        }

        $this->render('pages/financeiro', [
            'title' => 'Portal Financeiro',
            'currentRoute' => '/financeiro',
            'acordo' => $acordo,
            'token' => $token,
            'sucesso' => false,
            'inscricaoId' => 0,
            'invoiceUrl' => '',
            'bankSlipUrl' => '',
            'pixQrCode' => null,
            'linhaDigitavel' => null,
            'billingType' => '',
            'asaasError' => null,
            'abrirCheckoutNovaAba' => false,
        ]);
    }

    public function continuar(): void
    {
        $token = trim((string) ($_GET['token'] ?? ''));
        $acordo = $token !== '' ? $this->acordoService->findByToken($token) : null;

        if ($acordo === null || (int) ($acordo['ativo'] ?? 0) !== 1) {
            http_response_code(404);
            $this->render('pages/404', ['title' => 'Acordo não encontrado', 'currentRoute' => '/financeiro']);
            return;
        }

        if ((int) ($acordo['utilizado'] ?? 0) === 1) {
            $this->render('pages/financeiro', [
                'title' => 'Portal Financeiro',
                'currentRoute' => '/financeiro',
                'acordo' => $acordo,
                'token' => $token,
                'sucesso' => false,
                'inscricaoId' => 0,
                'invoiceUrl' => '',
                'bankSlipUrl' => '',
                'pixQrCode' => null,
                'linhaDigitavel' => null,
                'billingType' => '',
                'asaasError' => null,
                'abrirCheckoutNovaAba' => false,
                'jaUtilizado' => true,
            ]);
            return;
        }

        $forma = trim((string) $this->input('forma_pagamento', 'pix'));
        if (!in_array($forma, ['pix', 'cartao', 'boleto'], true)) {
            $forma = 'pix';
        }

        $nome = trim((string) ($acordo['candidato_nome'] ?? ''));
        $email = trim((string) ($acordo['candidato_email'] ?? ''));
        $telefone = trim((string) ($acordo['candidato_telefone'] ?? ''));
        $cpf = preg_replace('/\D/', '', (string) ($acordo['cpf'] ?? ''));
        $idCurso = (int) ($acordo['plano_id_curso'] ?? $acordo['curso_id'] ?? 0);
        $idCursoPagamento = (int) ($acordo['id_curso_pagamento'] ?? 0);
        $idAcordo = (int) ($acordo['id'] ?? 0);
        $idPreInscricao = (int) ($acordo['id_pre_inscricao'] ?? 0);
        $descricaoPlano = (string) ($acordo['plano_descricao'] ?? 'Plano negociado');
        $tipoAcordo = (int) ($acordo['tipo'] ?? 1);
        $totalParcelas = max(1, (int) ($acordo['total_parcelas'] ?? 1));
        $valorEntrada = (float) ($acordo['valor_entrada'] ?? 0);
        $valorDemaisParcelas = (float) ($acordo['valor_demais_parcelas'] ?? 0);
        $dataVencimentoEntrada = trim((string) ($acordo['data_vencimento_entrada'] ?? ''));
        $valorParcela = $valorEntrada > 0 ? $valorEntrada : round((float) ($acordo['valor_demais_parcelas'] ?? 0), 2);
        if ($valorParcela <= 0) {
            $valorParcela = round((float) ($acordo['plano_valor'] ?? 0), 2);
        }

        $curso = $this->cursoService->findCurso($idCurso);
        $nomeCurso = $curso ? (string) ($curso['nome'] ?? 'Curso') : 'Curso';

        if ($nome === '' || $cpf === '' || $idCurso <= 0) {
            $this->render('pages/financeiro', [
                'title' => 'Portal Financeiro',
                'currentRoute' => '/financeiro',
                'acordo' => $acordo,
                'token' => $token,
                'sucesso' => false,
                'inscricaoId' => 0,
                'invoiceUrl' => '',
                'bankSlipUrl' => '',
                'pixQrCode' => null,
                'linhaDigitavel' => null,
                'billingType' => '',
                'asaasError' => 'Dados do acordo incompletos. Contate a secretaria.',
                'abrirCheckoutNovaAba' => false,
            ]);
            return;
        }

        $inscricaoId = $this->parcelaService->criarComAcordo([
            'id_curso' => $idCurso,
            'id_pagamento' => $idCursoPagamento,
            'id_pre_inscricao' => $idPreInscricao,
            'id_acordo_pagamento' => $idAcordo,
            'numero_parcela' => 1,
            'total_parcelas' => $totalParcelas,
            'descricao_pagamento' => $descricaoPlano . ' (' . $totalParcelas . 'x)',
            'nome' => $nome,
            'cpf' => $cpf,
            'email' => $email,
            'telefone' => $telefone,
            'valor' => $valorParcela,
            'data_vencimento' => $dataVencimentoEntrada !== '' ? $dataVencimentoEntrada : date('Y-m-d', strtotime('+3 days')),
        ]);

        if ($inscricaoId <= 0) {
            $this->render('pages/financeiro', [
                'title' => 'Portal Financeiro',
                'currentRoute' => '/financeiro',
                'acordo' => $acordo,
                'token' => $token,
                'sucesso' => false,
                'inscricaoId' => 0,
                'invoiceUrl' => '',
                'bankSlipUrl' => '',
                'pixQrCode' => null,
                'linhaDigitavel' => null,
                'billingType' => '',
                'asaasError' => 'Erro ao processar o pagamento. Tente novamente.',
                'abrirCheckoutNovaAba' => false,
            ]);
            return;
        }

        $asaas = new AsaasService();
        $cliente = $asaas->criarCliente([
            'nome' => $nome,
            'cpf' => $cpf,
            'email' => $email,
            'telefone' => $telefone,
        ]);

        if (!$cliente) {
            $this->render('pages/financeiro', [
                'title' => 'Portal Financeiro',
                'currentRoute' => '/financeiro',
                'acordo' => $acordo,
                'token' => $token,
                'sucesso' => false,
                'inscricaoId' => $inscricaoId,
                'invoiceUrl' => '',
                'bankSlipUrl' => '',
                'pixQrCode' => null,
                'linhaDigitavel' => null,
                'billingType' => '',
                'asaasError' => 'Não foi possível registrar o cliente no gateway de pagamento: ' . ($asaas->getLastError() ?? 'erro desconhecido'),
                'abrirCheckoutNovaAba' => false,
            ]);
            return;
        }

        $clienteId = (string) $cliente['id'];
        $billingTypeMap = [
            'pix' => 'PIX',
            'cartao' => 'CREDIT_CARD',
            'boleto' => 'BOLETO',
        ];
        $billingType = $billingTypeMap[$forma] ?? 'PIX';
        $descricao = $nomeCurso . ' - ' . $descricaoPlano . ' (' . $totalParcelas . 'x) - 1ª parcela';

        $cobranca = $asaas->criarCobranca([
            'customer_id' => $clienteId,
            'billing_type' => $billingType,
            'value' => $valorParcela,
            'description' => $descricao,
            'external_reference' => (string) $inscricaoId,
        ]);

        $invoiceUrl = (string) ($cobranca['invoiceUrl'] ?? '');
        $bankSlipUrl = (string) ($cobranca['bankSlipUrl'] ?? '');
        $pixQrCode = null;
        $linhaDigitavel = null;

        if ($cobranca && $billingType === 'PIX') {
            $pixQrCode = $asaas->obterPixQrCode((string) ($cobranca['id'] ?? ''));
        }

        $this->parcelaService->atualizarAsaasInfo($inscricaoId, [
            'asaas_customer' => $clienteId,
            'asaas_payment' => $cobranca['id'] ?? null,
            'invoice_url' => $invoiceUrl !== '' ? $invoiceUrl : $bankSlipUrl,
            'bank_slip_url' => $bankSlipUrl !== '' ? $bankSlipUrl : null,
            'status' => $cobranca['status'] ?? 'PENDENTE',
        ]);

        if ($cobranca) {
            $this->acordoService->marcarUtilizado($idAcordo);
        }

        $this->render('pages/financeiro', [
            'title' => 'Portal Financeiro',
            'currentRoute' => '/financeiro',
            'acordo' => $acordo,
            'token' => $token,
            'sucesso' => true,
            'inscricaoId' => $inscricaoId,
            'invoiceUrl' => $invoiceUrl,
            'bankSlipUrl' => $bankSlipUrl,
            'pixQrCode' => $pixQrCode,
            'linhaDigitavel' => $linhaDigitavel,
            'billingType' => $billingType,
            'asaasError' => $asaas->getLastError(),
            'abrirCheckoutNovaAba' => in_array($billingType, ['CREDIT_CARD', 'BOLETO'], true) && ($invoiceUrl !== '' || $bankSlipUrl !== ''),
        ]);
    }

    /**
     * Exibe o portal financeiro para pagamento de uma parcela específica.
     * Requer aluno logado e parcela pertencente ao aluno.
     */
    public function parcela(): void
    {
        if (!$this->auth->checkRole('aluno')) {
            Session::setFlash('flash', 'Faça login como aluno para acessar o financeiro.');
            $this->redirect('/aluno/login');
            return;
        }

        $user = Session::get('user');
        $studentId = (int) ($user['id'] ?? 0);
        $idParcela = (int) ($_GET['id'] ?? 0);

        $parcela = $idParcela > 0 ? $this->parcelaService->buscar($idParcela) : null;
        if ($parcela === null || (int) ($parcela['id_aluno'] ?? 0) !== $studentId || (int) ($parcela['ativo'] ?? 1) !== 1) {
            Session::setFlash('flash', 'Parcela não encontrada.');
            $this->redirect('/aluno/financeiro');
            return;
        }

        if (in_array((string) ($parcela['status'] ?? ''), ['RECEBIDO', 'CONFIRMADO'], true)) {
            Session::setFlash('flash', 'Esta parcela já foi paga.');
            $this->redirect('/aluno/financeiro');
            return;
        }

        $parcela = $this->comCursoNome($parcela);

        $this->render('pages/financeiro', [
            'title' => 'Pagamento de parcela',
            'currentRoute' => '/financeiro',
            'modo' => 'parcela',
            'parcela' => $parcela,
            'acordo' => [],
            'token' => (string) $idParcela,
            'sucesso' => false,
            'jaUtilizado' => false,
            'inscricaoId' => $idParcela,
            'invoiceUrl' => '',
            'bankSlipUrl' => '',
            'pixQrCode' => null,
            'linhaDigitavel' => null,
            'billingType' => '',
            'asaasError' => null,
            'abrirCheckoutNovaAba' => false,
        ]);
    }

    /**
     * Gera a cobrança Asaas para uma parcela específica.
     */
    public function continuarParcela(): void
    {
        if (!$this->auth->checkRole('aluno')) {
            Session::setFlash('flash', 'Faça login como aluno para acessar o financeiro.');
            $this->redirect('/aluno/login');
            return;
        }

        $user = Session::get('user');
        $studentId = (int) ($user['id'] ?? 0);
        $idParcela = (int) ($_GET['id'] ?? 0);

        $parcela = $idParcela > 0 ? $this->parcelaService->buscar($idParcela) : null;
        if ($parcela === null || (int) ($parcela['id_aluno'] ?? 0) !== $studentId || (int) ($parcela['ativo'] ?? 1) !== 1) {
            Session::setFlash('flash', 'Parcela não encontrada.');
            $this->redirect('/aluno/financeiro');
            return;
        }

        if (in_array((string) ($parcela['status'] ?? ''), ['RECEBIDO', 'CONFIRMADO'], true)) {
            Session::setFlash('flash', 'Esta parcela já foi paga.');
            $this->redirect('/aluno/financeiro');
            return;
        }

        $forma = trim((string) $this->input('forma_pagamento', 'pix'));
        if (!in_array($forma, ['pix', 'cartao', 'boleto'], true)) {
            $forma = 'pix';
        }

        $nome = (string) ($parcela['nome'] ?? '');
        $email = (string) ($parcela['email'] ?? '');
        $telefone = (string) ($parcela['telefone'] ?? '');
        $cpf = preg_replace('/\D/', '', (string) ($parcela['cpf'] ?? ''));
        $idCurso = (int) ($parcela['id_curso'] ?? 0);
        $valor = (float) ($parcela['valor'] ?? 0);
        $vencimento = (string) ($parcela['data_vencimento'] ?? '');
        $numeroParcela = (int) ($parcela['numero_parcela'] ?? 0);
        $totalParcelas = (int) ($parcela['total_parcelas'] ?? 0);

        $curso = $this->cursoService->findCurso($idCurso);
        $nomeCurso = $curso ? (string) ($curso['nome'] ?? 'Curso') : 'Curso';
        $parcela = $this->comCursoNome($parcela, $nomeCurso);

        if ($nome === '' || $cpf === '' || $idCurso <= 0 || $valor <= 0) {
            $this->render('pages/financeiro', [
                'title' => 'Pagamento de parcela',
                'currentRoute' => '/financeiro',
                'modo' => 'parcela',
                'parcela' => $parcela,
                'acordo' => [],
                'token' => (string) $idParcela,
                'sucesso' => false,
                'jaUtilizado' => false,
                'inscricaoId' => $idParcela,
                'invoiceUrl' => '',
                'bankSlipUrl' => '',
                'pixQrCode' => null,
                'linhaDigitavel' => null,
                'billingType' => '',
                'asaasError' => 'Dados da parcela incompletos. Contate a secretaria.',
                'abrirCheckoutNovaAba' => false,
            ]);
            return;
        }

        $asaas = new AsaasService();
        $cliente = $asaas->criarCliente([
            'nome' => $nome,
            'cpf' => $cpf,
            'email' => $email,
            'telefone' => $telefone,
        ]);

        if (!$cliente) {
            $this->render('pages/financeiro', [
                'title' => 'Pagamento de parcela',
                'currentRoute' => '/financeiro',
                'modo' => 'parcela',
                'parcela' => $parcela,
                'acordo' => [],
                'token' => (string) $idParcela,
                'sucesso' => false,
                'jaUtilizado' => false,
                'inscricaoId' => $idParcela,
                'invoiceUrl' => '',
                'bankSlipUrl' => '',
                'pixQrCode' => null,
                'linhaDigitavel' => null,
                'billingType' => '',
                'asaasError' => 'Não foi possível registrar o cliente no gateway de pagamento: ' . ($asaas->getLastError() ?? 'erro desconhecido'),
                'abrirCheckoutNovaAba' => false,
            ]);
            return;
        }

        $clienteId = (string) $cliente['id'];
        $billingTypeMap = [
            'pix' => 'PIX',
            'cartao' => 'CREDIT_CARD',
            'boleto' => 'BOLETO',
        ];
        $billingType = $billingTypeMap[$forma] ?? 'PIX';
        $descricao = $nomeCurso . ' - ' . $numeroParcela . 'ª parcela de ' . $totalParcelas;

        $cobranca = $asaas->criarCobranca([
            'customer_id' => $clienteId,
            'billing_type' => $billingType,
            'value' => $valor,
            'description' => $descricao,
            'external_reference' => (string) $idParcela,
            'due_date' => $vencimento !== '' ? $vencimento : null,
        ]);

        $invoiceUrl = (string) ($cobranca['invoiceUrl'] ?? '');
        $bankSlipUrl = (string) ($cobranca['bankSlipUrl'] ?? '');
        $pixQrCode = null;
        $linhaDigitavel = null;

        if ($cobranca && $billingType === 'PIX') {
            $pixQrCode = $asaas->obterPixQrCode((string) ($cobranca['id'] ?? ''));
        }

        $this->parcelaService->atualizarAsaasInfo($idParcela, [
            'asaas_customer' => $clienteId,
            'asaas_payment' => $cobranca['id'] ?? null,
            'invoice_url' => $invoiceUrl !== '' ? $invoiceUrl : $bankSlipUrl,
            'bank_slip_url' => $bankSlipUrl !== '' ? $bankSlipUrl : null,
            'status' => $cobranca['status'] ?? 'PENDENTE',
        ]);

        $this->render('pages/financeiro', [
            'title' => 'Pagamento de parcela',
            'currentRoute' => '/financeiro',
            'modo' => 'parcela',
            'parcela' => $parcela,
            'acordo' => [],
            'token' => (string) $idParcela,
            'sucesso' => true,
            'jaUtilizado' => false,
            'inscricaoId' => $idParcela,
            'invoiceUrl' => $invoiceUrl,
            'bankSlipUrl' => $bankSlipUrl,
            'pixQrCode' => $pixQrCode,
            'linhaDigitavel' => $linhaDigitavel,
            'billingType' => $billingType,
            'asaasError' => $asaas->getLastError(),
            'abrirCheckoutNovaAba' => in_array($billingType, ['CREDIT_CARD', 'BOLETO'], true) && ($invoiceUrl !== '' || $bankSlipUrl !== ''),
        ]);
    }

    /**
     * Enriquece a parcela com o nome do curso para exibição na view.
     *
     * @param array<string, mixed> $parcela
     * @return array<string, mixed>
     */
    private function comCursoNome(array $parcela, string $nomeCurso = ''): array
    {
        if ($nomeCurso !== '') {
            $parcela['curso_nome'] = $nomeCurso;
            return $parcela;
        }

        $idCurso = (int) ($parcela['id_curso'] ?? 0);
        $curso = $idCurso > 0 ? $this->cursoService->findCurso($idCurso) : null;
        $parcela['curso_nome'] = $curso ? (string) ($curso['nome'] ?? 'Curso') : 'Curso';
        return $parcela;
    }
}
