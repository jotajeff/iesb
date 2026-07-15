<section class="container py-4">
  <div class="bg-white border rounded-3 p-4 shadow-sm">
    <?php
    $currentRole = (string) ($authUser['role'] ?? '');
    $currentUserId = (int) ($authUser['id'] ?? 0);
    $isAdmin = $currentRole === 'admin';
    $logEntries = $logs ?? [];
    $currentPerfil = $perfil ?? '';
    $currentNome = $nome ?? '';

    if (!$isAdmin && $currentUserId > 0) {
      $logEntries = array_values(array_filter($logEntries, static fn(array $log): bool => (int) ($log['usuario_id'] ?? 0) === $currentUserId));
    }

    function queryString(array $extra = []): string {
      $params = array_merge($_GET, $extra);
      return http_build_query($params);
    }
    ?>
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
      <h4 class="mb-0"><i class="bi bi-clock-history"></i> &nbsp; Logs</h4>
      <?php if (!$isAdmin): ?>
        <span class="badge bg-secondary">Exibindo apenas seus registros</span>
      <?php endif; ?>
    </div>

    <?php if ($isAdmin): ?>
    <div class="mb-3">
      <div class="d-flex flex-wrap gap-2 align-items-center">
        <div class="btn-group btn-group-sm">
          <a href="?<?= queryString(['perfil' => 'sistema']) ?>"
             class="btn <?= $currentPerfil !== 'aluno' ? 'btn-primary' : 'btn-outline-secondary' ?>">
            <i class="bi bi-server me-1"></i>Sistema
          </a>
          <a href="?<?= queryString(['perfil' => 'aluno']) ?>"
             class="btn <?= $currentPerfil === 'aluno' ? 'btn-primary' : 'btn-outline-secondary' ?>">
            <i class="bi bi-people me-1"></i>Aluno
          </a>
        </div>

        <form method="get" action="/admin/logs" class="d-flex gap-2 ms-2">
          <?php if ($currentPerfil !== ''): ?>
            <input type="hidden" name="perfil" value="<?= htmlspecialchars($currentPerfil, ENT_QUOTES, 'UTF-8') ?>">
          <?php endif; ?>
          <input type="text" name="nome" class="form-control form-control-sm" style="width:220px;"
                 placeholder="Buscar por nome..." value="<?= htmlspecialchars($currentNome, ENT_QUOTES, 'UTF-8') ?>">
          <button class="btn btn-sm btn-outline-primary" type="submit"><i class="bi bi-search"></i></button>
          <?php if ($currentNome !== ''): ?>
            <a href="?<?= queryString(['nome' => '', 'page' => 1]) ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-x-lg"></i></a>
          <?php endif; ?>
        </form>
      </div>
    </div>
    <?php endif; ?>

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
                  <?php $nomeLog = trim((string) ($log['usuario_nome'] ?? '')); ?>
                  <?= $nomeLog !== '' ? htmlspecialchars($nomeLog, ENT_QUOTES, 'UTF-8') : ('#' . (int) ($log['usuario_id'] ?? 0)) ?>
                </td>
                 <td><?= \App\Helpers\LogHelper::render((string) $log['acao']) ?></td>
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
            <a class="page-link" href="?<?= queryString(['page' => $pagination['currentPage'] - 1]) ?>">Anterior</a>
          </li>
          <?php for ($p = 1; $p <= $pagination['totalPages']; $p++): ?>
            <li class="page-item <?= $p === $pagination['currentPage'] ? 'active' : '' ?>">
              <a class="page-link" href="?<?= queryString(['page' => $p]) ?>"><?= $p ?></a>
            </li>
          <?php endfor; ?>
          <li class="page-item <?= $pagination['currentPage'] >= $pagination['totalPages'] ? 'disabled' : '' ?>">
            <a class="page-link" href="?<?= queryString(['page' => $pagination['currentPage'] + 1]) ?>">Próximo</a>
          </li>
        </ul>
      </nav>

      <div class="text-muted small text-center mt-2">
        Exibindo <?= count($logEntries) ?> de <?= $pagination['total'] ?> registro(s)
      </div>
    <?php endif; ?>
  </div>
</section>
