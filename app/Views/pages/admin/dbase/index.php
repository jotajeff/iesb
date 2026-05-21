<section class="container py-4">
  <div class="bg-white border rounded-3 p-4 shadow-sm">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
      <h4 class="mb-0"><i class="bi bi-database me-2"></i>Explorador de Banco de Dados</h4>
      <span class="text-muted small">
        <i class="bi bi-server me-1"></i>162.241.2.92 / <?= htmlspecialchars($dbName ?? '', ENT_QUOTES, 'UTF-8') ?>
      </span>
    </div>

    <?php if (!empty($error)): ?>
      <div class="alert alert-danger"><i class="bi bi-exclamation-triangle me-1"></i><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>

    <?php if ($currentTable !== ''): ?>
      <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
        <a class="btn btn-outline-secondary btn-sm" href="/admin/dbase"><i class="bi bi-arrow-left me-1"></i>Voltar para tabelas</a>
        <span class="text-muted small">Tabela: <code><?= htmlspecialchars($currentTable, ENT_QUOTES, 'UTF-8') ?></code></span>
        <span class="badge bg-primary"><?= $totalRows ?? 0 ?> registros</span>
      </div>

      <div class="table-responsive">
        <table class="table table-striped table-hover table-sm table-bordered align-middle">
          <thead class="table-light">
            <tr>
              <?php foreach ($columns ?? [] as $col): ?>
                <th class="text-nowrap small"><?= htmlspecialchars($col, ENT_QUOTES, 'UTF-8') ?></th>
              <?php endforeach; ?>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($rows)): ?>
              <tr><td colspan="<?= count($columns ?? []) ?>" class="text-muted text-center"><i class="bi bi-inbox me-1"></i>Nenhum registro encontrado.</td></tr>
            <?php else: ?>
              <?php foreach ($rows as $row): ?>
                <tr>
                  <?php foreach ($columns ?? [] as $col): ?>
                    <td class="small" style="max-width: 300px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="<?= htmlspecialchars((string) ($row[$col] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                      <?= htmlspecialchars((string) ($row[$col] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                    </td>
                  <?php endforeach; ?>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    <?php else: ?>
      <p class="text-muted mb-3">Selecione uma tabela para visualizar os registros.</p>

      <div class="row g-3">
        <?php foreach ($tables ?? [] as $table): ?>
          <div class="col-md-6 col-lg-4">
            <a href="/admin/dbase?table=<?= htmlspecialchars($table['name'], ENT_QUOTES, 'UTF-8') ?>" class="text-decoration-none">
              <div class="card border shadow-sm h-100 table-card">
                <div class="card-body d-flex align-items-center gap-3 p-3">
                  <div class="table-icon">
                    <i class="bi bi-table"></i>
                  </div>
                  <div>
                    <h6 class="mb-0 table-name"><?= htmlspecialchars($table['name'], ENT_QUOTES, 'UTF-8') ?></h6>
                    <small class="text-muted"><?= (int) $table['row_count'] ?> registros</small>
                  </div>
                </div>
              </div>
            </a>
          </div>
        <?php endforeach; ?>

        <?php if (empty($tables)): ?>
          <div class="col-12 text-center text-muted">
            <i class="bi bi-database-x fs-1 d-block mb-2"></i>
            Nenhuma tabela encontrada no banco de dados.
          </div>
        <?php endif; ?>
      </div>
    <?php endif; ?>
  </div>
</section>

<style>
.table-card {
  transition: all 0.3s ease;
  cursor: pointer;
}
.table-card:hover {
  transform: translateY(-3px);
  box-shadow: 0 6px 20px rgba(0,0,0,0.1) !important;
  border-color: var(--primary) !important;
}
.table-card:hover .table-name {
  color: var(--primary);
}
.table-icon {
  width: 42px;
  height: 42px;
  border-radius: 10px;
  background: rgba(var(--primary-rgb), 0.1);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.3rem;
  color: var(--primary);
  flex-shrink: 0;
}
</style>
