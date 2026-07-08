<?php
$s = $sessao ?? [];
$midiaVal = match ((int) ($s['midia'] ?? -1)) {
    1 => 'C',
    0 => 'G',
    default => '',
};
?>
<section class="container py-4">
  <div class="bg-white border rounded-3 p-4 shadow-sm">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
      <h4 class="mb-0"><i class="bi bi-pencil me-2"></i>Editar Sessão</h4>
      <a class="btn btn-outline-secondary btn-sm" href="/admin/sessao"><i class="bi bi-arrow-left me-1"></i>Voltar</a>
    </div>

    <form method="post" action="/admin/sessao/salvar" enctype="multipart/form-data">
      <input type="hidden" name="id" value="<?= (int) ($s['id'] ?? 0) ?>">

      <div class="row g-3">
        <div class="col-md-6">
          <label class="form-label">Título <span class="text-danger">*</span></label>
          <input type="text" name="titulo" class="form-control" maxlength="150" value="<?= htmlspecialchars((string) ($s['titulo'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" required>
        </div>
        <div class="col-md-3">
          <label class="form-label">Slug</label>
          <select name="slug" class="form-select">
            <option value="">Selecione...</option>
            <option value="eventos"<?= ((string) ($s['slug'] ?? '') === 'eventos') ? ' selected' : '' ?>>eventos</option>
            <option value="parcerias"<?= ((string) ($s['slug'] ?? '') === 'parcerias') ? ' selected' : '' ?>>parcerias</option>
            <option value="sobre"<?= ((string) ($s['slug'] ?? '') === 'sobre') ? ' selected' : '' ?>>sobre</option>
          </select>
        </div>
        <div class="col-md-3">
          <label class="form-label">Badge</label>
          <input type="text" name="badge" class="form-control" maxlength="50" value="<?= htmlspecialchars((string) ($s['badge'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
        </div>
        <div class="col-12">
          <label class="form-label">Apresenta</label>
          <input type="text" name="apresenta" class="form-control" maxlength="255" value="<?= htmlspecialchars((string) ($s['apresenta'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" placeholder="Subtítulo ou chamada">
        </div>
        <div class="col-12">
          <label class="form-label">Banner (imagem)</label>
          <input type="file" name="banner" class="form-control" accept="image/*">
          <?php $bannerPath = (string) ($s['banner'] ?? ''); ?>
          <?php if ($bannerPath !== ''): ?>
            <div class="mt-2">
              <img src="/<?= $bannerPath ?>" alt="Banner atual" class="border rounded" style="max-height:80px;">
              <small class="text-muted d-block">Atual: <?= htmlspecialchars($bannerPath, ENT_QUOTES, 'UTF-8') ?></small>
            </div>
          <?php endif; ?>
        </div>
        <div class="col-12">
          <label class="form-label">Texto</label>
          <textarea name="texto" id="texto" class="form-control" rows="6"><?= htmlspecialchars((string) ($s['texto'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>
        </div>
        <div class="col-md-3">
          <label class="form-label">Mídia</label>
          <select name="midia" class="form-select">
            <option value="">—</option>
            <option value="C"<?= $midiaVal === 'C' ? ' selected' : '' ?>>C — Carrossel</option>
            <option value="G"<?= $midiaVal === 'G' ? ' selected' : '' ?>>G — Galeria</option>
          </select>
        </div>
      </div>
      <div class="mt-4">
        <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Salvar</button>
        <a class="btn btn-outline-secondary ms-2" href="/admin/sessao">Cancelar</a>
      </div>
    </form>
  </div>
</section>

<script src="https://cdn.ckeditor.com/ckeditor5/41.4.2/classic/ckeditor.js"></script>
<style>
  .ck-editor__editable_inline { min-height: 280px; }
</style>
<script>
  ClassicEditor.create(document.querySelector('#texto')).catch(function (error) { console.error(error); });
</script>
