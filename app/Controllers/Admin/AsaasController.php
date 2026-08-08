<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Database;
use App\Services\AuthService;
use App\Services\AsaasService;
use App\Services\WebhookLogService;
use App\Support\Session;
use PDO;

final class AsaasController extends Controller
{
    public function index(): void
    {
        if (!$this->isAdmin()) {
            Session::setFlash('flash', 'Faça login como admin para acessar as cobranças do Asaas.');
            $this->redirect('/admin/login');
        }

        $page = max(1, (int) ($_GET['page'] ?? 1));
        $status = trim((string) ($_GET['status'] ?? ''));
        $status = $this->normalizeStatus($status);
        $billingType = trim((string) ($_GET['billingType'] ?? ''));
        $billingType = $this->normalizeBillingType($billingType);

        $service = new AsaasService();
        $result = $service->listarCobrancas([
            'limit' => 20,
            'offset' => ($page - 1) * 20,
            'status' => $status,
            'billingType' => $billingType,
        ]);

        $payments = $result['data'] ?? [];
        $totalCount = (int) ($result['totalCount'] ?? count($payments));
        $hasMore = (bool) ($result['hasMore'] ?? false);
        $inscricaoNomes = $this->buscarNomesInscricao($payments);

        $pagination = [
            'current_page' => $page,
            'per_page' => 20,
            'total' => $totalCount,
            'has_more' => $hasMore,
            'next_page' => $hasMore ? $page + 1 : null,
            'prev_page' => $page > 1 ? $page - 1 : null,
        ];

        $this->render('pages/admin/asaas/index', [
            'title' => 'Cobranças Asaas',
            'currentRoute' => '/admin/asaas',
            'payments' => $payments,
            'pagination' => $pagination,
            'status' => $status,
            'billingType' => $billingType,
            'asaasError' => $service->getLastError(),
            'totalCount' => $totalCount,
            'inscricaoNomes' => $inscricaoNomes,
        ], 'admin');
    }

    public function webhookTest(): void
    {
        if (!$this->isAdmin()) {
            Session::setFlash('flash', 'Faça login como admin para acessar as cobranças do Asaas.');
            $this->redirect('/admin/login');
        }

        $appUrl = rtrim((string) (getenv('APP_URL') ?: 'https://inteligenciaeducacionalsouzabrazil.com'), '/');
        $webhookUrl = $appUrl . '/asaas-webhook.php';
        $token = trim((string) getenv('ASAAS_WEBHOOK_TOKEN'));

        $result = [
            'appUrl' => $appUrl,
            'webhookUrl' => $webhookUrl,
            'tokenDefinido' => $token !== '',
            'tokenTamanho' => strlen($token),
            'tokenValido' => null,
            'curlOk' => false,
            'httpCode' => null,
            'resposta' => null,
            'erro' => null,
        ];

        if ($token !== '') {
            $result['tokenValido'] = strlen($token) >= 32 && strlen($token) <= 255 && !str_contains($token, ' ');
        }

        $payload = [
            'event' => 'PAYMENT_RECEIVED',
            'payment' => [
                'id' => 'pay_webhook_test_' . date('YmdHis'),
            ],
        ];

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $webhookUrl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'asaas-access-token: ' . $token,
            ],
        ]);

        $response = curl_exec($ch);
        $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            $result['erro'] = $error;
        } else {
            $result['curlOk'] = true;
            $result['httpCode'] = $httpCode;
            $result['resposta'] = is_string($response) ? $response : null;
        }

        $this->render('pages/admin/asaas/webhook-test', [
            'title' => 'Teste de Webhook Asaas',
            'currentRoute' => '/admin/asaas/webhook-test',
            'result' => $result,
        ], 'admin');
    }

    public function webhookLogs(): void
    {
        if (!$this->isAdmin()) {
            Session::setFlash('flash', 'Faça login como admin para acessar os logs do webhook.');
            $this->redirect('/admin/login');
        }

        $service = new WebhookLogService();
        $arquivos = $service->listarArquivos();

        $this->render('pages/admin/asaas/webhook-logs', [
            'title' => 'Logs do Webhook Asaas',
            'currentRoute' => '/admin/asaas/webhook-logs',
            'arquivos' => $arquivos,
        ], 'admin');
    }

    public function webhookLogDetalhe(): void
    {
        if (!$this->isAdmin()) {
            Session::setFlash('flash', 'Faça login como admin para acessar os logs do webhook.');
            $this->redirect('/admin/login');
        }

        $arquivo = trim((string) ($_GET['arquivo'] ?? ''));
        $service = new WebhookLogService();

        if ($arquivo === '' || !$service->existe($arquivo)) {
            Session::setFlash('flash', 'Arquivo de log não encontrado.');
            $this->redirect('/admin/asaas/webhook-logs');
        }

        $conteudo = $service->lerArquivo($arquivo);
        if ($conteudo === null) {
            Session::setFlash('flash', 'Não foi possível ler o arquivo de log.');
            $this->redirect('/admin/asaas/webhook-logs');
        }

        $eventos = $service->parsear($conteudo);

        $totais = ['INFO' => 0, 'ERROR' => 0, 'WARN' => 0];
        $agrupados = [];

        foreach ($eventos as $evento) {
            $level = (string) ($evento['level'] ?? 'INFO');
            if (isset($totais[$level])) {
                $totais[$level]++;
            }

            $chave = (string) ($evento['payment'] ?? '');
            if ($chave === '') {
                $chave = '_geral';
            }

            if (!isset($agrupados[$chave])) {
                $agrupados[$chave] = [
                    'payment' => $evento['payment'] ?? '',
                    'eventos' => [],
                ];
            }

            $agrupados[$chave]['eventos'][] = $evento;
        }

        krsort($agrupados);

        $this->render('pages/admin/asaas/webhook-log-detalhe', [
            'title' => 'Log do Webhook — ' . $arquivo,
            'currentRoute' => '/admin/asaas/webhook-log-detalhe',
            'arquivo' => $arquivo,
            'eventos' => $eventos,
            'totais' => $totais,
            'agrupados' => $agrupados,
        ], 'admin');
    }

    private function normalizeStatus(string $status): string
    {
        $allowed = [
            'PENDING',
            'RECEIVED',
            'CONFIRMED',
            'OVERDUE',
            'REFUNDED',
            'RECEIVED_IN_CASH',
            'REFUND_REQUESTED',
            'REFUND_IN_PROGRESS',
            'CHARGEBACK_REQUESTED',
            'CHARGEBACK_DISPUTE',
            'AWAITING_CHARGEBACK_REVERSAL',
            'DUNNING_REQUESTED',
            'DUNNING_RECEIVED',
            'AWAITING_RISK_ANALYSIS',
        ];

        return in_array($status, $allowed, true) ? $status : '';
    }

    private function normalizeBillingType(string $billingType): string
    {
        $allowed = ['BOLETO', 'PIX', 'CREDIT_CARD'];

        return in_array($billingType, $allowed, true) ? $billingType : '';
    }

    private function isAdmin(): bool
    {
        return (new AuthService())->isAdmin();
    }

    /**
     * @param array<int, array<string, mixed>> $payments
     * @return array<int, string>
     */
    private function buscarNomesInscricao(array $payments): array
    {
        $ids = [];

        foreach ($payments as $payment) {
            $externalReference = trim((string) ($payment['externalReference'] ?? ''));
            if ($externalReference !== '' && ctype_digit($externalReference)) {
                $ids[] = (int) $externalReference;
            }
        }

        $ids = array_values(array_unique(array_filter($ids, static fn (int $id): bool => $id > 0)));
        if ($ids === []) {
            return [];
        }

        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $sql = 'SELECT id, nome FROM cursos_inscricao WHERE id IN (' . $placeholders . ')';
        $stmt = $pdo->prepare($sql);

        foreach ($ids as $index => $id) {
            $stmt->bindValue($index + 1, $id, PDO::PARAM_INT);
        }

        $stmt->execute();

        $map = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $map[(int) ($row['id'] ?? 0)] = (string) ($row['nome'] ?? '');
        }

        return $map;
    }
}
