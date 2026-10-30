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

    <?php
    $perfilCards = [
      ['label' => 'Dispositivo', 'items' => $a['devices'] ?? [], 'type' => 'device', 'icon' => 'bi-laptop', 'color' => '#efc02b', 'colorDark' => '#c99b0d'],
      ['label' => 'Sistema', 'items' => $a['systems'] ?? [], 'type' => 'os', 'icon' => 'bi-cpu', 'color' => '#4d4f4e', 'colorDark' => '#2f3130'],
      ['label' => 'Navegador', 'items' => $a['browsers'] ?? [], 'type' => 'browser', 'icon' => 'bi-compass', 'color' => '#0f9d58', 'colorDark' => '#0a7440'],
    ];
    ?>

    <div class="mt-4 pt-4 border-top">
      <div class="d-flex align-items-center gap-2 mb-3">
        <span class="d-inline-flex align-items-center justify-content-center rounded-circle" style="width:36px;height:36px;background:rgba(239,192,43,.18);color:#c99b0d;">
          <i class="bi bi-person-badge"></i>
        </span>
        <h5 class="mb-0">Perfil</h5>
      </div>
      <div class="row g-3">
        <?php foreach ($perfilCards as $card): ?>
          <?php
          $top = $card['items'][0] ?? null;
          $topLabel = (string) ($top['label'] ?? 'Sem dados');
          $topIcon = match ($card['type']) {
              'device' => \App\Support\UiIconHelper::device($topLabel),
              'os' => \App\Support\UiIconHelper::os($topLabel),
              default => \App\Support\UiIconHelper::browser($topLabel),
          };
          $topPercent = (float) ($top['percent'] ?? 0);
          ?>
          <div class="col-12 col-md-4">
            <div class="h-100 rounded-4 overflow-hidden border-0 shadow-sm" style="background:#fff;">
              <div class="position-relative text-center px-3 pt-4 pb-3" style="background: linear-gradient(135deg, <?= $card['color'] ?>, <?= $card['colorDark'] ?>);">
                <div class="position-absolute top-0 start-0 w-100 h-100" style="background:radial-gradient(circle at 75% 15%, rgba(255,255,255,.35), transparent 45%);"></div>
                <div class="position-relative d-inline-flex align-items-center justify-content-center rounded-circle mb-3" style="width:92px;height:92px;background:rgba(255,255,255,.22);border:1px solid rgba(255,255,255,.45);box-shadow:0 0 0 8px rgba(255,255,255,.12), inset 0 0 22px rgba(255,255,255,.25);backdrop-filter:blur(2px);">
                  <i class="bi <?= htmlspecialchars($topIcon, ENT_QUOTES, 'UTF-8') ?> text-white" style="font-size:2.6rem;filter:drop-shadow(0 3px 6px rgba(0,0,0,.25));"></i>
                </div>
                <div class="position-relative text-white text-uppercase small fw-semibold" style="letter-spacing:.1em;opacity:.85;">
                  <i class="bi <?= htmlspecialchars((string) $card['icon'], ENT_QUOTES, 'UTF-8') ?> me-1"></i><?= htmlspecialchars((string) $card['label'], ENT_QUOTES, 'UTF-8') ?>
                </div>
              </div>
              <div class="px-3 py-3 text-center">
                <div class="fw-bold fs-5 mb-1"><?= htmlspecialchars($topLabel, ENT_QUOTES, 'UTF-8') ?></div>
                <?php if ($top !== null): ?>
                  <div class="text-muted small mb-2">
                    <strong><?= (int) ($top['count'] ?? 0) ?></strong> acessos
                    <span class="mx-1">·</span>
                    <?= number_format($topPercent, 1, ',', '.') ?>%
                  </div>
                  <div class="progress" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="<?= $topPercent ?>" style="height:6px;background:rgba(77,79,78,.1);">
                    <div class="progress-bar" style="width: <?= $topPercent ?>%;background: linear-gradient(90deg, <?= $card['color'] ?>, <?= $card['colorDark'] ?>);"></div>
                  </div>
                <?php else: ?>
                  <div class="text-muted small">Sem dados no período.</div>
                <?php endif; ?>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</section>
