              <!-- ==================== HERO ==================== -->
              <section class="hero-section" id="home">
                <div class="hero-bg"></div>
                <div class="hero-shapes">
                  <div class="shape shape-1"></div>
                  <div class="shape shape-2"></div>
                  <div class="shape shape-3"></div>
                </div>
                <div class="container hero-content">
                  <div class="hero-layout">
                    <div
                      class="hero-text-wrapper"
                      data-aos="fade-up"
                      data-aos-duration="800">
                      <a href="#emec" class="hero-badge">
                        <i class="bi bi-award-fill"></i> Reconhecida pelo MEC
                      </a>
                      <h4 class="hero-title">
                        Transforme seu futuro com <span class="highlight">educação de qualidade</span>, você mais preparado para os desafios do <span class="highlight">mercado</span>.
                      </h4>
                      <p class="hero-subtitle">
                        Cursos com metodologia prática, professores especializados e certificação reconhecida em todo o Brasil. Invista na sua carreira agora.
                      </p>
                      <div class="d-flex flex-wrap gap-3">
                        <a href="#cursos" class="btn-primary-custom"><i class="bi bi-book"></i> Ver Cursos</a>
                        <a
                          href="/sobre"
                          class="btn-outline-custom"
                          style="
                            border-color: rgba(229, 229, 217, 0.3);
                            color: #e5e5d9;
                          "><i class="bi bi-play-circle"></i> Conheça a IESB</a>
                      </div>
                      <div class="hero-stats">
                        <div class="hero-stat">
                          <div class="hero-stat-number">
                            <span class="hero-stat-plus">+</span><span class="counter-number" data-target="5000">0</span>
                          </div>
                          <div class="hero-stat-label">Alunos Formados</div>
                        </div>

                        <div class="hero-stat">
                          <div
                            class="hero-stat-number counter-number"
                            data-target="20">
                            <span class="hero-stat-plus">+</span>0
                          </div>
                          <div class="hero-stat-label">Cursos Ativos</div>
                        </div>

                        <div class="hero-stat">
                          <div
                            class="hero-stat-number counter-number"
                            data-target="15">
                            <span class="hero-stat-plus">+</span>0
                          </div>
                          <div class="hero-stat-label">Anos de Mercado</div>
                        </div>
                      </div>
                      <div class="hero-highlight-note">
                        <i class="bi bi-stars"></i>
                        Pós Graduação a partir 2026/2
                      </div>
                    </div>
                  </div>
                </div>
                <div class="hero-image-col" data-aos="fade-left" data-aos-duration="800">
                  <div class="hero-image-wrapper">
                    <img src="/assets/img/hero.jpg" alt="Hero" class="hero-image" />
                  </div>
                </div>
              </section>

              <!-- ==================== ABOUT ==================== -->
              <section class="section-dark" id="sobre">
                <div class="container">
                  <div class="row align-items-center g-5">
                    <div
                      class="col-lg-6"
                      data-aos="fade-right"
                      data-aos-duration="800">
                      <div class="about-image-wrapper">
                        <div class="about-img-placeholder">
                          <i class="bi bi-building"></i>
                        </div>
                        <div class="about-floating-card">
                          <div class="afc-icon">
                            <i class="bi bi-trophy-fill"></i>
                          </div>
                          <div>
                            <div class="afc-number">+15 anos</div>
                            <div class="afc-label">
                              de excelência educacional
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                    <div
                      class="col-lg-6"
                      data-aos="fade-left"
                      data-aos-duration="800"
                      data-aos-delay="200">
                      <div class="section-header">
                        <div class="section-label">Sobre Nós</div>
                        <h2 class="section-title">
                          Formando profissionais preparados para o mercado
                        </h2>
                        <p class="section-desc">
                          A IESB Escola é referência em educação técnica,
                          oferecendo cursos atualizados e alinhados com as
                          demandas do mercado de trabalho. Nossa missão é
                          transformar vidas através do conhecimento prático.
                        </p>
                      </div>
                      <ul class="about-list">
                        <li>
                          <i class="bi bi-check-circle-fill"></i><span>Laboratórios modernos e equipados com tecnologia de
                            ponta</span>
                        </li>
                        <li>
                          <i class="bi bi-check-circle-fill"></i><span>Corpo docente com ampla experiência profissional e
                            acadêmica</span>
                        </li>
                        <li>
                          <i class="bi bi-check-circle-fill"></i><span>Parcerias com empresas para estágios e inserção no
                            mercado</span>
                        </li>
                        <li>
                          <i class="bi bi-check-circle-fill"></i><span>Certificação reconhecida pelo MEC e Conselhos
                            Regionais</span>
                        </li>
                        <li>
                          <i class="bi bi-check-circle-fill"></i><span>Horários flexíveis: manhã, tarde e noite</span>
                        </li>
                      </ul>
                      <a href="#contato" class="btn-primary-custom">Saiba Mais <i class="bi bi-arrow-right"></i></a>
                    </div>
                  </div>
                </div>
              </section>

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
                        <h3 class="feature-title">Certificação MEC</h3>
                        <p class="feature-desc">
                          Diploma reconhecido pelo MEC e conselhos regionais, valorizado por empregadores em todo o Brasil.
                        </p>
                      </div>
                    </div>
                  </div>
                </div>
              </section>

              <!-- ==================== TESTIMONIALS ==================== -->
              <section id="depoimentos">
                <div class="container">
                  <div class="section-header text-center" data-aos="fade-up">
                    <div class="section-label justify-content-center">
                      Depoimentos
                    </div>
                    <h2 class="section-title">O que nossos alunos dizem</h2>
                    <p class="section-desc centered">
                      Histórias reais de transformação profissional de quem
                      passou pela IESB.
                    </p>
                  </div>
                  <div class="row g-4">
                    <div
                      class="col-lg-4 col-md-6"
                      data-aos="fade-up"
                      data-aos-delay="100">
                      <div class="testimonial-card">
                        <div class="quote-icon">
                          <i class="bi bi-quote"></i>
                        </div>
                        <div class="testimonial-stars">
                          <i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i>
                        </div>
                        <p class="testimonial-text">
                          "Graças ao curso de Informática da IESB, consegui
                          minha primeira vaga como desenvolvedor em apenas 2
                          meses após a formatura. Aulas práticas fizeram toda a
                          diferença!"
                        </p>
                        <div class="testimonial-author">
                          <div class="testimonial-avatar">RS</div>
                          <div>
                            <div class="testimonial-name">Rafael Silva</div>
                            <div class="testimonial-role">
                              Formado em Informática — 2024
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                    <div
                      class="col-lg-4 col-md-6"
                      data-aos="fade-up"
                      data-aos-delay="200">
                      <div class="testimonial-card">
                        <div class="quote-icon">
                          <i class="bi bi-quote"></i>
                        </div>
                        <div class="testimonial-stars">
                          <i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i>
                        </div>
                        <p class="testimonial-text">
                          "A estrutura da escola é incrível e os professores são
                          muito dedicados. Hoje trabalho em um hospital público
                          graças à formação em Enfermagem que recebi."
                        </p>
                        <div class="testimonial-author">
                          <div class="testimonial-avatar">AM</div>
                          <div>
                            <div class="testimonial-name">Ana Martins</div>
                            <div class="testimonial-role">
                              Formada em Enfermagem — 2023
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                    <div
                      class="col-lg-4 col-md-6"
                      data-aos="fade-up"
                      data-aos-delay="300">
                      <div class="testimonial-card">
                        <div class="quote-icon">
                          <i class="bi bi-quote"></i>
                        </div>
                        <div class="testimonial-stars">
                          <i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-half"></i>
                        </div>
                        <p class="testimonial-text">
                          "Fiz o técnico em Administração à noite enquanto
                          trabalhava. A flexibilidade de horários e o suporte
                          dos professores me permitiram concluir com sucesso."
                        </p>
                        <div class="testimonial-author">
                          <div class="testimonial-avatar">LC</div>
                          <div>
                            <div class="testimonial-name">Lucas Costa</div>
                            <div class="testimonial-role">
                              Formado em Administração — 2024
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </section>
