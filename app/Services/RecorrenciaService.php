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
            $dataInicio = $this->calcularVencimentoMensal($dataBase, 1);
        }
        if ($dataFim === '') {
            $dataFim = $this->calcularVencimentoMensal($dataBase, $totalParcelas - 1);
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

        $cobranca = $this->asaas->primeiraCobrancaAssinatura((string) ($resultado['id'] ?? ''));
        $invoiceUrl = (string) ($cobranca['invoiceUrl'] ?? $cobranca['paymentLink'] ?? '');
        $bankSlipUrl = (string) ($cobranca['bankSlipUrl'] ?? '');

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
            'invoiceUrl' => $invoiceUrl,
            'bankSlipUrl' => $bankSlipUrl,
            'paymentId' => (string) ($cobranca['id'] ?? ''),
        ];
    }

    private function calcularVencimentoMensal(string $dataBase, int $meses): string
    {
        try {
            $base = new \DateTimeImmutable($dataBase);
        } catch (\Throwable) {
            $base = new \DateTimeImmutable('today');
        }

        $mes = $base->modify('first day of +' . max(0, $meses) . ' month');
        return $mes->setDate(
            (int) $mes->format('Y'),
            (int) $mes->format('m'),
            10
        )->format('Y-m-d');
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

        $subscription = trim((string) ($payment['subscription'] ?? ''));

        if ($subscription !== '') {
            // 1) Acordo de pagamento: asaas_subscription em acordo_pagamento.
            $acordo = $this->acordoService->findByAsaasSubscription($subscription);
            if ($acordo !== null) {
                return $this->vincularParcelaPorAcordo((int) $acordo['id'], $payment);
            }

            // 2) Inscrição direta: asaas_subscription na curso_parcela (parcela 1).
            $parcelaOrigem = $this->parcelaService->findByAsaasSubscription($subscription);
            if ($parcelaOrigem !== null) {
                return $this->vincularParcelaPorInscricao($parcelaOrigem, $payment);
            }

            // Link de pagamento recorrente: a assinatura só nasce depois que
            // o aluno informa o cartão, portanto ainda não existe no acordo.
            $externalReference = (string) ($payment['externalReference'] ?? '');
            if ($externalReference !== '' && ctype_digit($externalReference)) {
                $acordo = $this->acordoService->findById((int) $externalReference);
                if ($acordo !== null) {
                    $this->acordoService->atualizarRecorrencia((int) $acordo['id'], [
                        'asaas_subscription' => $subscription,
                        'status_recorrencia' => 'ATIVA',
                    ]);
                    return $this->vincularParcelaPorAcordo((int) $acordo['id'], $payment);
                }
            }

            return null;
        }

        // Fallback legado (cobranças recorrentes antigas sem subscription):
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

        return $this->vincularParcelaPorAcordo((int) $acordo['id'], $payment);
    }

    /**
     * Associa a cobrança à próxima parcela pendente de um acordo (numero >= 2).
     *
     * @param array<string, mixed> $payment
     * @return array<string, mixed>|null
     */
    private function vincularParcelaPorAcordo(int $idAcordo, array $payment): ?array
    {
        $parcelas = $this->parcelaService->listarPorAcordo($idAcordo);
        $alvo = null;

        foreach ($parcelas as $parcela) {
            // Nunca associar cobrança recorrente à primeira parcela (avulsa).
            if ((int) ($parcela['numero_parcela'] ?? 0) < 2) {
                continue;
            }
            if ((int) ($parcela['id_acordo_pagamento'] ?? 0) !== $idAcordo) {
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

        $this->associarCobranca($alvo, $payment);

        return $this->parcelaService->buscar((int) $alvo['id']);
    }

    /**
     * Associa a cobrança à próxima parcela pendente de uma inscrição direta
     * (mesma inscrição: id_aluno + id_pagamento + id_curso), numero >= 2.
     *
     * @param array<string, mixed> $parcelaOrigem Parcela 1 (dona da assinatura).
     * @param array<string, mixed> $payment
     * @return array<string, mixed>|null
     */
    private function vincularParcelaPorInscricao(array $parcelaOrigem, array $payment): ?array
    {
        $parcelas = $this->parcelaService->listarPorInscricao(
            (int) ($parcelaOrigem['id_aluno'] ?? 0),
            (int) ($parcelaOrigem['id_pagamento'] ?? 0),
            (int) ($parcelaOrigem['id_curso'] ?? 0)
        );

        $alvo = null;
        foreach ($parcelas as $parcela) {
            if ((int) ($parcela['numero_parcela'] ?? 0) < 2) {
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

        $this->associarCobranca($alvo, $payment);

        return $this->parcelaService->buscar((int) $alvo['id']);
    }

    /**
     * Grava asaas_payment (e status/invoice_url quando disponíveis) na parcela.
     *
     * @param array<string, mixed> $parcela
     * @param array<string, mixed> $payment
     */
    private function associarCobranca(array $parcela, array $payment): void
    {
        $this->logDivergenciaVencimento($parcela, $payment);

        $data = [
            'asaas_payment' => (string) ($payment['id'] ?? ''),
        ];

        $status = $this->normalizarStatus((string) ($payment['status'] ?? ''));
        if ($status !== null) {
            $data['status'] = $status;
        }

        if (isset($payment['invoiceUrl']) && is_string($payment['invoiceUrl']) && $payment['invoiceUrl'] !== '') {
            $data['invoice_url'] = $payment['invoiceUrl'];
        }

        $this->parcelaService->atualizarAsaasInfo((int) $parcela['id'], $data);
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
