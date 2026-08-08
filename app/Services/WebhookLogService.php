<?php

declare(strict_types=1);

namespace App\Services;

final class WebhookLogService
{
    private string $logDir;

    public function __construct()
    {
        $this->logDir = dirname(__DIR__, 2) . '/storage/logs/asaas';
    }

    /**
     * Lista os arquivos de log do webhook em ordem decrescente (mais recente primeiro).
     *
     * @return array<int, array<string, mixed>>
     */
    public function listarArquivos(): array
    {
        if (!is_dir($this->logDir)) {
            return [];
        }

        $files = glob($this->logDir . '/webhook-*.log') ?: [];
        $result = [];

        foreach ($files as $path) {
            $basename = basename($path);
            if (!preg_match('/^webhook-\d{4}-\d{2}-\d{2}\.log$/', $basename)) {
                continue;
            }

            $size = filesize($path);
            $mtime = filemtime($path);
            $lines = $this->contarLinhas($path);

            $result[] = [
                'arquivo' => $basename,
                'data' => str_replace(['webhook-', '.log'], '', $basename),
                'tamanho' => $size !== false ? (int) $size : 0,
                'tamanho_formatado' => $this->formatarBytes((int) $size),
                'modificado_em' => $mtime !== false ? (int) $mtime : 0,
                'modificado_formatado' => $mtime !== false ? date('d/m/Y H:i:s', $mtime) : '-',
                'linhas' => $lines,
            ];
        }

        usort($result, static fn (array $a, array $b): int => strcmp((string) $b['arquivo'], (string) $a['arquivo']));

        return $result;
    }

    public function lerArquivo(string $arquivo): ?string
    {
        if (!preg_match('/^webhook-\d{4}-\d{2}-\d{2}\.log$/', $arquivo)) {
            return null;
        }

        $path = $this->logDir . '/' . $arquivo;
        if (!is_file($path) || !is_readable($path)) {
            return null;
        }

        $content = file_get_contents($path);

        return $content === false ? null : $content;
    }

    public function existe(string $arquivo): bool
    {
        if (!preg_match('/^webhook-\d{4}-\d{2}-\d{2}\.log$/', $arquivo)) {
            return false;
        }

        return is_file($this->logDir . '/' . $arquivo);
    }

    /**
     * Parseia as linhas do log em eventos estruturados.
     *
     * @return array<int, array<string, mixed>>
     */
    public function parsear(string $content): array
    {
        if ($content === '') {
            return [];
        }

        $lines = preg_split('/\r?\n/', $content) ?: [];
        $events = [];

        foreach ($lines as $line) {
            if (trim($line) === '') {
                continue;
            }

            $event = $this->parseLinha($line);
            if ($event !== null) {
                $events[] = $event;
            }
        }

        return $events;
    }

    /**
     * @return array<string, mixed>|null
     */
    private function parseLinha(string $line): ?array
    {
        if (!preg_match('/^\[(.+?)\] \[(INFO|ERROR|WARN)\](?: \[payment:(.+?)\])? (.*)$/', $line, $m)) {
            return null;
        }

        $timestamp = trim($m[1]);
        $level = $m[2];
        $payment = trim($m[3]);
        $rest = trim($m[4]);

        $message = $rest;
        $context = [];
        $payload = [];

        if (preg_match('/^(.*?) \| context: (.*?)(?: \| payload: (.*))?$/', $rest, $parts)) {
            $message = trim($parts[1]);
            $context = $this->decodeJson($parts[2]);
            $payload = isset($parts[3]) ? $this->decodeJson($parts[3]) : [];
        } elseif (preg_match('/^(.*?) \| payload: (.*)$/', $rest, $parts)) {
            $message = trim($parts[1]);
            $payload = $this->decodeJson($parts[2]);
        }

        return [
            'timestamp' => $timestamp,
            'timestamp_formatado' => $timestamp !== '' ? date('d/m/Y H:i:s', strtotime($timestamp)) : '-',
            'level' => $level,
            'payment' => $payment,
            'message' => $message,
            'context' => $context,
            'payload' => $payload,
            'dados' => $this->extrairDadosRelevantes($payload),
        ];
    }

    /**
     * Extrai os dados mais relevantes do payload para conferência.
     *
     * @return array<string, mixed>
     */
    private function extrairDadosRelevantes(array $payload): array
    {
        $dados = [];

        if (isset($payload['event'])) {
            $dados['evento'] = (string) $payload['event'];
        }
        if (isset($payload['id'])) {
            $dados['id_evento'] = (string) $payload['id'];
        }
        if (isset($payload['dateCreated'])) {
            $dados['data_criacao'] = (string) $payload['dateCreated'];
        }

        if (isset($payload['message'])) {
            $dados['mensagem'] = (string) $payload['message'];
        }
        if (isset($payload['inscricaoId'])) {
            $dados['inscricao_id'] = (int) $payload['inscricaoId'];
        }
        if (isset($payload['alunoId'])) {
            $dados['aluno_id'] = (int) $payload['alunoId'];
        }
        if (isset($payload['matriculaId'])) {
            $dados['matricula_id'] = (int) $payload['matriculaId'];
        }
        if (isset($payload['numeroMatricula'])) {
            $dados['numero_matricula'] = (string) $payload['numeroMatricula'];
        }
        if (isset($payload['status'])) {
            $dados['status'] = (string) $payload['status'];
        }

        $payment = is_array($payload['payment'] ?? null) ? $payload['payment'] : [];
        if ($payment !== []) {
            if (isset($payment['id'])) {
                $dados['payment_id'] = (string) $payment['id'];
            }
            if (isset($payment['status'])) {
                $dados['status'] = (string) $payment['status'];
            }
            if (isset($payment['billingType'])) {
                $dados['billing_type'] = (string) $payment['billingType'];
            }
            if (isset($payment['value'])) {
                $dados['valor'] = (float) $payment['value'];
            }
            if (isset($payment['description'])) {
                $dados['descricao'] = (string) $payment['description'];
            }
            if (isset($payment['externalReference'])) {
                $dados['external_reference'] = (string) $payment['externalReference'];
            }
            if (isset($payment['customer'])) {
                $dados['customer'] = (string) $payment['customer'];
            }
            if (isset($payment['invoiceUrl'])) {
                $dados['invoice_url'] = (string) $payment['invoiceUrl'];
            }
            if (isset($payment['dueDate'])) {
                $dados['vencimento'] = (string) $payment['dueDate'];
            }
            if (isset($payment['paymentDate'])) {
                $dados['data_pagamento'] = (string) $payment['paymentDate'];
            }
        }

        return $dados;
    }

    /**
     * @return array<string, mixed>
     */
    private function decodeJson(string $json): array
    {
        $decoded = json_decode($json, true);

        return is_array($decoded) ? $decoded : [];
    }

    private function contarLinhas(string $path): int
    {
        $handle = @fopen($path, 'rb');
        if ($handle === false) {
            return 0;
        }

        $lines = 0;
        while (!feof($handle)) {
            $buffer = fgets($handle);
            if ($buffer !== false && trim($buffer) !== '') {
                $lines++;
            }
        }
        fclose($handle);

        return $lines;
    }

    private function formatarBytes(int $bytes): string
    {
        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 1, ',', '.') . ' MB';
        }
        if ($bytes >= 1024) {
            return number_format($bytes / 1024, 1, ',', '.') . ' KB';
        }

        return $bytes . ' B';
    }
}
