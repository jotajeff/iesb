<section class="container py-4">
  <div class="bg-white border rounded-3 p-4 shadow-sm">
    <h4 class="mb-3"><i class="bi bi-clock-history"></i> &nbsp; Meus Logs</h4>
    <div class="table-responsive">
      <table class="table table-sm align-middle">
        <thead>
          <tr>
            <th>#</th>
            <th>Aluno</th>
            <th>Ação</th>
            <th>Entidade</th>
            <th>Descrição</th>
            <th>IP</th>
            <th>Data</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($entries ?? [])): ?>
            <tr>
              <td colspan="7" class="text-muted">Nenhum registro de log.</td>
            </tr>
          <?php endif; ?>
          <?php foreach ($entries as $log): ?>
            <?php
              $createdAtRaw = (string) ($log['created_at'] ?? '');
              $createdAt = '-';
              if ($createdAtRaw !== '') {
                  try {
                      $dateObj = new DateTime($createdAtRaw);
                      $createdAt = $dateObj->format('d/m/Y H:i');
                  } catch (Throwable $e) {
                      $createdAt = $createdAtRaw;
                  }
              }
            ?>
            <tr>
              <td><?= (int) ($log['id'] ?? 0) ?></td>
              <td><?= htmlspecialchars((string) ($log['aluno_nome'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
              <td><?= \App\Helpers\LogHelper::render((string) ($log['acao'] ?? '-')) ?></td>
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
              <td><?= htmlspecialchars($createdAt, ENT_QUOTES, 'UTF-8') ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</section>
