<?php

use App\Support\SessaoMidia;

?>
<style>
  #galeria-eventos .glightbox-item {
    transition: transform 0.35s ease, box-shadow 0.35s ease;
    will-change: transform;
  }

  #galeria-eventos .glightbox-item:hover {
    transform: translateY(-8px) rotate(1.6deg);
    box-shadow: 0 14px 26px rgba(0, 0, 0, 0.18);
  }
</style>
<?php if ($sessaoBanner !== null): ?>
  <div class="session-hero session-hero--banner">
    <img src="/<?= htmlspecialchars($sessaoBanner, ENT_QUOTES, 'UTF-8') ?>" alt="Eventos" class="session-hero-img">
  </div>
<?php else: ?>
  <section class="hero-section" id="eventos" style="min-height: 60vh;">
    <div class="hero-bg"></div>
    <div class="container hero-content" style="padding-top: 120px;">
      <div class="row justify-content-center">
        <div class="col-lg-10 text-center" data-aos="fade-up">
          <div class="hero-badge"><i class="bi bi-calendar-event-fill"></i> Agenda</div>
          <h1 class="hero-title">Página de <span class="highlight">Eventos</span></h1>
          <p class="hero-subtitle">Estrutura criada para calendário acadêmico, palestras e workshops.</p>
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
          <?php if ($sessaoTitulo !== ''): ?>
            <div class="section-header text-center mb-4" data-aos="fade-up">
              <h2 class="section-title"><?= $sessaoTitulo ?></h2>
            </div>
          <?php endif; ?>
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

<?= SessaoMidia::html($sessaoMidia, $galeria, 'eventos') ?>
