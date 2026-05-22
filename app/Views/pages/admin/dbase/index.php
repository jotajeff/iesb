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
        <?php if ($viewMode === 'structure'): ?>
          <a class="btn btn-outline-primary btn-sm ms-auto" href="/admin/dbase?table=<?= htmlspecialchars($currentTable, ENT_QUOTES, 'UTF-8') ?>&view=records">
            <i class="bi bi-list-ul me-1"></i>Ver registros
          </a>
          <?php if ($totalRows > 0): ?>
            <span class="badge bg-info"><?= $totalRows ?> registros</span>
          <?php endif; ?>
        <?php else: ?>
          <a class="btn btn-outline-info btn-sm ms-auto" href="/admin/dbase?table=<?= htmlspecialchars($currentTable, ENT_QUOTES, 'UTF-8') ?>">
            <i class="bi bi-diagram-3 me-1"></i>Ver estrutura
          </a>
          <span class="badge bg-primary"><?= $totalRows ?? 0 ?> registros</span>
        <?php endif; ?>
      </div>

      <?php if ($viewMode === 'structure'): ?>
        <div class="table-responsive">
          <table class="table table-striped table-hover table-sm table-bordered align-middle">
            <thead class="table-dark">
              <tr>
                <th class="text-nowrap small">#</th>
                <th class="text-nowrap small">Campo</th>
                <th class="text-nowrap small">Tipo</th>
                <th class="text-nowrap small">Nulo</th>
                <th class="text-nowrap small">Chave</th>
                <th class="text-nowrap small">Padrão</th>
                <th class="text-nowrap small">Extra</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($columns)): ?>
                <tr><td colspan="7" class="text-muted text-center"><i class="bi bi-inbox me-1"></i>Nenhuma coluna encontrada.</td></tr>
              <?php else: ?>
                <?php $idx = 1; ?>
                <?php foreach ($columns as $col): ?>
                  <tr>
                    <td class="small text-muted"><?= $idx++ ?></td>
                    <td class="small fw-semibold"><?= htmlspecialchars($col['Field'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                    <td class="small"><code><?= htmlspecialchars($col['Type'] ?? '', ENT_QUOTES, 'UTF-8') ?></code></td>
                    <td class="small"><?= htmlspecialchars($col['Null'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                    <td class="small"><?= htmlspecialchars($col['Key'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                    <td class="small"><?= htmlspecialchars($col['Default'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                    <td class="small"><?= htmlspecialchars($col['Extra'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      <?php else: ?>
        <div class="table-responsive">
          <table class="table table-striped table-hover table-sm table-bordered align-middle">
            <thead class="table-light">
              <tr>
                <?php foreach ($columns ?? [] as $col): ?>
                  <th class="text-nowrap small"><?= htmlspecialchars($col['Field'] ?? '', ENT_QUOTES, 'UTF-8') ?></th>
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
                      <td class="small" style="max-width: 300px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="<?= htmlspecialchars((string) ($row[$col['Field']] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                        <?= htmlspecialchars((string) ($row[$col['Field']] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                      </td>
                    <?php endforeach; ?>
                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    <?php else: ?>
      <p class="text-muted mb-3">Selecione uma tabela para visualizar a estrutura.</p>

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
