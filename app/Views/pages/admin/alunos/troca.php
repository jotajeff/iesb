<?php
  $alunoData = is_array($aluno ?? null) ? $aluno : null;
  $matriculaData = is_array($matricula ?? null) ? $matricula : null;
  $turmasAtivas = is_array($turmasAtivas ?? null) ? $turmasAtivas : [];
?>

<section class="container py-4">
  <div class="bg-white border rounded-3 p-4 shadow-sm">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
      <h4 class="mb-0"><i class="bi bi-arrow-left-right me-2"></i>Trocar Turma</h4>
      <a class="btn btn-outline-secondary btn-sm" href="/admin/alunos/show?id=<?= (int) ($alunoData['id'] ?? 0) ?>">
        <i class="bi bi-arrow-left me-1"></i>Voltar
      </a>
    </div>

    <?php if (!$alunoData || !$matriculaData): ?>
      <div class="alert alert-warning mb-0">
        <i class="bi bi-exclamation-triangle me-2"></i>Dados inválidos. Retorne e tente novamente.
      </div>
    <?php else: ?>
      <div class="row g-4">
        <div class="col-md-6">
          <div class="card border">
            <div class="card-body">
              <h6 class="card-title text-muted small text-uppercase mb-3">
                <i class="bi bi-person me-1"></i>Aluno
              </h6>
              <h5 class="card-text mb-0"><?= htmlspecialchars((string) ($alunoData['nome'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></h5>
            </div>
          </div>
        </div>
        <div class="col-md-6">
          <div class="card border">
            <div class="card-body">
              <h6 class="card-title text-muted small text-uppercase mb-3">
                <i class="bi bi-book me-1"></i>Turma Atual
              </h6>
              <h5 class="card-text mb-0"><?= htmlspecialchars((string) ($matriculaData['turma_nome'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></h5>
              <?php if (!empty($matriculaData['curso_nome'])): ?>
                <small class="text-muted"><?= htmlspecialchars((string) ($matriculaData['curso_nome'] ?? ''), ENT_QUOTES, 'UTF-8') ?></small>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>

      <hr>

      <form action="/admin/alunos/trocar" method="post" class="needs-validation" novalidate>
        <input type="hidden" name="id_aluno" value="<?= (int) ($alunoData['id'] ?? 0) ?>">
        <input type="hidden" name="id_matricula" value="<?= (int) ($matriculaData['id'] ?? 0) ?>">

        <div class="row g-3">
          <div class="col-md-6">
            <label for="id_turma_destino" class="form-label">Nova Turma <span class="text-danger">*</span></label>
            <select class="form-select" id="id_turma_destino" name="id_turma_destino" required>
              <option value="">Selecione a turma de destino</option>
              <?php foreach ($turmasAtivas as $turma): ?>
                <?php
                  $selected = (int) ($turma['id'] ?? 0) === (int) ($matriculaData['id_turma'] ?? 0);
                  $label = htmlspecialchars(
                    (string) ($turma['curso_nome'] ?? '') . ' - ' . (string) ($turma['nome'] ?? ''),
                    ENT_QUOTES,
                    'UTF-8'
                  );
                ?>
                <option value="<?= (int) ($turma['id'] ?? 0) ?>"<?= $selected ? ' disabled' : '' ?>><?= $label ?><?= $selected ? ' (atual)' : '' ?></option>
              <?php endforeach; ?>
            </select>
            <div class="invalid-feedback">Selecione a nova turma.</div>
          </div>

          <div class="col-md-6">
            <label for="motivo" class="form-label">Motivo da Troca <span class="text-danger">*</span></label>
            <textarea class="form-control" id="motivo" name="motivo" rows="3" required placeholder="Ex: Alteração de horário, mudança de nível..."></textarea>
            <div class="invalid-feedback">Informe o motivo da troca.</div>
          </div>
        </div>

        <div class="d-flex justify-content-end gap-2 mt-4">
          <a class="btn btn-outline-secondary" href="/admin/alunos/show?id=<?= (int) ($alunoData['id'] ?? 0) ?>">Cancelar</a>
          <button type="submit" class="btn btn-info text-white">
            <i class="bi bi-arrow-left-right me-1"></i>Confirmar Troca
          </button>
        </div>
      </form>
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
