<?php if (!empty($authUser)): ?>
  <?php
  $auth = new \App\Services\AuthService();
  $can = fn(string $route): bool => $auth->hasPermission($route, 'consultar');
  $groups = [
      ['Cadastros', 'bi-journal-bookmark-fill', [
          ['/admin/matriculas', 'Matrículas', 'bi-card-checklist'], ['/admin/alunos', 'Alunos', 'bi-person-badge'], ['/admin/preinscricao', 'Pré-inscrições', 'bi-inbox'],
      ]],
      ['Secretaria', 'bi-folder2-open', [
          ['/admin/tarefas', 'Kanban', 'bi-kanban'], ['/admin/tarefas/lista', 'Lista de tarefas', 'bi-list-ul'], ['/admin/notificacoes', 'Notificações', 'bi-bell'],
          ['/admin/protocolos', 'Protocolos', 'bi-chat-left-text'], ['/admin/documentos', 'Documentos', 'bi-folder2-open'], ['/admin/chamadas', 'Chamadas', 'bi-clipboard-check'],
          ['/admin/chamadas/relatorio', 'Relatório de Presenças', 'bi-clipboard-data'], ['/admin/material', 'Material', 'bi-folder2-open'], ['/admin/matriculas/notificacao', 'Email Matrículas', 'bi-envelope-paper'],
      ]],
      ['Acadêmico', 'bi-book', [
          ['/admin/cursos', 'Cursos', 'bi-journal-bookmark-fill'], ['/admin/academico/matrizes', 'Matrizes Curriculares', 'bi-diagram-3'], ['/admin/academico/modulos', 'Módulos', 'bi-grid-3x3-gap'],
          ['/admin/turmas', 'Turmas', 'bi-people'], ['/admin/professores', 'Professores', 'bi-person-workspace'], ['/admin/academico/situacao', 'Situação Acadêmica', 'bi-clipboard-check'],
          ['/admin/professores/turmas', 'Minhas Turmas', 'bi-people'], ['/admin/professores/perfil', 'Meu Perfil', 'bi-person-circle'], ['/admin/professores/documentos', 'Meus Documentos', 'bi-folder2-open'],
      ]],
      ['Asaas', 'bi-currency-dollar', [
          ['/admin/asaas', 'Cobranças', 'bi-receipt'],
      ]],
      ['Acesso', 'bi-shield-lock', [
          ['/admin/usuarios', 'Usuários', 'bi-people'], ['/admin/funcao', 'Funções de usuários', 'bi-person-gear'], ['/admin/logs', 'Logs', 'bi-clipboard-data'],
          ['/admin/visitas', 'Visitas', 'bi-eye'], ['/admin/visitas/mensal', 'Visitas por mês', 'bi-calendar-month'], ['/admin/visitas/analytics', 'Analytics', 'bi-graph-up'],
          ['/admin/visitas/paginas', 'Visitas por página', 'bi-file-earmark-text'], ['/admin/visitas/cursos', 'Visitas por curso', 'bi-journal-bookmark-fill'], ['/admin/visitas/referer', 'Referer & UTM', 'bi-link-45deg'],
      ]],
      ['Conteúdo', 'bi-file-earmark-text', [
          ['/admin/sessao', 'Sessões', 'bi-layers'], ['/admin/config/carousel', 'Carrossel', 'bi-images'], ['/admin/config/banner-aluno', 'Banner-Aluno', 'bi-easel'], ['/admin/config/noticias', 'Notícias', 'bi-newspaper'],
      ]],
      ['Setup', 'bi-gear', [
          ['/admin/modalidade', 'Modalidade', 'bi-journal-bookmark-fill'], ['/admin/segmento', 'Segmento', 'bi-diagram-3'], ['/admin/setor', 'Setor', 'bi-building'], ['/admin/nivel', 'Nível', 'bi-bar-chart-steps'],
          ['/admin/tipos-documentos', 'Tipos documentos', 'bi-file-earmark-text'], ['/admin/storage', 'Storage', 'bi-google'], ['/admin/config/email', 'Email', 'bi-envelope'], ['/admin/config/categoria', 'Categoria', 'bi-newspaper'],
          ['/admin/funcoes-docente', 'Funções Docente', 'bi-person-workspace'], ['/admin/config/cliente', 'Configuração', 'bi-building-gear'], ['/admin/dbase', 'DB', 'bi-database'], ['/admin/permissoes', 'Permissões', 'bi-shield-check'],
      ]],
  ];
  ?>
  <nav class="navbar navbar-expand-lg" style="background: #1f2937 !important; border-bottom: 0; position: sticky; top:0; z-index:1100;">
    <div class="container">
      <a class="navbar-brand text-white" href="/admin"><span class="d-inline-flex align-items-center justify-content-center rounded-circle" style="width: 44px; height: 44px; background: rgba(255,255,255,0.15); backdrop-filter: blur(2px);"><img src="/assets/img/logo-top-admin.png" alt="Logo" class="img-fluid" style="max-height: 32px;"></span></a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#adminNavbar"><span class="navbar-toggler-icon"><span></span></span></button>
      <div class="collapse navbar-collapse" id="adminNavbar">
        <ul class="navbar-nav me-auto">
          <?php if ($can('/admin') || $can('/admin/dashboard')): ?>
            <li class="nav-item"><a class="nav-link <?= ($currentRoute ?? '') === '/admin' || ($currentRoute ?? '') === '/admin/dashboard' ? 'active' : '' ?>" href="/admin"><i class="bi bi-speedometer2 me-1"></i>Dashboard</a></li>
          <?php endif; ?>
          <?php foreach ($groups as [$label, $icon, $items]): ?>
            <?php $visibleItems = array_values(array_filter($items, fn(array $item): bool => $can($item[0]))); ?>
            <?php if ($visibleItems === []) { continue; } ?>
            <?php $groupActive = false; foreach ($visibleItems as $item) { if (str_starts_with((string) ($currentRoute ?? ''), $item[0])) { $groupActive = true; break; } } ?>
            <li class="nav-item dropdown">
              <a class="nav-link dropdown-toggle <?= $groupActive ? 'active' : '' ?>" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false"><i class="bi <?= htmlspecialchars($icon, ENT_QUOTES, 'UTF-8') ?> me-1"></i><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></a>
              <ul class="dropdown-menu">
                <?php foreach ($visibleItems as [$route, $itemLabel, $itemIcon]): ?>
                  <li><a class="dropdown-item" href="<?= htmlspecialchars($route, ENT_QUOTES, 'UTF-8') ?>"><i class="bi <?= htmlspecialchars($itemIcon, ENT_QUOTES, 'UTF-8') ?> me-1"></i><?= htmlspecialchars($itemLabel, ENT_QUOTES, 'UTF-8') ?></a></li>
                <?php endforeach; ?>
              </ul>
            </li>
          <?php endforeach; ?>
        </ul>
        <div class="d-flex align-items-center gap-2"><span class="text-white-50 small"><?= htmlspecialchars($authUser['name'] ?? 'Administrador', ENT_QUOTES, 'UTF-8') ?></span><form method="post" action="/logout" class="d-inline"><button type="submit" class="btn btn-sm btn-outline-light">Sair</button></form></div>
      </div>
    </div>
  </nav>
<?php endif; ?>

<?php if (!empty($flash)): ?>
  <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1300; margin-top: 70px;"><div id="flashToastAdmin" class="toast border-0 shadow-sm text-bg-info" role="alert" aria-live="assertive" aria-atomic="true"><div class="d-flex"><div class="toast-body"><?= htmlspecialchars($flash, ENT_QUOTES, 'UTF-8') ?></div><button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Fechar"></button></div></div></div>
  <script>document.addEventListener('DOMContentLoaded', function() { const toastEl = document.getElementById('flashToastAdmin'); if (!toastEl || typeof bootstrap === 'undefined') return; new bootstrap.Toast(toastEl, { delay: 3500 }).show(); });</script>
<?php endif; ?>
