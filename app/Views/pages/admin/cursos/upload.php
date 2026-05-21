<section class="container py-4">
  <div class="bg-white border rounded-3 p-4 shadow-sm">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
      <h4 class="mb-0"><i class="bi bi-image me-2"></i>Upload Imagem do Card</h4>
      <a class="btn btn-outline-secondary btn-sm" href="/admin/cursos"><i class="bi bi-arrow-left me-1"></i>Voltar para lista</a>
    </div>

    <div class="mb-4">
      <h5><?= htmlspecialchars((string) ($course['nome'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></h5>
      <p class="text-muted small mb-0">Imagem atual: <code><?= htmlspecialchars((string) ($course['imagem_card'] ?? 'nenhuma'), ENT_QUOTES, 'UTF-8') ?></code></p>
    </div>

    <?php if (!empty($course['imagem_card'])): ?>
      <div class="mb-4">
        <img class="border rounded shadow-sm" src="/<?= htmlspecialchars((string) $course['imagem_card'], ENT_QUOTES, 'UTF-8') ?>" alt="Card atual" style="max-height: 200px;">
      </div>
    <?php endif; ?>

    <form method="post" action="/admin/cursos/upload" enctype="multipart/form-data" class="row g-3">
      <input type="hidden" name="id" value="<?= (int) ($course['id'] ?? 0) ?>">
      <div class="col-md-6">
        <label class="form-label">Selecione uma imagem (jpg, png, gif, webp)</label>
        <input class="form-control" type="file" name="imagem_card" accept="image/png,image/jpeg,image/gif,image/webp" required>
      </div>
      <div class="col-md-6 d-flex align-items-end">
        <button class="btn btn-success" type="submit"><i class="bi bi-upload me-1"></i>Enviar</button>
      </div>
    </form>
  </div>
</section>
