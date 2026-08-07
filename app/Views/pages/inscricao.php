<?php
$curso = $curso ?? [];
$pagamentos = $pagamentos ?? [];
$erro = $erro ?? null;
$dados = $dados ?? [];
$sucesso = $sucesso ?? false;
$inscricaoId = $inscricaoId ?? 0;
$invoiceUrl = $invoiceUrl ?? '';
$bankSlipUrl = $bankSlipUrl ?? '';
$pixQrCode = $pixQrCode ?? null;
$linhaDigitavel = $linhaDigitavel ?? null;
$billingType = $billingType ?? '';
$asaasError = $asaasError ?? null;
$idCurso = (int) ($curso['id'] ?? 0);
?>
<section class="hero-section" id="home" style="min-height:40vh;">
  <div class="hero-bg"></div>
  <div class="container hero-content">
    <div class="row justify-content-center">
      <div class="col-lg-10 text-center courses-hero-copy" data-aos="fade-up">
        <h1 class="hero-title">Garantir minha vaga</h1>
        <p class="hero-subtitle"><?= htmlspecialchars((string) ($curso['nome'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></p>
      </div>
    </div>
  </div>
</section>

<section class="py-5">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-lg-8">

        <?php if ($sucesso): ?>
          <div class="bg-white border rounded-4 p-5 shadow-sm text-center">
            <div class="mb-3 text-success"><i class="bi bi-check-circle-fill" style="font-size:4rem;"></i></div>
            <h3 class="mb-2">Inscrição recebida!</h3>
            <p class="text-muted mb-1">Sua inscrição para o curso <strong><?= htmlspecialchars((string) ($curso['nome'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></strong> foi registrada com sucesso.</p>
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
            <?php elseif ((string) $billingType === 'BOLETO' && is_array($linhaDigitavel) && !empty($linhaDigitavel['identificationField'])): ?>
              <div class="border rounded-4 p-4 mt-4 text-start bg-light">
                <h5 class="mb-3"><i class="bi bi-upc-scan me-2"></i>Linha digitável do boleto</h5>
                <label class="form-label small text-muted mb-1">Código para pagamento</label>
                <div class="input-group mb-3">
                  <input id="boletoLinhaDigitavel" type="text" class="form-control" value="<?= htmlspecialchars((string) ($linhaDigitavel['identificationField'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" readonly>
                  <button class="btn btn-outline-secondary" type="button" onclick="copiarTexto('boletoLinhaDigitavel')">Copiar</button>
                </div>
                <?php if (!empty($linhaDigitavel['barCode'])): ?>
                  <p class="small text-muted mb-1">Código de barras</p>
                  <p class="mb-0 font-monospace small text-break"><?= htmlspecialchars((string) $linhaDigitavel['barCode'], ENT_QUOTES, 'UTF-8') ?></p>
                <?php endif; ?>
              </div>
            <?php elseif ($invoiceUrl !== '' || $bankSlipUrl !== ''): ?>
              <p class="text-muted mb-4">Sua inscrição foi registrada. Clique no botão abaixo para concluir o pagamento no ambiente seguro do Asaas.</p>
              <?php $checkoutUrl = $invoiceUrl !== '' ? $invoiceUrl : $bankSlipUrl; ?>
              <a class="btn-primary-custom mb-3" href="<?= htmlspecialchars($checkoutUrl, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener noreferrer" id="btnAbrirCheckout"><i class="bi bi-credit-card me-1"></i>Abrir pagamento</a>
              <br>
            <?php else: ?>
              <p class="text-muted mb-4">A cobrança foi criada, mas ainda não foi possível recuperar os dados de pagamento. Se o problema persistir, verifique a chave sandbox e os logs do Asaas.</p>
            <?php endif; ?>

            <a class="btn-primary-custom" href="/curso/<?= htmlspecialchars((string) ($curso['slug'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"><i class="bi bi-arrow-left me-1"></i>Voltar ao curso</a>
          </div>
        <?php else: ?>

          <?php if ($erro): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($erro, ENT_QUOTES, 'UTF-8') ?></div>
          <?php endif; ?>

          <div class="bg-white border rounded-4 p-4 shadow-sm">
            <h5 class="mb-3"><i class="bi bi-pencil-square me-2"></i>Dados do aluno</h5>
            <form method="post" action="/inscricao/salvar">
              <input type="hidden" name="id_curso" value="<?= $idCurso ?>">
              <input type="hidden" name="id_turma" value="<?= (int) ($idTurma ?? 0) ?>">

              <div class="row g-3">
                <div class="col-12">
                  <label class="form-label">Nome completo <span class="text-danger">*</span></label>
                  <input type="text" name="nome" class="form-control-custom" value="<?= htmlspecialchars((string) ($dados['nome'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" required>
                </div>
                <div class="col-md-6">
                  <label class="form-label">CPF <span class="text-danger">*</span></label>
                  <input type="text" name="cpf" class="form-control-custom" value="<?= htmlspecialchars((string) ($dados['cpf'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" required placeholder="000.000.000-00">
                </div>
                <div class="col-md-6">
                  <label class="form-label">Telefone <span class="text-danger">*</span></label>
                  <input type="text" name="telefone" class="form-control-custom" value="<?= htmlspecialchars((string) ($dados['telefone'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" required placeholder="(00) 00000-0000">
                </div>
                <div class="col-12">
                  <label class="form-label">E-mail <span class="text-danger">*</span></label>
                  <input type="email" name="email" class="form-control-custom" value="<?= htmlspecialchars((string) ($dados['email'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" required>
                </div>
              </div>

              <?php if (!empty($pagamentos)): ?>
                <hr class="my-4">
                <h5 class="mb-3"><i class="bi bi-currency-dollar me-2"></i>Escolha o plano de pagamento</h5>
                <div class="row g-2">
                  <?php foreach ($pagamentos as $p): ?>
                    <div class="col-md-6">
                      <label class="d-block border rounded-3 p-3 cursor-pointer" style="cursor:pointer;transition:all .2s;" onmouseover="this.style.borderColor='var(--primary)'" onmouseout="this.style.borderColor=''">
                        <div class="d-flex align-items-center gap-2 mb-1">
                          <input type="radio" name="id_pagamento" value="<?= (int) ($p['id'] ?? 0) ?>" class="form-check-input"<?= ((int) ($dados['idPagamento'] ?? 0) === (int) ($p['id'] ?? 0)) ? ' checked' : '' ?> required>
                          <strong class="small"><?= htmlspecialchars((string) ($p['descricao'] ?? ''), ENT_QUOTES, 'UTF-8') ?></strong>
                        </div>
                        <div class="d-flex justify-content-between ps-4">
                          <span class="text-muted small"><?= (int) ($p['parcelas'] ?? 1) ?>x</span>
                          <span class="fw-bold">R$ <?= number_format((float) ($p['valor'] ?? 0), 2, ',', '.') ?></span>
                        </div>
                      </label>
                    </div>
                  <?php endforeach; ?>
                </div>
              <?php endif; ?>

              <hr class="my-4">
              <h5 class="mb-3"><i class="bi bi-credit-card me-2"></i>Forma de pagamento</h5>
              <div class="row g-2">
                <div class="col-md-6">
                  <label class="d-block border rounded-3 p-3 cursor-pointer" style="cursor:pointer;transition:all .2s;" onmouseover="this.style.borderColor='var(--primary)'" onmouseout="this.style.borderColor=''">
                    <div class="d-flex align-items-center gap-2">
                      <input type="radio" name="forma_pagamento" value="pix" class="form-check-input"<?= ((string) ($dados['formaPagamento'] ?? 'pix') === 'pix') ? ' checked' : '' ?> required>
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
                      <input type="radio" name="forma_pagamento" value="cartao" class="form-check-input"<?= ((string) ($dados['formaPagamento'] ?? 'pix') === 'cartao') ? ' checked' : '' ?> required>
                      <i class="bi bi-credit-card fs-4 text-primary"></i>
                      <div>
                        <strong class="small d-block">Cartão de Crédito</strong>
                        <span class="text-muted small">Pague com segurança no Asaas</span>
                      </div>
                    </div>
                  </label>
                </div>
              </div>

              <div class="mt-4">
                <button type="submit" class="btn-primary-custom w-100 justify-content-center"><i class="bi bi-check-lg me-1"></i>Finalizar inscrição</button>
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
