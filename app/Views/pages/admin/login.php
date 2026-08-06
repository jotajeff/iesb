<section class="hero-section" id="home" style="min-height: 100vh; display: flex; align-items: center; background: #0b1120;">
  <div class="hero-bg" style="background: url('https://images.unsplash.com/photo-1550751827-4bd374c3f58b?auto=format&fit=crop&w=1920&q=80') center/cover no-repeat; background-blend-mode: overlay;"></div>
  <div style="position:absolute;inset:0;background:linear-gradient(135deg,rgba(11,17,32,.92),rgba(11,17,32,.75));z-index:1;"></div>
  <div class="container hero-content">
    <div class="row justify-content-center">
      <div class="col-md-6 col-lg-5" data-aos="fade-up">
        <div style="background: rgba(255,255,255,.97); border: 1px solid rgba(255,255,255,.2); border-radius: 16px; padding: 2rem; box-shadow: 0 25px 60px rgba(0,0,0,.45);">
          <div class="text-center mb-3">
            <div class="d-inline-flex align-items-center justify-content-center rounded-circle" style="width:64px;height:64px;background:#0d6efd;color:#fff;">
              <i class="bi bi-shield-lock" style="font-size:1.8rem;"></i>
            </div>
          </div>
          <h2 class="mb-2 text-center">Login Administrativo</h2>
          <p class="text-muted mb-3 text-center">Use suas credenciais de <strong>Admin</strong>, <strong>Operador</strong> ou <strong>Professor</strong> para acessar o painel.</p>
          <div class="d-flex flex-wrap gap-2 mb-4 justify-content-center">
            <span class="badge bg-dark">Admin</span>
            <span class="badge bg-warning text-dark">Operador</span>
            <span class="badge bg-primary">Professor</span>
          </div>

          <form method="post" action="/admin/login" class="d-grid gap-3">
            <input type="email" name="email" class="form-control-custom" placeholder="E-mail" required>
            <div style="position:relative;">
              <input type="password" name="password" id="senhaInput" class="form-control-custom" placeholder="Senha" required style="padding-right:2.5rem;">
              <button type="button" id="olhoBtn" tabindex="-1" style="position:absolute;right:8px;top:50%;transform:translateY(-50%);background:none;border:none;color:#6c757d;cursor:pointer;padding:4px;line-height:1;" onclick="alternarSenha()" title="Mostrar/esconder senha">
                <i class="bi bi-eye" id="olhoIcone"></i>
              </button>
            </div>
            <button class="btn-primary-custom justify-content-center" type="submit">Entrar no Painel</button>
          </form>
          <div class="text-center mt-3">
            <a href="/admin/solicitar-redefinicao" style="color: #0d6efd; text-decoration: none; font-size: 14px;">
              <i class="bi bi-question-circle me-1"></i>Esqueceu a senha?
            </a>
          </div>
          <script>
          function alternarSenha() {
            var input = document.getElementById('senhaInput');
            var icone = document.getElementById('olhoIcone');
            if (input.type === 'password') {
              input.type = 'text';
              icone.className = 'bi bi-eye-slash';
            } else {
              input.type = 'password';
              icone.className = 'bi bi-eye';
            }
          }
          </script>
        </div>
        <div class="text-center mt-3">
          <a href="/" class="badge bg-secondary text-decoration-none"><i class="bi bi-arrow-left me-1"></i>Retornar ao site</a>
        </div>
      </div>
    </div>
  </div>
</section>
