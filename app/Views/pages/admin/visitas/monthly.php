<section class="container py-4">
  <div class="bg-white border rounded-3 p-4 shadow-sm">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
      <h4 class="mb-0">Visitas por Mes</h4>
      <div class="btn-group" role="group" aria-label="Relatorios de visitas">
        <a class="btn btn-outline-secondary btn-sm" href="/admin/visitas">Lista</a>
        <a class="btn btn-outline-secondary btn-sm active" href="/admin/visitas/mensal">Por mes</a>
        <a class="btn btn-outline-secondary btn-sm" href="/admin/visitas/analytics">Analytics</a>
        <a class="btn btn-outline-secondary btn-sm" href="/admin/visitas/paginas">Por pagina</a>
      </div>
    </div>

    <?php
    $m = $monthly ?? ['month' => (int) date('m'), 'year' => (int) date('Y'), 'month_label' => 'Mes', 'total_month' => 0, 'days' => []];
    $monthOptions = [
      1 => 'Janeiro', 2 => 'Fevereiro', 3 => 'Marco', 4 => 'Abril', 5 => 'Maio', 6 => 'Junho',
      7 => 'Julho', 8 => 'Agosto', 9 => 'Setembro', 10 => 'Outubro', 11 => 'Novembro', 12 => 'Dezembro',
    ];
    $currentYear = (int) date('Y');
    $yearOptions = [$currentYear - 2, $currentYear - 1, $currentYear, $currentYear + 1];
    $days = $m['days'] ?? [];
    usort($days, static function (array $a, array $b): int {
      return ((int) ($b['day'] ?? 0)) <=> ((int) ($a['day'] ?? 0));
    });
    ?>
    <form class="row g-2 align-items-end mb-4" method="get" action="/admin/visitas/mensal">
      <div class="col-sm-4 col-md-3">
        <label class="form-label">Mes</label>
        <select class="form-select" name="month">
          <?php foreach ($monthOptions as $monthNumber => $monthLabel): ?>
            <option value="<?= $monthNumber ?>" <?= (int) $m['month'] === $monthNumber ? 'selected' : '' ?>><?= htmlspecialchars($monthLabel, ENT_QUOTES, 'UTF-8') ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-sm-4 col-md-2">
        <label class="form-label">Ano</label>
        <select class="form-select" name="year">
          <?php foreach ($yearOptions as $yearOption): ?>
            <option value="<?= $yearOption ?>" <?= (int) $m['year'] === (int) $yearOption ? 'selected' : '' ?>><?= $yearOption ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-sm-4 col-md-3">
        <button type="submit" class="btn btn-primary">Atualizar</button>
      </div>
    </form>

    <div class="alert alert-light border mb-4">
      <strong><?= htmlspecialchars((string) $m['month_label'], ENT_QUOTES, 'UTF-8') ?>/<?= (int) $m['year'] ?></strong>
      <span class="ms-2">Total no mes: <strong><?= (int) $m['total_month'] ?></strong> visitas</span>
    </div>

    <?php
    $paisUrl = '/admin/visitas/mensal';
    $paisHidden = ['month' => (int) $m['month'], 'year' => (int) $m['year']];
    require __DIR__ . '/_filtro_pais.php';
    ?>

    <div class="table-responsive">
      <table class="table table-sm align-middle">
        <thead>
          <tr>
            <th>Dia</th>
            <th>Total de visitas</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($days)): ?>
            <tr><td colspan="2" class="text-muted">Sem visitas para o periodo selecionado.</td></tr>
          <?php endif; ?>
          <?php foreach ($days as $day): ?>
            <tr>
              <td><?= htmlspecialchars((string) $m['month_label'], ENT_QUOTES, 'UTF-8') ?> dia <?= (int) ($day['day'] ?? 0) ?></td>
              <td><span class="badge text-bg-primary"><?= (int) ($day['total'] ?? 0) ?></span></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <?php
    $chartDays = [];
    $chartValues = [];
    foreach (($days ?? []) as $day) {
      $chartDays[] = (int) ($day['day'] ?? 0);
      $chartValues[] = (int) ($day['total'] ?? 0);
    }
    $chartLabels = array_reverse($chartDays);
    $chartData = array_reverse($chartValues);
    ?>

    <div class="mt-4 p-3 rounded-3 border" style="background:#f8f9fa;">
      <h6 class="mb-1"><i class="bi bi-graph-up me-1"></i>Evolução de visitas — <?= htmlspecialchars((string) $m['month_label'], ENT_QUOTES, 'UTF-8') ?>/<?= (int) $m['year'] ?></h6>
      <p class="text-muted small mb-3">Visitas por dia, <?= $pais === 'todos' ? 'todos os países' : 'apenas Brasil' ?>.</p>
      <?php if (empty($chartData)): ?>
        <p class="text-muted mb-0">Sem dados para exibir o gráfico.</p>
      <?php else: ?>
        <canvas id="graficoVisitasMes" height="100"></canvas>
      <?php endif; ?>
    </div>
  </div>
</section>

<?php if (!empty($chartData)): ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
  var canvas = document.getElementById('graficoVisitasMes');
  if (!canvas || typeof Chart === 'undefined') return;

  new Chart(canvas, {
    type: 'line',
    data: {
      labels: <?= json_encode($chartLabels, JSON_UNESCAPED_UNICODE) ?>,
      datasets: [{
        label: 'Visitas',
        data: <?= json_encode($chartData) ?>,
        borderColor: '#0d6efd',
        backgroundColor: 'rgba(13,110,253,0.15)',
        fill: true,
        tension: 0.3,
        pointRadius: 3,
      }]
    },
    options: {
      responsive: true,
      plugins: {
        legend: { display: false }
      },
      scales: {
        x: { title: { display: true, text: 'Dia do mês' } },
        y: { beginAtZero: true, title: { display: true, text: 'Visitas' } }
      }
    }
  });
});
</script>
<?php endif; ?>
