<nav class="navbar navbar-expand-lg" style="background: #1f2937 !important; border-bottom: 0; position: sticky; top:0; z-index:1100;">
  <div class="container">
    <a class="navbar-brand text-white" href="/admin"><i class="bi bi-shield-lock-fill me-2" style="color:#facc15"></i>Admin IESB</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#adminNavbar">
      <span class="navbar-toggler-icon"><span></span></span>
    </button>
    <div class="collapse navbar-collapse" id="adminNavbar">
      <ul class="navbar-nav me-auto">
        <li class="nav-item"><a class="nav-link <?= ($currentRoute ?? '') === '/admin' ? 'active' : '' ?>" href="/admin">Dashboard</a></li>
        <li class="nav-item"><a class="nav-link <?= ($currentRoute ?? '') === '/admin/logs' ? 'active' : '' ?>" href="/admin/logs">Logs</a></li>
        <li class="nav-item"><a class="nav-link <?= ($currentRoute ?? '') === '/admin/visitas' ? 'active' : '' ?>" href="/admin/visitas">Visitas</a></li>
      </ul>
      <div class="d-flex align-items-center gap-2">
        <span class="text-white-50 small"><?= htmlspecialchars($authUser['name'] ?? 'Administrador', ENT_QUOTES, 'UTF-8') ?></span>
        <form method="post" action="/logout" class="d-inline">
          <button type="submit" class="btn btn-sm btn-outline-light">Sair</button>
        </form>
      </div>
    </div>
  </div>
</nav>

<?php if (!empty($flash)): ?>
  <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1300; margin-top: 70px;">
    <div id="flashToastAdmin" class="toast border-0 shadow-sm text-bg-info" role="alert" aria-live="assertive" aria-atomic="true">
      <div class="d-flex">
        <div class="toast-body"><?= htmlspecialchars($flash, ENT_QUOTES, 'UTF-8') ?></div>
        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Fechar"></button>
      </div>
    </div>
  </div>
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      const toastEl = document.getElementById('flashToastAdmin');
      if (!toastEl || typeof bootstrap === 'undefined') return;
      const toast = new bootstrap.Toast(toastEl, { delay: 3500 });
      toast.show();
    });
  </script>
<?php endif; ?>
