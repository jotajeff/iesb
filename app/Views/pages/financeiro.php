<?php
$acordo = $acordo ?? [];
$parcela = $parcela ?? [];
$modo = $modo ?? 'acordo';
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

$ehParcela = $modo === 'parcela';
$primeiraJaPaga = !$ehParcela && (int) ($acordo['tipo'] ?? 0) === 5;

if ($ehParcela) {
    $cursoNome = (string) ($parcela['curso_nome'] ?? '-');
    $candidatoNome = (string) ($parcela['nome'] ?? '-');
    $numeroParcela = (int) ($parcela['numero_parcela'] ?? 0);
    $totalParcelas = (int) ($parcela['total_parcelas'] ?? 1);
    $valorParcela = (float) ($parcela['valor'] ?? 0);
    $vencimentoParcela = (string) ($parcela['data_vencimento'] ?? '');
    $descricaoParcela = (string) ($parcela['descricao_pagamento'] ?? '');
    $parcelas = $totalParcelas;
} else {
    $cursoNome = (string) ($acordo['curso_nome'] ?? '-');
    $candidatoNome = (string) ($acordo['candidato_nome'] ?? '-');
    $valorEntrada = (float) ($acordo['valor_entrada'] ?? 0);
    $valorDemais = (float) ($acordo['valor_demais_parcelas'] ?? 0);
    $desconto = (float) ($acordo['desconto'] ?? 0);
    $parcelas = (int) ($acordo['total_parcelas'] ?? 1);
    $observacao = (string) ($acordo['observacao'] ?? '');
    $valorParcela = $valorEntrada > 0 ? $valorEntrada : $valorDemais;
    if ($valorParcela <= 0) {
        $valorParcela = (float) ($acordo['plano_valor'] ?? 0);
    }
    $numeroParcela = 1;
    $totalParcelas = $parcelas;
}
$formAction = $ehParcela
    ? '/financeiro/parcela/' . (int) $inscricaoId . '/continuar'
    : '/financeiro/' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '/continuar';
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
            <h3 class="mb-2"><?= $primeiraJaPaga ? 'Link de recorrência criado!' : 'Cobrança gerada!' ?></h3>
            <?php if ($ehParcela): ?>
              <p class="text-muted mb-1"><?= $numeroParcela ?>ª parcela (de <?= $totalParcelas ?>) para <strong><?= htmlspecialchars($cursoNome, ENT_QUOTES, 'UTF-8') ?></strong>.</p>
              <p class="mb-4">Valor da parcela: <strong>R$ <?= number_format($valorParcela, 2, ',', '.') ?></strong></p>
            <?php else: ?>
              <?php if ((int) ($acordo['tipo'] ?? 0) === 4): ?>
                <p class="text-muted mb-1">Primeira parcela do acordo para <strong><?= htmlspecialchars($cursoNome, ENT_QUOTES, 'UTF-8') ?></strong>.</p>
                <p class="mb-4">Valor da parcela: <strong>R$ <?= number_format($valorParcela, 2, ',', '.') ?></strong> (total de <?= $parcelas ?>x)</p>
              <?php else: ?>
                <?php if ($primeiraJaPaga): ?>
                  <p class="text-muted mb-1">A primeira parcela já foi paga para <strong><?= htmlspecialchars($cursoNome, ENT_QUOTES, 'UTF-8') ?></strong>.</p>
                  <p class="mb-4">Abra o link abaixo para cadastrar o cartão e ativar as <?= max(0, $parcelas - 1) ?> cobranças restantes.</p>
                <?php else: ?>
                  <p class="text-muted mb-1">Primeira parcela (entrada) do acordo para <strong><?= htmlspecialchars($cursoNome, ENT_QUOTES, 'UTF-8') ?></strong>.</p>
                  <p class="mb-4">Valor da entrada: <strong>R$ <?= number_format($valorParcela, 2, ',', '.') ?></strong> (total de <?= $parcelas ?>x)</p>
                <?php endif; ?>
              <?php endif; ?>
            <?php endif; ?>
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
              <?php if ((string) $billingType === 'BOLETO'): ?>
                <p class="text-muted mb-4">Seu boleto foi gerado com sucesso. Clique no botão abaixo para visualizar e efetuar o pagamento. O vencimento é em até 3 dias úteis.</p>
                <?php $checkoutUrl = $bankSlipUrl !== '' ? $bankSlipUrl : $invoiceUrl; ?>
                <a class="btn-primary-custom mb-3" href="<?= htmlspecialchars($checkoutUrl, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener noreferrer" id="btnAbrirCheckout"><i class="bi bi-upc-scan me-1"></i>Abrir Boleto</a>
              <?php else: ?>
                <p class="text-muted mb-4">Sua cobrança foi gerada. Clique no botão abaixo para concluir o pagamento no ambiente seguro do Asaas.</p>
                <?php $checkoutUrl = $invoiceUrl !== '' ? $invoiceUrl : $bankSlipUrl; ?>
                <a class="btn-primary-custom mb-3" href="<?= htmlspecialchars($checkoutUrl, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener noreferrer" id="btnAbrirCheckout"><i class="bi bi-credit-card me-1"></i><?= $primeiraJaPaga ? 'Cadastrar cartão e ativar recorrência' : 'Abrir pagamento' ?></a>
              <?php endif; ?>
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
            <h5 class="mb-3"><i class="bi bi-person-check me-2"></i><?= $ehParcela ? 'Parcela a pagar' : ($primeiraJaPaga ? 'Autorizar recorrência das parcelas restantes' : 'Acordo negociado') ?></h5>
            <div class="row g-3">
              <div class="col-md-6">
                <div class="text-muted small text-uppercase">Candidato</div>
                <div class="fw-semibold"><?= htmlspecialchars($candidatoNome, ENT_QUOTES, 'UTF-8') ?></div>
              </div>
              <div class="col-md-6">
                <div class="text-muted small text-uppercase">Curso</div>
                <div class="fw-semibold"><?= htmlspecialchars($cursoNome, ENT_QUOTES, 'UTF-8') ?></div>
              </div>
              <?php if ($ehParcela): ?>
                <div class="col-md-4">
                  <div class="text-muted small text-uppercase">Parcelas</div>
                  <div class="fw-semibold"><?= $totalParcelas ?>x</div>
                </div>
                <div class="col-md-4">
                  <div class="text-muted small text-uppercase"><?= (int) ($acordo['tipo'] ?? 0) === 4 ? 'Parcelas' : 'Entrada e demais parcelas' ?></div>
                  <div class="fw-semibold"><?= (int) ($acordo['tipo'] ?? 0) === 4 ? $totalParcelas . ' parcelas' : '1ª (entrada) + ' . max(0, $totalParcelas - 1) . ' demais' ?></div>
                </div>
                <div class="col-md-4">
                  <div class="text-muted small text-uppercase">Parcela atual</div>
                  <div class="fw-semibold"><?= $numeroParcela ?>ª de <?= $totalParcelas ?>x</div>
                </div>
                <div class="col-md-4">
                  <div class="text-muted small text-uppercase">Valor</div>
                  <div class="fw-semibold">R$ <?= number_format($valorParcela, 2, ',', '.') ?></div>
                </div>
                <div class="col-md-4">
                  <div class="text-muted small text-uppercase">Vencimento</div>
                  <div class="fw-semibold"><?= htmlspecialchars(
                    $vencimentoParcela !== '' ? (new \DateTime($vencimentoParcela))->format('d/m/Y') : '-',
                    ENT_QUOTES,
                    'UTF-8'
                  ) ?></div>
                </div>
              <?php else: ?>
                <div class="col-md-4">
                  <div class="text-muted small text-uppercase">Parcelas</div>
                  <div class="fw-semibold"><?= $parcelas ?>x</div>
                </div>
                <div class="col-md-4">
                  <div class="text-muted small text-uppercase">Entrada</div>
                  <div class="fw-semibold">R$ <?= number_format($valorEntrada, 2, ',', '.') ?></div>
                </div>
                <div class="col-md-4">
                  <div class="text-muted small text-uppercase">Valor demais parcelas</div>
                  <div class="fw-semibold">R$ <?= number_format($valorDemais, 2, ',', '.') ?></div>
                </div>
              <?php endif; ?>
              <?php if (!$ehParcela && $observacao !== ''): ?>
                <div class="col-12">
                  <div class="text-muted small text-uppercase">Observações</div>
                  <div class="fw-semibold"><?= nl2br(htmlspecialchars($observacao, ENT_QUOTES, 'UTF-8')) ?></div>
                </div>
              <?php endif; ?>
            </div>
          </div>

          <div class="bg-white border rounded-4 p-4 shadow-sm">
            <h5 class="mb-3"><i class="bi bi-credit-card me-2"></i>Escolha a forma de pagamento</h5>
            <form method="post" action="<?= htmlspecialchars($formAction, ENT_QUOTES, 'UTF-8') ?>">
              <div class="row g-2">
                <div class="col-md-4">
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
                <div class="col-md-4">
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
                <div class="col-md-4">
                  <label class="d-block border rounded-3 p-3 cursor-pointer" style="cursor:pointer;transition:all .2s;" onmouseover="this.style.borderColor='var(--primary)'" onmouseout="this.style.borderColor=''">
                    <div class="d-flex align-items-center gap-2">
                      <input type="radio" name="forma_pagamento" value="boleto" class="form-check-input" required>
                      <i class="bi bi-upc-scan fs-4 text-warning"></i>
                      <div>
                        <strong class="small d-block">Boleto Bancário</strong>
                        <span class="text-muted small">Vencimento em até 3 dias úteis</span>
                      </div>
                    </div>
                  </label>
                </div>
              </div>

              <?php if (!$ehParcela): ?>
                <div class="recorrencia-box" id="recorrenciaBox" style="display:none;">
                  <div class="form-check mb-0">
                    <input class="form-check-input" type="checkbox" name="recorrencia" value="1" id="recorrenciaCheck"<?= $primeiraJaPaga ? ' checked required' : '' ?>>
                    <label class="form-check-label" for="recorrenciaCheck">
                      <i class="bi bi-arrow-repeat me-1"></i>Autorizar cobrança automática das próximas parcelas
                      <span class="text-muted">no cartão de crédito</span>
                    </label>
                  </div>
                  <div class="form-text mb-0"><?= $primeiraJaPaga ? 'A primeira parcela já foi paga. As parcelas restantes serão cobradas automaticamente no seu cartão no dia 10 de cada mês.' : 'Ao autorizar, as parcelas restantes do acordo serão cobradas automaticamente no seu cartão após a confirmação do pagamento da entrada.' ?></div>
                </div>
              <?php endif; ?>

              <div class="alert alert-light border mt-4 mb-0">
                <i class="bi bi-info-circle me-1"></i>
                <?php if ($ehParcela): ?>
                  Ao continuar, será gerada a cobrança da <strong><?= $numeroParcela ?>ª parcela</strong>
                  (R$ <?= number_format($valorParcela, 2, ',', '.') ?>).
                <?php else: ?>
                  <?php if ($primeiraJaPaga): ?>
                    A primeira parcela já está registrada como paga. Ao continuar, a recorrência das <strong><?= max(0, $totalParcelas - 1) ?> parcelas restantes</strong> será ativada.
                  <?php else: ?>
                    Ao continuar, será gerada a cobrança referente à <strong>entrada</strong>
                    (R$ <?= number_format($valorParcela, 2, ',', '.') ?>). As demais parcelas serão geradas após a confirmação do pagamento.
                  <?php endif; ?>
                <?php endif; ?>
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

<style>
  .recorrencia-box {
    border: 2px solid #fd7e14;
    border-radius: 0.75rem;
    padding: 0.9rem 1.1rem;
    margin-top: 0.9rem;
    background: #fff8ef;
  }

  .recorrencia-box .form-check-input:checked {
    background-color: #fd7e14;
    border-color: #fd7e14;
  }

  .recorrencia-box .form-check-label {
    font-weight: 600;
  }
</style>

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

  document.addEventListener('DOMContentLoaded', function() {
    const recorrenciaBox = document.getElementById('recorrenciaBox');
    if (!recorrenciaBox) return;

    function atualizarRecorrencia() {
      const selecionado = document.querySelector('input[name="forma_pagamento"]:checked');
      const mostra = selecionado && selecionado.value === 'cartao';
      recorrenciaBox.style.display = mostra ? 'block' : 'none';
      const check = document.getElementById('recorrenciaCheck');
      if (check) check.checked = false;
    }

    document.querySelectorAll('input[name="forma_pagamento"]').forEach(function(radio) {
      radio.addEventListener('change', atualizarRecorrencia);
    });
    atualizarRecorrencia();
  });
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
