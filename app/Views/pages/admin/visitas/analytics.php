<section class="container py-4">
  <div class="bg-white border rounded-3 p-4 shadow-sm">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
      <h4 class="mb-0">Analytics de Visitas</h4>
      <div class="btn-group" role="group" aria-label="Relatorios de visitas">
        <a class="btn btn-outline-secondary btn-sm" href="/admin/visitas">Lista</a>
        <a class="btn btn-outline-secondary btn-sm" href="/admin/visitas/mensal">Por mes</a>
        <a class="btn btn-outline-secondary btn-sm active" href="/admin/visitas/analytics">Analytics</a>
        <a class="btn btn-outline-secondary btn-sm" href="/admin/visitas/paginas">Por pagina</a>
      </div>
    </div>

    <?php
    $a = $analytics ?? ['month' => (int) date('m'), 'year' => (int) date('Y'), 'month_label' => 'Mes', 'total' => 0, 'countries' => [], 'cities' => [], 'devices' => [], 'systems' => [], 'browsers' => []];
    $monthOptions = [
      1 => 'Janeiro', 2 => 'Fevereiro', 3 => 'Marco', 4 => 'Abril', 5 => 'Maio', 6 => 'Junho',
      7 => 'Julho', 8 => 'Agosto', 9 => 'Setembro', 10 => 'Outubro', 11 => 'Novembro', 12 => 'Dezembro',
    ];
    $currentYear = (int) date('Y');
    $yearOptions = [$currentYear - 2, $currentYear - 1, $currentYear, $currentYear + 1];
    ?>
    <form class="row g-2 align-items-end mb-4" method="get" action="/admin/visitas/analytics">
      <div class="col-sm-4 col-md-3">
        <label class="form-label">Mes</label>
        <select class="form-select" name="month">
          <?php foreach ($monthOptions as $monthNumber => $monthLabel): ?>
            <option value="<?= $monthNumber ?>" <?= (int) $a['month'] === $monthNumber ? 'selected' : '' ?>><?= htmlspecialchars($monthLabel, ENT_QUOTES, 'UTF-8') ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-sm-4 col-md-2">
        <label class="form-label">Ano</label>
        <select class="form-select" name="year">
          <?php foreach ($yearOptions as $yearOption): ?>
            <option value="<?= $yearOption ?>" <?= (int) $a['year'] === (int) $yearOption ? 'selected' : '' ?>><?= $yearOption ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-sm-4 col-md-3">
        <button type="submit" class="btn btn-primary">Atualizar</button>
      </div>
    </form>

    <div class="alert alert-light border mb-4">
      <strong><?= htmlspecialchars((string) $a['month_label'], ENT_QUOTES, 'UTF-8') ?>/<?= (int) $a['year'] ?></strong>
      <span class="ms-2">Total analisado: <strong><?= (int) $a['total'] ?></strong> visitas</span>
    </div>

    <?php
    $paisUrl = '/admin/visitas/analytics';
    $paisHidden = ['month' => (int) $a['month'], 'year' => (int) $a['year']];
    require __DIR__ . '/_filtro_pais.php';
    ?>

    <?php
    $blocks = [
      ['title' => 'Paises', 'items' => $a['countries'] ?? [], 'icon' => 'bi-globe-americas'],
      ['title' => 'Cidades', 'items' => $a['cities'] ?? [], 'icon' => 'bi-geo-alt-fill'],
      ['title' => 'Dispositivos', 'items' => $a['devices'] ?? [], 'icon' => 'bi-phone-fill'],
      ['title' => 'Sistemas', 'items' => $a['systems'] ?? [], 'icon' => 'bi-windows'],
      ['title' => 'Navegadores', 'items' => $a['browsers'] ?? [], 'icon' => 'bi-compass-fill'],
    ];
    ?>

    <div class="row g-3">
      <?php foreach ($blocks as $block): ?>
        <div class="col-12 col-lg-6">
          <div class="border rounded-3 p-3 h-100">
            <h5 class="mb-3"><i class="bi <?= htmlspecialchars((string) $block['icon'], ENT_QUOTES, 'UTF-8') ?> me-2"></i><?= htmlspecialchars((string) $block['title'], ENT_QUOTES, 'UTF-8') ?></h5>
            <?php if (empty($block['items'])): ?>
              <p class="text-muted mb-0">Sem dados.</p>
            <?php endif; ?>
            <?php foreach (($block['items'] ?? []) as $row): ?>
              <div class="d-flex justify-content-between small mb-1">
                <span><?= htmlspecialchars((string) ($row['label'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></span>
                <span><?= (int) ($row['count'] ?? 0) ?> (<?= number_format((float) ($row['percent'] ?? 0), 1, ',', '.') ?>%)</span>
              </div>
              <div class="progress mb-2" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="<?= (float) ($row['percent'] ?? 0) ?>">
                <div class="progress-bar bg-warning text-dark" style="width: <?= (float) ($row['percent'] ?? 0) ?>%"></div>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
