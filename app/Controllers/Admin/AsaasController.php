<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Database;
use App\Services\AuthService;
use App\Services\AsaasService;
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
