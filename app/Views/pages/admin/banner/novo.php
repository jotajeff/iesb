<?php
  $banner = is_array($banner ?? null) ? $banner : null;
  $cursosView = is_array($cursos ?? null) ? $cursos : [];
  $editando = $banner !== null;
  $bannerImg = trim((string) ($banner['banner'] ?? ''));
?>

<section class="container py-4">
  <div class="bg-white border rounded-3 p-4 shadow-sm">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
      <h4 class="mb-0"><i class="bi bi-images me-2"></i><?= $editando ? 'Editar' : 'Novo' ?> Banner Aluno</h4>
      <a class="btn btn-outline-secondary btn-sm" href="/admin/config/banner-aluno"><i class="bi bi-arrow-left me-1"></i>Voltar</a>
    </div>

    <form method="post" action="/admin/config/banner-aluno/salvar" enctype="multipart/form-data" class="row g-3">
      <?php if ($editando): ?>
        <input type="hidden" name="id" value="<?= (int) ($banner['id'] ?? 0) ?>">
      <?php endif; ?>

      <div class="col-12">
        <label class="form-label">Imagem do banner <span class="text-danger">*</span></label>
        <?php if ($editando && $bannerImg !== ''): ?>
          <div class="mb-2">
            <img src="/<?= htmlspecialchars($bannerImg, ENT_QUOTES, 'UTF-8') ?>" alt="Banner atual" class="border rounded" style="max-height: 160px;">
          </div>
        <?php endif; ?>
        <input type="file" class="form-control" name="banner" accept="image/png,image/jpeg,image/gif,image/webp" <?= $editando ? '' : 'required' ?>>
        <div class="form-text">Imagens salvas em <code>/assets/img/banner</code> com o prefixo <code>aluno_</code>. Formatos: JPG, PNG, GIF ou WebP.</div>
      </div>

      <div class="col-md-6">
        <label class="form-label">Texto (opcional)</label>
        <input type="text" class="form-control" name="texto" maxlength="256" value="<?= htmlspecialchars((string) ($banner['texto'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
      </div>

      <div class="col-md-6">
        <label class="form-label">Link <span class="text-danger">*</span></label>
        <input type="text" class="form-control" name="link" maxlength="256" required value="<?= htmlspecialchars((string) ($banner['link'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" placeholder="https://...">
      </div>

      <div class="col-md-4">
        <label class="form-label">Curso (opcional)</label>
        <select class="form-select" name="id_curso">
          <option value="">Nenhum</option>
          <?php foreach ($cursosView as $curso): ?>
            <option value="<?= (int) ($curso['id'] ?? 0) ?>" <?= (int) ($banner['id_curso'] ?? 0) === (int) ($curso['id'] ?? 0) ? 'selected' : '' ?>>
              <?= htmlspecialchars((string) ($curso['nome'] ?? '-'), ENT_QUOTES, 'UTF-8') ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="col-md-2">
        <label class="form-label">Ativo</label>
        <select class="form-select" name="ativo">
          <option value="1" <?= ((int) ($banner['ativo'] ?? 1) === 1) ? 'selected' : '' ?>>Sim</option>
          <option value="0" <?= ((int) ($banner['ativo'] ?? 1) === 0) ? 'selected' : '' ?>>Não</option>
        </select>
      </div>

      <div class="col-12">
        <button class="btn btn-success" type="submit"><i class="bi bi-check-lg me-1"></i><?= $editando ? 'Salvar alterações' : 'Cadastrar banner' ?></button>
      </div>
    </form>
  </div>
</section>
