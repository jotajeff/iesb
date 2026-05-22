<section class="hero-section" id="home" style="min-height: 60vh;">
  <div class="hero-bg"></div>
  <div class="container hero-content" style="padding-top: 120px;">
    <div class="row justify-content-center">
      <div class="col-lg-10 text-center" data-aos="fade-up">
        <div class="hero-badge"><i class="bi bi-journal-bookmark-fill"></i> Agenda de Cursos</div>
        <h1 class="hero-title">Nossos <span class="highlight">Cursos</span></h1>
        <p class="hero-subtitle">
          Conheça nossa agenda de cursos e escolha a formação ideal para impulsionar sua carreira.
        </p>
      </div>
    </div>
  </div>
</section>

<section class="py-5">
  <div class="container">
    <div class="section-header text-center mb-4" data-aos="fade-up">
      <div class="section-label justify-content-center">
        Cursos Disponíveis
      </div>
      <h2 class="section-title">Confira nossa agenda completa</h2>
      <p class="section-desc centered">
        Cursos com metodologia prática, professores especializados e certificação reconhecida.
      </p>
    </div>

    <div class="row g-4">
      <?php $cursosDisponiveis = $courses ?? []; ?>
      <?php if (empty($cursosDisponiveis)): ?>
        <div class="col-12 text-center text-muted" data-aos="fade-up" data-aos-delay="100">
          Nenhum curso disponível no momento.
        </div>
      <?php else: ?>
        <?php foreach ($cursosDisponiveis as $index => $course): ?>
          <?php
            $courseImage = trim((string) ($course['imagem_card'] ?? ''));
            $courseName = htmlspecialchars((string) ($course['nome'] ?? '-'), ENT_QUOTES, 'UTF-8');
            $courseLocation = htmlspecialchars((string) ($course['local_curso'] ?? '-'), ENT_QUOTES, 'UTF-8');
            $courseHorario = htmlspecialchars((string) ($course['horario'] ?? '-'), ENT_QUOTES, 'UTF-8');
            $linkIngresso = trim((string) ($course['link_ingresso'] ?? ''));
            $dateText = '-';
            $rawDate = (string) ($course['data_curso'] ?? '');
            $dtDate = \DateTime::createFromFormat('Y-m-d', $rawDate);
            if ($dtDate instanceof \DateTime) {
              $dateText = $dtDate->format('d/m/Y');
            } elseif ($rawDate !== '') {
              $dateText = $rawDate;
            }
            $delay = 100 + ($index % 3) * 100;
            $isConfirmed = strtoupper(trim((string) ($course['confirmado'] ?? 'N'))) === 'S';
          ?>
          <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="<?= $delay ?>">
            <div class="course-card<?= $isConfirmed ? ' course-card-confirmed' : '' ?>">
              <div class="course-card-image">
                <?php if ($courseImage !== ''): ?>
                  <img
                    src="/<?= htmlspecialchars($courseImage, ENT_QUOTES, 'UTF-8') ?>"
                    alt="Imagem do curso <?= $courseName ?>"
                    style="width: 100%; height: 100%; object-fit: cover; display: block;"
                  />
                <?php else: ?>
                  <div class="course-img-placeholder" style="background: linear-gradient(135deg, #2c3e50, #0f172a);">
                    <i class="bi bi-journal-bookmark"></i>
                  </div>
                <?php endif; ?>
                <?php if ($isConfirmed): ?>
                  <span class="course-badge course-badge-confirmed">
                    <i class="bi bi-award-fill"></i> Confirmado
                  </span>
                <?php endif; ?>
              </div>
              <div class="course-card-body">
                <h3 class="course-card-title"><?= $courseName ?></h3>
                <p class="course-card-desc"><?= $courseLocation ?></p>
                <div class="course-meta">
                  <div class="course-meta-item">
                    <i class="bi bi-calendar-event"></i> <?= htmlspecialchars($dateText, ENT_QUOTES, 'UTF-8') ?>
                  </div>
                  <div class="course-meta-item">
                    <i class="bi bi-clock"></i> <?= $courseHorario ?>
                  </div>
                </div>
                <div class="course-card-footer">
                  <?php if ($linkIngresso !== ''): ?>
                    <a
                      class="course-btn"
                      href="<?= htmlspecialchars($linkIngresso, ENT_QUOTES, 'UTF-8') ?>"
                      target="_blank"
                      rel="noopener noreferrer"
                    >
                      Inscreva-se
                    </a>
                  <?php else: ?>
                    <span class="course-btn" style="pointer-events: none; opacity: 0.5;">Inscrições em breve</span>
                  <?php endif; ?>
                </div>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>
</section>

<section class="section-dark" id="inscricoes">
  <div class="container">
    <div class="section-header text-center mb-4" data-aos="fade-up">
      <div class="section-label justify-content-center">
        Inscrições
      </div>
      <h2 class="section-title">Garanta sua vaga agora</h2>
      <p class="section-desc centered">
        Acesse nossa plataforma de inscrições e garanta sua participação nos cursos disponíveis.
      </p>
    </div>

    <div class="row justify-content-center">
      <div class="col-lg-6 text-center" data-aos="fade-up" data-aos-delay="100">
        <div class="card border-0 shadow-soft">
          <div class="card-body p-5">
            <div class="mb-3 text-primary"><i class="bi bi-pencil-square fs-3"></i></div>
            <h3 class="h5 mb-3">Inscreva-se pela Sympla</h3>
            <p class="mb-4" style="color: var(--bs-secondary-color);">
              Todas as inscrições são realizadas de forma segura e prática através da plataforma Sympla.
            </p>
            <a
              href="https://www.sympla.com.br/produtor/magdabrazilcursos"
              target="_blank"
              rel="noopener noreferrer"
              class="btn-primary-custom"
            >
              <i class="bi bi-box-arrow-up-right"></i> Acessar Página de Inscrições
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
