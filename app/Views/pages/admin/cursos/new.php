<section class="container py-4">
  <div class="bg-white border rounded-3 p-4 shadow-sm">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
      <h4 class="mb-0"><i class="bi bi-plus-circle me-2"></i>Novo Curso</h4>
      <a class="btn btn-outline-secondary btn-sm" href="/admin/cursos"><i class="bi bi-arrow-left me-1"></i>Voltar para lista</a>
    </div>

    <form method="post" action="/admin/cursos/salvar" class="row g-3">
      <div class="col-md-8">
        <label class="form-label">Nome</label>
        <input class="form-control" type="text" name="nome" required>
        <small class="text-muted">O slug será gerado automaticamente a partir deste nome.</small>
      </div>
      <div class="col-md-4">
        <label class="form-label">Tipo</label>
        <select class="form-select" name="tipo_curso" required>
          <option value="">Selecione...</option>
          <?php foreach (($cursosTipos ?? []) as $t): ?>
            <option value="<?= (int) ($t['id'] ?? 0) ?>"><?= (int) ($t['id'] ?? 0) ?> - <?= htmlspecialchars((string) ($t['tipo'] ?? ''), ENT_QUOTES, 'UTF-8') ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-4">
        <label class="form-label">Calendário</label>
        <input class="form-control" type="date" name="curso_calendario">
      </div>
      <div class="col-md-4">
        <label class="form-label">Ativo</label>
        <select class="form-select" name="ativo">
          <option value="S" selected>Sim</option>
          <option value="N">Não</option>
        </select>
      </div>
      <div class="col-md-4">
        <label class="form-label">Confirmado</label>
        <select class="form-select" name="confirmado">
          <option value="N" selected>Não</option>
          <option value="S">Sim</option>
        </select>
      </div>
      <div class="col-md-4">
        <label class="form-label">Data do curso</label>
        <input class="form-control" type="text" name="data_curso" placeholder="ex: 10/06/2026">
      </div>
      <div class="col-md-4">
        <label class="form-label">Horario</label>
        <input class="form-control" type="text" name="horario" placeholder="ex: 18h30 as 21h">
      </div>
      <div class="col-md-4">
        <label class="form-label">Local</label>
        <input class="form-control" type="text" name="local_curso" required>
      </div>
      <div class="col-md-8">
        <label class="form-label">Link de ingresso</label>
        <input class="form-control" type="url" name="link_ingresso" placeholder="https://...">
      </div>
      <div class="col-12">
        <button class="btn btn-primary" type="submit"><i class="bi bi-check-lg me-1"></i>Salvar Curso</button>
      </div>
    </form>
  </div>
</section>
