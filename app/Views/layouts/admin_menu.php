<nav class="navbar navbar-expand-lg" style="background: #1f2937 !important; border-bottom: 0; position: sticky; top:0; z-index:1100;">
  <div class="container">
    <a class="navbar-brand text-white" href="/admin">/</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#adminNavbar">
      <span class="navbar-toggler-icon"><span></span></span>
    </button>
    <div class="collapse navbar-collapse" id="adminNavbar">
      <ul class="navbar-nav me-auto">
        <li class="nav-item"><a class="nav-link <?= ($currentRoute ?? '') === '/admin' ? 'active' : '' ?>" href="/admin"><i class="bi bi-speedometer2 me-1"></i>Dashboard</a></li>
        <li class="nav-item"><a class="nav-link <?= ($currentRoute ?? '') === '/admin/cursos' ? 'active' : '' ?>" href="/admin/cursos"><i class="bi bi-journal-bookmark-fill me-1"></i>Cursos</a></li>
        <li class="nav-item"><a class="nav-link <?= (strpos((string) ($currentRoute ?? ''), '/admin/tarefas') === 0 ? 'active' : '') ?>" href="/admin/tarefas"><i class="bi bi-kanban me-1"></i>Tarefas</a></li>
        
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle <?= ((strpos($currentRoute ?? '', '/admin/usuarios') === 0) || (($currentRoute ?? '') === '/admin/logs')) ? 'active' : '' ?>" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false"><i class="bi bi-shield-lock me-1"></i>Acesso</a>
          <ul class="dropdown-menu">
            <li><a class="dropdown-item" href="/admin/usuarios"><i class="bi bi-people me-1"></i>Usuários</a></li>
            <li><a class="dropdown-item" href="/admin/logs"><i class="bi bi-clipboard-data me-1"></i>Logs</a></li>
          </ul>
        </li>
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle <?= ($currentRoute ?? '') === '/admin/visitas' ? 'active' : '' ?>" href="/admin/visitas" role="button" data-bs-toggle="dropdown" aria-expanded="false"><i class="bi bi-eye me-1"></i>Visitas</a>
          <ul class="dropdown-menu">
            <li><a class="dropdown-item" href="/admin/visitas"><i class="bi bi-list-ul me-1"></i>Lista</a></li>
            <li><a class="dropdown-item" href="/admin/visitas/mensal"><i class="bi bi-calendar-month me-1"></i>Por mes</a></li>
            <li><a class="dropdown-item" href="/admin/visitas/analytics"><i class="bi bi-graph-up me-1"></i>Analytics</a></li>
            <li><a class="dropdown-item" href="/admin/visitas/paginas"><i class="bi bi-file-earmark-text me-1"></i>Por pagina</a></li>
          </ul>
        </li>

        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle <?= ((strpos($currentRoute ?? '', '/admin/modalidade') === 0) || (strpos($currentRoute ?? '', '/admin/segmento') === 0) || (strpos($currentRoute ?? '', '/admin/setor') === 0) || (strpos($currentRoute ?? '', '/admin/nivel') === 0) || (($currentRoute ?? '') === '/admin/dbase')) ? 'active' : '' ?>" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false"><i class="bi bi-gear me-1"></i>Setup</a>
          <ul class="dropdown-menu">
            <li><a class="dropdown-item" href="/admin/modalidade"><i class="bi bi-journal-bookmark-fill me-1"></i>Modalidade</a></li>
            <li><a class="dropdown-item" href="/admin/segmento"><i class="bi bi-diagram-3 me-1"></i>Segmento</a></li>
            <li><a class="dropdown-item" href="/admin/setor"><i class="bi bi-building me-1"></i>Setor</a></li>
            <li><a class="dropdown-item" href="/admin/nivel"><i class="bi bi-bar-chart-steps me-1"></i>Nível</a></li>
            <li><a class="dropdown-item" href="/admin/dbase"><i class="bi bi-database me-1"></i>DB</a></li>
          </ul>
        </li>
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
    document.addEventListener('DOMContentLoaded', function() {
      const toastEl = document.getElementById('flashToastAdmin');
      if (!toastEl || typeof bootstrap === 'undefined') return;
      const toast = new bootstrap.Toast(toastEl, {
        delay: 3500
      });
      toast.show();
    });
  </script>
<?php endif; ?>
