<?php
$item = $item ?? null;
$isEditing = $item !== null;
$id = $isEditing ? (int) ($item['id'] ?? 0) : 0;
?>
<section class="container py-4">
  <div class="bg-white border rounded-3 p-4 shadow-sm">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
      <h4 class="mb-0"><i class="bi bi-images me-2"></i><?= $isEditing ? 'Editar Item' : 'Novo Item' ?></h4>
      <a class="btn btn-outline-secondary btn-sm" href="/admin/config/carousel"><i class="bi bi-arrow-left me-1"></i>Voltar</a>
    </div>

    <?php if (!empty($flash ?? '')): ?>
      <div class="alert alert-info"><?= htmlspecialchars((string) $flash, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>

    <form method="post" action="/admin/config/carousel/salvar" enctype="multipart/form-data">
      <?php if ($isEditing): ?>
        <input type="hidden" name="id" value="<?= $id ?>">
      <?php endif; ?>

      <div class="row g-3">
        <div class="col-md-6">
          <label class="form-label">Título</label>
          <input type="text" name="titulo" class="form-control" placeholder="Ex: Curso de Destaque"
                 value="<?= htmlspecialchars((string) ($item['titulo'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
        </div>

        <div class="col-md-3">
          <label class="form-label">Ordem</label>
          <input type="number" name="ordem" class="form-control" min="0" value="<?= (int) ($item['ordem'] ?? 0) ?>">
        </div>

        <div class="col-md-3">
          <label class="form-label">Ativo</label>
          <select name="ativo" class="form-select">
            <option value="S" <?= (($item['ativo'] ?? 'S') === 'S') ? 'selected' : '' ?>>Sim</option>
            <option value="N" <?= (($item['ativo'] ?? 'S') === 'N') ? 'selected' : '' ?>>Não</option>
          </select>
        </div>

        <div class="col-12">
          <label class="form-label">Link</label>
          <input type="url" name="link" class="form-control" placeholder="https://"
                 value="<?= htmlspecialchars((string) ($item['link'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
        </div>

        <div class="col-md-3">
          <label class="form-label">Abrir link em</label>
          <select name="target" class="form-select">
            <option value="_self" <?= (($item['target'] ?? '_self') === '_self') ? 'selected' : '' ?>>Mesma aba (_self)</option>
            <option value="_blank" <?= (($item['target'] ?? '_self') === '_blank') ? 'selected' : '' ?>>Nova aba (_blank)</option>
          </select>
        </div>

        <div class="col-12">
          <label class="form-label">Imagem</label>
          <?php if ($isEditing && ($item['imagem'] ?? '') !== ''): ?>
            <div class="mb-2">
              <img src="/<?= htmlspecialchars((string) $item['imagem'], ENT_QUOTES, 'UTF-8') ?>"
                   style="max-width:200px;max-height:120px;border-radius:8px;object-fit:cover;"
                   alt="Imagem atual">
              <small class="d-block text-muted">Imagem atual. Faça upload para substituir.</small>
            </div>
          <?php endif; ?>
          <input type="file" name="imagem" class="form-control" accept="image/*" <?= $isEditing ? '' : 'required' ?>>
        </div>

        <div class="col-12 d-flex justify-content-end gap-2 mt-3">
          <a href="/admin/config/carousel" class="btn btn-outline-secondary">Cancelar</a>
          <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Salvar</button>
        </div>
      </div>
    </form>
  </div>
</section>
