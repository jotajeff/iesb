<section class="container py-4">
  <div class="bg-white border rounded-3 p-4 shadow-sm">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
      <h4 class="mb-0"><i class="bi bi-book me-2"></i><?= $disciplina ? 'Editar Disciplina' : 'Nova Disciplina' ?></h4>
      <a class="btn btn-outline-secondary btn-sm" href="/admin/cursos/show?id=<?= (int) ($course['id'] ?? 0) ?>"><i class="bi bi-arrow-left me-1"></i>Voltar</a>
    </div>

    <p class="text-muted mb-4">Curso: <strong><?= htmlspecialchars((string) ($course['nome'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></strong></p>

    <form method="post" action="/admin/cursos/salvar-disciplina" class="row g-3">
      <input type="hidden" name="id_curso" value="<?= (int) ($course['id'] ?? 0) ?>">
      <?php if ($disciplina): ?>
        <input type="hidden" name="id" value="<?= (int) ($disciplina['id'] ?? 0) ?>">
      <?php endif; ?>

      <div class="col-md-6">
        <label class="form-label">Nome da disciplina <span class="text-danger">*</span></label>
        <input type="text" name="nome" class="form-control" value="<?= htmlspecialchars((string) ($disciplina['nome'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" required maxlength="150">
      </div>

      <div class="col-md-3">
        <label class="form-label">Carga horária (horas)</label>
        <input type="number" name="carga_horaria" class="form-control" value="<?= (int) ($disciplina['carga_horaria'] ?? 0) ?>" min="0">
      </div>

      <div class="col-md-3">
        <label class="form-label">Ativo</label>
        <select name="ativo" class="form-select">
          <option value="1" <?= (int) ($disciplina['ativo'] ?? 1) == 1 ? 'selected' : '' ?>>Sim</option>
          <option value="0" <?= (int) ($disciplina['ativo'] ?? 1) == 0 ? 'selected' : '' ?>>Não</option>
        </select>
      </div>

      <div class="col-12">
        <button class="btn btn-success" type="submit"><i class="bi bi-check-lg me-1"></i>Salvar</button>
      </div>
    </form>
  </div>
</section>
