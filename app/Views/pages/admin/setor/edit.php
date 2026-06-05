<?php $id = (int) ($setor['id'] ?? 0); ?>
<section class="container py-4">
  <div class="bg-white border rounded-3 p-4 shadow-sm">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
      <h4 class="mb-0">
        <i class="bi bi-pencil-square me-2"></i><?= $id > 0 ? 'Editar Setor #' . $id : 'Novo Setor' ?>
      </h4>
      <a class="btn btn-outline-secondary btn-sm" href="/admin/setor"><i class="bi bi-arrow-left me-1"></i>Voltar para lista</a>
    </div>

    <form method="post" action="/admin/setor/update" class="row g-3">
      <input type="hidden" name="id" value="<?= $id ?>">

      <div class="col-md-8">
        <label class="form-label">Setor</label>
        <input class="form-control" type="text" name="setor" required value="<?= htmlspecialchars((string) ($setor['setor'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
      </div>

      <div class="col-12">
        <button class="btn btn-success" type="submit">
          <i class="bi bi-check-lg me-1"></i><?= $id > 0 ? 'Atualizar Setor' : 'Criar Setor' ?>
        </button>
      </div>
    </form>
  </div>
</section>
