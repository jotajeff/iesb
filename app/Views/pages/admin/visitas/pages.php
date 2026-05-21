<section class="container py-4">
  <div class="bg-white border rounded-3 p-4 shadow-sm">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
      <h4 class="mb-0">Visitas por Pagina</h4>
      <div class="btn-group" role="group" aria-label="Relatorios de visitas">
        <a class="btn btn-outline-secondary btn-sm" href="/admin/visitas">Lista</a>
        <a class="btn btn-outline-secondary btn-sm" href="/admin/visitas/mensal">Por mes</a>
        <a class="btn btn-outline-secondary btn-sm" href="/admin/visitas/analytics">Analytics</a>
        <a class="btn btn-outline-secondary btn-sm active" href="/admin/visitas/paginas">Por pagina</a>
      </div>
    </div>

    <?php
    $s = $pagesStats ?? ['month' => (int) date('m'), 'year' => (int) date('Y'), 'month_label' => 'Mes', 'total' => 0, 'pages' => []];
    $monthOptions = [
      1 => 'Janeiro', 2 => 'Fevereiro', 3 => 'Marco', 4 => 'Abril', 5 => 'Maio', 6 => 'Junho',
      7 => 'Julho', 8 => 'Agosto', 9 => 'Setembro', 10 => 'Outubro', 11 => 'Novembro', 12 => 'Dezembro',
    ];
    $currentYear = (int) date('Y');
    $yearOptions = [$currentYear - 2, $currentYear - 1, $currentYear, $currentYear + 1];
    ?>
    <form class="row g-2 align-items-end mb-4" method="get" action="/admin/visitas/paginas">
      <div class="col-sm-4 col-md-3">
        <label class="form-label">Mes</label>
        <select class="form-select" name="month">
          <?php foreach ($monthOptions as $monthNumber => $monthLabel): ?>
            <option value="<?= $monthNumber ?>" <?= (int) $s['month'] === $monthNumber ? 'selected' : '' ?>><?= htmlspecialchars($monthLabel, ENT_QUOTES, 'UTF-8') ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-sm-4 col-md-2">
        <label class="form-label">Ano</label>
        <select class="form-select" name="year">
          <?php foreach ($yearOptions as $yearOption): ?>
            <option value="<?= $yearOption ?>" <?= (int) $s['year'] === (int) $yearOption ? 'selected' : '' ?>><?= $yearOption ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-sm-4 col-md-3">
        <button type="submit" class="btn btn-primary">Atualizar</button>
      </div>
    </form>

    <div class="alert alert-light border mb-4">
      <strong><?= htmlspecialchars((string) $s['month_label'], ENT_QUOTES, 'UTF-8') ?>/<?= (int) $s['year'] ?></strong>
      <span class="ms-2">Total de visitas nas paginas: <strong><?= (int) $s['total'] ?></strong></span>
    </div>

    <?php if (empty($s['pages'])): ?>
      <p class="text-muted">Sem visitas de paginas para o periodo.</p>
    <?php endif; ?>

    <?php foreach (($s['pages'] ?? []) as $page): ?>
      <div class="border rounded-3 p-3 mb-3">
        <div class="d-flex justify-content-between align-items-center mb-2">
          <div>
            <strong><?= htmlspecialchars((string) ($page['name'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></strong>
            <small class="text-muted ms-2">/<?= htmlspecialchars((string) ($page['slug'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></small>
          </div>
          <div>
            <span class="badge text-bg-primary me-1"><?= (int) ($page['total'] ?? 0) ?></span>
            <span class="badge text-bg-secondary"><?= number_format((float) ($page['percent'] ?? 0), 1, ',', '.') ?>%</span>
          </div>
        </div>
        <div class="progress" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="<?= (float) ($page['percent'] ?? 0) ?>">
          <div class="progress-bar bg-info" style="width: <?= (float) ($page['percent'] ?? 0) ?>%"></div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</section>
