<?php if ($destaque !== null): ?>
  <?php
  $titulo = htmlspecialchars((string) ($destaque['titulo'] ?? ''), ENT_QUOTES, 'UTF-8');
  $imagem = trim((string) ($destaque['imagem_capa'] ?? ''));
  $conteudo = (string) ($destaque['conteudo'] ?? '');
  $catNome = htmlspecialchars((string) ($destaque['categoria_nome'] ?? ''), ENT_QUOTES, 'UTF-8');
  $autor = htmlspecialchars((string) ($destaque['autor'] ?? ''), ENT_QUOTES, 'UTF-8');
  $dt = \DateTime::createFromFormat('Y-m-d H:i:s', (string) ($destaque['data_publicacao'] ?? ''));
  $data = $dt ? $dt->format('d/m/Y') : '';
  ?>
  <section class="pt-5 pb-4">
    <div class="container">
      <div class="row justify-content-center">
        <div class="col-lg-10 mt-5">

          <div class="mb-3" data-aos="fade-up">
            <?php if ($catNome !== ''): ?>
              <span class="badge bg-warning text-dark fs-6 px-4 py-3"><?= $catNome ?></span>
            <?php endif; ?>
          </div>

          <h4 class="fw-bold mb-3" data-aos="fade-up"><?= $titulo ?></h4>

          <?php if ($imagem !== ''): ?>
            <div data-aos="fade-up">
              <img src="/<?= $imagem ?>" alt="<?= $titulo ?>" class="img-fluid rounded-3 mb-4" style="width:100%;box-shadow:0 4px 16px rgba(0,0,0,0.12);">
            </div>
          <?php endif; ?>

          <div class="noticia-conteudo fs-5 lh-lg" data-aos="fade-up">
            <?= $conteudo ?>
          </div>

          <hr class="my-4" data-aos="fade-up">

          <p class="text-muted small mb-0" data-aos="fade-up">
            <?php if ($autor !== ''): ?>
              Publicado por <strong><?= $autor ?></strong>
            <?php endif; ?>
            <?php if ($data !== ''): ?>
              em <?= $data ?>
            <?php endif; ?>
          </p>

        </div>
      </div>
    </div>
  </section>

  <?php if (!empty($historico)): ?>
    <section class="py-5 bg-light">
      <div class="container">
        <div class="section-header text-center mb-4" data-aos="fade-up">
          <h6 style="font-size:1rem;font-weight:700;"><i class="bi bi-calendar3 me-2"></i>Histórico de Notícias</h6>
        </div>
        <div class="row justify-content-center">
          <div class="col-lg-10">
            <div class="list-group list-group-flush">
              <?php foreach ($historico as $n): ?>
                <?php
                $hTitulo = htmlspecialchars((string) ($n['titulo'] ?? '-'), ENT_QUOTES, 'UTF-8');
                $hSlug = htmlspecialchars((string) ($n['slug'] ?? ''), ENT_QUOTES, 'UTF-8');
                $hCat = htmlspecialchars((string) ($n['categoria_nome'] ?? ''), ENT_QUOTES, 'UTF-8');
                $hDt = \DateTime::createFromFormat('Y-m-d H:i:s', (string) ($n['data_publicacao'] ?? ''));
                $hData = $hDt ? $hDt->format('d/m/Y') : '-';
                ?>
                <a href="/noticias/<?= rawurlencode($hSlug) ?>" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center gap-3 py-3">
                  <div>
                    <strong><?= $hTitulo ?></strong>
                    <br><small class="text-muted"><?= $hData ?></small>
                  </div>
                  <?php if ($hCat !== ''): ?>
                    <span class="badge bg-secondary rounded-pill flex-shrink-0"><?= $hCat ?></span>
                  <?php endif; ?>
                </a>
              <?php endforeach; ?>
            </div>
          </div>
        </div>
      </div>
    </section>
  <?php endif; ?>

<?php else: ?>
  <section class="hero-section" id="noticias" style="min-height: 70vh;">
    <div class="hero-bg"></div>
    <div class="container hero-content" style="padding-top: 120px;">
      <div class="row justify-content-center">
        <div class="col-lg-10 text-center" data-aos="fade-up">
          <div class="hero-badge"><i class="bi bi-newspaper"></i> Notícias</div>
          <h1 class="hero-title">Nenhuma <span class="highlight">Notícia</span> publicada</h1>
          <p class="hero-subtitle">Em breve traremos novidades. Fique atento!</p>
        </div>
      </div>
    </div>
  </section>
<?php endif; ?>