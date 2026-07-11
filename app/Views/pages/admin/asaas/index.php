<?php
$payments = $payments ?? [];
$pagination = $pagination ?? [];
$status = $status ?? '';
$billingType = $billingType ?? '';
$asaasError = $asaasError ?? null;
$totalCount = (int) ($totalCount ?? 0);
$inscricaoNomes = $inscricaoNomes ?? [];

$statusLabels = [
  'PENDING' => 'Pendente',
  'RECEIVED' => 'Recebido',
  'CONFIRMED' => 'Confirmado',
  'OVERDUE' => 'Vencido',
  'REFUNDED' => 'Estornado',
  'RECEIVED_IN_CASH' => 'Recebido em caixa',
  'REFUND_REQUESTED' => 'Estorno solicitado',
  'REFUND_IN_PROGRESS' => 'Estorno em andamento',
  'CHARGEBACK_REQUESTED' => 'Chargeback solicitado',
  'CHARGEBACK_DISPUTE' => 'Disputa de chargeback',
  'AWAITING_CHARGEBACK_REVERSAL' => 'Aguardando reversão',
  'DUNNING_REQUESTED' => 'Cobrança em cobrança',
  'DUNNING_RECEIVED' => 'Cobrança recebida',
  'AWAITING_RISK_ANALYSIS' => 'Análise de risco',
];

$billingTypeLabels = [
  'BOLETO' => 'Boleto',
  'PIX' => 'Pix',
  'CREDIT_CARD' => 'Cartão',
];

$statusClass = static function (string $value): string {
  return match ($value) {
    'RECEIVED', 'CONFIRMED', 'RECEIVED_IN_CASH' => 'bg-success',
    'OVERDUE', 'CHARGEBACK_REQUESTED', 'CHARGEBACK_DISPUTE' => 'bg-danger',
    'REFUNDED', 'REFUND_REQUESTED', 'REFUND_IN_PROGRESS', 'AWAITING_CHARGEBACK_REVERSAL' => 'bg-warning text-dark',
    default => 'bg-secondary',
  };
};

function asaasQuery(array $params): string
{
  return http_build_query(array_filter($params, static fn ($value) => $value !== '' && $value !== null), '', '&', PHP_QUERY_RFC3986);
}
?>

<section class="py-4">
  <div class="container-fluid">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
      <div>
        <h1 class="h3 mb-1">Cobranças Asaas</h1>
        <p class="text-muted mb-0">Listagem das cobranças criadas no sandbox e correlacionadas com as inscrições.</p>
      </div>
      <div class="d-flex gap-2">
        <a href="/admin/asaas" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-clockwise me-1"></i>Atualizar</a>
      </div>
    </div>

    <?php if ($asaasError): ?>
      <div class="alert alert-warning">
        <strong>Asaas:</strong> <?= htmlspecialchars((string) $asaasError, ENT_QUOTES, 'UTF-8') ?>
      </div>
    <?php endif; ?>

    <div class="card border-0 shadow-sm mb-4">
      <div class="card-body">
        <form method="get" action="/admin/asaas" class="row g-3 align-items-end">
          <div class="col-md-4">
            <label class="form-label">Status</label>
            <select name="status" class="form-select">
              <option value="">Todos</option>
              <?php foreach ($statusLabels as $value => $label): ?>
                <option value="<?= htmlspecialchars($value, ENT_QUOTES, 'UTF-8') ?>"<?= $status === $value ? ' selected' : '' ?>><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-4">
            <label class="form-label">Forma de pagamento</label>
            <select name="billingType" class="form-select">
              <option value="">Todas</option>
              <?php foreach ($billingTypeLabels as $value => $label): ?>
                <option value="<?= htmlspecialchars($value, ENT_QUOTES, 'UTF-8') ?>"<?= $billingType === $value ? ' selected' : '' ?>><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-4 d-flex gap-2">
            <button type="submit" class="btn btn-primary"><i class="bi bi-funnel me-1"></i>Filtrar</button>
            <a href="/admin/asaas" class="btn btn-outline-secondary">Limpar</a>
          </div>
        </form>
      </div>
    </div>

    <div class="row g-3 mb-4">
      <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
          <div class="card-body">
            <div class="text-muted small">Total listado</div>
            <div class="fs-3 fw-bold"><?= number_format($totalCount, 0, ',', '.') ?></div>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
          <div class="card-body">
            <div class="text-muted small">Página atual</div>
            <div class="fs-3 fw-bold"><?= number_format((int) ($pagination['current_page'] ?? 1), 0, ',', '.') ?></div>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
          <div class="card-body">
            <div class="text-muted small">Por página</div>
            <div class="fs-3 fw-bold"><?= number_format((int) ($pagination['per_page'] ?? 20), 0, ',', '.') ?></div>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
          <div class="card-body">
            <div class="text-muted small">Próxima página</div>
            <div class="fs-3 fw-bold"><?= !empty($pagination['next_page']) ? (int) $pagination['next_page'] : '-' ?></div>
          </div>
        </div>
      </div>
    </div>

    <div class="card border-0 shadow-sm">
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
              <tr>
                <th>Cobrança</th>
                <th>Inscrição</th>
                <th>Cliente</th>
                <th>Tipo</th>
                <th>Status</th>
                <th>Valor</th>
                <th>Vencimento</th>
                <th class="text-end">Ações</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($payments)): ?>
                <tr>
                  <td colspan="8" class="text-center py-5 text-muted">Nenhuma cobrança encontrada.</td>
                </tr>
              <?php else: ?>
                <?php foreach ($payments as $payment): ?>
                  <?php
                    $paymentId = (string) ($payment['id'] ?? '');
                    $externalReference = trim((string) ($payment['externalReference'] ?? ''));
                    $inscricaoId = ctype_digit($externalReference) ? (int) $externalReference : 0;
                    $inscricaoNome = $inscricaoId > 0 ? (string) ($inscricaoNomes[$inscricaoId] ?? '') : '';
                    $statusValue = (string) ($payment['status'] ?? '');
                    $billingValue = (string) ($payment['billingType'] ?? '');
                    $invoiceUrl = (string) ($payment['invoiceUrl'] ?? '');
                    $bankSlipUrl = (string) ($payment['bankSlipUrl'] ?? '');
                    $paymentLink = (string) ($payment['paymentLink'] ?? '');
                    $customerValue = (string) ($payment['customer'] ?? '');
                    $customerDisplay = $customerValue !== '' ? $customerValue : '-';
                    $description = (string) ($payment['description'] ?? '-');
                  ?>
                  <tr>
                    <td>
                      <div class="fw-semibold"><?= htmlspecialchars($paymentId !== '' ? $paymentId : '-', ENT_QUOTES, 'UTF-8') ?></div>
                      <div class="text-muted small"><?= htmlspecialchars($description, ENT_QUOTES, 'UTF-8') ?></div>
                    </td>
                    <td>
                      <?php if ($inscricaoId > 0): ?>
                        <a href="/admin/dbase?table=cursos_iesb_inscricao&view=detail&id=<?= $inscricaoId ?>" class="text-decoration-none">#<?= $inscricaoId ?></a>
                        <?php if ($inscricaoNome !== ''): ?>
                          <div class="text-muted small"><?= htmlspecialchars($inscricaoNome, ENT_QUOTES, 'UTF-8') ?></div>
                        <?php endif; ?>
                      <?php elseif ($externalReference !== ''): ?>
                        <span class="font-monospace small"><?= htmlspecialchars($externalReference, ENT_QUOTES, 'UTF-8') ?></span>
                      <?php else: ?>
                        <span class="text-muted">-</span>
                      <?php endif; ?>
                    </td>
                    <td class="small">
                      <div class="font-monospace"><?= htmlspecialchars($customerDisplay, ENT_QUOTES, 'UTF-8') ?></div>
                      <?php if ($externalReference !== ''): ?>
                        <div class="text-muted">Ref. interna: <?= htmlspecialchars($externalReference, ENT_QUOTES, 'UTF-8') ?></div>
                      <?php endif; ?>
                    </td>
                    <td>
                      <span class="badge bg-dark"><?= htmlspecialchars($billingTypeLabels[$billingValue] ?? $billingValue ?: '-', ENT_QUOTES, 'UTF-8') ?></span>
                    </td>
                    <td>
                      <span class="badge <?= $statusClass($statusValue) ?>"><?= htmlspecialchars($statusLabels[$statusValue] ?? $statusValue ?: '-', ENT_QUOTES, 'UTF-8') ?></span>
                    </td>
                    <td>R$ <?= number_format((float) ($payment['value'] ?? 0), 2, ',', '.') ?></td>
                    <td><?= htmlspecialchars((string) ($payment['dueDate'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                    <td class="text-end">
                      <div class="btn-group btn-group-sm" role="group">
                        <?php if ($invoiceUrl !== ''): ?>
                          <a href="<?= htmlspecialchars($invoiceUrl, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener noreferrer" class="btn btn-outline-primary">Fatura</a>
                        <?php endif; ?>
                        <?php if ($bankSlipUrl !== ''): ?>
                          <a href="<?= htmlspecialchars($bankSlipUrl, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener noreferrer" class="btn btn-outline-secondary">Boleto</a>
                        <?php endif; ?>
                        <?php if ($paymentLink !== ''): ?>
                          <a href="<?= htmlspecialchars($paymentLink, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener noreferrer" class="btn btn-outline-success">Link</a>
                        <?php endif; ?>
                      </div>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <?php
      $currentPage = (int) ($pagination['current_page'] ?? 1);
      $prevPage = $pagination['prev_page'] ?? null;
      $nextPage = $pagination['next_page'] ?? null;
    ?>

    <div class="d-flex justify-content-between align-items-center mt-4">
      <div class="text-muted small">
        Página <?= $currentPage ?>.
      </div>
      <div class="btn-group">
        <?php if ($prevPage): ?>
          <a class="btn btn-outline-secondary" href="/admin/asaas?<?= htmlspecialchars(asaasQuery(['status' => $status, 'billingType' => $billingType, 'page' => $prevPage]), ENT_QUOTES, 'UTF-8') ?>">Anterior</a>
        <?php else: ?>
          <button class="btn btn-outline-secondary" disabled>Anterior</button>
        <?php endif; ?>
        <?php if ($nextPage): ?>
          <a class="btn btn-outline-secondary" href="/admin/asaas?<?= htmlspecialchars(asaasQuery(['status' => $status, 'billingType' => $billingType, 'page' => $nextPage]), ENT_QUOTES, 'UTF-8') ?>">Próxima</a>
        <?php else: ?>
          <button class="btn btn-outline-secondary" disabled>Próxima</button>
        <?php endif; ?>
      </div>
    </div>
  </div>
</section>
