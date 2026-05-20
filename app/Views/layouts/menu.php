<nav class="navbar navbar-expand-lg fixed-top site-navbar">
  <div class="container">
    <a class="navbar-brand" href="/home">
      <img src="/assets/img/logo-top.png" alt="IESB Logo" class="logo w-50">
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent">
      <span class="navbar-toggler-icon"><span></span></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarContent">
      <ul class="navbar-nav mx-auto">
        <?php
        $menuItems = [
          '/home' => 'Início',
          '/sobre' => 'Sobre',
          '/eventos' => 'Eventos',
          '/parcerias' => 'Parcerias',

        ];
        ?>
        <?php foreach ($menuItems as $path => $label): ?>
          <li class="nav-item">
            <a class="nav-link <?= ($currentRoute ?? '') === $path ? 'active' : '' ?>" href="<?= $path ?>"><?= $label ?></a>
          </li>
        <?php endforeach; ?>
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle <?= ($currentRoute ?? '') === '/cursos' ? 'active' : '' ?>" href="/cursos" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            Cursos
          </a>
          <ul class="dropdown-menu">
            <li><a class="dropdown-item" href="/cursos?tipo=graduacao">Graduação</a></li>
            <li><a class="dropdown-item" href="/cursos?tipo=pos-graduacao">Pós Graduação</a></li>
            <li><a class="dropdown-item" href="/cursos?tipo=cursos-livres">Cursos Livres</a></li>
          </ul>
        </li>
      </ul>
      <div class="d-flex align-items-center gap-2">
        <button class="theme-toggle" id="themeToggle" aria-label="Alternar tema">
          <div class="toggle-ball">
            <i class="bi bi-sun-fill toggle-icon" id="toggleIcon"></i>
          </div>
        </button>
        <?php if (($authUser['role'] ?? null) === 'admin'): ?>
          <a class="btn btn-sm btn-warning" href="/admin">Admin</a>
        <?php endif; ?>
        <?php if (($authUser['role'] ?? null) === 'aluno'): ?>
          <a class="btn btn-sm btn-warning" href="/aluno">Meu Espaço</a>
        <?php endif; ?>
        <?php if (!isset($authUser)): ?>
          <a class="btn btn-sm btn-outline-secondary" href="/admin/login">Admin</a>
          <a class="btn btn-sm btn-warning" href="/aluno/login">Aluno</a>
        <?php else: ?>
          <form method="post" action="/logout" class="d-inline">
            <button type="submit" class="btn btn-sm btn-outline-danger">Sair</button>
          </form>
        <?php endif; ?>
      </div>
    </div>
  </div>
</nav>

<?php if (!empty($flash)): ?>
  <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1200; margin-top: 80px;">
    <div id="flashToastFront" class="toast border-0 shadow-sm text-bg-info" role="alert" aria-live="assertive" aria-atomic="true">
      <div class="d-flex">
        <div class="toast-body"><?= htmlspecialchars($flash, ENT_QUOTES, 'UTF-8') ?></div>
        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Fechar"></button>
      </div>
    </div>
  </div>
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const toastEl = document.getElementById('flashToastFront');
      if (!toastEl || typeof bootstrap === 'undefined') return;
      const toast = new bootstrap.Toast(toastEl, {
        delay: 3500
      });
      toast.show();
    });
  </script>
<?php endif; ?>
