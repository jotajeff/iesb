  <!-- ==================== CTA ==================== -->
  <section class="cta-section" style="padding: 80px 0">
    <div
      class="container text-center position-relative"
      style="z-index: 2"
      data-aos="zoom-in"
      data-aos-duration="800">
      <h2 class="cta-title mb-0">
        Parceria forte para seu crescimento
      </h2>
      <div class="row g-3 justify-content-center mt-4 cta-gallery">
        <div class="col-6 col-md-3">
          <div class="cta-image-wrap">
            <img
              src="/assets/img/mbrazil_1.jpg"
              alt="Parceria IESB MBrazil"
              class="img-fluid cta-image">
          </div>
        </div>
        <div class="col-6 col-md-3">
          <div class="cta-image-wrap">
            <img
              src="/assets/img/mbrazil_iesb_2.jpg"
              alt="Parceria IESB MBrazil 2"
              class="img-fluid cta-image">
          </div>
        </div>
        <div class="col-6 col-md-3">
          <div class="cta-image-wrap">
            <a href="https://faculdadesaomarcos.com.br/" target="_blank" rel="noopener noreferrer">
              <img
                src="/assets/img/smarcos_3.jpg"
                alt="Parceria IESB São Marcos"
                class="img-fluid cta-image">
            </a>
          </div>
        </div>
        <div class="col-6 col-md-3">
          <div class="cta-image-wrap">
            <a href="https://faculdadesaomarcos.com.br/" target="_blank" rel="noopener noreferrer">
              <img
                src="/assets/img/fasm_4.jpg"
                alt="Parceria IESB FASM"
                class="img-fluid cta-image">
            </a>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- ==================== CONTACT ==================== -->
  <section class="section-dark" id="contato">
    <div class="container">
      <div class="section-header text-center" data-aos="fade-up">
        <div class="section-label justify-content-center">
          Contato
        </div>
        <h2 class="section-title">Fale Conosco</h2>
        <p class="section-desc centered">
          Tem dúvidas? Preencha o formulário ou entre em contato
          pelos nossos canais.
        </p>
      </div>
      <div class="row g-4">
        <div
          class="col-lg-5"
          data-aos="fade-right"
          data-aos-duration="800">
          <div class="contact-info-card">
            <h5
              style="
                            color: var(--text-heading);
                            margin-bottom: 1.5rem;
                          ">
              <i
                class="bi bi-geo-alt-fill"
                style="color: var(--primary)"></i>
              Informações de Contato
            </h5>
            <div class="contact-info-item">
              <div class="contact-info-icon">
                <i class="bi bi-whatsapp"></i>
              </div>
              <div>
                <div class="contact-info-label">WhatsApp</div>
                <div class="contact-info-value">
                  (51) 992975503
                </div>
              </div>
            </div>
            <div class="contact-info-item">
              <div class="contact-info-icon">
                <i class="bi bi-envelope-fill"></i>
              </div>
              <div>
                <div class="contact-info-label">E-mail</div>
                <div class="contact-info-value">
                  atendimento@<br>
                  inteligenciaeducacional.com.br
                </div>
              </div>
            </div>
            <div class="contact-info-item">
              <div class="contact-info-icon">
                <i class="bi bi-clock-fill"></i>
              </div>
              <div>
                <div class="contact-info-label">Horário</div>
                <div class="contact-info-value">
                  Segunda à Quinta : das 13h às 18h
                </div>
              </div>
            </div>
            <div class="contact-info-item">
              <div class="contact-info-icon">
                <i class="bi bi-geo-alt-fill"></i>
              </div>
              <div>
                <div class="contact-info-label">Endereço</div>
                <div class="contact-info-value">
                  Av. Loureiro da Silva nº 2001 / sala 916 <br>Porto Alegre/RS
                </div>
              </div>
            </div>
          </div>
        </div>
        <div
          class="col-lg-7"
          data-aos="fade-left"
          data-aos-duration="800"
          data-aos-delay="200">
          <div
            style="
                          background: var(--bg-card);
                          border: 1px solid var(--border-color);
                          border-radius: 16px;
                          padding: 2.5rem;
                          box-shadow: var(--card-shadow);
                        ">
            <form id="contactForm">
              <div class="row g-3">
                <div class="col-md-6">
                  <label
                    class="form-label"
                    style="
                                  font-size: 0.85rem;
                                  font-weight: 600;
                                  color: var(--text-primary);
                                ">Nome Completo</label>
                  <input
                    type="text"
                    class="form-control-custom"
                    placeholder="Seu nome"
                    required />
                </div>
                <div class="col-md-6">
                  <label
                    class="form-label"
                    style="
                                  font-size: 0.85rem;
                                  font-weight: 600;
                                  color: var(--text-primary);
                                ">E-mail</label>
                  <input
                    type="email"
                    class="form-control-custom"
                    placeholder="seu@email.com"
                    required />
                </div>
                <div class="col-md-6">
                  <label
                    class="form-label"
                    style="
                                  font-size: 0.85rem;
                                  font-weight: 600;
                                  color: var(--text-primary);
                                ">Telefone</label>
                  <input
                    type="tel"
                    class="form-control-custom"
                    placeholder="(00) 00000-0000" />
                </div>
                <div class="col-md-6">
                  <label
                    class="form-label"
                    style="
                                  font-size: 0.85rem;
                                  font-weight: 600;
                                  color: var(--text-primary);
                                ">Curso de Interesse</label>
                  <?php
                  $coursesForSelect = (new \App\Services\CourseService())->list();
                  usort(
                    $coursesForSelect,
                    static fn(array $a, array $b): int => strcmp(
                      (string) ($a['curso_calendario'] ?? ''),
                      (string) ($b['curso_calendario'] ?? '')
                    )
                  );
                  ?>
                  <select class="form-select-custom">
                    <option value="">Selecione um curso</option>
                    <?php foreach ($coursesForSelect as $course): ?>
                      <option value="<?= htmlspecialchars($course['nome'], ENT_QUOTES, 'UTF-8'); ?>">
                        <?= htmlspecialchars($course['nome'], ENT_QUOTES, 'UTF-8'); ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                </div>
                <div class="col-12">
                  <label
                    class="form-label"
                    style="
                                  font-size: 0.85rem;
                                  font-weight: 600;
                                  color: var(--text-primary);
                                ">Mensagem</label>
                  <textarea
                    class="form-control-custom"
                    rows="4"
                    placeholder="Escreva sua mensagem..."
                    style="resize: none"></textarea>
                </div>
                <div class="col-12">
                  <button
                    type="submit"
                    class="btn-primary-custom w-100 justify-content-center">
                    <i class="bi bi-send"></i> Enviar Mensagem
                  </button>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </section>


  <footer>
    <div class="container">
      <div class="row g-4">
        <div class="col-lg-4 col-md-6">
          <h5><i class="bi bi-mortarboard-fill" style="color: var(--primary)"></i> IESB </h5>
          <p style="font-size: 0.9rem; line-height: 1.7; color: #a5aaaa;">
            Formando profissionais qualificados desde 2014. Nossa missão é transformar vidas através da educação técnica de qualidade e acessível.
          </p>

          <div class="logo-footer mt-3" style="max-width: 250px;">
            <img src="/assets/img/logo-main.png" alt="IESB" class="img-fluid">
          </div>

          <div class="mt-3">
            <h6 style="color: #e5e5d9; font-size: 0.9rem; margin-bottom: 0.5rem;">Redes Sociais</h6>
            <a href="https://instagram.com/iesb_inteligenciaeducacional" target="_blank" rel="noopener noreferrer" class="text-decoration-none" style="color: #a5aaaa; font-size: 0.9rem;">
              <i class="bi bi-instagram"></i> @iesb_inteligenciaeducacional
            </a>
          </div>

        </div>
        <div class="col-lg-4 col-md-6" id="emec">
          <h5>e-MEC</h5>

          <p>O selo e-MEC do IESB comprova o reconhecimento da instituição pelo MEC e reforça seu compromisso com a qualidade do ensino superior.
          </p>

          <a href="https://emec.mec.gov.br/emec/consulta-cadastro/detalhamento/d96957f455f6405d14c6542552b0f6eb/MTM2NDg=" target="_blank" rel="noopener noreferrer">
            <img src="/assets/img/emec.png" alt="e-MEC" class="img-fluid" style="max-width: 180px; margin-top: 10px;" />
          </a>

        </div>

        <div class="col-lg-4 col-md-6">
          <h5>Canais</h5>
          <ul class="footer-links">
            <li><a href="/parcerias">Parcerias</a></li>
            <li><a href="/area-do-aluno">Área do Aluno</a></li>
            <li><a href="mailto:contato@iesb.edu.br">contato@iesb.edu.br</a></li>
            <li><a href="https://wa.me/5551992975503" target="_blank" rel="noopener noreferrer">WhatsApp: (51) 99297-5503</a></li>


          </ul>
        </div>
      </div>
      <div class="footer-bottom text-center">
        <p>&copy; 2026 IESB. Todos os direitos reservados.</p>
      </div>
    </div>
  </footer>

  <button class="scroll-top" id="scrollTop"><i class="bi bi-chevron-up"></i></button>

  <a href="https://wa.me/5551992975503" class="whatsapp-float" target="_blank" rel="noopener noreferrer">
    <i class="bi bi-whatsapp"></i>
  </a>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js"></script>
  <script src="/assets/js/app.js"></script>
  </body>

  </html>