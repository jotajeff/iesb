<section class="container py-4">
  <div class="bg-white border rounded-3 p-4 shadow-sm">
    <?php
    $currentRole = (string) ($authUser['role'] ?? '');
    $currentUserId = (int) ($authUser['id'] ?? 0);
    $isAdmin = $currentRole === 'admin';
    $logEntries = $logs ?? [];
    if (!$isAdmin && $currentUserId > 0) {
      $logEntries = array_values(array_filter($logEntries, static fn(array $log): bool => (int) ($log['usuario_id'] ?? 0) === $currentUserId));
    }
    ?>
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
      <h4 class="mb-0"><i class="bi bi-clock-history"></i> &nbsp; Logs </h4>
      <?php if (!$isAdmin): ?>
        <span class="badge bg-secondary">Exibindo apenas seus registros</span>
      <?php endif; ?>
    </div>
    <div class="table-responsive">
      <table class="table table-sm align-middle">
        <thead>
          <tr>
            <th>#</th>
            <th>Ação</th>
            <th>Entidade</th>
            <th>Usuário</th>
            <th>IP</th>
            <th>Data</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($logEntries)): ?>
            <tr>
              <td colspan="7" class="text-muted">Sem registros de log.</td>
            </tr>
          <?php endif; ?>
          <?php foreach ($logEntries as $log): ?>
             <?php
               $createdAtRaw = (string) ($log['created_at'] ?? '');
               $createdAtFormatted = '-';
               if ($createdAtRaw !== '') {
                   try {
                       $dateObj = new DateTime($createdAtRaw);
                       $createdAtFormatted = $dateObj->format('d/m/Y H:i');
                   } catch (Throwable $e) {
                       $createdAtFormatted = $createdAtRaw;
                   }
               }
             ?>
             <tr>
               <td><?= (int) $log['id'] ?></td>
               <td><?= htmlspecialchars((string) $log['acao'], ENT_QUOTES, 'UTF-8') ?></td>
               <td><?= htmlspecialchars((string) ($log['entidade'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
               <td>
                 <?php $nome = trim((string) ($log['usuario_nome'] ?? '')); ?>
                 <?= $nome !== '' ? htmlspecialchars($nome, ENT_QUOTES, 'UTF-8') : ('#' . (int) ($log['usuario_id'] ?? 0)) ?>
               </td>
               <td><?= htmlspecialchars((string) ($log['ip'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
               <td><?= htmlspecialchars($createdAtFormatted, ENT_QUOTES, 'UTF-8') ?></td>
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
