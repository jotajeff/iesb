<?php

declare(strict_types=1);

namespace App\Services;

final class AsaasWebhookService
{
    private string $logDir;

    public function __construct(
        private readonly MatriculaService $matriculaService = new MatriculaService(),
    ) {
        $this->logDir = dirname(__DIR__, 2) . '/storage/logs/asaas';
        if (!is_dir($this->logDir)) {
            mkdir($this->logDir, 0755, true);
        }
    }

    public function processar(string $token, array $payload): array
    {
        $event = (string) ($payload['event'] ?? '');
        $payment = is_array($payload['payment'] ?? null) ? $payload['payment'] : [];
        $paymentId = (string) ($payment['id'] ?? '');

        $this->log(
            'INFO',
            $event !== '' ? "Evento recebido: {$event}" : 'Evento recebido sem nome',
            $paymentId,
            $payload,
        );

        if (!$this->validarToken($token)) {
            $this->log('ERROR', 'Token inválido', $paymentId);
            return [
                'success' => false,
                'error' => 'Token inválido',
                'httpStatus' => 401,
            ];
        }

        if ($event === '') {
            $this->log('WARN', 'Evento não informado', $paymentId);
            return [
                'success' => false,
                'error' => 'Evento não informado',
                'httpStatus' => 400,
            ];
        }

        if ($payment === []) {
            $this->log('ERROR', 'Dados de pagamento ausentes', $paymentId);
            return [
                'success' => false,
                'error' => 'Dados de pagamento ausentes',
                'httpStatus' => 400,
            ];
        }

        try {
            return match ($event) {
                'PAYMENT_RECEIVED' => $this->handlePaymentReceived($payment),
                'PAYMENT_UPDATED' => $this->handlePaymentUpdated($payment),
                default => $this->handleIgnoredEvent($event, $paymentId),
            };
        } catch (\Throwable $e) {
            $this->log('ERROR', 'Erro no processamento: ' . $e->getMessage(), $paymentId);
            return [
                'success' => false,
                'error' => 'Erro interno',
                'httpStatus' => 500,
            ];
        }
    }

    public function validarToken(string $token): bool
    {
        $expected = (string) getenv('ASAAS_WEBHOOK_TOKEN');

        return $expected !== '' && hash_equals($expected, $token);
    }

    private function handlePaymentReceived(array $payment): array
    {
        $paymentId = (string) ($payment['id'] ?? '');
        if ($paymentId === '') {
            $this->log('ERROR', 'Payment ID vazio');
            return [
                'success' => false,
                'error' => 'Payment ID inválido',
                'httpStatus' => 400,
            ];
        }

        $result = $this->matriculaService->confirmarPagamento($payment);
        $message = (string) ($result['message'] ?? 'Pagamento processado');

        $this->log('INFO', $message, $paymentId, $result);

        return [
            'success' => true,
            'httpStatus' => 200,
        ];
    }

    private function handlePaymentUpdated(array $payment): array
    {
        $paymentId = (string) ($payment['id'] ?? '');
        if ($paymentId === '') {
            $this->log('ERROR', 'Payment ID vazio');
            return [
                'success' => false,
                'error' => 'Payment ID inválido',
                'httpStatus' => 400,
            ];
        }

        $result = $this->matriculaService->atualizarPagamento($payment);
        $message = (string) ($result['message'] ?? 'Status atualizado');

        $this->log('INFO', $message, $paymentId, $result);

        return [
            'success' => true,
            'httpStatus' => 200,
        ];
    }

    private function handleIgnoredEvent(string $event, string $paymentId): array
    {
        $this->log('WARN', "Evento ignorado: {$event}", $paymentId);

        return [
            'success' => true,
            'httpStatus' => 200,
        ];
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function log(string $level, string $message, string $paymentId = '', array $payload = []): void
    {
        $timestamp = date('Y-m-d H:i:s');
        $line = "[{$timestamp}] [{$level}]";

        if ($paymentId !== '') {
            $line .= " [payment:{$paymentId}]";
        }

        $line .= " {$message}";

        if ($payload !== []) {
            $encoded = json_encode($payload, JSON_UNESCAPED_UNICODE);
            $line .= ' | payload: ' . ($encoded !== false ? $encoded : '[json_error]');
        }

        $line .= PHP_EOL;

        $filename = $this->logDir . '/webhook-' . date('Y-m-d') . '.log';
        file_put_contents($filename, $line, FILE_APPEND | LOCK_EX);
    }

}
