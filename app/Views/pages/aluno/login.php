<section class="hero-section" id="home" style="min-height: 100vh;">
  <div class="hero-bg"></div>
  <div class="container hero-content" style="padding-top: 120px;">
    <div class="row justify-content-center">
      <div class="col-md-6 col-lg-5" data-aos="fade-up">
        <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 16px; padding: 2rem; box-shadow: var(--card-shadow);">
          <h2 class="mb-3">Login Aluno</h2>
          <p class="mb-3">Use: aluno@iesb.local / aluno123</p>
          <form method="post" action="/aluno/login" class="d-grid gap-3">
            <input type="email" name="email" class="form-control-custom" placeholder="E-mail" required>
            <input type="password" name="password" class="form-control-custom" placeholder="Senha" required>
            <button class="btn-primary-custom justify-content-center" type="submit">Entrar na Área do Aluno</button>
          </form>
        </div>
      </div>
    </div>
  </div>
</section>
