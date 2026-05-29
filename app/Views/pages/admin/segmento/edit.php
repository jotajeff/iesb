<?php $id = (int) ($segmento['id'] ?? 0); ?>
<section class="container py-4">
  <div class="bg-white border rounded-3 p-4 shadow-sm">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
      <h4 class="mb-0">
        <i class="bi bi-pencil-square me-2"></i><?= $id > 0 ? 'Editar Segmento #' . $id : 'Novo Segmento' ?>
      </h4>
      <a class="btn btn-outline-secondary btn-sm" href="/admin/segmento"><i class="bi bi-arrow-left me-1"></i>Voltar para lista</a>
    </div>

    <form method="post" action="/admin/segmento/update" class="row g-3">
      <input type="hidden" name="id" value="<?= $id ?>">

      <div class="col-md-8">
        <label class="form-label">Nome</label>
        <input class="form-control" type="text" name="nome" required value="<?= htmlspecialchars((string) ($segmento['nome'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
      </div>

      <div class="col-md-4">
        <label class="form-label">Ativo</label>
        <?php $ativo = strtoupper((string) ($segmento['ativo'] ?? 'S')); ?>
        <select class="form-select" name="ativo">
          <option value="S" <?= $ativo === 'S' ? 'selected' : '' ?>>Sim</option>
          <option value="N" <?= $ativo === 'N' ? 'selected' : '' ?>>Não</option>
        </select>
      </div>

      <div class="col-12">
        <button class="btn btn-success" type="submit">
          <i class="bi bi-check-lg me-1"></i><?= $id > 0 ? 'Atualizar Segmento' : 'Criar Segmento' ?>
        </button>
      </div>
    </form>
  </div>
</section>
