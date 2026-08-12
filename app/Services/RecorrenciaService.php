<?php

declare(strict_types=1);

namespace App\Services;

/**
 * Gerencia a recorrência no cartão de crédito dos Acordos de Pagamento.
 *
 * A assinatura recorrente no Asaas só deve ser criada DEPOIS do pagamento
 * da primeira parcela (avulsa) e somente quando o aluno autorizou
 * explicitamente (recorrencia_cartao = 1).
 */
final class RecorrenciaService
{
    public function __construct(
        private readonly AcordoPagamentoService $acordoService = new AcordoPagamentoService(),
        private readonly CursoParcelaService $parcelaService = new CursoParcelaService(),
        private readonly AsaasService $asaas = new AsaasService(),
    ) {
    }

    /**
     * Cria a assinatura recorrente no Asaas após a confirmação da primeira parcela.
     *
     * Protegida contra duplicidade: só cria se recorrencia_cartao = 1
     * e asaas_subscription IS NULL.
     *
     * @param array<string, mixed> $parcelaOrigem Parcela 1 (avulsa) já confirmada.
     * @return array<string, mixed>
     */
    public function criarAssinatura(array $parcelaOrigem): array
    {
        $idAcordo = (int) ($parcelaOrigem['id_acordo_pagamento'] ?? 0);
        if ($idAcordo <= 0) {
            return ['success' => false, 'message' => 'Parcela sem acordo vinculado'];
        }

        // Recarrega a parcela origem do banco: garante id_aluno/id_matricula
        // atuais ao gerar as parcelas restantes (2..N).
        $idParcelaOrigem = (int) ($parcelaOrigem['id'] ?? 0);
        if ($idParcelaOrigem > 0) {
            $atual = $this->parcelaService->buscar($idParcelaOrigem);
            if (is_array($atual)) {
                $parcelaOrigem = array_merge($parcelaOrigem, $atual);
            }
        }

        $acordo = $this->acordoService->findById($idAcordo);
        if ($acordo === null) {
            return ['success' => false, 'message' => 'Acordo não encontrado'];
        }

        if ((int) ($acordo['recorrencia_cartao'] ?? 0) !== 1) {
            return ['success' => false, 'message' => 'Recorrência não autorizada pelo aluno'];
        }

        $assinaturaExistente = trim((string) ($acordo['asaas_subscription'] ?? ''));
        if ($assinaturaExistente !== '') {
            return ['success' => false, 'message' => 'Assinatura já existente para este acordo'];
        }

        $totalParcelas = (int) ($acordo['total_parcelas'] ?? (int) ($parcelaOrigem['total_parcelas'] ?? 1));
        if ($totalParcelas <= 1) {
            return ['success' => false, 'message' => 'Acordo com parcela única não gera recorrência'];
        }

        // Garante a existência das parcelas 2..N (idempotente) para servir de
        // fonte de verdade quando as cobranças recorrentes chegarem ao webhook.
        $this->parcelaService->gerarParcelasRestantes($parcelaOrigem, $acordo);

        $parcelas = $this->parcelaService->listarPorAcordo($idAcordo);
        $dataInicio = '';
        $dataFim = '';
        foreach ($parcelas as $parcela) {
            $numero = (int) ($parcela['numero_parcela'] ?? 0);
            if ($numero === 2 && $dataInicio === '') {
                $dataInicio = (string) ($parcela['data_vencimento'] ?? '');
            }
            if ($numero === $totalParcelas && $dataFim === '') {
                $dataFim = (string) ($parcela['data_vencimento'] ?? '');
            }
        }

        $dataBase = (string) ($parcelaOrigem['data_vencimento'] ?? date('Y-m-d'));
        if ($dataInicio === '') {
            $dataInicio = date('Y-m-d', strtotime($dataBase . ' + 30 days'));
        }
        if ($dataFim === '') {
            $dataFim = date('Y-m-d', strtotime($dataInicio . ' + ' . (($totalParcelas - 2) * 30) . ' days'));
        }

        $valor = (float) ($acordo['valor_demais_parcelas'] ?? 0);
        if ($valor <= 0) {
            $valor = (float) ($parcelaOrigem['valor'] ?? 0);
        }

        $customerId = (string) ($parcelaOrigem['asaas_customer'] ?? '');
        $descricao = (string) ($parcelaOrigem['descricao_pagamento'] ?? 'Acordo de pagamento');

        $resultado = $this->asaas->criarAssinatura([
            'customer_id' => $customerId,
            'billing_type' => 'CREDIT_CARD',
            'value' => $valor,
            'cycle' => 'MONTHLY',
            'next_due_date' => $dataInicio,
            'end_date' => $dataFim,
            'description' => $descricao . ' - recorrência',
            'external_reference' => (string) $idAcordo,
        ]);

        if ($resultado === null) {
            $this->acordoService->atualizarRecorrencia($idAcordo, [
                'status_recorrencia' => 'ERRO',
            ]);
            return [
                'success' => false,
                'message' => 'Falha ao criar assinatura: ' . ($this->asaas->getLastError() ?? 'erro desconhecido'),
            ];
        }

        $this->acordoService->atualizarRecorrencia($idAcordo, [
            'asaas_subscription' => (string) ($resultado['id'] ?? ''),
            'status_recorrencia' => 'ATIVA',
            'data_inicio_recorrencia' => $dataInicio,
            'data_fim_recorrencia' => $dataFim,
        ]);

        return [
            'success' => true,
            'subscription' => (string) ($resultado['id'] ?? ''),
            'dataInicio' => $dataInicio,
            'dataFim' => $dataFim,
        ];
    }

    /**
     * Vincula uma cobrança recorrente (gerada pelo Asaas) à parcela
     * correspondente do acordo. Usado pelo webhook quando o asaas_payment
     * ainda não está associado a nenhuma curso_parcela.
     *
     * O acordo é identificado prioritariamente por payment.subscription
     * (acordo_pagamento.asaas_subscription). O externalReference é usado
     * somente como fallback para cobranças antigas.
     *
     * Idempotente: se a parcela já possui o mesmo asaas_payment, retorna ela
     * sem alterar nada.
     *
     * @param array<string, mixed> $payment
     * @return array<string, mixed>|null A parcela vinculada, ou null se não identificada.
     */
    public function vincularParcelaRecorrente(array $payment): ?array
    {
        $paymentId = (string) ($payment['id'] ?? '');
        if ($paymentId === '') {
            return null;
        }

        // Idempotência: cobrança já associada a uma parcela.
        $existente = $this->parcelaService->findByAsaasPayment($paymentId);
        if ($existente !== null) {
            return $existente;
        }

        $acordo = $this->localizarAcordo($payment);
        if ($acordo === null) {
            return null;
        }

        $parcelas = $this->parcelaService->listarPorAcordo((int) $acordo['id']);
        $alvo = null;
        foreach ($parcelas as $parcela) {
            // Nunca associar cobrança recorrente à primeira parcela (avulsa).
            if ((int) ($parcela['numero_parcela'] ?? 0) < 2) {
                continue;
            }
            if ((int) ($parcela['id_acordo_pagamento'] ?? 0) !== (int) ($acordo['id'] ?? 0)) {
                continue;
            }
            if (trim((string) ($parcela['asaas_payment'] ?? '')) !== '') {
                continue;
            }
            if (in_array((string) ($parcela['status'] ?? ''), ['RECEBIDO', 'CONFIRMADO'], true)) {
                continue;
            }
            $alvo = $parcela;
            break;
        }

        if ($alvo === null) {
            return null;
        }

        $this->logDivergenciaVencimento($alvo, $payment);

        $data = [
            'asaas_payment' => $paymentId,
        ];

        $status = $this->normalizarStatus((string) ($payment['status'] ?? ''));
        if ($status !== null) {
            $data['status'] = $status;
        }

        if (isset($payment['invoiceUrl']) && is_string($payment['invoiceUrl']) && $payment['invoiceUrl'] !== '') {
            $data['invoice_url'] = $payment['invoiceUrl'];
        }

        $this->parcelaService->atualizarAsaasInfo((int) $alvo['id'], $data);

        return $this->parcelaService->buscar((int) $alvo['id']);
    }

    /**
     * Localiza o acordo de uma cobrança recorrente.
     *
     * @param array<string, mixed> $payment
     * @return array<string, mixed>|null
     */
    private function localizarAcordo(array $payment): ?array
    {
        $subscription = trim((string) ($payment['subscription'] ?? ''));

        if ($subscription !== '') {
            $acordo = $this->acordoService->findByAsaasSubscription($subscription);
            if ($acordo !== null) {
                return $acordo;
            }
            return null;
        }

        // Fallback (cobranças recorrentes antigas que não enviam subscription):
        // externalReference contém o id do acordo.
        $externalReference = (string) ($payment['externalReference'] ?? '');
        if ($externalReference === '' || !ctype_digit($externalReference)) {
            return null;
        }

        $acordo = $this->acordoService->findById((int) $externalReference);
        if ($acordo === null) {
            return null;
        }

        $assinatura = trim((string) ($acordo['asaas_subscription'] ?? ''));
        if ($assinatura === '') {
            return null;
        }

        return $acordo;
    }

    /**
     * Converte o status do Asaas para a convenção do sistema.
     */
    private function normalizarStatus(string $status): ?string
    {
        $map = [
            'PENDING' => 'PENDENTE',
            'RECEIVED' => 'RECEBIDO',
            'CONFIRMED' => 'CONFIRMADO',
            'OVERDUE' => 'CANCELADO',
            'REFUNDED' => 'ESTORNADO',
            'CANCELED' => 'CANCELADO',
            'RECEIVED_IN_CASH' => 'RECEBIDO',
        ];

        return $map[$status] ?? null;
    }

    /**
     * Investiga diferenças entre o vencimento contratado (parcela) e o
     * dueDate enviado pelo Asaas. Apenas registra em log; não altera datas.
     *
     * @param array<string, mixed> $parcela
     * @param array<string, mixed> $payment
     */
    private function logDivergenciaVencimento(array $parcela, array $payment): void
    {
        $vencimentoParcela = (string) ($parcela['data_vencimento'] ?? '');
        $dueDateAsaas = (string) ($payment['dueDate'] ?? '');

        if ($vencimentoParcela === '' || $dueDateAsaas === '') {
            return;
        }

        $tsParcela = strtotime($vencimentoParcela);
        $tsAsaas = strtotime($dueDateAsaas);
        if ($tsParcela === false || $tsAsaas === false) {
            return;
        }

        $diferenca = (int) round(((int) $tsParcela - (int) $tsAsaas) / 86400);

        if ($diferenca !== 0) {
            error_log(sprintf(
                '[RECORRENCIA] Divergência de vencimento na parcela #%d (acordo %d): parcela=%s asaas_dueDate=%s (diferença %+d dias). Não alterada.',
                (int) ($parcela['id'] ?? 0),
                (int) ($parcela['id_acordo_pagamento'] ?? 0),
                $vencimentoParcela,
                $dueDateAsaas,
                $diferenca
            ));
        }
    }
}
