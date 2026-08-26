<section class="container py-4">
    <div class="bg-white border rounded-3 p-4 shadow-sm">
      <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <h4 class="mb-0"><i class="bi bi-pencil-square me-2"></i>Editar Professor</h4>
        <a class="btn btn-outline-secondary btn-sm" href="<?= htmlspecialchars((string) ($backRoute ?? '/admin/professores'), ENT_QUOTES, 'UTF-8') ?>"><i class="bi bi-arrow-left me-1"></i>Voltar</a>
      </div>

    <form method="post" action="/admin/professores/atualizar" class="row g-3">
      <input type="hidden" name="id" value="<?= (int) ($professor['id'] ?? 0) ?>">

      <div class="col-md-6">
        <label class="form-label">Nome <span class="text-danger">*</span></label>
        <input class="form-control" type="text" name="nome" required value="<?= htmlspecialchars((string) ($professor['nome'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
      </div>
      <div class="col-md-6">
        <label class="form-label">Email</label>
        <input class="form-control" type="email" value="<?= htmlspecialchars((string) ($professor['email'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" readonly disabled>
      </div>
      <div class="col-md-6">
        <label class="form-label">Telefone</label>
        <input class="form-control" type="text" name="telefone" value="<?= htmlspecialchars((string) ($professor['telefone'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
      </div>
      <div class="col-md-6">
        <label class="form-label">Titulação</label>
        <input class="form-control" type="text" name="titulacao" value="<?= htmlspecialchars((string) ($professor['titulacao'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" placeholder="Especialista, Mestre, Doutor...">
      </div>
      <div class="col-md-3">
        <label class="form-label">Ativo</label>
        <select class="form-select" name="ativo">
          <option value="1" <?= ((int) ($professor['ativo'] ?? 1) === 1) ? 'selected' : '' ?>>Sim</option>
          <option value="0" <?= ((int) ($professor['ativo'] ?? 1) === 0) ? 'selected' : '' ?>>Não</option>
        </select>
      </div>

      <div class="col-12">
        <button class="btn btn-success" type="submit"><i class="bi bi-check-lg me-1"></i>Salvar</button>
      </div>
    </form>
  </div>
</section>
