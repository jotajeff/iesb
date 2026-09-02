<?php
  $alunoSelecionado = is_array($aluno ?? null) ? $aluno : null;
  $turmasLista = is_array($turmas ?? null) ? $turmas : [];
  $matriculaLista = is_array($matricula ?? null) ? $matricula : [];
  $turmasMatriculadas = is_array($turmasMatriculadas ?? null) ? $turmasMatriculadas : [];
  $statusOptions = ['inscrito', 'matriculado', 'ativo', 'concluido', 'cancelado', 'inadimplente'];
?>

<section class="container py-4">
  <div class="bg-white border rounded-3 p-4 shadow-sm">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
      <div>
        <div class="text-uppercase text-muted small fw-semibold mb-1">Matrícula de alunos</div>
        <h4 class="mb-0"><i class="bi bi-journal-plus me-2"></i>Matricular aluno</h4>
      </div>
      <a class="btn btn-outline-secondary btn-sm" href="/admin/alunos">
        <i class="bi bi-arrow-left-short me-1"></i>Voltar
      </a>
    </div>

    <?php if (!$alunoSelecionado): ?>
      <div class="alert alert-warning mb-0">
        <i class="bi bi-exclamation-triangle me-2"></i>
        Nenhum aluno válido foi carregado. Retorne à lista e selecione novamente.
      </div>
    <?php else: ?>
      <div class="alert alert-info">
        <i class="bi bi-person me-2"></i>
        Aluno: <strong><?= htmlspecialchars((string) ($alunoSelecionado['nome'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></strong>
      </div>
      <?php if (!empty($linkFinanceiroMatricula ?? '')): ?>
        <div class="alert alert-success" role="status">
          <div class="fw-semibold mb-2"><i class="bi bi-link-45deg me-1"></i>Link financeiro da opção 2</div>
          <div class="input-group">
            <input type="text" class="form-control" id="linkFinanceiroMatricula" value="<?= htmlspecialchars((string) $linkFinanceiroMatricula, ENT_QUOTES, 'UTF-8') ?>" readonly>
            <button type="button" class="btn btn-outline-success" id="copiarLinkFinanceiro"><i class="bi bi-clipboard me-1"></i>Copiar</button>
          </div>
          <div class="form-text">Copie este link e envie ao aluno pelo WhatsApp para ele escolher cartão e recorrência.</div>
        </div>
      <?php endif; ?>

      <form action="/admin/alunos/matricular" method="post" class="needs-validation" id="formMatricula" novalidate>
        <input type="hidden" name="id_aluno" value="<?= (int) ($alunoSelecionado['id'] ?? 0) ?>">

        <div class="row g-3">
          <div class="col-md-6">
            <label for="id_turma" class="form-label">Turma <span class="text-danger">*</span></label>
            <select class="form-select" id="id_turma" name="id_turma" required>
              <option value="">Selecione uma turma</option>
              <?php foreach ($turmasLista as $turma): ?>
                <?php $jaMatriculado = in_array((int) ($turma['id'] ?? 0), $turmasMatriculadas, true); ?>
                <option value="<?= (int) ($turma['id'] ?? 0) ?>" data-curso="<?= (int) ($turma['id_curso'] ?? 0) ?>"<?= $jaMatriculado ? ' disabled' : '' ?>><?= htmlspecialchars((string) ($turma['nome'] ?? '-') . ' - ' . (string) ($turma['curso_nome'] ?? ''), ENT_QUOTES, 'UTF-8') ?><?= $jaMatriculado ? ' (já matriculado)' : '' ?></option>
              <?php endforeach; ?>
            </select>
            <div class="invalid-feedback">Selecione a turma.</div>
          </div>

          <div class="col-md-3">
            <label for="status" class="form-label">Status</label>
            <select class="form-select" id="status" name="status">
              <?php foreach ($statusOptions as $opt): ?>
                <option value="<?= $opt ?>"<?= $opt === 'matriculado' ? ' selected' : '' ?>><?= ucfirst($opt) ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="col-md-3 d-flex align-items-end">
            <button type="submit" class="btn btn-success"><i class="bi bi-check-lg me-1"></i>Matricular</button>
          </div>
        </div>
        <hr class="my-4">
        <h5 class="mb-3"><i class="bi bi-cash-coin me-2"></i>Financeiro da matrícula</h5>
        <p class="text-muted small">A primeira parcela será lançada como paga. Escolha como as parcelas restantes serão tratadas.</p>
        <div class="row g-3">
          <div class="col-12">
            <label for="tipo_financeiro" class="form-label">Forma de tratamento financeiro <span class="text-danger">*</span>
              <button type="button" class="btn btn-sm p-0 border-0 bg-transparent" data-bs-toggle="modal" data-bs-target="#modalFormaFinanceira" title="O que cada opção significa?" style="color: #0d6efd;">
                <i class="bi bi-question-circle"></i>
              </button>
            </label>
            <select class="form-select" id="tipo_financeiro" name="tipo_financeiro" required>
              <option value="1">Opção 1 — Gerar cobranças independentes no Asaas</option>
              <option value="2">Opção 2 — Enviar link para o aluno escolher cartão e recorrência</option>
              <option value="3">Opção 3 — Acordo direto no Asaas (lançamento manual posterior)</option>
            </select>
            <div class="form-text" id="tipoFinanceiroAjuda">As parcelas restantes serão criadas e cobradas individualmente no Asaas.</div>
          </div>
          <div class="col-md-6">
            <label for="id_curso_pagamento" class="form-label">Plano de pagamento <span class="text-danger">*</span></label>
            <select class="form-select" id="id_curso_pagamento" name="id_curso_pagamento" required>
              <option value="">Selecione um plano</option>
              <?php foreach (($planos ?? []) as $plano): ?>
                <option value="<?= (int) ($plano['id'] ?? 0) ?>" data-curso="<?= (int) ($plano['id_curso'] ?? 0) ?>" data-parcelas="<?= (int) ($plano['parcelas'] ?? 1) ?>" data-valor="<?= (float) ($plano['valor'] ?? 0) ?>">
                  <?= htmlspecialchars((string) ($plano['descricao'] ?? '-'), ENT_QUOTES, 'UTF-8') ?> — R$ <?= number_format((float) ($plano['valor'] ?? 0), 2, ',', '.') ?> (<?= (int) ($plano['parcelas'] ?? 1) ?>x)
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-3">
            <label for="data_vencimento" class="form-label">Data da 1ª parcela</label>
            <input type="date" class="form-control" id="data_vencimento" name="data_vencimento" value="<?= date('Y-m-d') ?>">
          </div>
          <div class="col-md-3">
            <label for="total_parcelas" class="form-label">Total de parcelas</label>
            <input type="number" class="form-control" id="total_parcelas" name="total_parcelas" min="1" max="120" value="1" required>
          </div>
          <div class="col-md-3">
            <label for="valor_primeira" class="form-label">1ª parcela paga (R$)</label>
            <input type="text" class="form-control" id="valor_primeira" name="valor_primeira" placeholder="0,00" required>
          </div>
          <div class="col-md-3">
            <label for="valor_demais" class="form-label">Demais parcelas (R$)</label>
            <input type="text" class="form-control" id="valor_demais" name="valor_demais" placeholder="0,00">
          </div>
        </div>
      </form>

      <?php if (!empty($matriculaLista)): ?>
        <hr>
        <h5 class="mb-3"><i class="bi bi-list-ul me-2"></i>Matrículas existentes</h5>
        <div class="table-responsive">
          <table class="table table-sm align-middle">
            <thead>
              <tr>
                <th>#</th>
                <th>Turma</th>
                <th>Data</th>
                <th>Status</th>
                <th>Ação</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($matriculaLista as $mat): ?>
                <tr>
                  <td><?= (int) ($mat['id'] ?? 0) ?></td>
                  <td><?= htmlspecialchars((string) ($mat['turma_nome'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                  <td><?php
                    $raw = (string) ($mat['data_matricula'] ?? '');
                    $dt = $raw !== '' ? \DateTime::createFromFormat('Y-m-d H:i:s', $raw) ?: \DateTime::createFromFormat('Y-m-d', $raw) : false;
                    echo htmlspecialchars($dt ? $dt->format('d/m/Y H:i') : ($raw ?: '-'), ENT_QUOTES, 'UTF-8');
                  ?></td>
                  <td>
                    <?php
                      $statusClass = match ($mat['status'] ?? '') {
                        'ativo' => 'bg-success',
                        'concluido' => 'bg-primary',
                        'cancelado' => 'bg-danger',
                        'inadimplente' => 'bg-warning text-dark',
                        'inscrito' => 'bg-info',
                        default => 'bg-secondary',
                      };
                    ?>
                    <span class="badge <?= $statusClass ?>"><?= htmlspecialchars((string) ($mat['status'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></span>
                  </td>
                  <td>
                    <?php if ((string) ($mat['status'] ?? '') !== 'cancelado'): ?>
                      <button type="button" class="btn btn-sm btn-outline-danger btn-cancelar-matricula"
                              data-bs-toggle="modal" data-bs-target="#modalCancelarMatricula"
                              data-id-matricula="<?= (int) ($mat['id'] ?? 0) ?>"
                              data-id-aluno="<?= (int) ($alunoSelecionado['id'] ?? 0) ?>"
                              data-turma="<?= htmlspecialchars((string) ($mat['turma_nome'] ?? '-'), ENT_QUOTES, 'UTF-8') ?>">
                        <i class="bi bi-trash me-1"></i>Cancelar
                      </button>
                    <?php else: ?>
                      <span class="text-muted small">Cancelada</span>
                    <?php endif; ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    <?php endif; ?>
  </div>
</section>

<div class="modal fade" id="modalCancelarMatricula" tabindex="-1" aria-labelledby="modalCancelarMatriculaLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form method="post" action="/admin/alunos/matricula/cancelar" id="formCancelarMatricula">
        <input type="hidden" name="id_aluno" id="cancelarIdAluno">
        <input type="hidden" name="id_matricula" id="cancelarIdMatricula">
        <div class="modal-header bg-danger-subtle">
          <h5 class="modal-title" id="modalCancelarMatriculaLabel"><i class="bi bi-shield-exclamation me-2"></i>Cancelar matrícula</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
        </div>
        <div class="modal-body">
          <p>Você está cancelando a matrícula da turma <strong id="cancelarTurma">-</strong>.</p>
          <p class="small text-muted mb-3">O cadastro do aluno será preservado. As parcelas vinculadas serão inativadas e permanecerão no histórico.</p>
          <label for="senhaConfirmacao" class="form-label">Digite sua senha para confirmar</label>
          <input type="password" class="form-control" name="senha_confirmacao" id="senhaConfirmacao" autocomplete="current-password" required>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Voltar</button>
          <button type="submit" class="btn btn-danger"><i class="bi bi-trash me-1"></i>Confirmar cancelamento</button>
        </div>
      </form>
    </div>
  </div>
</div>

<div class="modal fade" id="modalConfirmarMatricula" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <div class="modal-header bg-warning-subtle">
        <h5 class="modal-title"><i class="bi bi-exclamation-triangle me-2"></i>Confirmar matrícula</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
      </div>
      <div class="modal-body">
        <p class="text-muted small mb-3">Revise os dados abaixo antes de efetivar a matrícula:</p>
        <dl class="row mb-0">
          <dt class="col-sm-5 text-muted">Aluno</dt>
          <dd class="col-sm-7" id="cfAluno">-</dd>
          <dt class="col-sm-5 text-muted">Turma</dt>
          <dd class="col-sm-7" id="cfTurma">-</dd>
          <dt class="col-sm-5 text-muted">Status</dt>
          <dd class="col-sm-7" id="cfStatus">-</dd>
          <dt class="col-sm-5 text-muted">Plano de pagamento</dt>
          <dd class="col-sm-7" id="cfPlano">-</dd>
          <dt class="col-sm-5 text-muted">Data da 1ª parcela</dt>
          <dd class="col-sm-7" id="cfData">-</dd>
          <dt class="col-sm-5 text-muted">Total de parcelas</dt>
          <dd class="col-sm-7" id="cfTotal">-</dd>
          <dt class="col-sm-5 text-muted">1ª parcela (paga)</dt>
          <dd class="col-sm-7" id="cfPrimeira">-</dd>
          <dt class="col-sm-5 text-muted">Demais parcelas</dt>
          <dd class="col-sm-7" id="cfDemais">-</dd>
        </dl>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal"><i class="bi bi-x me-1"></i>Cancelar</button>
        <button type="button" class="btn btn-success" id="btnConfirmarMatricula"><i class="bi bi-check-lg me-1"></i>Confirmar matrícula</button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="modalFormaFinanceira" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-primary-subtle">
        <h5 class="modal-title"><i class="bi bi-question-circle me-2"></i>Forma de tratamento financeiro</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
      </div>
      <div class="modal-body">
        <p class="text-muted small mb-3">Entenda o que cada opção faz com as parcelas após a primeira (paga):</p>
        <div class="d-flex gap-3 mb-3">
          <span class="badge bg-primary rounded-pill align-self-start mt-1">Opção 1</span>
          <span>Primeira paga, demais geradas automaticamente no painel financeiro do aluno.</span>
        </div>
        <div class="d-flex gap-3 mb-3">
          <span class="badge bg-primary rounded-pill align-self-start mt-1">Opção 2</span>
          <span>Primeira paga, demais, a forma de pagamento será decidida pelo aluno. Via link enviado para o mesmo.</span>
        </div>
        <div class="d-flex gap-3">
          <span class="badge bg-primary rounded-pill align-self-start mt-1">Opção 3</span>
          <span>Controle de parcelas fica a cargo do admin / IESB.</span>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Entendi</button>
      </div>
    </div>
  </div>
</div>

<script>
(function () {
  'use strict'
  var forms = document.querySelectorAll('.needs-validation')
  Array.prototype.slice.call(forms)
    .forEach(function (form) {
      form.addEventListener('submit', function (event) {
        if (!form.checkValidity()) {
          event.preventDefault()
          event.stopPropagation()
        }
        form.classList.add('was-validated')
      }, false)
    })
})()
</script>
<script>
document.addEventListener('DOMContentLoaded', function () {
  var plano = document.getElementById('id_curso_pagamento');
  var total = document.getElementById('total_parcelas');
  var primeira = document.getElementById('valor_primeira');
  var demais = document.getElementById('valor_demais');
  var tipoFinanceiro = document.getElementById('tipo_financeiro');
  var tipoFinanceiroAjuda = document.getElementById('tipoFinanceiroAjuda');
  var turma = document.getElementById('id_turma');
  if (!plano) return;
  if (tipoFinanceiro && tipoFinanceiroAjuda) {
    tipoFinanceiro.addEventListener('change', function () {
      var textos = {
        '1': 'As parcelas restantes serão criadas e cobradas individualmente no Asaas.',
        '2': 'O aluno receberá um link para escolher cartão e autorizar a recorrência das parcelas restantes.',
        '3': 'As parcelas ficarão registradas localmente; a conciliação com o acordo feito no Asaas será manual.'
      };
      tipoFinanceiroAjuda.textContent = textos[tipoFinanceiro.value] || '';
    });
  }
  function filtrarPlanos() {
    var optTurma = turma && turma.options[turma.selectedIndex];
    var curso = optTurma ? optTurma.getAttribute('data-curso') : '';
    Array.prototype.forEach.call(plano.options, function (opt) { if (opt.value) opt.hidden = curso !== '' && opt.getAttribute('data-curso') !== curso; });
    if (plano.selectedOptions.length && plano.selectedOptions[0].hidden) plano.value = '';
  }
  if (turma) turma.addEventListener('change', filtrarPlanos);
  filtrarPlanos();
  plano.addEventListener('change', function () {
    var opt = plano.options[plano.selectedIndex];
    var parcelas = parseInt(opt.getAttribute('data-parcelas') || '1', 10);
    var valor = parseFloat(opt.getAttribute('data-valor') || '0');
    total.value = parcelas;
    var mensal = parcelas > 0 ? valor / parcelas : valor;
    primeira.value = mensal.toLocaleString('pt-BR', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    demais.value = primeira.value;
  });
});
</script>
<script>
document.addEventListener('DOMContentLoaded', function () {
  var linkFinanceiro = document.getElementById('linkFinanceiroMatricula');
  var copiarLink = document.getElementById('copiarLinkFinanceiro');
  if (linkFinanceiro && copiarLink) {
    copiarLink.addEventListener('click', function () {
      var textoOriginal = copiarLink.innerHTML;
      var concluir = function () {
        copiarLink.innerHTML = '<i class="bi bi-check-lg me-1"></i>Copiado';
        window.setTimeout(function () { copiarLink.innerHTML = textoOriginal; }, 1800);
      };
      if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(linkFinanceiro.value).then(concluir).catch(function () {
          linkFinanceiro.select();
          document.execCommand('copy');
          concluir();
        });
      } else {
        linkFinanceiro.select();
        document.execCommand('copy');
        concluir();
      }
    });
  }

  document.querySelectorAll('.btn-cancelar-matricula').forEach(function (btn) {
    btn.addEventListener('click', function () {
      document.getElementById('cancelarIdAluno').value = btn.getAttribute('data-id-aluno') || '';
      document.getElementById('cancelarIdMatricula').value = btn.getAttribute('data-id-matricula') || '';
      document.getElementById('cancelarTurma').textContent = btn.getAttribute('data-turma') || '-';
      document.getElementById('senhaConfirmacao').value = '';
    });
  });

  document.getElementById('formCancelarMatricula').addEventListener('submit', function (event) {
    if (!window.confirm('Confirma o cancelamento desta matrícula?')) {
      event.preventDefault();
    }
  });

  var form = document.getElementById('formMatricula');
  if (!form) return;
  var confirmado = false;

  form.addEventListener('submit', function (event) {
    if (confirmado) return;
    if (!form.checkValidity()) return;
    event.preventDefault();

    var sTurma = document.getElementById('id_turma');
    var sStatus = document.getElementById('status');
    var sPlano = document.getElementById('id_curso_pagamento');
    var sAluno = document.querySelector('.alert-info strong');

    document.getElementById('cfAluno').textContent = sAluno ? sAluno.textContent.trim() : '-';
    document.getElementById('cfTurma').textContent = (sTurma && sTurma.selectedIndex >= 0) ? sTurma.options[sTurma.selectedIndex].textContent.trim() : '-';
    document.getElementById('cfStatus').textContent = (sStatus && sStatus.selectedIndex >= 0) ? sStatus.options[sStatus.selectedIndex].textContent.trim() : '-';
    document.getElementById('cfPlano').textContent = (sPlano && sPlano.selectedIndex >= 0) ? sPlano.options[sPlano.selectedIndex].textContent.trim() : '-';
    document.getElementById('cfData').textContent = (document.getElementById('data_vencimento') ? document.getElementById('data_vencimento').value : '') || '-';
    document.getElementById('cfTotal').textContent = (document.getElementById('total_parcelas') ? document.getElementById('total_parcelas').value : '') || '-';
    document.getElementById('cfPrimeira').textContent = (document.getElementById('valor_primeira') ? document.getElementById('valor_primeira').value : '') || '-';
    document.getElementById('cfDemais').textContent = (document.getElementById('valor_demais') ? document.getElementById('valor_demais').value : '') || '-';

    bootstrap.Modal.getOrCreateInstance(document.getElementById('modalConfirmarMatricula')).show();
  });

  document.getElementById('btnConfirmarMatricula').addEventListener('click', function () {
    confirmado = true;
    var modal = bootstrap.Modal.getInstance(document.getElementById('modalConfirmarMatricula'));
    if (modal) modal.hide();
    form.submit();
  });
});
</script>
