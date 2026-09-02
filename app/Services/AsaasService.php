<?php

declare(strict_types=1);

namespace App\Services;

final class AsaasService
{
    private string $baseUrl;
    private string $apiKey;
    private ?string $lastError = null;

    public function __construct()
    {
        $this->apiKey = trim((string) getenv('ASAAS_API_KEY'));
        if ($this->apiKey !== '' && !str_starts_with($this->apiKey, '$')) {
            $this->apiKey = '$' . $this->apiKey;
        }
        $sandbox = (string) getenv('ASAAS_SANDBOX');
        $this->baseUrl = $sandbox === 'true'
            ? 'https://api-sandbox.asaas.com/v3'
            : 'https://api.asaas.com/v3';
    }

    public function criarCliente(array $data): ?array
    {
        $cpf = preg_replace('/\D/', '', (string) ($data['cpf'] ?? ''));

        $existing = $this->buscarClientePorCpf($cpf);
        if ($existing) {
            $this->configurarNotificacoesCliente((string) ($existing['id'] ?? ''));
            return $existing;
        }

        $body = [
            'name' => (string) ($data['nome'] ?? ''),
            'cpfCnpj' => $cpf,
            'email' => (string) ($data['email'] ?? ''),
            'phone' => preg_replace('/\D/', '', (string) ($data['telefone'] ?? '')),
            'notificationDisabled' => false,
        ];

        $response = $this->request('POST', '/customers', $body);

        if ($response && isset($response['id'])) {
            $cliente = [
                'id' => $response['id'],
                'cpfCnpj' => $cpf,
            ];
            $this->configurarNotificacoesCliente((string) $response['id']);
            return $cliente;
        }

        return null;
    }

    public function criarCobranca(array $data): ?array
    {
        $body = [
            'customer' => (string) ($data['customer_id'] ?? ''),
            'billingType' => (string) ($data['billing_type'] ?? 'PIX'),
            'value' => (float) ($data['value'] ?? 0),
            'dueDate' => $data['due_date'] ?? date('Y-m-d', strtotime('+3 days')),
            'description' => mb_substr((string) ($data['description'] ?? ''), 0, 200),
            'externalReference' => (string) ($data['external_reference'] ?? ''),
            'postalService' => false,
        ];

        $response = $this->request('POST', '/payments', $body);

        if ($response && isset($response['id'])) {
            return [
                'id' => $response['id'],
                'status' => $response['status'] ?? 'PENDING',
                'invoiceUrl' => $response['invoiceUrl'] ?? '',
                'bankSlipUrl' => $response['bankSlipUrl'] ?? '',
                'paymentLink' => $response['paymentLink'] ?? '',
            ];
        }

        return null;
    }

    public function criarAssinatura(array $data): ?array
    {
        $body = [
            'customer' => (string) ($data['customer_id'] ?? ''),
            'billingType' => (string) ($data['billing_type'] ?? 'CREDIT_CARD'),
            'value' => (float) ($data['value'] ?? 0),
            'cycle' => (string) ($data['cycle'] ?? 'MONTHLY'),
            'nextDueDate' => $data['next_due_date'] ?? $this->proximoDiaDez(),
            'description' => mb_substr((string) ($data['description'] ?? ''), 0, 200),
            'externalReference' => (string) ($data['external_reference'] ?? ''),
        ];

        if (!empty($data['end_date'])) {
            $body['endDate'] = $data['end_date'];
        }

        $response = $this->request('POST', '/subscriptions', $body);

        if ($response && isset($response['id'])) {
            return [
                'id' => (string) $response['id'],
                'status' => $response['status'] ?? 'ACTIVE',
                'nextDueDate' => $response['nextDueDate'] ?? '',
                'endDate' => $response['endDate'] ?? '',
            ];
        }

        return null;
    }

    /**
     * Recupera a primeira cobrança criada pelo Asaas para uma assinatura.
     * Essa cobrança contém a invoiceUrl usada pelo aluno para informar o cartão.
     */
    public function primeiraCobrancaAssinatura(string $subscriptionId): ?array
    {
        $subscriptionId = trim($subscriptionId);
        if ($subscriptionId === '') {
            return null;
        }

        $response = $this->request(
            'GET',
            '/subscriptions/' . rawurlencode($subscriptionId) . '/payments?limit=1'
        );

        $cobrancas = is_array($response['data'] ?? null) ? $response['data'] : [];
        if (!isset($cobrancas[0]) || !is_array($cobrancas[0])) {
            return null;
        }

        $cobranca = $cobrancas[0];
        return [
            'id' => (string) ($cobranca['id'] ?? ''),
            'status' => (string) ($cobranca['status'] ?? 'PENDING'),
            'invoiceUrl' => (string) ($cobranca['invoiceUrl'] ?? ''),
            'bankSlipUrl' => (string) ($cobranca['bankSlipUrl'] ?? ''),
            'paymentLink' => (string) ($cobranca['paymentLink'] ?? ''),
        ];
    }

    public function criarLinkPagamento(array $data): ?array
    {
        $body = [
            'name' => mb_substr((string) ($data['name'] ?? 'Pagamento'), 0, 100),
            'description' => mb_substr((string) ($data['description'] ?? ''), 0, 500),
            'value' => (float) ($data['value'] ?? 0),
            'billingType' => (string) ($data['billing_type'] ?? 'CREDIT_CARD'),
            'chargeType' => (string) ($data['charge_type'] ?? 'RECURRENT'),
            'subscriptionCycle' => (string) ($data['subscription_cycle'] ?? 'MONTHLY'),
            'externalReference' => (string) ($data['external_reference'] ?? ''),
            'notificationEnabled' => true,
        ];

        if (!empty($data['end_date'])) {
            $body['endDate'] = (string) $data['end_date'];
        }

        $response = $this->request('POST', '/paymentLinks', $body);
        if (!$response || empty($response['url'])) {
            return null;
        }

        return [
            'id' => (string) ($response['id'] ?? ''),
            'url' => (string) $response['url'],
        ];
    }

    private function proximoDiaDez(): string
    {
        $mes = (new \DateTimeImmutable('today'))->modify('first day of next month');
        return $mes->setDate(
            (int) $mes->format('Y'),
            (int) $mes->format('m'),
            10
        )->format('Y-m-d');
    }

    public function listarCobrancas(array $filters = []): ?array
    {
        $query = array_filter([
            'limit' => isset($filters['limit']) ? max(1, min(100, (int) $filters['limit'])) : 20,
            'offset' => isset($filters['offset']) ? max(0, (int) $filters['offset']) : 0,
            'status' => isset($filters['status']) && $filters['status'] !== '' ? (string) $filters['status'] : null,
            'billingType' => isset($filters['billingType']) && $filters['billingType'] !== '' ? (string) $filters['billingType'] : null,
            'customer' => isset($filters['customer']) && $filters['customer'] !== '' ? (string) $filters['customer'] : null,
            'externalReference' => isset($filters['externalReference']) && $filters['externalReference'] !== '' ? (string) $filters['externalReference'] : null,
        ], static fn ($value) => $value !== null && $value !== '');

        $endpoint = '/payments';
        if ($query !== []) {
            $endpoint .= '?' . http_build_query($query, '', '&', PHP_QUERY_RFC3986);
        }

        $response = $this->request('GET', $endpoint);

        return $response && isset($response['data']) ? $response : null;
    }

    public function obterPixQrCode(string $paymentId): ?array
    {
        $response = $this->request('GET', '/payments/' . rawurlencode($paymentId) . '/pixQrCode');

        if (!$response) {
            return null;
        }

        return [
            'encodedImage' => $response['encodedImage'] ?? ($response['qrCode']['encodedImage'] ?? null),
            'payload' => $response['payload'] ?? ($response['qrCode']['payload'] ?? null),
            'expirationDate' => $response['expirationDate'] ?? null,
        ];
    }

    public function obterLinhaDigitavel(string $paymentId): ?array
    {
        $response = $this->request('GET', '/payments/' . rawurlencode($paymentId) . '/identificationField');

        if (!$response) {
            return null;
        }

        return [
            'identificationField' => $response['identificationField'] ?? null,
            'barCode' => $response['barCode'] ?? null,
        ];
    }

    /**
     * Mantém somente as notificações de cobrança solicitadas para o cliente:
     * 10 dias antes, no vencimento e um dia após o vencimento.
     */
    public function configurarNotificacoesCliente(string $customerId): bool
    {
        $customerId = trim($customerId);
        if ($customerId === '') {
            return false;
        }

        $response = $this->request('GET', '/customers/' . rawurlencode($customerId) . '/notifications');
        $notifications = is_array($response['data'] ?? null) ? $response['data'] : [];
        if ($notifications === []) {
            return false;
        }

        $desejadas = [
            'PAYMENT_DUEDATE_WARNING:10' => true,
            'PAYMENT_DUEDATE_WARNING:0' => true,
            'PAYMENT_OVERDUE:1' => true,
        ];
        $sucesso = true;

        foreach ($notifications as $notification) {
            $id = trim((string) ($notification['id'] ?? ''));
            if ($id === '') {
                continue;
            }

            $event = (string) ($notification['event'] ?? '');
            $offset = (int) ($notification['scheduleOffset'] ?? 0);
            $chave = $event . ':' . $offset;
            $ativa = isset($desejadas[$chave]);

            $ok = $this->request('PUT', '/notifications/' . rawurlencode($id), [
                'enabled' => $ativa,
                'emailEnabledForProvider' => false,
                'smsEnabledForProvider' => false,
                'emailEnabledForCustomer' => $ativa,
                'smsEnabledForCustomer' => $ativa,
                'phoneCallEnabledForCustomer' => false,
                'whatsappEnabledForCustomer' => false,
                'scheduleOffset' => $offset,
            ]) !== null;
            $sucesso = $sucesso && $ok;
        }

        return $sucesso;
    }

    public function getLastError(): ?string
    {
        return $this->lastError;
    }

    private function buscarClientePorCpf(string $cpf): ?array
    {
        $response = $this->request('GET', '/customers?cpfCnpj=' . $cpf);

        if ($response && isset($response['data'][0])) {
            $c = $response['data'][0];
            return [
                'id' => $c['id'],
                'cpfCnpj' => $cpf,
            ];
        }

        return null;
    }

    private function request(string $method, string $endpoint, array $body = []): ?array
    {
        $url = $this->baseUrl . $endpoint;
        $this->lastError = null;
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_HTTPHEADER => [
                'access_token: ' . $this->apiKey,
                'Content-Type: application/json',
                'User-Agent: IESB/1.0',
            ],
        ]);

        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
        } elseif ($method === 'PUT') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            $this->lastError = $error;
            error_log('[ASAAS] Erro na requisição: ' . $error);
            return null;
        }

        $data = json_decode($response, true);

        if ($httpCode >= 400) {
            $mensagemErro = $data['errors'][0]['description'] ?? $response;
            $this->lastError = sprintf('HTTP %d: %s', $httpCode, (string) $mensagemErro);
            error_log('[ASAAS] ' . $this->lastError);
            return null;
        }

        return $data;
    }
}
