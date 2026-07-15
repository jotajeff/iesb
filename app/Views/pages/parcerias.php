<?php

use App\Support\SessaoMidia;

?>
<?php if ($sessaoBanner !== null): ?>
  <div class="session-hero session-hero--banner">
    <img src="/<?= htmlspecialchars($sessaoBanner, ENT_QUOTES, 'UTF-8') ?>" alt="Parcerias" class="session-hero-img">
  </div>
<?php else: ?>
  <section class="hero-section" id="parcerias" style="min-height: 60vh;">
    <div class="hero-bg"></div>
    <div class="container hero-content" style="padding-top: 120px;">
      <div class="row justify-content-center">
        <div class="col-lg-10 text-center" data-aos="fade-up">
          <div class="hero-badge"><i class="bi bi-people-fill"></i> Empresas</div>
          <h1 class="hero-title">Página de <span class="highlight">Parcerias</span></h1>
          <p class="hero-subtitle">Área preparada para convênios, estágios e empresas parceiras.</p>
        </div>
      </div>
    </div>
  </section>
<?php endif; ?>

<?php if ($sessaoTitulo !== '' || $sessaoTexto !== ''): ?>
  <section class="py-5">
    <div class="container">
      <div class="row justify-content-center">
        <div class="col-lg-10" data-aos="fade-up">
          <div class="section-header text-center mb-4" data-aos="fade-up">
            <?php if ($sessaoTitulo !== ''): ?>
              <h2 class="section-title"><?= $sessaoTitulo ?></h2>
            <?php endif; ?>
          </div>
          <?php if ($sessaoTexto !== ''): ?>
            <div class="sobre-texto-box">
              <?= $sessaoTexto ?>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </section>
<?php endif; ?>

<?= SessaoMidia::html($sessaoMidia, $galeria, 'parcerias') ?>
