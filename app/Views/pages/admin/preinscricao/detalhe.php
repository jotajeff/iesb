<?php
$preId = (int) ($pre['id'] ?? 0);
$comentariosTotal = (int) ($comentariosTotal ?? 0);
$criadoEm = trim((string) ($pre['created_at'] ?? ''));
$criadoEmFormatado = $criadoEm !== '' ? date('d/m/Y H:i', strtotime($criadoEm)) : '-';
?>
<section class="container py-4">
  <div class="bg-white border rounded-3 p-4 shadow-sm">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
      <div>
        <h4 class="mb-1"><i class="bi bi-inbox me-2"></i>Pré-inscrição #<?= $preId ?></h4>
        <p class="text-muted mb-0">Dados do formulário de pré-inscrição.</p>
      </div>
      <div class="d-flex gap-2">
        <?php if (!empty($planos)): ?>
          <button type="button" class="btn-acordo-orange" data-bs-toggle="modal" data-bs-target="#modalAcordo">
            <i class="bi bi-file-earmark-text me-1"></i>Gerar Acordo
          </button>
        <?php endif; ?>
        <a class="btn btn-outline-secondary btn-sm" href="/admin/preinscricao"><i class="bi bi-arrow-left me-1"></i>Voltar</a>
      </div>
    </div>

    <div class="row g-3 mb-4">
      <div class="col-lg-8">
        <div class="card border-0 shadow-sm h-100">
          <div class="card-body">
            <h5 class="mb-3"><i class="bi bi-person-fill me-2"></i>Dados do candidato</h5>
            <div class="row g-3">
              <div class="col-md-6">
                <div class="p-3 rounded-3 bg-light border">
                  <div class="text-muted small text-uppercase">Nome</div>
                  <div class="fw-semibold"><?= htmlspecialchars((string) ($pre['nome'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></div>
                </div>
              </div>
              <div class="col-md-6">
                <div class="p-3 rounded-3 bg-light border">
                  <div class="text-muted small text-uppercase">E-mail</div>
                  <div class="fw-semibold"><?= htmlspecialchars((string) ($pre['email'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></div>
                </div>
              </div>
              <div class="col-md-6">
                <div class="p-3 rounded-3 bg-light border">
                  <div class="text-muted small text-uppercase">WhatsApp</div>
                  <div class="fw-semibold">
                    <?php $wa = \App\Helpers\WhatsAppHelper::onlyDigits((string) ($pre['whatsapp'] ?? '')); ?>
                    <?php if ($wa !== ''): ?>
                      <a href="https://wa.me/<?= $wa ?>" target="_blank" class="text-decoration-none"><i class="bi bi-whatsapp me-1" style="color:#128C7E;"></i><?= htmlspecialchars(\App\Helpers\WhatsAppHelper::format((string) ($pre['whatsapp'] ?? '-')), ENT_QUOTES, 'UTF-8') ?></a>
                    <?php else: ?>
                      <?= htmlspecialchars(\App\Helpers\WhatsAppHelper::format((string) ($pre['whatsapp'] ?? '-')), ENT_QUOTES, 'UTF-8') ?>
                    <?php endif; ?>
                  </div>
                </div>
              </div>
              <div class="col-md-6">
                <div class="p-3 rounded-3 bg-light border">
                  <div class="text-muted small text-uppercase">Curso de interesse</div>
                  <div class="fw-semibold"><?= htmlspecialchars((string) ($pre['curso_nome'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-lg-4">
        <div class="card border-0 shadow-sm h-100">
          <div class="card-body">
            <div class="text-uppercase text-muted small fw-semibold mb-2">Resumo</div>
            <div class="d-grid gap-3">
              <div class="p-3 rounded-3 bg-light border">
                <div class="text-muted small">Situação</div>
                <div class="fw-semibold"><span class="badge bg-warning text-dark"><?= htmlspecialchars((string) ($pre['situacao'] ?? 'recebido'), ENT_QUOTES, 'UTF-8') ?></span></div>
              </div>
              <div class="p-3 rounded-3 bg-light border">
                <div class="text-muted small">Recebido em</div>
                <div class="fw-semibold"><?= htmlspecialchars($criadoEmFormatado, ENT_QUOTES, 'UTF-8') ?></div>
              </div>
              <div class="p-3 rounded-3 bg-light border">
                <div class="text-muted small">Localização</div>
                <div class="fw-semibold"><?= (string) ($bandeira ?? '') ?> <?= htmlspecialchars((string) ($cidade ?? '-'), ENT_QUOTES, 'UTF-8') ?>, <?= htmlspecialchars((string) ($pais ?? '-'), ENT_QUOTES, 'UTF-8') ?></div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="row g-3 mb-4">
      <div class="col-lg-12">
        <div class="card border-0 shadow-sm h-100">
          <div class="card-header bg-white border-0 pt-3 px-3">
            <h5 class="mb-0"><i class="bi bi-file-earmark-text me-2"></i>Acordos de pagamento</h5>
          </div>
          <div class="card-body pt-0">
            <div id="acordoEmailFeedback" class="alert d-none"></div>
            <?php if (empty($acordos)): ?>
              <div class="alert alert-light border mb-0">
                <i class="bi bi-info-circle me-1"></i>Nenhum acordo gerado para esta pré-inscrição.
              </div>
            <?php else: ?>
              <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                  <thead>
                    <tr>
                      <th>#</th>
                      <th>Tipo</th>
                      <th>Plano</th>
                      <th>Entrada</th>
                      <th>Parcelas</th>
                      <th>Valor demais</th>
                      <th>Desconto</th>
                      <th>Status</th>
                      <th>Criado em</th>
                      <th>Link</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($acordos as $acordo): ?>
                      <?php
                        $acordoToken = (string) ($acordo['token'] ?? '');
                        $acordoUtilizado = (int) ($acordo['utilizado'] ?? 0) === 1;
                        $acordoData = (string) ($acordo['created_at'] ?? '');
                        $acordoTipo = (int) ($acordo['tipo'] ?? 1);
                        $acordoTipos = [1 => 'Padrão', 2 => 'À vista', 3 => 'Entrada + parcelas'];
                      ?>
                      <tr>
                        <td><?= (int) ($acordo['id'] ?? 0) ?></td>
                        <td><span class="badge bg-light text-dark"><?= htmlspecialchars($acordoTipos[$acordoTipo] ?? 'Tipo ' . $acordoTipo, ENT_QUOTES, 'UTF-8') ?></span></td>
                        <td><?= htmlspecialchars((string) ($acordo['plano_descricao'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                        <td>R$ <?= number_format((float) ($acordo['valor_entrada'] ?? 0), 2, ',', '.') ?></td>
                        <td><?= (int) ($acordo['total_parcelas'] ?? 1) ?>x</td>
                        <td>R$ <?= number_format((float) ($acordo['valor_demais_parcelas'] ?? 0), 2, ',', '.') ?></td>
                        <td>R$ <?= number_format((float) ($acordo['desconto'] ?? 0), 2, ',', '.') ?></td>
                        <td>
                          <?php if ($acordoUtilizado): ?>
                            <span class="badge bg-success">Utilizado</span>
                          <?php else: ?>
                            <span class="badge bg-warning text-dark">Pendente</span>
                          <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($acordoData !== '' ? date('d/m/Y H:i', strtotime($acordoData)) : '-', ENT_QUOTES, 'UTF-8') ?></td>
                        <td>
                          <?php if ($acordoToken !== ''): ?>
                            <?php
                              $linkFinanceiro = '/financeiro/' . $acordoToken;
                            ?>
                            <div class="d-flex gap-1 flex-wrap align-items-center">
                              <button type="button" class="btn btn-sm btn-outline-primary btn-copiar-link"
                                      data-link="<?= htmlspecialchars($linkFinanceiro, ENT_QUOTES, 'UTF-8') ?>"
                                      title="Copiar link do Portal Financeiro">
                                <i class="bi bi-link-45deg me-1"></i>Copiar
                              </button>
                              <button type="button" class="btn btn-sm btn-outline-danger btn-enviar-email"
                                      data-acordo-id="<?= (int) ($acordo['id'] ?? 0) ?>"
                                      data-pre-id="<?= $preId ?>"
                                      title="Enviar link por e-mail">
                                <i class="bi bi-envelope me-1"></i>Email
                              </button>
                            </div>
                          <?php else: ?>
                            -
                          <?php endif; ?>
                        </td>
                      </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>

    <div class="row g-3">
      <div class="col-lg-7">
        <div class="card border-0 shadow-sm h-100">
          <div class="card-header bg-white border-0 pt-3 px-3">
            <div class="d-flex align-items-center justify-content-between">
              <h5 class="mb-0"><i class="bi bi-chat-dots me-2"></i>Comentários</h5>
              <span class="badge bg-secondary"><?= $comentariosTotal ?></span>
            </div>
          </div>
          <div class="card-body pt-0">
            <?php if (empty($comentarios)): ?>
              <div class="alert alert-info mb-0">
                <i class="bi bi-chat-left-text me-1"></i>Nenhum comentário ainda.
              </div>
            <?php else: ?>
              <div class="list-group list-group-flush">
                <?php foreach ($comentarios as $comentario): ?>
                  <?php
                    $comentarioTexto = (string) ($comentario['comentario'] ?? '');
                    $comentarioData = (string) ($comentario['created_at'] ?? '');
                    $comentarioDataFormatada = $comentarioData !== '' ? date('d/m/Y H:i', strtotime($comentarioData)) : '-';
                  ?>
                  <div class="list-group-item px-0">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                      <strong class="small text-muted">#<?= (int) ($comentario['id'] ?? 0) ?></strong>
                      <small class="text-muted"><?= htmlspecialchars($comentarioDataFormatada, ENT_QUOTES, 'UTF-8') ?></small>
                    </div>
                    <div><?= nl2br(htmlspecialchars($comentarioTexto, ENT_QUOTES, 'UTF-8')) ?></div>
                  </div>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>
          </div>
        </div>
      </div>
      <div class="col-lg-5">
        <div class="card border-0 shadow-sm h-100">
          <div class="card-header bg-white border-0 pt-3 px-3">
            <h5 class="mb-0"><i class="bi bi-plus-circle me-2"></i>Adicionar comentário</h5>
          </div>
          <div class="card-body">
            <form method="post" action="/admin/preinscricao/comentario" class="d-grid gap-3">
              <input type="hidden" name="pre_id" value="<?= $preId ?>">
              <div>
                <textarea class="form-control" name="comentario" rows="5" maxlength="100" required placeholder="Escreva um comentário..."></textarea>
              </div>
              <div>
                <button class="btn btn-success" type="submit"><i class="bi bi-send me-1"></i>Salvar comentário</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<?php if (!empty($planos)): ?>
<div class="modal fade" id="modalAcordo" tabindex="-1" aria-labelledby="modalAcordoLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <form id="formAcordo">
        <div class="modal-header">
          <h5 class="modal-title" id="modalAcordoLabel"><i class="bi bi-file-earmark-text me-2"></i>Gerar Acordo</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
        </div>
        <div class="modal-body">
          <div class="row g-3">
            <div class="col-md-4">
              <div class="mb-3">
                <label class="form-label">Nome</label>
                <input type="text" class="form-control" value="<?= htmlspecialchars((string) ($pre['nome'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" readonly>
              </div>
              <div class="mb-3">
                <label class="form-label">E-mail</label>
                <input type="text" class="form-control" value="<?= htmlspecialchars((string) ($pre['email'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" readonly>
              </div>
              <div class="mb-3">
                <label class="form-label">Telefone</label>
                <input type="text" class="form-control" value="<?= htmlspecialchars(\App\Helpers\WhatsAppHelper::format((string) ($pre['whatsapp'] ?? '')), ENT_QUOTES, 'UTF-8') ?>" readonly>
              </div>
              <div class="mb-3">
                <label class="form-label">Curso</label>
                <input type="text" class="form-control" value="<?= htmlspecialchars((string) ($pre['curso_nome'] ?? '-'), ENT_QUOTES, 'UTF-8') ?>" readonly>
              </div>
            </div>
            <div class="col-md-4">
              <div class="mb-3">
                <label class="form-label">CPF <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="acordoCpf" name="cpf" maxlength="14" required placeholder="000.000.000-00">
              </div>
              <div class="mb-3">
                <label class="form-label">Tipo do acordo</label>
                <select class="form-select" name="tipo">
                  <option value="2" selected>À vista</option>
                  <option value="3">Entrada + parcelas</option>
                </select>
              </div>
              <div class="mb-3">
                <label class="form-label">Plano de pagamento <span class="text-danger">*</span></label>
                <select class="form-select" id="acordoPlano" name="id_curso_pagamento" required>
                  <option value="">Selecione...</option>
                  <?php foreach ($planos as $plano): ?>
                    <option value="<?= (int) ($plano['id'] ?? 0) ?>"
                            data-valor="<?= (float) ($plano['valor'] ?? 0) ?>"
                            data-parcelas="<?= (int) ($plano['parcelas'] ?? 1) ?>">
                      <?= htmlspecialchars((string) ($plano['descricao'] ?? '-'), ENT_QUOTES, 'UTF-8') ?>
                      — R$ <?= number_format((float) ($plano['valor'] ?? 0), 2, ',', '.') ?>
                      (<?= (int) ($plano['parcelas'] ?? 1) ?>x)
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="row g-2">
                <div class="col-6">
                  <label class="form-label">Valor de entrada</label>
                  <input type="text" class="form-control" id="acordoValorEntrada" name="valor_entrada" required placeholder="0,00">
                </div>
                <div class="col-6">
                  <label class="form-label">Vencimento da entrada</label>
                  <input type="date" class="form-control" id="acordoVencimentoEntrada" name="data_vencimento_entrada">
                </div>
                <div class="col-6">
                  <label class="form-label">Total de parcelas</label>
                  <input type="number" class="form-control" id="acordoTotalParcelas" name="total_parcelas" min="1" value="1" required>
                </div>
                <div class="col-6">
                  <label class="form-label">Valor demais parcelas</label>
                  <input type="text" class="form-control" id="acordoValorDemais" name="valor_demais_parcelas" placeholder="0,00">
                </div>
                <div class="col-6">
                  <label class="form-label">Desconto</label>
                  <input type="text" class="form-control" id="acordoDesconto" name="desconto" placeholder="0,00" value="0,00">
                </div>
                <div class="col-6">
                  <label class="form-label">Tipo do desconto</label>
                  <select class="form-select" name="tipo_desconto">
                    <?php foreach (['ALUNO', 'CONVENIO', 'BOLSA', 'CAMPANHA', 'NEGOCIACAO', 'OUTRO'] as $tipo): ?>
                      <option value="<?= $tipo ?>" <?= $tipo === 'NEGOCIACAO' ? 'selected' : '' ?>><?= $tipo ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
              </div>
            </div>
            <div class="col-md-4">
              <label class="form-label">Plano selecionado</label>
              <div class="border rounded-3 p-3 mb-3" id="acordoPlanoResumo" style="background:#f8f9fa;">
                <div class="text-muted small">Nenhum plano selecionado.</div>
              </div>
              <div class="mb-3">
                <label class="form-label">Motivo</label>
                <input type="text" class="form-control" name="motivo" maxlength="150" placeholder="Motivo da negociação">
              </div>
              <div class="mb-3">
                <label class="form-label">Observação</label>
                <textarea class="form-control" name="observacao" rows="3" placeholder="Observações adicionais"></textarea>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <span id="acordoMsg" class="text-success small me-auto"></span>
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-primary" id="acordoBtnSalvar"><i class="bi bi-check2 me-1"></i>Salvar acordo</button>
        </div>
      </form>
    </div>
  </div>
</div>
<?php endif; ?>

<script>
document.addEventListener('DOMContentLoaded', function () {
  var form = document.getElementById('formAcordo');
  if (!form) {
    return;
  }

  var plano = document.getElementById('acordoPlano');
  var valorEntrada = document.getElementById('acordoValorEntrada');
  var totalParcelas = document.getElementById('acordoTotalParcelas');
  var valorDemais = document.getElementById('acordoValorDemais');
  var desconto = document.getElementById('acordoDesconto');
  var msg = document.getElementById('acordoMsg');
  var btn = document.getElementById('acordoBtnSalvar');

  var planosResumo = <?= json_encode(array_map(
      static fn (array $plano): array => [
          'id' => (int) ($plano['id'] ?? 0),
          'descricao' => (string) ($plano['descricao'] ?? ''),
          'tipo' => (string) ($plano['tipo'] ?? ''),
          'parcelas' => (int) ($plano['parcelas'] ?? 1),
          'valor' => (float) ($plano['valor'] ?? 0),
      ],
      $planos
  ), JSON_UNESCAPED_UNICODE) ?>;

  var resumoPlano = document.getElementById('acordoPlanoResumo');

  function atualizarResumoPlano() {
    if (!resumoPlano) {
      return;
    }
    var id = parseInt(plano.value || '0', 10);
    var encontrado = null;
    planosResumo.forEach(function (p) {
      if (p.id === id) encontrado = p;
    });
    if (!encontrado) {
      resumoPlano.innerHTML = '<div class="text-muted small">Nenhum plano selecionado.</div>';
      return;
    }
    var tipoBadge = encontrado.tipo ? '<span class="badge bg-secondary">' + encontrado.tipo + '</span>' : '';
    resumoPlano.innerHTML =
      '<div class="fw-semibold mb-1">' + encontrado.descricao + '</div>'
      + '<div class="small text-muted mb-1">' + tipoBadge + '</div>'
      + '<div class="small">Parcelas: <strong>' + encontrado.parcelas + 'x</strong></div>'
      + '<div class="small">Valor do plano: <strong>R$ ' + encontrado.valor.toLocaleString("pt-BR", { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + '</strong></div>';
  }

  function toFloat(str) {
    if (typeof str !== 'string') {
      return 0;
    }
    return parseFloat(str.replace(/\./g, '').replace(',', '.')) || 0;
  }

  function toBRL(num) {
    return num.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
  }

  plano.addEventListener('change', function () {
    var opt = plano.options[plano.selectedIndex];
    if (!opt || opt.value === '') {
      atualizarResumoPlano();
      return;
    }
    var planoParcelas = parseInt(opt.getAttribute('data-parcelas') || '1', 10) || 1;
    var planoValor = toFloat(opt.getAttribute('data-valor'));
    totalParcelas.value = planoParcelas;
    if (planoParcelas === 1) {
      valorEntrada.value = toBRL(planoValor);
      valorDemais.value = '0,00';
    } else {
      var valorParcela = planoParcelas > 0 ? planoValor / planoParcelas : planoValor;
      valorEntrada.value = toBRL(valorParcela);
      valorDemais.value = toBRL(valorParcela);
    }
    atualizarResumoPlano();
  });

  atualizarResumoPlano();

  form.addEventListener('submit', function (e) {
    e.preventDefault();
    if (msg) msg.textContent = '';
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Salvando...';

    var data = new URLSearchParams(new FormData(form));
    data.set('pre_id', <?= $preId ?>);

    fetch('/admin/preinscricao/acordo', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: data.toString()
    })
      .then(function (res) { return res.json(); })
      .then(function (res) {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-check2 me-1"></i>Salvar acordo';
        if (res.sucesso) {
          if (msg) {
            msg.textContent = 'Acordo salvo!';
            msg.className = 'text-success small me-auto';
          }
          form.reset();
          var modalEl = document.getElementById('modalAcordo');
          var modal = bootstrap.Modal.getInstance(modalEl);
          if (modal) modal.hide();
          setTimeout(function () { window.location.reload(); }, 600);
        } else {
          if (msg) {
            msg.textContent = res.erro || 'Erro ao salvar acordo.';
            msg.className = 'text-danger small me-auto';
          }
        }
      })
      .catch(function () {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-check2 me-1"></i>Salvar acordo';
        if (msg) {
          msg.textContent = 'Erro inesperado. Tente novamente.';
          msg.className = 'text-danger small me-auto';
        }
      });
  });

  var copiarBtns = document.querySelectorAll('.btn-copiar-link');
  copiarBtns.forEach(function (el) {
    el.addEventListener('click', function () {
      var link = window.location.origin + el.getAttribute('data-link');
      if (navigator.clipboard) {
        navigator.clipboard.writeText(link).then(function () {
          var original = el.innerHTML;
          el.innerHTML = '<i class="bi bi-check2 me-1"></i>Copiado';
          setTimeout(function () { el.innerHTML = original; }, 1500);
        });
      }
    });
  });

  var emailFeedback = document.getElementById('acordoEmailFeedback');
  function showEmailFeedback(mensagem, tipo) {
    if (!emailFeedback) {
      return;
    }
    emailFeedback.textContent = mensagem;
    emailFeedback.className = 'alert mb-3 ' + (tipo === 'danger' ? 'alert-danger' : 'alert-success');
  }

  document.querySelectorAll('.btn-enviar-email').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var acordoId = btn.getAttribute('data-acordo-id');
      var original = btn.innerHTML;
      btn.disabled = true;
      btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Enviando...';

      var body = new URLSearchParams();
      body.set('acordo_id', acordoId);

      fetch('/admin/preinscricao/acordo/enviar-email', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: body.toString()
      })
        .then(function (res) {
          return res.json().then(function (data) { return { ok: res.ok, data: data }; });
        })
        .then(function (result) {
          btn.disabled = false;
          btn.innerHTML = original;
          if (result.ok && result.data.sucesso) {
            showEmailFeedback(result.data.mensagem || 'E-mail enviado com sucesso.', 'success');
          } else {
            showEmailFeedback(result.data.erro || 'O e-mail não foi enviado.', 'danger');
          }
        })
        .catch(function () {
          btn.disabled = false;
          btn.innerHTML = original;
          showEmailFeedback('Erro inesperado ao enviar o e-mail. Tente novamente.', 'danger');
        });
    });
  });
});
</script>
