<section class="container py-4">
  <div class="bg-white border rounded-3 p-4 shadow-sm">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
      <h4 class="mb-0"><i class="bi bi-people me-2"></i>Vincular Docente</h4>
      <a class="btn btn-outline-secondary btn-sm" href="/admin/cursos/show?id=<?= (int) ($course['id'] ?? 0) ?>"><i class="bi bi-arrow-left me-1"></i>Voltar</a>
    </div>

    <p class="text-muted mb-4">Curso: <strong><?= htmlspecialchars((string) ($course['nome'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></strong></p>

    <?php if (empty($professores)): ?>
      <div class="alert alert-warning">
        <i class="bi bi-exclamation-triangle me-1"></i>Nenhum professor cadastrado no sistema.
        <a class="btn btn-sm btn-outline-primary ms-2" href="/admin/professores/novo">Cadastrar professor</a>
      </div>
    <?php elseif (empty($funcoes)): ?>
      <div class="alert alert-warning">
        <i class="bi bi-exclamation-triangle me-1"></i>Nenhuma função docente cadastrada.
      </div>
    <?php else: ?>
      <form method="post" action="/admin/cursos/salvar-corpo-docente" class="row g-3">
        <input type="hidden" name="id_curso" value="<?= (int) ($course['id'] ?? 0) ?>">

        <div class="col-md-6">
          <label class="form-label">Professor(es) <span class="text-danger">*</span></label>
          <div class="border rounded p-3" style="max-height:250px;overflow-y:auto;">
            <?php foreach ($professores as $prof): ?>
              <?php $pid = (int) ($prof['id'] ?? 0); ?>
              <div class="form-check">
                <input class="form-check-input" type="checkbox" name="usuarios[]" value="<?= $pid ?>" id="prof_<?= $pid ?>" <?= isset($vinculados[$pid]) ? 'disabled' : '' ?>>
                <label class="form-check-label <?= isset($vinculados[$pid]) ? 'text-muted text-decoration-line-through' : '' ?>" for="prof_<?= $pid ?>">
                  <?= htmlspecialchars((string) ($prof['nome'] ?? '-'), ENT_QUOTES, 'UTF-8') ?>
                  <?php if (isset($vinculados[$pid])): ?>
                    <small class="text-muted">(já vinculado)</small>
                  <?php endif; ?>
                </label>
              </div>
            <?php endforeach; ?>
          </div>
        </div>

        <div class="col-md-4">
          <label class="form-label">Função <span class="text-danger">*</span></label>
          <select name="id_funcao" class="form-select" required>
            <option value="">— Selecione —</option>
            <?php foreach ($funcoes as $f): ?>
              <option value="<?= (int) ($f['id'] ?? 0) ?>"><?= htmlspecialchars((string) ($f['nome'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></option>
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

<script>
function removerDocente(id, cursoId) {
  if (!confirm('Remover este vínculo docente?')) return;
  const formData = new FormData();
  formData.append('id', id);
  fetch('/admin/cursos/remover-corpo-docente', { method: 'POST', body: formData })
    .then(r => r.json())
    .then(data => {
      if (data.sucesso) {
        location.reload();
      } else {
        alert('Erro: ' + (data.erro || 'Erro desconhecido'));
      }
    })
    .catch(() => alert('Erro ao remover vínculo.'));
}
</script>
