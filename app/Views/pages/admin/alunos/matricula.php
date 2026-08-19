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

      <form action="/admin/alunos/matricular" method="post" class="needs-validation" novalidate>
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
        <p class="text-muted small">A primeira parcela será lançada como paga. Cada parcela restante será criada como uma cobrança independente no Asaas.</p>
        <div class="row g-3">
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
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    <?php endif; ?>
  </div>
</section>

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
  var turma = document.getElementById('id_turma');
  if (!plano) return;
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
