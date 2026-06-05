<section class="hero-section" id="home" style="min-height: 100vh;">
  <div class="hero-bg"></div>
  <div class="container hero-content" style="padding-top: 120px;">
    <div class="row justify-content-center">
      <div class="col-md-6 col-lg-5" data-aos="fade-up">
        <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 16px; padding: 2rem; box-shadow: var(--card-shadow);">
          <h2 class="mb-2">Login Administrativo</h2>
          <p class="text-muted mb-3">Use suas credenciais de <strong>Admin</strong>, <strong>Operador</strong> ou <strong>Professor</strong> para acessar o painel.</p>
          <div class="d-flex flex-wrap gap-2 mb-4">
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
      </div>
    </div>
  </div>
</section>
