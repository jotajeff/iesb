<section class="hero-section" id="home" style="min-height: 100vh;">
  <div class="hero-bg"></div>
  <div class="container hero-content" style="padding-top: 120px;">
    <div class="row justify-content-center">
      <div class="col-md-6 col-lg-5" data-aos="fade-up">
        <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 16px; padding: 2rem; box-shadow: var(--card-shadow);">
          <h2 class="mb-3">Login Aluno</h2>

          <form method="post" action="/aluno/login" class="d-grid gap-3">
            <input type="email" name="email" class="form-control-custom" placeholder="E-mail" required>
            <div class="position-relative">
              <input type="password" name="password" id="senhaInput" class="form-control-custom" placeholder="Senha" required>
              <button type="button" id="toggleSenha" class="btn btn-sm position-absolute top-50 end-0 translate-middle-y me-2 border-0 bg-transparent" tabindex="-1" style="color: var(--text-secondary);">
                <i class="bi bi-eye"></i>
              </button>
            </div>
            <button class="btn-primary-custom justify-content-center" type="submit">Entrar na Área do Aluno</button>
          </form>
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