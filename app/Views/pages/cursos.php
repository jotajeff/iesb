<section class="hero-section" id="home" style="min-height: 70vh;">
  <div class="hero-bg"></div>
  <div class="container hero-content" style="padding-top: 120px;">
    <div class="row">
      <div class="col-12" data-aos="fade-up">
        <div class="hero-badge"><i class="bi bi-journal-bookmark-fill"></i> Catálogo Dinâmico</div>
        <h1 class="hero-title">Cursos gerenciados pelo <span class="highlight">backoffice</span></h1>
      </div>
    </div>
    <div class="row g-4 mt-1">
      <?php foreach (($courses ?? []) as $course): ?>
        <div class="col-lg-4 col-md-6">
          <div class="course-card">
            <div class="course-card-body">
              <h3 class="course-card-title"><?= htmlspecialchars($course['name'], ENT_QUOTES, 'UTF-8') ?></h3>
              <p class="course-card-desc"><?= htmlspecialchars($course['description'], ENT_QUOTES, 'UTF-8') ?></p>
              <div class="course-meta">
                <div class="course-meta-item"><i class="bi bi-clock"></i> <?= htmlspecialchars($course['duration'], ENT_QUOTES, 'UTF-8') ?></div>
                <div class="course-meta-item"><i class="bi bi-currency-dollar"></i> R$ <?= number_format((float) $course['price'], 2, ',', '.') ?>/mês</div>
              </div>
              <div class="course-card-footer">
                <a class="course-btn" href="/aluno/login">Entrar para matricular</a>
              </div>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
