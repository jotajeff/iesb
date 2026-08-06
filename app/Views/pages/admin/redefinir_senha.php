<section class="hero-section" id="home" style="min-height: 100vh; display: flex; align-items: center;">
  <div class="hero-bg"></div>
  <div class="container hero-content">
    <div class="row justify-content-center">
      <div class="col-md-6 col-lg-5" data-aos="fade-up">
        <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 16px; padding: 2rem; box-shadow: var(--card-shadow);">
          <h2 class="mb-3">Nova Senha</h2>
          <p class="text-muted mb-4">Defina uma nova senha para sua conta.</p>

          <?php if (!empty($flash)): ?>
            <div class="alert alert-info"><?= htmlspecialchars((string) $flash, ENT_QUOTES, 'UTF-8') ?></div>
          <?php endif; ?>

          <form method="post" action="/admin/redefinir-senha" class="d-grid gap-3">
            <input type="hidden" name="token" value="<?= htmlspecialchars((string) ($token ?? ''), ENT_QUOTES, 'UTF-8') ?>">

            <div class="position-relative">
              <input type="password" name="senha" id="senhaInput" class="form-control-custom" placeholder="Nova senha" required minlength="6">
              <button type="button" id="toggleSenha" class="btn btn-sm position-absolute top-50 end-0 translate-middle-y me-2 border-0 bg-transparent" tabindex="-1" style="color: var(--text-secondary);">
                <i class="bi bi-eye"></i>
              </button>
            </div>

            <input type="password" name="senha_confirmar" class="form-control-custom" placeholder="Confirmar nova senha" required minlength="6">

            <button class="btn-primary-custom justify-content-center" type="submit">Salvar Nova Senha</button>
          </form>

          <div class="text-center mt-3">
            <a href="/admin/login" style="color: var(--accent); text-decoration: none; font-size: 14px;">
              <i class="bi bi-arrow-left me-1"></i>Voltar para o login
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<script>
document.getElementById('toggleSenha').addEventListener('click', function() {
  var input = document.getElementById('senhaInput');
  var icon = this.querySelector('i');
  if (input.type === 'password') {
    input.type = 'text';
    icon.className = 'bi bi-eye-slash';
  } else {
    input.type = 'password';
    icon.className = 'bi bi-eye';
  }
});
</script>
