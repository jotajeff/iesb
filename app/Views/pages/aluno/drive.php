<section class="py-4" style="margin-top: 20px;">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-lg-10">
        <div class="video-box">
          <div class="video-box-header">
            <h5 class="mb-0">
              <i class="bi bi-google me-2"></i>
              <?= htmlspecialchars($material['titulo'] ?? 'Documento', ENT_QUOTES, 'UTF-8') ?>
            </h5>
            <a class="btn btn-outline-secondary btn-sm" href="#" onclick="history.back();return false;">
              <i class="bi bi-arrow-left me-1"></i>Voltar
            </a>
          </div>
          <div class="video-box-body">
            <?php
            $link = $material['link'] ?? '';
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
              <div class="video-box-player" style="background:#f8f9fa;">
                <iframe src="<?= htmlspecialchars($embedSrc, ENT_QUOTES, 'UTF-8') ?>" style="aspect-ratio:4/3;" frameborder="0" allowfullscreen></iframe>
              </div>
            <?php else: ?>
              <div class="alert alert-info">
                <i class="bi bi-link-45deg me-1"></i>
                <a href="<?= htmlspecialchars($link, ENT_QUOTES, 'UTF-8') ?>" target="_blank"><?= htmlspecialchars($link, ENT_QUOTES, 'UTF-8') ?></a>
              </div>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
