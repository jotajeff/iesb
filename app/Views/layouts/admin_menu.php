<?php if (!empty($authUser)): ?>
<nav class="navbar navbar-expand-lg" style="background: #1f2937 !important; border-bottom: 0; position: sticky; top:0; z-index:1100;">
  <div class="container">
    <a class="navbar-brand text-white" href="/admin">/</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#adminNavbar">
      <span class="navbar-toggler-icon"><span></span></span>
    </button>
    <div class="collapse navbar-collapse" id="adminNavbar">
      <ul class="navbar-nav me-auto">
        <?php $userRole = (string) ($authUser['role'] ?? ''); ?>
        <?php if ($userRole === 'professor'): ?>
          <li class="nav-item"><a class="nav-link <?= ($currentRoute ?? '') === '/admin' ? 'active' : '' ?>" href="/admin"><i class="bi bi-speedometer2 me-1"></i>Dashboard</a></li>
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle <?= ((strpos((string) ($currentRoute ?? ''), '/admin/tarefas') === 0) || (strpos((string) ($currentRoute ?? ''), '/admin/professores/documentos') === 0)) ? 'active' : '' ?>" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false"><i class="bi bi-folder2-open me-1"></i>Secretaria</a>
            <ul class="dropdown-menu">
              <li><span class="dropdown-item-text fw-bold"><i class="bi bi-kanban me-1"></i>Tarefas</span></li>
              <li><a class="dropdown-item" href="/admin/tarefas" style="padding-left:2.2rem;"><i class="bi bi-kanban me-1"></i>Kanban</a></li>
              <li><a class="dropdown-item" href="/admin/tarefas/lista" style="padding-left:2.2rem;"><i class="bi bi-list-ul me-1"></i>Lista</a></li>
              <li><a class="dropdown-item" href="/admin/notificacoes"><i class="bi bi-bell me-1"></i>Notificações</a></li>
              <li><a class="dropdown-item" href="/admin/professores/documentos"><i class="bi bi-folder2-open me-1"></i>Documentos</a></li>
            </ul>
          </li>
          <li class="nav-item"><a class="nav-link <?= (strpos((string) ($currentRoute ?? ''), '/admin/professores/turmas') === 0 ? 'active' : '') ?>" href="/admin/professores/turmas"><i class="bi bi-people me-1"></i>Minhas Turmas</a></li>
          <li class="nav-item"><a class="nav-link <?= (strpos((string) ($currentRoute ?? ''), '/admin/professores/perfil') === 0 ? 'active' : '') ?>" href="/admin/professores/perfil"><i class="bi bi-person-circle me-1"></i>Perfil</a></li>
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle <?= ((strpos($currentRoute ?? '', '/admin/usuarios') === 0) || (($currentRoute ?? '') === '/admin/logs')) ? 'active' : '' ?>" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false"><i class="bi bi-shield-lock me-1"></i>Acesso</a>
            <ul class="dropdown-menu">
              <li><a class="dropdown-item" href="/admin/usuarios"><i class="bi bi-people me-1"></i>Usuários</a></li>
              <li><a class="dropdown-item" href="/admin/logs"><i class="bi bi-clipboard-data me-1"></i>Logs</a></li>
            </ul>
          </li>
        <?php else: ?>
          <li class="nav-item"><a class="nav-link <?= ($currentRoute ?? '') === '/admin' ? 'active' : '' ?>" href="/admin"><i class="bi bi-speedometer2 me-1"></i>Dashboard</a></li>
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle <?= ((strpos(($currentRoute ?? ''), '/admin/turmas') === 0) || (strpos(($currentRoute ?? ''), '/admin/matriculas') === 0) || (strpos(($currentRoute ?? ''), '/admin/alunos') === 0) || (strpos(($currentRoute ?? ''), '/admin/professores') === 0) || (strpos(($currentRoute ?? ''), '/admin/preinscricao') === 0)) ? 'active' : '' ?>" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false"><i class="bi bi-journal-bookmark-fill me-1"></i>Cadastros</a>
            <ul class="dropdown-menu">
              <li><a class="dropdown-item" href="/admin/turmas"><i class="bi bi-people me-1"></i>Turmas</a></li>
              <li><a class="dropdown-item" href="/admin/matriculas"><i class="bi bi-card-checklist me-1"></i>Matrículas</a></li>
              <li><a class="dropdown-item" href="/admin/alunos"><i class="bi bi-person-badge me-1"></i>Alunos</a></li>
              <li><a class="dropdown-item" href="/admin/professores"><i class="bi bi-person-workspace me-1"></i>Professores</a></li>
              <li><a class="dropdown-item" href="/admin/alunos/troca-historico"><i class="bi bi-arrow-left-right me-1"></i>Troca de Turmas</a></li>
              <li><a class="dropdown-item" href="/admin/preinscricao"><i class="bi bi-inbox me-1"></i>Pré-inscrições</a></li>
            </ul>
          </li>
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle <?= ((strpos($currentRoute ?? '', '/admin/sessao') === 0) || (strpos($currentRoute ?? '', '/admin/config/carousel') === 0) || (strpos($currentRoute ?? '', '/admin/config/noticias') === 0)) ? 'active' : '' ?>" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false"><i class="bi bi-file-earmark-text me-1"></i>Conteúdo</a>
            <ul class="dropdown-menu">
              <li><a class="dropdown-item" href="/admin/sessao"><i class="bi bi-layers me-1"></i>Sessões</a></li>
              <li><a class="dropdown-item" href="/admin/config/carousel"><i class="bi bi-images me-1"></i>Carrossel</a></li>
              <li><a class="dropdown-item" href="/admin/config/noticias"><i class="bi bi-newspaper me-1"></i>Notícias</a></li>
            </ul>
          </li>
          <?php if ($userRole === 'admin'): ?>
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle <?= (strpos((string) ($currentRoute ?? ''), '/admin/asaas') === 0) ? 'active' : '' ?>" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false"><i class="bi bi-currency-dollar me-1"></i>Asaas</a>
            <ul class="dropdown-menu">
              <li><a class="dropdown-item" href="/admin/asaas"><i class="bi bi-receipt me-1"></i>Cobranças</a></li>
            </ul>
          </li>
          <?php endif; ?>
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle <?= (strpos((string) ($currentRoute ?? ''), '/admin/tarefas') === 0 ? 'active' : '') ?>" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false"><i class="bi bi-folder2-open me-1"></i>Secretaria</a>
            <ul class="dropdown-menu">
              <li><span class="dropdown-item-text fw-bold"><i class="bi bi-kanban me-1"></i>Tarefas</span></li>
              <li><a class="dropdown-item" href="/admin/tarefas" style="padding-left:2.2rem;"><i class="bi bi-kanban me-1"></i>Kanban</a></li>
              <li><a class="dropdown-item" href="/admin/tarefas/lista" style="padding-left:2.2rem;"><i class="bi bi-list-ul me-1"></i>Lista</a></li>
              <li><a class="dropdown-item" href="/admin/notificacoes"><i class="bi bi-bell me-1"></i>Notificações</a></li>
            </ul>
          </li>
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle <?= ((strpos($currentRoute ?? '', '/admin/cursos') === 0) || (strpos($currentRoute ?? '', '/admin/academico') === 0)) ? 'active' : '' ?>" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false"><i class="bi bi-book me-1"></i>Acadêmico</a>
            <ul class="dropdown-menu">
              <li><a class="dropdown-item" href="/admin/cursos"><i class="bi bi-journal-bookmark-fill me-1"></i>Cursos</a></li>
              <li><a class="dropdown-item" href="/admin/academico/matrizes"><i class="bi bi-diagram-3 me-1"></i>Matrizes Curriculares</a></li>
              <li><a class="dropdown-item" href="/admin/academico/matrizes"><i class="bi bi-grid-3x3-gap me-1"></i>Módulos</a></li>
              <li><a class="dropdown-item" href="/admin/academico/matrizes"><i class="bi bi-journal-text me-1"></i>Disciplinas</a></li>
              <li><a class="dropdown-item" href="/admin/turmas"><i class="bi bi-people me-1"></i>Turmas</a></li>
              <li><a class="dropdown-item" href="/admin/professores"><i class="bi bi-person-workspace me-1"></i>Professores</a></li>
              <li><a class="dropdown-item" href="#"><i class="bi bi-calendar-event me-1"></i>Calendário</a></li>
              <li><a class="dropdown-item" href="/admin/academico/situacao"><i class="bi bi-clipboard-check me-1"></i>Situação Acadêmica</a></li>
            </ul>
          </li>
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle <?= ((strpos($currentRoute ?? '', '/admin/usuarios') === 0) || (($currentRoute ?? '') === '/admin/logs') || (strpos((string) ($currentRoute ?? ''), '/admin/visitas') === 0)) ? 'active' : '' ?>" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false"><i class="bi bi-shield-lock me-1"></i>Acesso</a>
            <ul class="dropdown-menu">
              <li><a class="dropdown-item" href="/admin/usuarios"><i class="bi bi-people me-1"></i>Usuários</a></li>
              <li><a class="dropdown-item" href="/admin/logs"><i class="bi bi-clipboard-data me-1"></i>Logs</a></li>
              <li><hr class="dropdown-divider"></li>
              <li><a class="dropdown-item" href="/admin/visitas"><i class="bi bi-eye me-1"></i>Visitas</a></li>
              <li><a class="dropdown-item" href="/admin/visitas/mensal"><i class="bi bi-calendar-month me-1"></i>Por mês</a></li>
              <li><a class="dropdown-item" href="/admin/visitas/analytics"><i class="bi bi-graph-up me-1"></i>Analytics</a></li>
              <li><a class="dropdown-item" href="/admin/visitas/paginas"><i class="bi bi-file-earmark-text me-1"></i>Por página</a></li>
              <li><a class="dropdown-item" href="/admin/visitas/cursos"><i class="bi bi-journal-bookmark-fill me-1"></i>Cursos</a></li>
              <li><a class="dropdown-item" href="/admin/visitas/referer"><i class="bi bi-link-45deg me-1"></i>Referer &amp; UTM</a></li>
            </ul>
          </li>
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle <?= ((strpos($currentRoute ?? '', '/admin/modalidade') === 0) || (strpos($currentRoute ?? '', '/admin/segmento') === 0) || (strpos($currentRoute ?? '', '/admin/setor') === 0) || (strpos($currentRoute ?? '', '/admin/nivel') === 0) || (strpos($currentRoute ?? '', '/admin/config/categoria') === 0) || (strpos($currentRoute ?? '', '/admin/config/cliente') === 0) || (strpos($currentRoute ?? '', '/admin/funcoes-docente') === 0) || (strpos($currentRoute ?? '', '/admin/storage') === 0) || (($currentRoute ?? '') === '/admin/dbase')) ? 'active' : '' ?>" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false"><i class="bi bi-gear me-1"></i>Setup</a>
            <ul class="dropdown-menu">
              <li><a class="dropdown-item" href="/admin/modalidade"><i class="bi bi-journal-bookmark-fill me-1"></i>Modalidade</a></li>
              <li><a class="dropdown-item" href="/admin/segmento"><i class="bi bi-diagram-3 me-1"></i>Segmento</a></li>
              <li><a class="dropdown-item" href="/admin/setor"><i class="bi bi-building me-1"></i>Setor</a></li>
              <li><a class="dropdown-item" href="/admin/nivel"><i class="bi bi-bar-chart-steps me-1"></i>Nível</a></li>
              <li><a class="dropdown-item" href="/admin/tipos-documentos"><i class="bi bi-file-earmark-text me-1"></i>Tipos documentos</a></li>
              <li><a class="dropdown-item" href="/admin/storage"><i class="bi bi-google me-1"></i>Storage</a></li>
              <li><a class="dropdown-item" href="/admin/config/categoria"><i class="bi bi-newspaper me-1"></i>Categoria</a></li>
              <li><a class="dropdown-item" href="/admin/funcoes-docente"><i class="bi bi-person-workspace me-1"></i>Funções Docente</a></li>
              <?php if (in_array((int) ($authUser['id'] ?? 0), [1, 6, 7], true)): ?>
              <li><a class="dropdown-item" href="/admin/config/cliente"><i class="bi bi-building-gear me-1"></i>Config</a></li>
              <?php endif; ?>
              <?php if ((int) ($authUser['id'] ?? 0) === 1): ?>
              <li><a class="dropdown-item" href="/admin/dbase"><i class="bi bi-database me-1"></i>DB</a></li>
              <?php endif; ?>
            </ul>
          </li>
        <?php endif; ?>
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
<?php endif; ?>

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
