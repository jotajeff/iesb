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
      <table class="table table-sm table-striped table-hover align-middle">
        <thead>
          <tr>
            <th>#</th>
            <th>Usuário</th>
            <th>Ação</th>
            <th>Entidade</th>
            <th>Descrição</th>
            <th>IP</th>
            <th>Data</th>
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
                <td>
                  <?php $nome = trim((string) ($log['usuario_nome'] ?? '')); ?>
                  <?= $nome !== '' ? htmlspecialchars($nome, ENT_QUOTES, 'UTF-8') : ('#' . (int) ($log['usuario_id'] ?? 0)) ?>
                </td>
                <td><?= htmlspecialchars((string) $log['acao'], ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars((string) ($log['entidade'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars((string) ($log['descricao'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                <td>
                  <span title="<?= htmlspecialchars((string) ($log['ip'] ?? '-'), ENT_QUOTES, 'UTF-8') ?>">
                    <?php $loc = $log['location'] ?? []; ?>
                    <?= $loc['flag'] ?? '🏳️' ?>
                    <?= htmlspecialchars((string) ($loc['country'] ?? '-'), ENT_QUOTES, 'UTF-8') ?>
                    <?php if (!empty($loc['city']) && $loc['city'] !== '-'): ?>
                      / <?= htmlspecialchars($loc['city'], ENT_QUOTES, 'UTF-8') ?>
                    <?php endif; ?>
                  </span>
                </td>
                <td><?= htmlspecialchars($createdAtFormatted, ENT_QUOTES, 'UTF-8') ?></td>
             </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <?php if ($isAdmin && !empty($pagination) && $pagination['totalPages'] > 1): ?>
      <nav class="d-flex justify-content-center mt-3">
        <ul class="pagination pagination-sm mb-0">
          <li class="page-item <?= $pagination['currentPage'] <= 1 ? 'disabled' : '' ?>">
            <a class="page-link" href="?page=<?= $pagination['currentPage'] - 1 ?>">Anterior</a>
          </li>
          <?php for ($p = 1; $p <= $pagination['totalPages']; $p++): ?>
            <li class="page-item <?= $p === $pagination['currentPage'] ? 'active' : '' ?>">
              <a class="page-link" href="?page=<?= $p ?>"><?= $p ?></a>
            </li>
          <?php endfor; ?>
          <li class="page-item <?= $pagination['currentPage'] >= $pagination['totalPages'] ? 'disabled' : '' ?>">
            <a class="page-link" href="?page=<?= $pagination['currentPage'] + 1 ?>">Próximo</a>
          </li>
        </ul>
      </nav>

      <div class="text-muted small text-center mt-2">
        Exibindo <?= count($logEntries) ?> de <?= $pagination['total'] ?> registro(s)
      </div>
    <?php endif; ?>
  </div>
</section>
