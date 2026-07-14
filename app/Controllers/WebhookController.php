<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\AsaasWebhookService;

final class WebhookController
{
    private string $logDir;

    public function __construct(
        private readonly AsaasWebhookService $webhookService = new AsaasWebhookService(),
    ) {
        $this->logDir = dirname(__DIR__, 2) . '/storage/logs/asaas';
        if (!is_dir($this->logDir)) {
            mkdir($this->logDir, 0755, true);
        }
    }

    public function asaas(): void
    {
        header('Content-Type: application/json; charset=UTF-8');

        if (strtoupper($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'error' => 'Método não permitido'], JSON_UNESCAPED_UNICODE);
            return;
        }

        $rawInput = file_get_contents('php://input') ?: '';
        $context = $this->collectRequestContext();
        $this->log('INFO', 'Webhook recebido', $context, $rawInput);

        $payload = json_decode($rawInput, true);
        if (!is_array($payload)) {
            $this->log('ERROR', 'Payload inválido', $context, $rawInput);
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Payload inválido'], JSON_UNESCAPED_UNICODE);
            return;
        }

        $token = $this->extractToken($payload);

        $result = $this->webhookService->processar($token, $payload);
        $status = (int) ($result['httpStatus'] ?? 200);
        unset($result['httpStatus']);

        http_response_code($status);
        echo json_encode($result, JSON_UNESCAPED_UNICODE);
    }

    private function extractToken(array $payload): string
    {
        $headers = $this->collectHeaders();

        return (string) (
            $headers['ASAAS_ACCESS_TOKEN']
                ?? $headers['X_ASAAS_ACCESS_TOKEN']
                ?? $payload['accessToken']
                ?? ''
        );
    }

    private function collectHeaders(): array
    {
        $headers = [];
        foreach ($_SERVER as $key => $value) {
            if (str_starts_with($key, 'HTTP_')) {
                $headers[substr($key, 5)] = $value;
            }
        }

        return $headers;
    }

    private function collectRequestContext(): array
    {
        return [
            'method' => $_SERVER['REQUEST_METHOD'] ?? 'UNKNOWN',
            'uri' => $_SERVER['REQUEST_URI'] ?? 'UNKNOWN',
            'remote_addr' => $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'UNKNOWN',
            'content_type' => $_SERVER['CONTENT_TYPE'] ?? 'UNKNOWN',
        ];
    }

    private function log(string $level, string $message, array $context = [], string $rawInput = ''): void
    {
        $timestamp = date('Y-m-d H:i:s');
        $line = "[{$timestamp}] [{$level}] {$message}";

        if ($context !== []) {
            $line .= ' | context: ' . json_encode($context, JSON_UNESCAPED_UNICODE);
        }

        if ($rawInput !== '') {
            $line .= ' | payload: ' . $rawInput;
        }

        $line .= PHP_EOL;

        $filename = $this->logDir . '/webhook-' . date('Y-m-d') . '.log';
        file_put_contents($filename, $line, FILE_APPEND | LOCK_EX);
    }
}
