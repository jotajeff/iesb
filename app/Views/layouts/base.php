<?php require dirname(__DIR__) . '/layouts/topo.php'; ?>
<?php require dirname(__DIR__) . '/layouts/menu.php'; ?>
<?php if (str_starts_with((string) ($currentRoute ?? ''), '/aluno')): ?>
  <div class="aluno-preloader" id="alunoPreloader" role="status" aria-label="Carregando página">
    <div class="aluno-preloader-bar"></div>
  </div>
  <script>window.__alunoPreloaderStartedAt = performance.now();</script>
<?php endif; ?>
<?php require $viewPath; ?>
<?php require dirname(__DIR__) . '/layouts/footer.php'; ?>
