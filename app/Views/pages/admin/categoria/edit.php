<?php $id = (int) ($categoria['id'] ?? 0); ?>
<section class="container py-4">
  <div class="bg-white border rounded-3 p-4 shadow-sm">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
      <h4 class="mb-0">
        <i class="bi bi-pencil-square me-2"></i><?= $id > 0 ? 'Editar Categoria #' . $id : 'Nova Categoria' ?>
      </h4>
      <a class="btn btn-outline-secondary btn-sm" href="/admin/config/categoria"><i class="bi bi-arrow-left me-1"></i>Voltar para lista</a>
    </div>

    <form method="post" action="/admin/config/categoria/update" class="row g-3">
      <input type="hidden" name="id" value="<?= $id ?>">

      <div class="col-md-6">
        <label class="form-label">Nome</label>
        <input class="form-control" type="text" name="nome" required value="<?= htmlspecialchars((string) ($categoria['nome'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
      </div>

      <div class="col-md-6">
        <label class="form-label">Slug</label>
        <input class="form-control" type="text" name="slug" value="<?= htmlspecialchars((string) ($categoria['slug'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
        <div class="form-text">Deixe em branco para gerar automaticamente.</div>
      </div>

      <div class="col-md-4">
        <label class="form-label">Ativo</label>
        <?php $ativo = (int) ($categoria['ativo'] ?? 1); ?>
        <select class="form-select" name="ativo">
          <option value="1" <?= $ativo === 1 ? 'selected' : '' ?>>Sim</option>
          <option value="0" <?= $ativo === 0 ? 'selected' : '' ?>>Não</option>
        </select>
      </div>

      <div class="col-12">
        <button class="btn btn-success" type="submit">
          <i class="bi bi-check-lg me-1"></i><?= $id > 0 ? 'Atualizar Categoria' : 'Criar Categoria' ?>
        </button>
      </div>
    </form>
  </div>
</section>
