<?php
  $n = is_array($noticia ?? null) ? $noticia : [];
  $titulo = htmlspecialchars((string) ($n['titulo'] ?? ''), ENT_QUOTES, 'UTF-8');
  $imagem = trim((string) ($n['imagem_capa'] ?? ''));
  $conteudo = (string) ($n['conteudo'] ?? '');
  $catNome = htmlspecialchars((string) ($n['categoria_nome'] ?? ''), ENT_QUOTES, 'UTF-8');
  $autor = htmlspecialchars((string) ($n['autor'] ?? ''), ENT_QUOTES, 'UTF-8');
  $dt = \DateTime::createFromFormat('Y-m-d H:i:s', (string) ($n['data_publicacao'] ?? ''));
  $data = $dt ? $dt->format('d/m/Y') : '';
  $historico = array_values(array_filter($noticias ?? [], static fn (array $item): bool => ((string) ($item['slug'] ?? '')) !== ((string) ($n['slug'] ?? ''))));
?>
<section class="py-4" style="margin-top: 20px;">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-lg-10">
        <a href="/aluno" class="btn btn-outline-secondary btn-sm mb-3"><i class="bi bi-arrow-left me-1"></i>Voltar</a>

        <div class="bg-white border rounded-3 p-4 shadow-sm" style="background: var(--bg-card); border-color: var(--border-color);">
          <?php if ($catNome !== ''): ?>
            <span class="badge bg-warning text-dark mb-2"><?= $catNome ?></span>
          <?php endif; ?>
          <h3 class="fw-bold mb-3" style="color: var(--text-heading);"><?= $titulo ?></h3>

          <?php if ($imagem !== ''): ?>
            <img src="/<?= htmlspecialchars($imagem, ENT_QUOTES, 'UTF-8') ?>" alt="<?= $titulo ?>" class="img-fluid rounded-3 mb-4" style="width:100%; box-shadow:0 4px 16px rgba(0,0,0,0.12);">
          <?php endif; ?>

          <div class="noticia-conteudo fs-5 lh-lg" data-aos="fade-up">
            <?= $conteudo ?>
          </div>

          <hr class="my-4">
          <p class="text-muted small mb-0">
            <?php if ($autor !== ''): ?>
              Publicado por <strong><?= $autor ?></strong>
            <?php endif; ?>
            <?php if ($data !== ''): ?>
              em <?= $data ?>
            <?php endif; ?>
          </p>
        </div>

        <?php if (!empty($historico)): ?>
          <div class="mt-4">
            <h5 class="mb-3"><i class="bi bi-clock-history me-2"></i>Outras notícias</h5>
            <div class="list-group list-group-flush">
              <?php foreach (array_slice($historico, 0, 8) as $h): ?>
                <?php
                $hTitulo = htmlspecialchars((string) ($h['titulo'] ?? '-'), ENT_QUOTES, 'UTF-8');
                $hSlug = htmlspecialchars((string) ($h['slug'] ?? ''), ENT_QUOTES, 'UTF-8');
                $hDt = \DateTime::createFromFormat('Y-m-d H:i:s', (string) ($h['data_publicacao'] ?? ''));
                $hData = $hDt ? $hDt->format('d/m/Y') : '-';
                ?>
                <a href="/aluno/noticia?slug=<?= rawurlencode($hSlug) ?>" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center gap-3 py-3">
                  <div>
                    <strong><?= $hTitulo ?></strong>
                    <br><small class="text-muted"><?= $hData ?></small>
                  </div>
                </a>
              <?php endforeach; ?>
            </div>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</section>
