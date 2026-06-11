<section class="container py-4">
    <div class="bg-white border rounded-3 p-4 shadow-sm">
      <?php
        $currentRole = (string) ($authUser['role'] ?? '');
        $currentUserId = (int) ($authUser['id'] ?? 0);
        $isAdmin = $currentRole === 'admin';
        $canCreateUser = $isAdmin;
      ?>
      <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <h4 class="mb-0"><i class="bi bi-people me-2"></i>Usuários IESB</h4>
        <div class="d-flex flex-wrap align-items-center gap-2">
          <?php if ($canCreateUser): ?>
            <a class="btn btn-primary btn-sm" href="/admin/usuarios/novo"><i class="bi bi-plus-circle me-1"></i>Novo usuário</a>
          <?php endif; ?>
        </div>
      </div>

    <div class="table-responsive">
      <table class="table table-striped table-sm align-middle">
        <thead>
          <tr>
            <th><i class="bi bi-hash"></i></th>
            <th><i class="bi bi-person me-1"></i>Nome</th>
            <th><i class="bi bi-envelope me-1"></i>Email</th>
            <th><i class="bi bi-tag me-1"></i>Tipo</th>
            <th><i class="bi bi-toggle-on me-1"></i>Ativo</th>
            <th><i class="bi bi-gear me-1"></i>Ações</th>
          </tr>
        </thead>
        <tbody>
          <?php
          $filtered = $isAdmin
              ? array_filter($usuarios ?? [], fn($u) => ((string) ($u['tipo'] ?? '')) !== 'professor')
              : array_filter($usuarios ?? [], fn($u) => (int) ($u['id'] ?? 0) === $currentUserId);

          $order = ['admin' => 0, 'operador' => 1];
          usort($filtered, fn($a, $b) =>
              (($order[(string) ($a['tipo'] ?? '')] ?? 9) <=> ($order[(string) ($b['tipo'] ?? '')] ?? 9))
              ?: strcasecmp((string) ($a['nome'] ?? ''), (string) ($b['nome'] ?? ''))
          );
          ?>

          <?php if (empty($filtered)): ?>
            <tr><td colspan="6" class="text-muted"><i class="bi bi-inbox me-1"></i>Nenhum usuário encontrado.</td></tr>
          <?php endif; ?>

          <?php foreach ($filtered as $user): ?>
            <?php $userId = (int) ($user['id'] ?? 0); ?>
            <tr>
              <td><?= $userId ?></td>
              <td><?= htmlspecialchars((string) ($user['nome'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
              <td><?= htmlspecialchars((string) ($user['email'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
              <td>
                <?php $tipo = (string) ($user['tipo'] ?? 'aluno'); ?>
                <?php if ($tipo === 'admin'): ?>
                  <span class="badge bg-dark">Admin</span>
                <?php elseif ($tipo === 'operador'): ?>
                  <span class="badge bg-warning text-dark">Operador</span>
                <?php elseif ($tipo === 'professor'): ?>
                  <span class="badge bg-primary">Professor</span>
                <?php else: ?>
                  <span class="badge bg-info text-dark">Aluno</span>
                <?php endif; ?>
              </td>
              <td>
                <?php $ativo = (int) ($user['ativo'] ?? 1); ?>
                <?php if ($ativo === 1): ?>
                  <span class="badge bg-success">Sim</span>
                <?php else: ?>
                  <span class="badge bg-secondary">Não</span>
                <?php endif; ?>
              </td>
              <td>
                <?php if ($isAdmin || $userId === $currentUserId): ?>
                  <a class="btn btn-outline-secondary btn-sm" href="/admin/usuarios/editar?id=<?= $userId ?>">
                    <i class="bi bi-pencil-square me-1"></i>Editar
                  </a>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
          <?php if (!empty($filtered)): ?>
            <tr class="table-secondary fw-semibold">
              <td colspan="6" class="text-end">Total: <?= count($filtered) ?> usuário(s)</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</section>
