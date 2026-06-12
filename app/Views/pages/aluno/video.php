<section class="py-4" style="margin-top: 20px;">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-lg-10">
        <div class="video-box">
          <div class="video-box-header">
            <h5 class="mb-0">
              <i class="bi bi-camera-reels me-2"></i>
              <?= htmlspecialchars($material['titulo'] ?? 'Vídeo', ENT_QUOTES, 'UTF-8') ?>
            </h5>
            <a class="btn btn-outline-secondary btn-sm" href="#" onclick="history.back();return false;">
              <i class="bi bi-arrow-left me-1"></i>Voltar
            </a>
          </div>
          <div class="video-box-body">
            <?php
            $link = $material['link'] ?? '';
            $embedHtml = '';

            if (str_contains($link, '<iframe')) {
                $embedHtml = $link;
            } elseif (preg_match('/youtube\.com\/watch\?v=([a-zA-Z0-9_-]+)/', $link, $m) || preg_match('/youtu\.be\/([a-zA-Z0-9_-]+)/', $link, $m)) {
                $embedHtml = '<iframe class="w-100 rounded-3" style="aspect-ratio:16/9;" src="https://www.youtube.com/embed/' . htmlspecialchars($m[1], ENT_QUOTES, 'UTF-8') . '" frameborder="0" allowfullscreen></iframe>';
            } elseif (preg_match('/vimeo\.com\/(\d+)/', $link, $m)) {
                $embedHtml = '<iframe class="w-100 rounded-3" style="aspect-ratio:16/9;" src="https://player.vimeo.com/video/' . (int) $m[1] . '" frameborder="0" allowfullscreen></iframe>';
            }

            if ($embedHtml !== ''): ?>
              <div class="video-box-player">
                <?= $embedHtml ?>
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
