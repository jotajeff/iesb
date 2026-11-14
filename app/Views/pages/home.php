              <!-- ==================== HERO CAROUSEL ==================== -->
              <?php if (!empty($carouselItems)): ?>
                <?php
                $carouselId = 'hero-carousel-items';
                ?>
                <section class="hero-carousel" id="<?= $carouselId ?>">
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
                          $link = trim((string) ($item['link'] ?? ''));
                          $titulo = htmlspecialchars((string) ($item['titulo'] ?? ''), ENT_QUOTES, 'UTF-8');
                          $target = trim((string) ($item['target'] ?? '_blank'));
                          ?>
                          <div class="carousel-item <?= $idx === 0 ? 'active' : '' ?>">
                            <?php if ($link !== ''): ?>
                              <a href="<?= htmlspecialchars($link, ENT_QUOTES, 'UTF-8') ?>"
                                target="<?= htmlspecialchars($target, ENT_QUOTES, 'UTF-8') ?>" rel="noopener noreferrer">
                              <?php endif; ?>
                              <img src="/<?= htmlspecialchars($imagem, ENT_QUOTES, 'UTF-8') ?>"
                                alt="<?= $titulo ?>">
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
                </section>
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
                      Oferecemos uma variedade de cursos livres com carga
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
                        $isConfirmed = intval($course['confirmado'] ?? 0) === 1;
                        ?>
                        <div class="col-lg-6 col-md-6" data-aos="fade-up" data-aos-delay="<?= $delay ?>">
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

              <div class="modal fade lead-modal" id="leadCaptureModal" tabindex="-1" aria-labelledby="leadCaptureTitle" aria-describedby="leadCaptureDesc" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                  <div class="modal-content">
                    <div class="lead-modal__hero">
                      <i class="bi bi-mortarboard-fill lead-modal__watermark" aria-hidden="true"></i>
                      <button type="button" class="lead-modal__close" data-bs-dismiss="modal" aria-label="Fechar">
                        <i class="bi bi-x-lg" aria-hidden="true"></i>
                      </button>
                      <h2 class="lead-modal__title" id="leadCaptureTitle">
                        Novos cursos <span>estão chegando!</span>
                      </h2>
                      <p class="lead-modal__subtitle" id="leadCaptureDesc">
                        Deixe seus dados e conte o que você gostaria de estudar. Assim que tivermos novidades, avisaremos você em primeira mão.
                      </p>
                    </div>
                    <div class="lead-modal__body">
                      <div id="leadFormWrap">
                        <form id="leadCaptureForm" class="lead-modal__form" novalidate>
                          <div class="lead-modal__honeypot" aria-hidden="true">
                            <label for="leadWebsite">Não preencha este campo</label>
                            <input type="text" id="leadWebsite" name="website" tabindex="-1" autocomplete="off">
                          </div>

                          <div class="lead-modal__field">
                            <label class="lead-modal__label" for="leadNome">Nome</label>
                            <div class="lead-modal__control">
                              <i class="bi bi-person" aria-hidden="true"></i>
                              <input
                                class="lead-modal__input"
                                id="leadNome"
                                name="nome"
                                type="text"
                                maxlength="50"
                                autocomplete="name"
                                placeholder="Seu nome completo"
                                required>
                            </div>
                          </div>

                          <div class="lead-modal__field">
                            <label class="lead-modal__label" for="leadTelefone">Telefone</label>
                            <div class="lead-modal__control">
                              <i class="bi bi-whatsapp" aria-hidden="true"></i>
                              <input
                                class="lead-modal__input"
                                id="leadTelefone"
                                name="telefone"
                                type="tel"
                                maxlength="20"
                                inputmode="tel"
                                autocomplete="tel"
                                placeholder="(51) 99999-9999"
                                required>
                            </div>
                          </div>

                          <div class="lead-modal__field">
                            <label class="lead-modal__label" for="leadInteresse">Interesse</label>
                            <div class="lead-modal__control">
                              <i class="bi bi-bookmark-star" aria-hidden="true"></i>
                              <input
                                class="lead-modal__input"
                                id="leadInteresse"
                                name="interesse"
                                type="text"
                                maxlength="100"
                                placeholder="Tributário, DP, Simples ........"
                                required>
                            </div>
                          </div>

                          <div class="lead-modal__error" id="leadError" role="alert" hidden></div>

                          <button type="submit" class="btn-primary-custom lead-modal__submit" id="leadSubmit">
                            <span class="lead-modal__submit-label">Quero ser avisado</span>
                            <i class="bi bi-arrow-right" aria-hidden="true"></i>
                          </button>

                          <p class="lead-modal__privacy">
                            Usaremos seus dados apenas para enviar avisos sobre novos cursos.
                            Ao enviar, você concorda com a
                            <a href="/privacidade" target="_blank" rel="noopener noreferrer">Política de Privacidade</a>.
                          </p>
                        </form>
                      </div>

                      <div class="lead-modal__success" id="leadSuccess" aria-live="polite" hidden>
                        <div class="lead-modal__success-icon">
                          <i class="bi bi-check2" aria-hidden="true"></i>
                        </div>
                        <h3 class="lead-modal__success-title">Você está na lista!</h3>
                        <p class="lead-modal__success-text">
                          Avisaremos você em primeira mão assim que os novos cursos forem anunciados.
                        </p>
                        <button type="button" class="btn-primary-custom lead-modal__submit" data-bs-dismiss="modal">
                          Continuar navegando
                        </button>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <style>
                .lead-modal .modal-dialog {
                  max-width: 560px;
                }

                .lead-modal .modal-content {
                  overflow: hidden;
                  border: 0;
                  border-radius: 20px;
                  background: var(--bg-card);
                  box-shadow: 0 30px 80px rgba(20, 22, 20, 0.32);
                }

                .lead-modal__hero {
                  position: relative;
                  overflow: hidden;
                  padding: 2.1rem 2.25rem 1.9rem;
                  background: linear-gradient(145deg, #292b2a 0%, #3f4241 100%);
                  color: #f5f3e8;
                }

                .lead-modal__hero::before {
                  content: "";
                  position: absolute;
                  top: -95px;
                  right: -70px;
                  width: 240px;
                  height: 240px;
                  border-radius: 50%;
                  background: radial-gradient(circle, rgba(239, 192, 43, 0.34) 0%, rgba(239, 192, 43, 0) 68%);
                  pointer-events: none;
                }

                .lead-modal__watermark {
                  position: absolute;
                  right: -0.4rem;
                  bottom: -2.8rem;
                  font-size: 9rem;
                  line-height: 1;
                  color: rgba(239, 192, 43, 0.1);
                  transform: rotate(-8deg);
                  pointer-events: none;
                }

                .lead-modal__close {
                  position: absolute;
                  top: 1rem;
                  right: 1rem;
                  z-index: 2;
                  display: inline-flex;
                  align-items: center;
                  justify-content: center;
                  width: 38px;
                  height: 38px;
                  border: 1px solid rgba(245, 243, 232, 0.24);
                  border-radius: 50%;
                  background: rgba(245, 243, 232, 0.1);
                  color: #f5f3e8;
                  font-size: 0.9rem;
                  transition: background-color 0.2s ease, transform 0.2s ease;
                }

                .lead-modal__close:hover,
                .lead-modal__close:focus-visible {
                  background: rgba(245, 243, 232, 0.2);
                  transform: rotate(6deg);
                }

                .lead-modal__title {
                  position: relative;
                  z-index: 1;
                  max-width: 12ch;
                  margin: 0;
                  padding-right: 2.5rem;
                  font-size: clamp(1.7rem, 4vw, 2.3rem);
                  font-weight: 800;
                  line-height: 1.08;
                  letter-spacing: -0.03em;
                  color: #f5f3e8;
                  text-wrap: balance;
                }

                .lead-modal__title span {
                  color: var(--primary);
                }

                .lead-modal__subtitle {
                  position: relative;
                  z-index: 1;
                  max-width: 44ch;
                  margin: 0.95rem 0 0;
                  color: rgba(245, 243, 232, 0.78);
                  font-size: 0.94rem;
                  line-height: 1.65;
                }

                .lead-modal__body {
                  padding: 1.7rem 2.25rem 1.85rem;
                }

                .lead-modal__field + .lead-modal__field {
                  margin-top: 1rem;
                }

                .lead-modal__label {
                  display: block;
                  margin-bottom: 0.4rem;
                  color: var(--text-primary);
                  font-size: 0.82rem;
                  font-weight: 700;
                  letter-spacing: 0.01em;
                }

                .lead-modal__control {
                  position: relative;
                }

                .lead-modal__control > i {
                  position: absolute;
                  top: 50%;
                  left: 0.95rem;
                  z-index: 1;
                  color: var(--text-secondary);
                  font-size: 1rem;
                  transform: translateY(-50%);
                  pointer-events: none;
                }

                .lead-modal__input {
                  width: 100%;
                  min-height: 48px;
                  padding: 0.7rem 1rem 0.7rem 2.65rem;
                  border: 1px solid var(--border-color);
                  border-radius: 12px;
                  background: var(--input-bg);
                  color: var(--text-primary);
                  font-size: 0.95rem;
                  transition: border-color 0.2s ease, box-shadow 0.2s ease;
                }

                .lead-modal__input::placeholder {
                  color: var(--text-secondary);
                  opacity: 1;
                }

                .lead-modal__input:focus {
                  outline: none;
                  border-color: var(--primary);
                  box-shadow: 0 0 0 3px rgba(239, 192, 43, 0.16);
                }

                .lead-modal__input:user-invalid {
                  border-color: #c94b4b;
                }

                .lead-modal__honeypot {
                  position: absolute;
                  left: -9999px;
                  width: 1px;
                  height: 1px;
                  overflow: hidden;
                }

                .lead-modal__error {
                  margin-top: 1rem;
                  padding: 0.75rem 0.9rem;
                  border-radius: 10px;
                  background: rgba(201, 75, 75, 0.1);
                  color: #8f2f2f;
                  font-size: 0.86rem;
                  line-height: 1.45;
                }

                .lead-modal__submit {
                  width: 100%;
                  justify-content: center;
                  margin-top: 1.2rem;
                  padding: 0.85rem 1.25rem;
                  border-radius: 12px;
                }

                .lead-modal__submit.is-loading {
                  pointer-events: none;
                  opacity: 0.8;
                }

                .lead-modal__submit.is-loading .bi-arrow-right {
                  display: none;
                }

                .lead-modal__submit.is-loading::after {
                  content: "";
                  width: 1rem;
                  height: 1rem;
                  border: 2px solid rgba(77, 79, 78, 0.3);
                  border-top-color: var(--btn-text);
                  border-radius: 50%;
                  animation: leadModalSpin 0.7s linear infinite;
                }

                .lead-modal__privacy {
                  margin: 0.9rem 0 0;
                  color: var(--text-secondary);
                  font-size: 0.78rem;
                  line-height: 1.55;
                  text-align: center;
                }

                .lead-modal__privacy a {
                  color: var(--text-primary);
                  font-weight: 600;
                  text-underline-offset: 2px;
                }

                .lead-modal__success {
                  padding: 0.4rem 0 0.2rem;
                  text-align: center;
                }

                .lead-modal__success-icon {
                  display: inline-flex;
                  align-items: center;
                  justify-content: center;
                  width: 62px;
                  height: 62px;
                  margin-bottom: 1rem;
                  border-radius: 50%;
                  background: rgba(15, 157, 88, 0.12);
                  color: var(--success);
                  font-size: 1.9rem;
                }

                .lead-modal__success-title {
                  margin: 0 0 0.6rem;
                  color: var(--text-heading);
                  font-size: 1.35rem;
                  font-weight: 800;
                  letter-spacing: -0.02em;
                }

                .lead-modal__success-text {
                  max-width: 38ch;
                  margin: 0 auto;
                  color: var(--text-secondary);
                  font-size: 0.92rem;
                  line-height: 1.65;
                }

                body.modal-open .cookie-consent {
                  transform: translateY(100%);
                  pointer-events: none;
                }

                @keyframes leadModalSpin {
                  to {
                    transform: rotate(360deg);
                  }
                }

                @media (max-width: 575.98px) {
                  .lead-modal .modal-dialog {
                    margin: 0.75rem;
                  }

                  .lead-modal__hero {
                    padding: 1.8rem 1.35rem 1.6rem;
                  }

                  .lead-modal__body {
                    padding: 1.4rem 1.35rem 1.5rem;
                  }

                  .lead-modal__title {
                    padding-right: 2rem;
                  }
                }

                @media (prefers-reduced-motion: reduce) {
                  .lead-modal__close,
                  .lead-modal__input {
                    transition: none;
                  }

                  .lead-modal__submit.is-loading::after {
                    animation-duration: 2s;
                  }
                }
              </style>

              <script>
                document.addEventListener('DOMContentLoaded', function() {
                  var modalElement = document.getElementById('leadCaptureModal');
                  if (!modalElement || typeof bootstrap === 'undefined') {
                    return;
                  }

                  var modal = new bootstrap.Modal(modalElement);
                  var storageKey = 'iesb_lead_modal_seen';
                  var form = document.getElementById('leadCaptureForm');
                  var formWrap = document.getElementById('leadFormWrap');
                  var successPanel = document.getElementById('leadSuccess');
                  var errorBox = document.getElementById('leadError');
                  var submitButton = document.getElementById('leadSubmit');
                  var submitLabel = submitButton.querySelector('.lead-modal__submit-label');
                  var phoneInput = document.getElementById('leadTelefone');
                  var autoOpenTimer = null;

                  function rememberModal() {
                    try {
                      sessionStorage.setItem(storageKey, '1');
                    } catch (error) {
                      return;
                    }
                  }

                  function formatPhone(value) {
                    var digits = value.replace(/\D+/g, '').slice(0, 11);

                    if (digits.length <= 2) {
                      return digits.length ? '(' + digits : '';
                    }

                    if (digits.length <= 6) {
                      return '(' + digits.slice(0, 2) + ') ' + digits.slice(2);
                    }

                    if (digits.length <= 10) {
                      return '(' + digits.slice(0, 2) + ') ' + digits.slice(2, 6) + '-' + digits.slice(6);
                    }

                    return '(' + digits.slice(0, 2) + ') ' + digits.slice(2, 7) + '-' + digits.slice(7);
                  }

                  function showError(message) {
                    errorBox.textContent = message;
                    errorBox.hidden = false;
                  }

                  function clearError() {
                    errorBox.textContent = '';
                    errorBox.hidden = true;
                  }

                  function setLoading(isLoading) {
                    submitButton.disabled = isLoading;
                    submitButton.classList.toggle('is-loading', isLoading);
                    submitLabel.textContent = isLoading ? 'Enviando...' : 'Quero ser avisado';
                  }

                  function openModal() {
                    try {
                      if (sessionStorage.getItem(storageKey) === '1') {
                        return;
                      }
                    } catch (error) {
                      modal.show();
                      return;
                    }

                    modal.show();
                  }

                  phoneInput.addEventListener('input', function() {
                    phoneInput.value = formatPhone(phoneInput.value);
                  });

                  form.addEventListener('submit', function(event) {
                    event.preventDefault();
                    clearError();

                    if (!form.checkValidity()) {
                      form.classList.add('was-validated');
                      showError('Preencha nome, telefone e interesse para continuar.');
                      return;
                    }

                    setLoading(true);

                    fetch('/leads', {
                        method: 'POST',
                        headers: {
                          'Accept': 'application/json',
                          'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: new FormData(form)
                      })
                      .then(function(response) {
                        return response.json()
                          .catch(function() {
                            return {};
                          })
                          .then(function(data) {
                            return {
                              ok: response.ok,
                              data: data
                            };
                          });
                      })
                      .then(function(result) {
                        if (!result.ok || !result.data.success) {
                          throw new Error(result.data.message || 'Não foi possível enviar seus dados. Tente novamente.');
                        }

                        rememberModal();
                        formWrap.hidden = true;
                        successPanel.hidden = false;
                        successPanel.querySelector('button').focus();
                      })
                      .catch(function(error) {
                        showError(error.message || 'Não foi possível enviar seus dados. Tente novamente.');
                      })
                      .finally(function() {
                        setLoading(false);
                      });
                  });

                  modalElement.addEventListener('hidden.bs.modal', rememberModal);

                  if (autoOpenTimer) {
                    window.clearTimeout(autoOpenTimer);
                  }

                  autoOpenTimer = window.setTimeout(openModal, 2000);
                });
              </script>