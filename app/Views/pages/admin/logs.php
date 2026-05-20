<section class="container py-4">
  <div class="bg-white border rounded-3 p-4 shadow-sm">
    <h4 class="mb-3">Logs de Auditoria</h4>
    <div class="table-responsive">
      <table class="table table-sm align-middle">
        <thead>
          <tr>
            <th>#</th>
            <th>Perfil</th>
            <th>Ação</th>
            <th>Entidade</th>
            <th>Usuário</th>
            <th>IP</th>
            <th>Data</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($logs)): ?>
            <tr><td colspan="8" class="text-muted">Sem registros de log.</td></tr>
          <?php endif; ?>
          <?php foreach (($logs ?? []) as $log): ?>
            <tr>
              <td><?= (int) $log['id'] ?></td>
              <td><?= htmlspecialchars((string) $log['perfil'], ENT_QUOTES, 'UTF-8') ?></td>
              <td><?= htmlspecialchars((string) $log['acao'], ENT_QUOTES, 'UTF-8') ?></td>
              <td><?= htmlspecialchars((string) ($log['entidade'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
              <td><?= (int) ($log['usuario_id'] ?? 0) ?></td>
              <td><?= htmlspecialchars((string) ($log['ip'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
              <td><?= htmlspecialchars((string) $log['created_at'], ENT_QUOTES, 'UTF-8') ?></td>
              <td>
                <?php if ((int) ($log['sucesso'] ?? 0) === 1): ?>
                  <span class="badge text-bg-success">Sucesso</span>
                <?php else: ?>
                  <span class="badge text-bg-danger">Falha</span>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</section>
