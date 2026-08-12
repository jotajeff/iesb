<?php
$parcelasView = is_array($parcelas ?? null) ? $parcelas : [];
$porCurso = [];
foreach ($parcelasView as $parcela) {
    $idCurso = (int) ($parcela['id_curso'] ?? 0);
    if (!isset($porCurso[$idCurso])) {
        $porCurso[$idCurso] = [
            'curso_nome' => (string) ($parcela['curso_nome'] ?? 'Curso'),
            'turma_nome' => (string) ($parcela['turma_nome'] ?? '-'),
            'total_parcelas' => (int) ($parcela['total_parcelas'] ?? 0),
            'parcelas' => [],
        ];
    }
    $porCurso[$idCurso]['parcelas'][] = $parcela;
}
?>

<section class="py-4" id="financeiro" style="margin-top: 20px;">
  <div class="container">
    <div class="bg-white border rounded-3 p-4 shadow-sm" style="background: var(--bg-card); border-color: var(--border-color);">
      <div class="d-flex align-items-center justify-content-between mb-4" data-aos="fade-up">
        <h4 class="mb-0"><i class="bi bi-cash-coin me-2"></i>Financeiro</h4>
        <span class="badge bg-primary fs-6"><?= count($porCurso) ?> curso(s)</span>
      </div>

      <?php if (empty($porCurso)): ?>
        <div class="text-center text-muted py-5" data-aos="fade-up">
          <i class="bi bi-wallet2" style="font-size: 3rem;"></i>
          <p class="mt-3 mb-0">Você ainda não possui parcelas de pagamento.</p>
        </div>
      <?php else: ?>
        <?php foreach ($porCurso as $grupo): ?>
          <?php $parcelasCurso = $grupo['parcelas']; ?>
          <?php
            $pago = 0;
            $recorrenciaAtiva = false;
            $proximaCobranca = null;
            foreach ($parcelasCurso as $p) {
                if (in_array((string) ($p['status'] ?? ''), ['RECEBIDO', 'CONFIRMADO'], true)) {
                    $pago++;
                    continue;
                }
                if (!empty($p['recorrencia_ativa']) && (int) ($p['numero_parcela'] ?? 0) >= 2 && $proximaCobranca === null) {
                    $recorrenciaAtiva = true;
                    $proximaCobranca = (string) ($p['data_vencimento'] ?? '');
                }
            }
          ?>
          <div class="p-4 rounded-4 shadow-sm mb-4" style="background: var(--bg-body-alt); border: 1px solid var(--border-color);" data-aos="fade-up">
            <div class="d-flex align-items-start justify-content-between flex-wrap gap-2 mb-3">
              <div>
                <h5 class="mb-1" style="color: var(--text-heading);"><?= htmlspecialchars($grupo['curso_nome'], ENT_QUOTES, 'UTF-8') ?></h5>
                <p class="mb-0 small" style="color: var(--text-secondary);">
                  <i class="bi bi-people me-1"></i>Turma: <?= htmlspecialchars($grupo['turma_nome'], ENT_QUOTES, 'UTF-8') ?>
                </p>
              </div>
              <span class="badge bg-success"><?= $pago ?> de <?= count($parcelasCurso) ?> paga(s)</span>
            </div>

            <?php if ($recorrenciaAtiva): ?>
              <div class="alert alert-info d-flex align-items-center gap-2 py-2 px-3 small mb-3">
                <i class="bi bi-arrow-repeat fs-5"></i>
                <div>
                  <strong>Cobrança automática ativa</strong>
                  <?php if ($proximaCobranca !== null && $proximaCobranca !== ''): ?>
                    — Próxima cobrança: <?= (new \DateTime($proximaCobranca))->format('d/m/Y') ?> no cartão de crédito.
                  <?php else: ?>
                    — As parcelas restantes serão cobradas no cartão de crédito.
                  <?php endif; ?>
                </div>
              </div>
            <?php endif; ?>

            <div class="table-responsive">
              <table class="table table-striped table-sm align-middle mb-0">
                <thead>
                  <tr>
                    <th>Parcela</th>
                    <th>Vencimento</th>
                    <th>Valor</th>
                    <th>Situação</th>
                    <th>Ações</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($parcelasCurso as $parcela): ?>
                    <?php
                      $numero = (int) ($parcela['numero_parcela'] ?? 0);
                      $status = (string) ($parcela['status'] ?? 'PENDENTE');
                      $vencimento = (string) ($parcela['data_vencimento'] ?? '');
                      $valor = (float) ($parcela['valor'] ?? 0);
                      $parcelaId = (int) ($parcela['id'] ?? 0);

                      $statusLabel = match ($status) {
                          'RECEBIDO', 'CONFIRMADO' => 'Pago',
                          'CANCELADO' => 'Cancelado',
                          'ESTORNADO' => 'Estornado',
                          default => 'Pendente',
                      };
                      $statusClass = match ($status) {
                          'RECEBIDO', 'CONFIRMADO' => 'success',
                          'CANCELADO', 'ESTORNADO' => 'danger',
                          default => 'warning',
                      };
                      $estaPaga = in_array($status, ['RECEBIDO', 'CONFIRMADO'], true);
                    ?>
                    <tr>
                      <td>
                        <?php if ($numero > 0): ?>
                          <span class="fw-semibold"><?= $numero ?>ª</span>
                          <?= $numero === 1 ? '<span class="badge bg-light text-dark border ms-1">Entrada</span>' : '' ?>
                        <?php else: ?>
                          -
                        <?php endif; ?>
                      </td>
                      <td><?= htmlspecialchars(
                        $vencimento !== ''
                          ? (new \DateTime($vencimento))->format('d/m/Y')
                          : '-',
                        ENT_QUOTES,
                        'UTF-8'
                      ) ?></td>
                      <td>R$ <?= number_format($valor, 2, ',', '.') ?></td>
                      <td>
                        <span class="badge bg-<?= $statusClass ?>"><?= $statusLabel ?></span>
                      </td>
                      <td>
                        <?php if (!$estaPaga && $parcelaId > 0): ?>
                          <?php if (!empty($parcela['recorrencia_ativa']) && $numero >= 2): ?>
                            <span class="badge bg-info text-dark" title="Esta parcela será cobrada automaticamente no cartão de crédito">
                              <i class="bi bi-arrow-repeat me-1"></i>Cobrança automática
                            </span>
                          <?php else: ?>
                            <a class="btn btn-success btn-sm" href="/financeiro/parcela/<?= $parcelaId ?>">
                              <i class="bi bi-credit-card me-1"></i>Pagar
                            </a>
                          <?php endif; ?>
                        <?php else: ?>
                          <span class="text-muted">-</span>
                        <?php endif; ?>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>
</section>
