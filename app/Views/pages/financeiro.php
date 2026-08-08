<?php
$acordo = $acordo ?? [];
$token = $token ?? '';
$sucesso = $sucesso ?? false;
$jaUtilizado = $jaUtilizado ?? false;
$inscricaoId = $inscricaoId ?? 0;
$invoiceUrl = $invoiceUrl ?? '';
$bankSlipUrl = $bankSlipUrl ?? '';
$pixQrCode = $pixQrCode ?? null;
$linhaDigitavel = $linhaDigitavel ?? null;
$billingType = $billingType ?? '';
$asaasError = $asaasError ?? null;
$abrirCheckoutNovaAba = $abrirCheckoutNovaAba ?? false;

$cursoNome = (string) ($acordo['curso_nome'] ?? '-');
$candidatoNome = (string) ($acordo['candidato_nome'] ?? '-');
$valorNegociado = (float) ($acordo['valor_negociado'] ?? 0);
$desconto = (float) ($acordo['desconto'] ?? 0);
$parcelas = (int) ($acordo['parcelas_negociadas'] ?? 1);
$observacao = (string) ($acordo['observacao'] ?? '');
$valorParcela = $parcelas > 0 ? round($valorNegociado / $parcelas, 2) : $valorNegociado;
?>
<section class="hero-section" id="home" style="min-height:40vh;">
  <div class="hero-bg"></div>
  <div class="container hero-content">
    <div class="row justify-content-center">
      <div class="col-lg-10 text-center courses-hero-copy" data-aos="fade-up">
        <h1 class="hero-title">Portal Financeiro</h1>
        <p class="hero-subtitle"><?= htmlspecialchars($cursoNome, ENT_QUOTES, 'UTF-8') ?></p>
      </div>
    </div>
  </div>
</section>

<section class="py-5">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-lg-8">

        <?php if ($jaUtilizado): ?>
          <div class="bg-white border rounded-4 p-5 shadow-sm text-center">
            <div class="mb-3 text-info"><i class="bi bi-hourglass-split" style="font-size:4rem;"></i></div>
            <h3 class="mb-2">Acordo já utilizado</h3>
            <p class="text-muted mb-0">Este acordo já foi utilizado para gerar uma cobrança. Entre em contato com a secretaria para mais informações.</p>
          </div>
        <?php elseif ($sucesso): ?>
          <div class="bg-white border rounded-4 p-5 shadow-sm text-center">
            <div class="mb-3 text-success"><i class="bi bi-check-circle-fill" style="font-size:4rem;"></i></div>
            <h3 class="mb-2">Cobrança gerada!</h3>
            <p class="text-muted mb-1">Primeira parcela do acordo para <strong><?= htmlspecialchars($cursoNome, ENT_QUOTES, 'UTF-8') ?></strong>.</p>
            <p class="mb-4">Valor: <strong>R$ <?= number_format($valorParcela, 2, ',', '.') ?></strong> (<?= $parcelas ?>x de R$ <?= number_format($valorParcela, 2, ',', '.') ?>)</p>
            <?php if ($asaasError): ?>
              <div class="alert alert-warning text-start mt-4 mb-4">
                <strong>Pagamento no Asaas não concluído:</strong> <?= htmlspecialchars((string) $asaasError, ENT_QUOTES, 'UTF-8') ?>
              </div>
            <?php endif; ?>

            <?php if ((string) $billingType === 'PIX' && is_array($pixQrCode) && !empty($pixQrCode['payload'])): ?>
              <div class="border rounded-4 p-4 mt-4 text-start bg-light">
                <h5 class="mb-3"><i class="bi bi-qr-code me-2"></i>Pagar via Pix</h5>
                <?php if (!empty($pixQrCode['encodedImage'])): ?>
                  <div class="text-center mb-3">
                    <img
                      src="data:image/png;base64,<?= htmlspecialchars((string) $pixQrCode['encodedImage'], ENT_QUOTES, 'UTF-8') ?>"
                      alt="QR Code Pix"
                      class="img-fluid border rounded-3 bg-white p-2"
                      style="max-width: 280px;"
                    >
                  </div>
                <?php endif; ?>
                <label class="form-label small text-muted mb-1">Pix copia e cola</label>
                <div class="input-group mb-3">
                  <input id="pixPayload" type="text" class="form-control" value="<?= htmlspecialchars((string) ($pixQrCode['payload'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" readonly>
                  <button class="btn btn-outline-secondary" type="button" onclick="copiarTexto('pixPayload')">Copiar</button>
                </div>
                <?php if (!empty($pixQrCode['expirationDate'])): ?>
                  <p class="small text-muted mb-0">Validade: <?= htmlspecialchars((string) $pixQrCode['expirationDate'], ENT_QUOTES, 'UTF-8') ?></p>
                <?php endif; ?>
              </div>
            <?php elseif ($invoiceUrl !== '' || $bankSlipUrl !== ''): ?>
              <p class="text-muted mb-4">Sua cobrança foi gerada. Clique no botão abaixo para concluir o pagamento no ambiente seguro do Asaas.</p>
              <?php $checkoutUrl = $invoiceUrl !== '' ? $invoiceUrl : $bankSlipUrl; ?>
              <a class="btn-primary-custom mb-3" href="<?= htmlspecialchars($checkoutUrl, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener noreferrer" id="btnAbrirCheckout"><i class="bi bi-credit-card me-1"></i>Abrir pagamento</a>
              <br>
            <?php else: ?>
              <p class="text-muted mb-4">A cobrança foi criada, mas ainda não foi possível recuperar os dados de pagamento. Se o problema persistir, entre em contato com a secretaria.</p>
            <?php endif; ?>
          </div>
        <?php else: ?>

          <?php if ($asaasError): ?>
            <div class="alert alert-danger"><?= htmlspecialchars((string) $asaasError, ENT_QUOTES, 'UTF-8') ?></div>
          <?php endif; ?>

          <div class="bg-white border rounded-4 p-4 shadow-sm mb-4">
            <h5 class="mb-3"><i class="bi bi-person-check me-2"></i>Acordo negociado</h5>
            <div class="row g-3">
              <div class="col-md-6">
                <div class="text-muted small text-uppercase">Candidato</div>
                <div class="fw-semibold"><?= htmlspecialchars($candidatoNome, ENT_QUOTES, 'UTF-8') ?></div>
              </div>
              <div class="col-md-6">
                <div class="text-muted small text-uppercase">Curso</div>
                <div class="fw-semibold"><?= htmlspecialchars($cursoNome, ENT_QUOTES, 'UTF-8') ?></div>
              </div>
              <div class="col-md-4">
                <div class="text-muted small text-uppercase">Valor negociado</div>
                <div class="fw-semibold">R$ <?= number_format($valorNegociado, 2, ',', '.') ?></div>
              </div>
              <div class="col-md-4">
                <div class="text-muted small text-uppercase">Parcelas</div>
                <div class="fw-semibold"><?= $parcelas ?>x de R$ <?= number_format($valorParcela, 2, ',', '.') ?></div>
              </div>
              <div class="col-md-4">
                <div class="text-muted small text-uppercase">Desconto</div>
                <div class="fw-semibold">R$ <?= number_format($desconto, 2, ',', '.') ?></div>
              </div>
              <?php if ($observacao !== ''): ?>
                <div class="col-12">
                  <div class="text-muted small text-uppercase">Observações</div>
                  <div class="fw-semibold"><?= nl2br(htmlspecialchars($observacao, ENT_QUOTES, 'UTF-8')) ?></div>
                </div>
              <?php endif; ?>
            </div>
          </div>

          <div class="bg-white border rounded-4 p-4 shadow-sm">
            <h5 class="mb-3"><i class="bi bi-credit-card me-2"></i>Escolha a forma de pagamento</h5>
            <form method="post" action="/financeiro/<?= htmlspecialchars($token, ENT_QUOTES, 'UTF-8') ?>/continuar">
              <div class="row g-2">
                <div class="col-md-6">
                  <label class="d-block border rounded-3 p-3 cursor-pointer" style="cursor:pointer;transition:all .2s;" onmouseover="this.style.borderColor='var(--primary)'" onmouseout="this.style.borderColor=''">
                    <div class="d-flex align-items-center gap-2">
                      <input type="radio" name="forma_pagamento" value="pix" class="form-check-input" checked required>
                      <i class="bi bi-qr-code fs-4 text-success"></i>
                      <div>
                        <strong class="small d-block">PIX / QR Code</strong>
                        <span class="text-muted small">Pagamento imediato via Pix</span>
                      </div>
                    </div>
                  </label>
                </div>
                <div class="col-md-6">
                  <label class="d-block border rounded-3 p-3 cursor-pointer" style="cursor:pointer;transition:all .2s;" onmouseover="this.style.borderColor='var(--primary)'" onmouseout="this.style.borderColor=''">
                    <div class="d-flex align-items-center gap-2">
                      <input type="radio" name="forma_pagamento" value="cartao" class="form-check-input" required>
                      <i class="bi bi-credit-card fs-4 text-primary"></i>
                      <div>
                        <strong class="small d-block">Cartão de Crédito</strong>
                        <span class="text-muted small">Pague com segurança no Asaas</span>
                      </div>
                    </div>
                  </label>
                </div>
              </div>

              <div class="alert alert-light border mt-4 mb-0">
                <i class="bi bi-info-circle me-1"></i>
                Ao continuar, será gerada a cobrança referente à <strong>primeira parcela</strong>
                (R$ <?= number_format($valorParcela, 2, ',', '.') ?>). As demais parcelas serão geradas após a confirmação do pagamento.
              </div>

              <div class="mt-4">
                <button type="submit" class="btn-primary-custom w-100 justify-content-center"><i class="bi bi-check-lg me-1"></i>Continuar</button>
              </div>
            </form>
          </div>
        <?php endif; ?>

      </div>
    </div>
  </div>
</section>

<script>
  function copiarTexto(elementId) {
    const el = document.getElementById(elementId);
    if (!el) return;

    const texto = el.value || el.textContent || '';
    if (navigator.clipboard && window.isSecureContext) {
      navigator.clipboard.writeText(texto);
      return;
    }

    el.select?.();
    document.execCommand('copy');
  }
</script>

<?php if (!empty($abrirCheckoutNovaAba) && ($invoiceUrl !== '' || $bankSlipUrl !== '')): ?>
<script>
  document.addEventListener('DOMContentLoaded', function() {
    var btn = document.getElementById('btnAbrirCheckout');
    if (!btn) return;
    var url = btn.getAttribute('href');
    if (url) {
      window.open(url, '_blank', 'noopener');
    }
  });
</script>
<?php endif; ?>
