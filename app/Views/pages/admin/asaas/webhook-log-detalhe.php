<?php
$arquivo = $arquivo ?? '';
$eventos = $eventos ?? [];
$totais = $totais ?? ['INFO' => 0, 'ERROR' => 0, 'WARN' => 0];
$agrupados = $agrupados ?? [];

$levelBadge = static function (string $level): string {
  return match ($level) {
    'ERROR' => 'bg-danger',
    'WARN' => 'bg-warning text-dark',
    default => 'bg-info text-dark',
  };
};
?>
<section class="py-4">
  <div class="container-fluid">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
      <div>
        <h1 class="h3 mb-1 font-monospace"><?= htmlspecialchars($arquivo, ENT_QUOTES, 'UTF-8') ?></h1>
        <p class="text-muted mb-0">Eventos recebidos do webhook neste arquivo, agrupados por cobrança (payment).</p>
      </div>
      <div class="d-flex gap-2">
        <a href="/admin/asaas/webhook-logs" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Voltar aos logs</a>
        <a href="/admin/asaas" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Cobranças</a>
      </div>
    </div>

    <div class="row g-3 mb-4">
      <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
          <div class="card-body">
            <div class="text-muted small">Total de eventos</div>
            <div class="fs-3 fw-bold"><?= count($eventos) ?></div>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
          <div class="card-body">
            <div class="text-muted small">INFO</div>
            <div class="fs-3 fw-bold"><?= (int) ($totais['INFO'] ?? 0) ?></div>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
          <div class="card-body">
            <div class="text-muted small">Erros (ERROR)</div>
            <div class="fs-3 fw-bold text-danger"><?= (int) ($totais['ERROR'] ?? 0) ?></div>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
          <div class="card-body">
            <div class="text-muted small">Avisos (WARN)</div>
            <div class="fs-3 fw-bold text-warning"><?= (int) ($totais['WARN'] ?? 0) ?></div>
          </div>
        </div>
      </div>
    </div>

    <?php if (empty($agrupados)): ?>
      <div class="card border-0 shadow-sm">
        <div class="card-body text-center py-5">
          <div class="mb-2 text-muted"><i class="bi bi-journal-x" style="font-size:3rem;"></i></div>
          <h5 class="mb-0">Nenhum evento neste arquivo</h5>
        </div>
      </div>
    <?php else: ?>
      <?php foreach ($agrupados as $grupo): ?>
        <?php
          $payment = (string) ($grupo['payment'] ?? '');
          $eventosGrupo = $grupo['eventos'] ?? [];
          $errosGrupo = array_filter($eventosGrupo, static fn (array $e): bool => ($e['level'] ?? '') === 'ERROR');
          $temErro = count($errosGrupo) > 0;
          $primeiroEvento = $eventosGrupo[0] ?? [];
        ?>
        <div class="card border-0 shadow-sm mb-4<?= $temErro ? ' border-start border-danger border-4' : '' ?>">
          <div class="card-header bg-white d-flex flex-wrap align-items-center justify-content-between gap-2 py-3">
            <div>
              <?php if ($payment !== ''): ?>
                <h5 class="mb-0 font-monospace"><i class="bi bi-credit-card me-2 text-primary"></i><?= htmlspecialchars($payment, ENT_QUOTES, 'UTF-8') ?></h5>
              <?php else: ?>
                <h5 class="mb-0"><i class="bi bi-globe me-2 text-muted"></i>Eventos gerais (sem cobrança associada)</h5>
              <?php endif; ?>
              <small class="text-muted"><?= count($eventosGrupo) ?> evento(s) nesta cobrança</small>
            </div>
            <?php if ($temErro): ?>
              <span class="badge bg-danger"><i class="bi bi-exclamation-triangle me-1"></i>Possui erros</span>
            <?php else: ?>
              <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Sem erros</span>
            <?php endif; ?>
          </div>
          <div class="card-body p-0">
            <div class="list-group list-group-flush">
              <?php foreach ($eventosGrupo as $evento): ?>
                <?php
                  $level = (string) ($evento['level'] ?? 'INFO');
                  $dados = is_array($evento['dados'] ?? null) ? $evento['dados'] : [];
                  $payload = is_array($evento['payload'] ?? null) ? $evento['payload'] : [];
                ?>
                <div class="list-group-item px-4 py-3">
                  <div class="d-flex flex-wrap align-items-start gap-2">
                    <span class="badge <?= $levelBadge($level) ?> mt-1"><?= htmlspecialchars($level, ENT_QUOTES, 'UTF-8') ?></span>
                    <div class="flex-grow-1">
                      <div class="small text-muted mb-1"><?= htmlspecialchars((string) ($evento['timestamp_formatado'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></div>
                      <div class="fw-semibold"><?= htmlspecialchars((string) ($evento['message'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></div>

                      <?php if ($dados !== []): ?>
                        <div class="row g-2 mt-2">
                          <?php foreach ($dados as $chave => $valor): ?>
                            <?php
                              $rotulos = [
                                'evento' => 'Evento',
                                'id_evento' => 'ID do evento',
                                'data_criacao' => 'Criado em',
                                'mensagem' => 'Mensagem',
                                'inscricao_id' => 'Inscrição',
                                'aluno_id' => 'Aluno',
                                'matricula_id' => 'Matrícula',
                                'numero_matricula' => 'Nº matrícula',
                                'status' => 'Status',
                                'payment_id' => 'Payment',
                                'billing_type' => 'Tipo',
                                'valor' => 'Valor',
                                'descricao' => 'Descrição',
                                'external_reference' => 'Ref. externa',
                                'customer' => 'Cliente',
                                'invoice_url' => 'Fatura',
                                'vencimento' => 'Vencimento',
                                'data_pagamento' => 'Data pagamento',
                              ];
                              $rotulo = $rotulos[$chave] ?? ucfirst(str_replace('_', ' ', $chave));
                            ?>
                            <div class="col-md-4">
                              <div class="p-2 rounded-3 bg-light border">
                                <div class="text-muted small text-uppercase"><?= htmlspecialchars($rotulo, ENT_QUOTES, 'UTF-8') ?></div>
                                <?php if ($chave === 'invoice_url' && $valor !== ''): ?>
                                  <a href="<?= htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener noreferrer" class="fw-semibold small text-break">Abrir fatura</a>
                                <?php elseif ($chave === 'valor'): ?>
                                  <div class="fw-semibold">R$ <?= number_format((float) $valor, 2, ',', '.') ?></div>
                                <?php else: ?>
                                  <div class="fw-semibold small text-break"><?= htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8') ?></div>
                                <?php endif; ?>
                              </div>
                            </div>
                          <?php endforeach; ?>
                        </div>
                      <?php endif; ?>

                      <?php if ($payload !== []): ?>
                        <div class="mt-2">
                          <button class="btn btn-sm btn-link text-muted p-0" type="button" data-bs-toggle="collapse" data-bs-target="#payload-<?= md5($evento['timestamp'] . $evento['message']) ?>" aria-expanded="false">
                            <i class="bi bi-chevron-down me-1"></i>Payload completo
                          </button>
                          <div class="collapse" id="payload-<?= md5($evento['timestamp'] . $evento['message']) ?>">
                            <pre class="bg-dark text-light rounded-3 p-3 mt-2 mb-0" style="max-height: 300px; overflow: auto; font-size: 0.8rem;"><code><?= htmlspecialchars(json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), ENT_QUOTES, 'UTF-8') ?></code></pre>
                          </div>
                        </div>
                      <?php endif; ?>
                    </div>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>
</section>
