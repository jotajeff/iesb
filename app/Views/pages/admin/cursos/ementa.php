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

    <?php if ($ementa): ?>
      <?php $ementaAtivo = (int) ($ementa['ativo'] ?? 1) === 1; ?>
      <hr>
      <div class="d-flex flex-wrap align-items-center gap-2">
        <span class="badge <?= $ementaAtivo ? 'bg-success' : 'bg-danger' ?>">
          <i class="bi bi-<?= $ementaAtivo ? 'check-circle' : 'x-circle' ?> me-1"></i>
          <?= $ementaAtivo ? 'Ativo' : 'Inativo' ?>
        </span>
        <span class="text-muted small">Status atual do registro.</span>
        <form method="post" action="/admin/cursos/alternar-ementa" class="d-inline ms-auto"
              onsubmit="return confirm('Tem certeza que deseja <?= $ementaAtivo ? 'desativar' : 'ativar' ?> esta ementa?');">
          <input type="hidden" name="id" value="<?= (int) ($ementa['id'] ?? 0) ?>">
          <input type="hidden" name="id_disciplina" value="<?= (int) ($disciplina['id'] ?? 0) ?>">
          <button type="submit" class="btn btn-sm btn-outline-<?= $ementaAtivo ? 'danger' : 'success' ?>">
            <i class="bi bi-<?= $ementaAtivo ? 'toggle-off' : 'toggle-on' ?> me-1"></i>
            <?= $ementaAtivo ? 'Inativar' : 'Ativar' ?>
          </button>
        </form>
      </div>
    <?php endif; ?>
  </div>
</section>
