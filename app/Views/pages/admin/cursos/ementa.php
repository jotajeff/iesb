<?php
$disciplina = $disciplina ?? [];
$ementa = $ementa ?? null;
?>
<section class="container py-4">
  <div class="bg-white border rounded-3 p-4 shadow-sm">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
      <h4 class="mb-0"><i class="bi bi-file-text me-2"></i>Ementa — <?= htmlspecialchars((string) ($disciplina['nome'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></h4>
      <a class="btn btn-outline-secondary btn-sm" href="/admin/cursos/show?id=<?= (int) ($disciplina['id_curso'] ?? 0) ?>"><i class="bi bi-arrow-left me-1"></i>Voltar</a>
    </div>

    <form method="post" action="/admin/cursos/salvar-ementa" class="row g-3">
      <input type="hidden" name="id_disciplina" value="<?= (int) ($disciplina['id'] ?? 0) ?>">
      <?php if ($ementa): ?>
        <input type="hidden" name="id" value="<?= (int) ($ementa['id'] ?? 0) ?>">
      <?php endif; ?>

      <div class="col-12">
        <label class="form-label">Ementa</label>
        <textarea class="form-control" name="ementa" rows="15"><?= htmlspecialchars((string) ($ementa['ementa'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>
      </div>

      <div class="col-12">
        <button class="btn btn-success" type="submit"><i class="bi bi-check-lg me-1"></i>Salvar</button>
      </div>
    </form>
  </div>
</section>
