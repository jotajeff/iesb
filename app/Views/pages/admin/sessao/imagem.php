<?php
$s = $sessao ?? [];
$imagens = $imagens ?? [];
$idFk = (int) ($idFk ?? 0);
$tabelaFk = (string) ($tabelaFk ?? '');
$flashUpload = $flash ?? '';
?>
<section class="container py-4">
  <div class="bg-white border rounded-3 p-4 shadow-sm">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
      <h4 class="mb-0"><i class="bi bi-images me-2"></i>Imagens — <?= htmlspecialchars((string) ($s['titulo'] ?? ''), ENT_QUOTES, 'UTF-8') ?></h4>
      <a class="btn btn-outline-secondary btn-sm" href="/admin/sessao"><i class="bi bi-arrow-left me-1"></i>Voltar</a>
    </div>

    <?php if ($flashUpload && strpos($flashUpload, 'sucesso') !== false || $flashUpload === 'Imagem salva com sucesso.'): ?>
      <div class="alert alert-success d-flex justify-content-between align-items-center">
        <span><?= htmlspecialchars($flashUpload, ENT_QUOTES, 'UTF-8') ?></span>
        <span>
          <a class="btn btn-sm btn-outline-primary me-2" href="/admin/sessao/imagem?id_fk=<?= $idFk ?>&tabela_fk=<?= $tabelaFk ?>"><i class="bi bi-plus-lg me-1"></i>Adicionar outra</a>
          <a class="btn btn-sm btn-outline-secondary" href="/admin/sessao"><i class="bi bi-list-ul me-1"></i>Voltar à listagem</a>
        </span>
      </div>
    <?php endif; ?>

    <?php if (!empty($imagens)): ?>
      <div class="row g-3 mb-4">
        <?php foreach ($imagens as $img): ?>
          <div class="col-md-3">
            <div class="card border shadow-sm">
              <img src="/<?= htmlspecialchars((string) ($img['path'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" class="card-img-top" alt="<?= htmlspecialchars((string) ($img['legenda'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" style="height:150px;object-fit:cover;">
              <div class="card-body p-2">
                <small class="text-muted"><?= htmlspecialchars((string) ($img['legenda'] ?? '—'), ENT_QUOTES, 'UTF-8') ?></small>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php elseif (!$flashUpload || $flashUpload !== 'Imagem salva com sucesso.'): ?>
      <div class="text-muted small mb-4">Nenhuma imagem cadastrada.</div>
    <?php endif; ?>

    <hr>
    <h5 class="mb-3"><i class="bi bi-cloud-arrow-up me-1"></i>Upload de imagem</h5>
    <form method="post" action="/admin/sessao/upload-imagem" enctype="multipart/form-data">
      <input type="hidden" name="id_fk" value="<?= $idFk ?>">
      <input type="hidden" name="tabela_fk" value="<?= htmlspecialchars($tabelaFk, ENT_QUOTES, 'UTF-8') ?>">

      <div class="row g-3">
        <div class="col-md-4">
          <label class="form-label">Imagem</label>
          <input type="file" name="imagem" class="form-control" accept="image/*" required>
        </div>
        <div class="col-md-4">
          <label class="form-label">Legenda</label>
          <input type="text" name="legenda" class="form-control" maxlength="150" placeholder="Opcional">
        </div>
        <div class="col-md-4 d-flex align-items-end">
          <button type="submit" class="btn btn-primary w-100"><i class="bi bi-cloud-arrow-up me-1"></i>Enviar</button>
        </div>
      </div>
    </form>
  </div>
</section>
