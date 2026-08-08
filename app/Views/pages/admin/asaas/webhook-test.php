<?php
$result = $result ?? [];
$appUrl = (string) ($result['appUrl'] ?? '');
$webhookUrl = (string) ($result['webhookUrl'] ?? '');
$tokenDefinido = (bool) ($result['tokenDefinido'] ?? false);
$tokenTamanho = (int) ($result['tokenTamanho'] ?? 0);
$tokenValido = $result['tokenValido'] ?? null;
$curlOk = (bool) ($result['curlOk'] ?? false);
$httpCode = $result['httpCode'] ?? null;
$resposta = (string) ($result['resposta'] ?? '');
$erro = (string) ($result['erro'] ?? '');
?>

<section class="py-4">
  <div class="container-fluid">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
      <div>
        <h1 class="h3 mb-1">Teste de Webhook Asaas</h1>
        <p class="text-muted mb-0">Valida o token configurado e dispara um POST de teste para o endpoint do webhook.</p>
      </div>
      <div class="d-flex gap-2">
        <a href="/admin/asaas/webhook-test" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-clockwise me-1"></i>Executar novamente</a>
        <a href="/admin/asaas" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Voltar</a>
      </div>
    </div>

    <div class="row g-3 mb-4">
      <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
          <div class="card-body">
            <div class="text-muted small">Endpoint</div>
            <div class="font-monospace small text-break"><?= htmlspecialchars($webhookUrl, ENT_QUOTES, 'UTF-8') ?></div>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
          <div class="card-body">
            <div class="text-muted small">Token (<?= $tokenTamanho ?> chars)</div>
            <?php if (!$tokenDefinido): ?>
              <span class="badge bg-danger">Não definido no .env</span>
            <?php elseif ($tokenValido === true): ?>
              <span class="badge bg-success">OK (32–255 chars, sem espaços)</span>
            <?php else: ?>
              <span class="badge bg-danger">Inválido (deve ter 32–255 chars, sem espaços)</span>
            <?php endif; ?>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
          <div class="card-body">
            <div class="text-muted small">Resultado do POST</div>
            <?php if (!$curlOk): ?>
              <span class="badge bg-danger">Falha de conexão</span>
            <?php elseif ($httpCode === 200): ?>
              <span class="badge bg-success">HTTP <?= $httpCode ?> — endpoint OK</span>
            <?php elseif ($httpCode === 401): ?>
              <span class="badge bg-warning text-dark">HTTP <?= $httpCode ?> — token rejeitado</span>
            <?php else: ?>
              <span class="badge bg-secondary">HTTP <?= $httpCode ?></span>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>

    <?php if ($erro !== ''): ?>
      <div class="alert alert-danger">
        <strong>Erro de conexão:</strong> <?= htmlspecialchars($erro, ENT_QUOTES, 'UTF-8') ?>
      </div>
    <?php endif; ?>

    <?php if ($curlOk && $resposta !== ''): ?>
      <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white">Resposta do endpoint</div>
        <div class="card-body">
          <pre class="mb-0 bg-light p-3 rounded small overflow-auto"><?= htmlspecialchars($resposta, ENT_QUOTES, 'UTF-8') ?></pre>
        </div>
      </div>
    <?php endif; ?>

    <div class="card border-0 shadow-sm">
      <div class="card-header bg-white">Como interpretar</div>
      <div class="card-body">
        <ul class="mb-0 small">
          <li><strong>HTTP 200</strong>: o endpoint processou o POST. Veja <code>storage/logs/asaas/webhook-&lt;data&gt;.log</code> para a entrada "Inscrição não encontrada" (esperado, pois o payment é fake).</li>
          <li><strong>HTTP 401</strong>: o endpoint recebeu o POST mas o token não bate. Confira se o <code>asaas-access-token</code> do painel Asaas é idêntico ao <code>ASAAS_WEBHOOK_TOKEN</code> do <code>.env</code>.</li>
          <li><strong>Erro de conexão</strong>: o servidor não consegue acessar a própria URL. Verifique DNS/SSL/firewall e se o domínio aponta para este servidor.</li>
        </ul>
      </div>
    </div>
  </div>
</section>
