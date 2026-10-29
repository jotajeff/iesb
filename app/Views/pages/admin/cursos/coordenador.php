<section class="container py-4">
  <div class="bg-white border rounded-3 p-4 shadow-sm">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
      <h4 class="mb-0"><i class="bi bi-person-badge me-2"></i>Vincular Coordenador</h4>
      <a class="btn btn-outline-secondary btn-sm" href="/admin/cursos/show?id=<?= (int) ($course['id'] ?? 0) ?>"><i class="bi bi-arrow-left me-1"></i>Voltar</a>
    </div>

    <p class="text-muted mb-4">Curso: <strong><?= htmlspecialchars((string) ($course['nome'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></strong></p>

    <?php if (empty($turmas)): ?>
      <div class="alert alert-warning">
        <i class="bi bi-exclamation-triangle me-1"></i>Nenhuma turma cadastrada para este curso.
      </div>
    <?php elseif (empty($coordenadores)): ?>
      <div class="alert alert-warning">
        <i class="bi bi-exclamation-triangle me-1"></i>Nenhum usuário com a função de coordenador cadastrado.
      </div>
    <?php else: ?>
      <form method="post" action="/admin/cursos/salvar-coordenador" class="row g-3">
        <input type="hidden" name="id_curso" value="<?= (int) ($course['id'] ?? 0) ?>">

        <div class="col-md-6">
          <label class="form-label">Turma <span class="text-danger">*</span></label>
          <select name="id_turma" class="form-select" required>
            <option value="">— Selecione —</option>
            <?php foreach ($turmas as $turma): ?>
              <option value="<?= (int) ($turma['id'] ?? 0) ?>"><?= htmlspecialchars((string) ($turma['nome'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="col-md-6">
          <label class="form-label">Coordenador <span class="text-danger">*</span></label>
          <select name="id_usuario" class="form-select" required>
            <option value="">— Selecione —</option>
            <?php foreach ($coordenadores as $coord): ?>
              <option value="<?= (int) ($coord['id'] ?? 0) ?>"><?= htmlspecialchars((string) ($coord['nome'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="col-12">
          <button class="btn btn-success" type="submit"><i class="bi bi-link-45deg me-1"></i>Vincular</button>
        </div>
      </form>
    <?php endif; ?>
  </div>
</section>
