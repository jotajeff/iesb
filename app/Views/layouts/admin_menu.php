<?php if (!empty($authUser)): ?>
  <?php
  $auth = new \App\Services\AuthService();
  $can = fn(string $route): bool => $auth->hasExactPermission($route, 'consultar');
  $modulosMenu = (new \App\Repositories\PermissaoRepository())->listarModulos();
  $groups = [];
  foreach ($modulosMenu as $modulo) {
      if ((int) ($modulo['ativo'] ?? 0) !== 1 || empty($modulo['rota']) || empty($modulo['menu_grupo'])) {
          continue;
      }
      $rota = (string) $modulo['rota'];
      if (!$can($rota)) {
          continue;
      }
      $grupo = (string) $modulo['menu_grupo'];
      if (!isset($groups[$grupo])) {
          $groups[$grupo] = [
              'label' => $grupo,
              'icon' => (string) ($modulo['menu_icone'] ?? 'bi-folder'),
              'ordem' => (int) ($modulo['menu_grupo_ordem'] ?? 0),
              'items' => [],
          ];
      }
      $groups[$grupo]['items'][] = $modulo;
  }
  uasort($groups, static fn(array $a, array $b): int => $a['ordem'] <=> $b['ordem']);
  ?>
  <nav class="navbar navbar-expand-lg" style="background: #1f2937 !important; border-bottom: 0; position: sticky; top:0; z-index:1100;">
    <div class="container">
      <a class="navbar-brand text-white" href="/admin/dashboard"><span class="d-inline-flex align-items-center justify-content-center rounded-circle" style="width: 44px; height: 44px; background: rgba(255,255,255,0.15); backdrop-filter: blur(2px);"><img src="/assets/img/logo-top-admin.png" alt="Logo" class="img-fluid" style="max-height: 32px;"></span></a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#adminNavbar"><span class="navbar-toggler-icon"><span></span></span></button>
      <div class="collapse navbar-collapse" id="adminNavbar">
        <ul class="navbar-nav me-auto">
          <?php if ($can('/admin') || $can('/admin/dashboard')): ?>
            <li class="nav-item"><a class="nav-link <?= ($currentRoute ?? '') === '/admin' || ($currentRoute ?? '') === '/admin/dashboard' ? 'active' : '' ?>" href="/admin/dashboard"><i class="bi bi-speedometer2 me-1"></i>Dashboard</a></li>
          <?php endif; ?>
          <?php foreach ($groups as $group): ?>
            <?php $label = $group['label']; $icon = $group['icon']; $visibleItems = $group['items']; ?>
            <?php $groupActive = false; foreach ($visibleItems as $item) { if (str_starts_with((string) ($currentRoute ?? ''), (string) $item['rota'])) { $groupActive = true; break; } } ?>
            <li class="nav-item dropdown">
              <a class="nav-link dropdown-toggle <?= $groupActive ? 'active' : '' ?>" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false"><i class="bi <?= htmlspecialchars($icon, ENT_QUOTES, 'UTF-8') ?> me-1"></i><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></a>
              <ul class="dropdown-menu">
                <?php foreach ($visibleItems as $item): $route = (string) $item['rota']; $itemLabel = (string) $item['nome']; $itemIcon = (string) ($item['icone'] ?? 'bi-circle'); ?>
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
