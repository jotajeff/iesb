<?php
$enviado = (bool) ($enviado ?? false);
$erro = (string) ($erro ?? '');
$nome = htmlspecialchars((string) ($nome ?? ''), ENT_QUOTES, 'UTF-8');
$email = htmlspecialchars((string) ($email ?? ''), ENT_QUOTES, 'UTF-8');
$whatsapp = htmlspecialchars((string) ($whatsapp ?? ''), ENT_QUOTES, 'UTF-8');
$cursoNome = htmlspecialchars((string) ($cursoNome ?? ''), ENT_QUOTES, 'UTF-8');
$cursoId = (int) ($cursoId ?? $_GET['curso_id'] ?? 0);
?>

<section class="hero-section" id="home" style="min-height: 70vh;">
  <div class="hero-bg"></div>
  <div class="container hero-content py-5">
    <div class="row justify-content-center">
      <div class="col-md-6 col-lg-5" data-aos="fade-up">

        <?php if ($enviado): ?>

          <div class="card border-0 shadow-soft">
            <div class="card-body p-5 text-center">
              <div class="mb-3" style="color: var(--success);">
                <i class="bi bi-check-circle-fill" style="font-size: 3rem;"></i>
              </div>
              <h3 class="mb-3">Formulário recebido!</h3>
              <p class="mb-0 text-muted">Recebemos seus dados. Entraremos em contato em breve.</p>
            </div>
          </div>

        <?php else: ?>

          <div class="card border-0 shadow-soft">
            <div class="card-body p-5">
              <div class="text-center mb-4">
                <div class="mb-2" style="color: var(--primary);">
                  <i class="bi bi-pencil-square" style="font-size: 2.2rem;"></i>
                </div>
                <h3 class="mb-1">Registrar interesse</h3>
                <p class="text-muted mb-0">Preencha seus dados para receber mais informações.</p>
              </div>

              <?php if ($cursoNome !== ''): ?>
                <div class="alert alert-info d-flex align-items-center gap-2 py-2 px-3 mb-3">
                  <i class="bi bi-bookmark-fill"></i>
                  <span>Curso de interesse: <strong><?= $cursoNome ?></strong></span>
                </div>
              <?php endif; ?>

              <?php if ($erro !== ''): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($erro, ENT_QUOTES, 'UTF-8') ?></div>
              <?php endif; ?>

              <form method="post" action="/pre-inscricao" class="d-grid gap-3" id="formPreInscricao">
                <?php if ($cursoNome !== ''): ?>
                  <input type="hidden" name="curso_id" value="<?= (int) ($cursoId ?? 0) ?>">
                <?php endif; ?>
                <div>
                  <label class="form-label fw-semibold small text-uppercase text-secondary" for="nome">
                    <i class="bi bi-person-fill me-1"></i>Nome completo
                  </label>
                  <input type="text" id="nome" name="nome" class="form-control-custom"
                    placeholder="Seu nome completo" required value="<?= $nome ?>">
                </div>

                <div>
                  <label class="form-label fw-semibold small text-uppercase text-secondary" for="email">
                    <i class="bi bi-envelope-fill me-1"></i>E-mail
                  </label>
                  <input type="email" id="email" name="email" class="form-control-custom"
                    placeholder="seu@email.com" required value="<?= $email ?>">
                </div>

                <div>
                  <label class="form-label fw-semibold small text-uppercase text-secondary" for="whatsapp">
                    <i class="bi bi-whatsapp me-1"></i>WhatsApp
                  </label>
                  <input type="tel" id="whatsapp" name="whatsapp" class="form-control-custom"
                    placeholder="(51) 99999-9999" required value="<?= $whatsapp ?>">
                </div>

                <button type="submit" class="btn-primary-custom justify-content-center mt-2" id="btnPreInscricao">
                  <span id="spinnerPre" class="spinner-border spinner-border-sm d-none me-1" role="status" aria-hidden="true"></span>
                  <span id="textoPre"><i class="bi bi-send-fill me-1"></i>Enviar</span>
                </button>
              </form>

              <script>
                document.getElementById('formPreInscricao')?.addEventListener('submit', function() {
                  document.getElementById('spinnerPre').classList.remove('d-none');
                  document.getElementById('textoPre').innerHTML = 'Enviando…';
                });
              </script>
            </div>
          </div>

        <?php endif; ?>

      </div>
    </div>
  </div>
</section>