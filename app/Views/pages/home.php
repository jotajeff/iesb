              <!-- ==================== HERO CAROUSEL ==================== -->
              <?php if (!empty($carousels)): ?>
                <?php foreach ($carousels as $carouselData): ?>
                  <?php
                  $carousel = $carouselData['carousel'];
                  $carouselItems = $carouselData['items'];
                  $carouselId = 'carousel-' . ((int) ($carousel['id'] ?? 0));
                  $carouselTitulo = htmlspecialchars((string) ($carousel['titulo'] ?? ''), ENT_QUOTES, 'UTF-8');
                  $carouselDescricao = (string) ($carousel['descricao'] ?? '');
                  ?>
                  <section class="hero-carousel" id="<?= $carouselId ?>">
                    <div class="hero-carousel-bg"></div>
                    <div class="hero-bubbles" aria-hidden="true">
                      <span class="hero-bubble hero-bubble-1"></span>
                      <span class="hero-bubble hero-bubble-2"></span>
                      <span class="hero-bubble hero-bubble-3"></span>
                      <span class="hero-bubble hero-bubble-4"></span>
                    </div>

                    <div class="hero-carousel-inner">
                      <div class="container">
                        <div class="hero-carousel-header">
                          <div class="hero-badge"><i class="bi bi-star-fill"></i> Destaque</div>
                          <h1 class="hero-title"><?= $carouselTitulo ?></h1>
                        </div>

                        <?php if (!empty($carouselItems)): ?>
                          <div class="hero-carousel-slider">
                            <div id="<?= $carouselId ?>Slider" class="carousel slide carousel-fade" data-bs-ride="carousel" data-bs-interval="5000">
                              <div class="carousel-indicators">
                                <?php foreach ($carouselItems as $idx => $item): ?>
                                  <button type="button"
                                    data-bs-target="#<?= $carouselId ?>Slider"
                                    data-bs-slide-to="<?= $idx ?>"
                                    class="<?= $idx === 0 ? 'active' : '' ?>"
                                    aria-current="<?= $idx === 0 ? 'true' : 'false' ?>"
                                    aria-label="Slide <?= $idx + 1 ?>"></button>
                                <?php endforeach; ?>
                              </div>
                              <div class="carousel-inner">
                                <?php foreach ($carouselItems as $idx => $item): ?>
                                  <?php
                                  $imagem = trim((string) ($item['imagem'] ?? ''));
                                  $carouselLink = trim((string) ($carousel['link'] ?? ''));
                                  $link = $carouselLink !== '' ? $carouselLink : trim((string) ($item['link'] ?? ''));
                                  ?>
                                  <div class="carousel-item <?= $idx === 0 ? 'active' : '' ?>">
                                    <?php if ($link !== ''): ?>
                                      <a href="<?= htmlspecialchars($link, ENT_QUOTES, 'UTF-8') ?>"
                                        target="_blank" rel="noopener noreferrer">
                                    <?php endif; ?>
                                      <img src="/<?= htmlspecialchars($imagem, ENT_QUOTES, 'UTF-8') ?>"
                                        alt="<?= htmlspecialchars((string) ($item['titulo'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                                    <?php if ($link !== ''): ?>
                                      </a>
                                    <?php endif; ?>
                                  </div>
                                <?php endforeach; ?>
                              </div>
                              <button class="carousel-control-prev" type="button"
                                data-bs-target="#<?= $carouselId ?>Slider" data-bs-slide="prev">
                                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                <span class="visually-hidden">Anterior</span>
                              </button>
                              <button class="carousel-control-next" type="button"
                                data-bs-target="#<?= $carouselId ?>Slider" data-bs-slide="next">
                                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                <span class="visually-hidden">Próximo</span>
                              </button>
                            </div>
                          </div>
                        <?php endif; ?>
                      </div>
                    </div>
                  </section>
                <?php endforeach; ?>
              <?php endif; ?>

              <!-- ==================== COURSES ==================== -->
              <section id="cursos">
                <div class="container">
                  <div class="section-header text-center" data-aos="fade-up">
                    <div class="section-label justify-content-center">
                      Nossos Cursos
                    </div>
                    <h2 class="section-title">
                      Escolha o curso ideal para sua carreira
                    </h2>
                    <p class="section-desc centered">
                      Oferecemos uma variedade de cursos técnicos com carga
                      horária otimizada, aulas práticas e certificação
                      reconhecida.
                    </p>
                  </div>
                  <div class="row g-4">
                    <?php $cursosDisponiveis = $cursosDestaque ?? []; ?>
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
                        $courseSegmento = trim((string) ($course['segmento_nome'] ?? ''));
                        $linkIngresso = trim((string) ($course['link_ingresso'] ?? ''));
                        $rawDate = (string) ($course['data_curso'] ?? '');
                        $dateText = '-';
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
                                    rel="noopener noreferrer">
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

              <!-- ==================== FEATURES ==================== -->
              <section class="section-dark" id="diferenciais">
                <div class="container">
                  <div class="section-header text-center" data-aos="fade-up">
                    <div class="section-label justify-content-center">
                      Diferenciais
                    </div>
                    <h2 class="section-title">Por que escolher a IESB?</h2>
                    <p class="section-desc centered">
                      Combinamos qualidade acadêmica com infraestrutura moderna
                      para oferecer a melhor experiência de aprendizado.
                    </p>
                  </div>
                  <div class="row g-4">
                    <div
                      class="col-lg-3 col-md-6"
                      data-aos="fade-up"
                      data-aos-delay="100">
                      <div class="feature-card">
                        <div class="feature-icon">
                          <i class="bi bi-people-fill"></i>
                        </div>
                        <h3 class="feature-title">Corpo Docente</h3>
                        <p class="feature-desc">
                          Docentes com ampla experiência real no mercado e formação
                          acadêmica sólida.
                        </p>
                      </div>
                    </div>
                    <div
                      class="col-lg-3 col-md-6"
                      data-aos="fade-up"
                      data-aos-delay="200">
                      <div class="feature-card">
                        <div class="feature-icon">
                          <i class="bi bi-tools"></i>
                        </div>
                        <h3 class="feature-title">Horários flexíveis</h3>
                        <p class="feature-desc">
                          Diversas modalidades de cursos, para se adequar à sua rotina.
                        </p>
                      </div>
                    </div>
                    <div
                      class="col-lg-3 col-md-6"
                      data-aos="fade-up"
                      data-aos-delay="300">
                      <div class="feature-card">
                        <div class="feature-icon">
                          <i class="bi bi-briefcase-fill"></i>
                        </div>
                        <h3 class="feature-title">Inserção no Mercado</h3>
                        <p class="feature-desc">
                          Ensino alinhado às exigências do mercado.
                        </p>
                      </div>
                    </div>
                    <div
                      class="col-lg-3 col-md-6"
                      data-aos="fade-up"
                      data-aos-delay="400">
                      <div class="feature-card">
                        <div class="feature-icon">
                          <i class="bi bi-patch-check-fill"></i>
                        </div>
                        <h3 class="feature-title">Polo Educacional FACSM MEC</h3>
                        <p class="feature-desc">
                          Diploma reconhecido pelo MEC , valorizado por empregadores em todo o Brasil.
                        </p>
                      </div>
                    </div>
                  </div>
                </div>
              </section>