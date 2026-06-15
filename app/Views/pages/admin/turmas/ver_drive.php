<section class="container py-4">
  <div class="bg-white border rounded-3 p-4 shadow-sm">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
      <h5 class="mb-0">
        <i class="bi bi-google me-2"></i>
        <?= htmlspecialchars((string) ($material['titulo'] ?? 'Documento'), ENT_QUOTES, 'UTF-8') ?>
      </h5>
      <a class="btn btn-outline-secondary btn-sm" href="/admin/turmas/show?id=<?= (int) ($material['id_fk'] ?? 0) ?>">
        <i class="bi bi-arrow-left me-1"></i>Voltar
      </a>
    </div>

    <?php
    $link = (string) ($material['link'] ?? '');
    $embedSrc = '';

    if (preg_match('/\/file\/d\/([a-zA-Z0-9_-]+)/', $link, $m)) {
        $embedSrc = 'https://drive.google.com/file/d/' . $m[1] . '/preview';
    } elseif (preg_match('/\/document\/d\/([a-zA-Z0-9_-]+)/', $link, $m)) {
        $embedSrc = 'https://docs.google.com/document/d/' . $m[1] . '/preview';
    } elseif (preg_match('/\/presentation\/d\/([a-zA-Z0-9_-]+)/', $link, $m)) {
        $embedSrc = 'https://docs.google.com/presentation/d/' . $m[1] . '/embed';
    } elseif (preg_match('/\/spreadsheets\/d\/([a-zA-Z0-9_-]+)/', $link, $m)) {
        $embedSrc = 'https://docs.google.com/spreadsheets/d/' . $m[1] . '/preview';
    }

    if ($embedSrc !== ''): ?>
      <div style="background:#f8f9fa; padding:1rem; border-radius:0.375rem;">
        <iframe src="<?= htmlspecialchars($embedSrc, ENT_QUOTES, 'UTF-8') ?>" style="width:100%; aspect-ratio:4/3;" frameborder="0" allowfullscreen></iframe>
      </div>
    <?php else: ?>
      <div class="alert alert-info">
        <i class="bi bi-link-45deg me-1"></i>
        <a href="<?= htmlspecialchars($link, ENT_QUOTES, 'UTF-8') ?>" target="_blank"><?= htmlspecialchars($link, ENT_QUOTES, 'UTF-8') ?></a>
      </div>
    <?php endif; ?>
  </div>
</section>
