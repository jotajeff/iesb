<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Services\AcordoPagamentoService;
use App\Services\AsaasService;
use App\Services\CursoInscricaoService;
use App\Services\CursoPagamentoService;
use App\Services\CursoService;

final class FinanceiroController extends Controller
{
    private AcordoPagamentoService $acordoService;
    private CursoPagamentoService $pagamentoService;
    private CursoService $cursoService;
    private CursoInscricaoService $inscricaoService;

    public function __construct()
    {
        $this->acordoService = new AcordoPagamentoService();
        $this->pagamentoService = new CursoPagamentoService();
        $this->cursoService = new CursoService();
        $this->inscricaoService = new CursoInscricaoService();
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
        if (!in_array($forma, ['pix', 'cartao'], true)) {
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
        $valorParcela = round((float) ($acordo['valor_negociado'] ?? 0) / max(1, (int) ($acordo['parcelas_negociadas'] ?? 1)), 2);

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

        $inscricaoId = $this->inscricaoService->criarComAcordo([
            'id_curso' => $idCurso,
            'id_pagamento' => $idCursoPagamento,
            'id_pre_inscricao' => $idPreInscricao,
            'id_acordo_pagamento' => $idAcordo,
            'descricao_pagamento' => $descricaoPlano . ' (' . (int) ($acordo['parcelas_negociadas'] ?? 1) . 'x)',
            'nome' => $nome,
            'cpf' => $cpf,
            'email' => $email,
            'telefone' => $telefone,
            'valor' => $valorParcela,
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
        $billingType = $forma === 'cartao' ? 'CREDIT_CARD' : 'PIX';
        $descricao = $nomeCurso . ' - ' . $descricaoPlano . ' (' . (int) ($acordo['parcelas_negociadas'] ?? 1) . 'x) - 1ª parcela';

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

        $this->inscricaoService->atualizarAsaasInfo($inscricaoId, [
            'asaas_customer' => $clienteId,
            'asaas_payment' => $cobranca['id'] ?? null,
            'invoice_url' => $invoiceUrl !== '' ? $invoiceUrl : $bankSlipUrl,
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
            'abrirCheckoutNovaAba' => $billingType === 'CREDIT_CARD' && $invoiceUrl !== '',
        ]);
    }
}
