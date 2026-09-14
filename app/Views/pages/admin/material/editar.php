<?php
  $material = is_array($material ?? null) ? $material : [];
  $turmasView = is_array($turmas ?? null) ? $turmas : [];
  $disciplinasView = is_array($disciplinas ?? null) ? $disciplinas : [];
  $tipoMaterial = (string) ($material['tipo'] ?? '');
  $turmaAtual = (int) ($material['id_fk'] ?? 0);
  $disciplinaAtual = (int) ($material['id_disciplina'] ?? 0);
?>

<section class="container py-4">
  <div class="bg-white border rounded-3 p-4 shadow-sm">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
      <h4 class="mb-0"><i class="bi bi-pencil-square me-2"></i>Editar Material</h4>
      <a class="btn btn-outline-secondary btn-sm" href="/admin/material"><i class="bi bi-arrow-left me-1"></i>Voltar</a>
    </div>

    <form method="post" action="/admin/material/atualizar" class="row g-3">
      <input type="hidden" name="id" value="<?= (int) ($material['id'] ?? 0) ?>">

      <div class="col-12">
        <span class="badge <?= $tipoMaterial === 'video' ? 'bg-danger' : ($tipoMaterial === 'drive' ? 'bg-primary' : 'bg-secondary') ?>">
          <i class="bi <?= $tipoMaterial === 'video' ? 'bi-camera-reels' : 'bi-google' ?> me-1"></i>
          <?= htmlspecialchars($tipoMaterial ?: '-', ENT_QUOTES, 'UTF-8') ?>
        </span>
      </div>

      <div class="col-md-6">
        <label class="form-label">Turma <span class="text-danger">*</span></label>
        <select class="form-select" name="id_fk" id="materialTurma" required>
          <option value="">Selecione a turma</option>
          <?php foreach ($turmasView as $turma): ?>
            <?php
              $label = (string) ($turma['turma_nome'] ?? '-');
              if (trim((string) ($turma['curso_nome'] ?? '')) !== '') {
                $label .= ' — ' . (string) $turma['curso_nome'];
              }
            ?>
            <option value="<?= (int) ($turma['id'] ?? 0) ?>" <?= $turmaAtual === (int) ($turma['id'] ?? 0) ? 'selected' : '' ?>>
              <?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="col-md-6">
        <label class="form-label">Disciplina</label>
        <select class="form-select" name="id_disciplina" id="materialDisciplina">
          <option value="0" <?= $disciplinaAtual === 0 ? 'selected' : '' ?>>Secretaria (material geral da turma)</option>
          <?php foreach ($disciplinasView as $disciplina): ?>
            <option value="<?= (int) ($disciplina['id'] ?? 0) ?>" <?= $disciplinaAtual === (int) ($disciplina['id'] ?? 0) ? 'selected' : '' ?>>
              <?= htmlspecialchars((string) ($disciplina['disciplina_nome'] ?? '-'), ENT_QUOTES, 'UTF-8') ?>
            </option>
          <?php endforeach; ?>
        </select>
        <div class="form-text">Selecione a disciplina ou deixe em "Secretaria" para material geral da turma.</div>
      </div>

      <div class="col-md-6">
        <label class="form-label">Título <span class="text-danger">*</span></label>
        <input type="text" class="form-control" name="titulo" required maxlength="256" value="<?= htmlspecialchars((string) ($material['titulo'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
      </div>

      <?php if ($tipoMaterial === 'video'): ?>
        <div class="col-12">
          <label class="form-label">Link do vídeo</label>
          <input type="text" class="form-control" name="link" value="<?= htmlspecialchars((string) ($material['link'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
        </div>
      <?php else: ?>
        <div class="col-12">
          <label class="form-label">Link atual</label>
          <input type="text" class="form-control" value="<?= htmlspecialchars((string) ($material['link'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" disabled>
          <div class="form-text">O arquivo enviado ao Google Drive não pode ser alterado por aqui.</div>
        </div>
      <?php endif; ?>

      <div class="col-12">
        <button class="btn btn-success" type="submit"><i class="bi bi-check-lg me-1"></i>Salvar alterações</button>
      </div>
    </form>
  </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function () {
  var selTurma = document.getElementById('materialTurma');
  var selDisciplina = document.getElementById('materialDisciplina');
  if (!selTurma || !selDisciplina) return;

  selTurma.addEventListener('change', function () {
    var idTurma = selTurma.value;
    selDisciplina.innerHTML = '<option value="0">Secretaria (material geral da turma)</option>';
    if (!idTurma) return;

    fetch('/admin/material/ajax-disciplinas?id_turma=' + encodeURIComponent(idTurma))
      .then(function (r) { return r.json(); })
      .then(function (itens) {
        itens.forEach(function (d) {
          var opt = document.createElement('option');
          opt.value = d.id;
          opt.textContent = d.disciplina_nome;
          selDisciplina.appendChild(opt);
        });
      })
      .catch(function () {});
  });
});
</script>