<?php
$nivelSelecionado = $nivelSelecionado ?? null;
$nivelNome = trim((string) ($nivelSelecionado['nome'] ?? 'Cursos'));
$nivelApresentacao = (string) ($nivelSelecionado['apresentacao'] ?? '');
$nivelApresentacaoHtml = trim($nivelApresentacao) !== ''
  ? $nivelApresentacao
  : '<p class="mb-0">Escolha um nível no menu para ver a apresentação e os cursos disponíveis.</p>';
$cursosDisponiveis = $courses ?? [];
$segmentosMenu = $segmentosMenu ?? [];
$segmentoSelecionado = $segmentoSelecionado ?? null;
$segmentoSelecionadoNome = trim((string) ($segmentoSelecionado['nome'] ?? ''));
$nivelCursoUrl = (string) ($nivelCursoUrl ?? '/cursos');
$sessaoBanner = $sessaoBanner ?? null;
?>

<?php if ($sessaoBanner !== null): ?>
  <div class="session-hero session-hero--banner">
    <img src="/<?= htmlspecialchars($sessaoBanner, ENT_QUOTES, 'UTF-8') ?>" alt="" class="session-hero-img">
  </div>
<?php else: ?>
<section class="hero-section hero-section--cursos" id="home">
  <div class="hero-content container">
    <div class="row justify-content-center">
      <div class="col-lg-10 text-center courses-hero-copy" data-aos="fade-up">
        <div class="hero-badge"><i class="bi bi-journal-bookmark-fill"></i> <?= htmlspecialchars($nivelNome, ENT_QUOTES, 'UTF-8') ?></div>

        <div class="hero-desc-box hero-presentation-content--hero">
          <?= $nivelApresentacaoHtml ?>
        </div>
      </div>
    </div>
  </div>
</section>
<?php endif; ?>

<section class="py-2">
  <div class="container">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-2" data-aos="fade-up">
      <div>
        <div class="section-label">Segmentos</div>
        <h4 class="section-title mb-0">Filtre os cursos por segmento</h4>
      </div>
      <?php if ($segmentoSelecionadoNome !== ''): ?>
        <a class="btn btn-outline-secondary btn-sm" href="<?= htmlspecialchars($nivelCursoUrl, ENT_QUOTES, 'UTF-8') ?>">
          <i class="bi bi-x-circle me-1"></i>Limpar filtro
        </a>
      <?php endif; ?>
    </div>

    <div class="d-flex flex-wrap gap-2 mb-2" data-aos="fade-up" data-aos-delay="100">
      <a
        class="btn btn-sm <?= $segmentoSelecionadoNome === '' ? 'btn-primary' : 'btn-outline-secondary' ?>"
        href="<?= htmlspecialchars($nivelCursoUrl, ENT_QUOTES, 'UTF-8') ?>">
        Todos os segmentos
      </a>

      <?php if (empty($segmentosMenu)): ?>
        <span class="text-muted small align-self-center">Nenhum segmento encontrado para este nível.</span>
      <?php else: ?>
        <?php foreach ($segmentosMenu as $segmento): ?>
          <a
            class="btn btn-sm <?= !empty($segmento['active']) ? 'btn-primary' : 'btn-outline-secondary' ?>"
            href="<?= htmlspecialchars((string) ($segmento['url'] ?? $nivelCursoUrl), ENT_QUOTES, 'UTF-8') ?>">
            <?= htmlspecialchars((string) ($segmento['nome'] ?? '-'), ENT_QUOTES, 'UTF-8') ?>
          </a>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>
</section>

<section class="pt-3 pb-5" id="lista-cursos">
  <div class="container">
    <div class="row g-4">
      <?php if (empty($cursosDisponiveis)): ?>
        <div class="col-12 text-center text-muted" data-aos="fade-up" data-aos-delay="100">
          Nenhum curso encontrado para este filtro.
        </div>
      <?php else: ?>
        <?php foreach ($cursosDisponiveis as $index => $course): ?>
          <?php
          $courseImage = trim((string) ($course['imagem_card'] ?? ''));
          $courseName = (string) ($course['nome'] ?? '-');
          $courseLocation = (string) ($course['local_curso'] ?? '-');
          $courseHorario = (string) ($course['horario'] ?? '-');
          $courseDate = (string) ($course['date_text'] ?? '-');
          $courseSegmento = trim((string) ($course['segmento_nome'] ?? ''));
          $linkIngresso = trim((string) ($course['link_ingresso'] ?? ''));
          $delay = 100 + ($index % 3) * 100;
          $isConfirmed = (int) ($course['confirmado'] ?? 0) == 1;
          ?>
          <div class="col-lg-6 col-md-6" data-aos="fade-up" data-aos-delay="<?= $delay ?>">
            <div class="course-card<?= $isConfirmed ? ' course-card-confirmed' : '' ?>">
              <div class="course-card-image">
                <?php if ($courseImage !== ''): ?>
                  <img
                    src="/<?= htmlspecialchars($courseImage, ENT_QUOTES, 'UTF-8') ?>"
                    alt="Imagem do curso <?= htmlspecialchars($courseName, ENT_QUOTES, 'UTF-8') ?>"
                    style="width: 100%; height: 100%; object-fit: cover; display: block;" />
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
                <?php if ($courseSegmento !== ''): ?>
                  <span class="course-badge course-badge-segment">
                    <?= htmlspecialchars($courseSegmento, ENT_QUOTES, 'UTF-8') ?>
                  </span>
                <?php endif; ?>
              </div>
              <div class="course-card-body">
                <h3 class="course-card-title"><?= htmlspecialchars($courseName, ENT_QUOTES, 'UTF-8') ?></h3>
                <p class="course-card-desc"><?= htmlspecialchars($courseLocation, ENT_QUOTES, 'UTF-8') ?></p>
                <div class="course-meta">
                  <div class="course-meta-item">
                    <i class="bi bi-calendar-event"></i> <?= htmlspecialchars($courseDate, ENT_QUOTES, 'UTF-8') ?>
                  </div>
                  <div class="course-meta-item">
                    <i class="bi bi-clock"></i> <?= htmlspecialchars($courseHorario, ENT_QUOTES, 'UTF-8') ?>
                  </div>
                </div>
                <div class="course-card-footer">
                  <?php if ($linkIngresso !== '' && stripos($linkIngresso, 'saiba') === false): ?>
                    <a class="course-btn-primary" href="<?= htmlspecialchars($linkIngresso, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener noreferrer">
                      <i class="bi bi-check-circle"></i> Inscrever-se
                    </a>
                  <?php elseif ($linkIngresso !== ''): ?>
                    <a class="course-btn-primary" href="/curso/<?= htmlspecialchars((string) ($course['slug'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                      <i class="bi bi-check-circle"></i> Inscrever-se
                    </a>
                  <?php else: ?>
                    <span class="course-btn-primary disabled">
                      <i class="bi bi-check-circle"></i> Inscrições em breve
                    </span>
                  <?php endif; ?>
                  <a class="course-btn-secondary" href="/curso/<?= htmlspecialchars((string) ($course['slug'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                    <i class="bi bi-info-circle"></i> Detalhes
                  </a>
                </div>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>
</section>
