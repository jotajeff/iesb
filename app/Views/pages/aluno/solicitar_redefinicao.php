<section class="hero-section" id="home" style="min-height: 100vh;">
  <div class="hero-bg"></div>
  <div class="container hero-content" style="padding-top: 120px;">
    <div class="row justify-content-center">
      <div class="col-md-6 col-lg-5" data-aos="fade-up">
        <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 16px; padding: 2rem; box-shadow: var(--card-shadow);">
          <h2 class="mb-3">Redefinir Senha</h2>
          <p class="text-muted mb-4">Informe seu e-mail cadastrado para receber o link de redefinição.</p>

          <?php if (!empty($flash)): ?>
            <?php $isError = str_contains($flash, 'Erro'); ?>
            <div class="alert <?= $isError ? 'alert-danger' : 'alert-info' ?>">
              <?= htmlspecialchars((string) $flash, ENT_QUOTES, 'UTF-8') ?>
            </div>
          <?php endif; ?>

          <form method="post" action="/aluno/solicitar-redefinicao" class="d-grid gap-3">
            <input type="email" name="email" class="form-control-custom" placeholder="Seu e-mail" required>
            <button class="btn-primary-custom justify-content-center" type="submit">Enviar Link</button>
          </form>

          <div class="text-center mt-3">
            <a href="/aluno/login" style="color: var(--accent); text-decoration: none; font-size: 14px;">
              <i class="bi bi-arrow-left me-1"></i>Voltar para o login
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
