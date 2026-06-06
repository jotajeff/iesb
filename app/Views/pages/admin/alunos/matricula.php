<?php
  $alunoSelecionado = is_array($aluno ?? null) ? $aluno : null;
  $turmasLista = is_array($turmas ?? null) ? $turmas : [];
  $matriculasLista = is_array($matriculas ?? null) ? $matriculas : [];
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
                <option value="<?= (int) ($turma['id'] ?? 0) ?>"<?= $jaMatriculado ? ' disabled' : '' ?>><?= htmlspecialchars((string) ($turma['nome'] ?? '-') . ' - ' . (string) ($turma['curso_nome'] ?? ''), ENT_QUOTES, 'UTF-8') ?><?= $jaMatriculado ? ' (já matriculado)' : '' ?></option>
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
      </form>

      <?php if (!empty($matriculasLista)): ?>
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
              <?php foreach ($matriculasLista as $mat): ?>
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
