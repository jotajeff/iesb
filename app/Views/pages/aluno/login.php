<section class="hero-section" id="home" style="min-height: 100vh; display: flex; align-items: center; background: #0b1120;">
  <div class="hero-bg" style="background: url('https://images.unsplash.com/photo-1521587760476-6c12a4b040da?auto=format&fit=crop&w=1920&q=80') center/cover no-repeat; background-blend-mode: overlay;"></div>
  <div style="position:absolute;inset:0;background:linear-gradient(135deg,rgba(11,17,32,.92),rgba(11,17,32,.75));z-index:1;"></div>
  <div class="container hero-content">
    <div class="row justify-content-center">
      <div class="col-md-6 col-lg-5" data-aos="fade-up">
        <div style="background: rgba(255,255,255,.97); border: 1px solid rgba(255,255,255,.2); border-radius: 16px; padding: 2rem; box-shadow: 0 25px 60px rgba(0,0,0,.45);">
          <div class="text-center mb-3">
            <div class="d-inline-flex align-items-center justify-content-center rounded-circle" style="width:64px;height:64px;background:#0d6efd;color:#fff;">
              <i class="bi bi-mortarboard" style="font-size:1.8rem;"></i>
            </div>
          </div>
          <h2 class="mb-3 text-center">Login Aluno</h2>
          <p class="text-muted mb-4 text-center">Acesse sua área acadêmica para acompanhar cursos, turmas e materiais.</p>

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

          <div class="text-center mt-3">
            <a href="/aluno/solicitar-redefinicao" style="color: #0d6efd; text-decoration: none; font-size: 14px;">
              <i class="bi bi-question-circle me-1"></i>Esqueceu a senha?
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