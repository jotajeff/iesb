<?php
$referer = $refererStats ?? ['month' => 0, 'year' => 0, 'month_label' => '-', 'total' => 0, 'referers' => []];
$utm = $utmStats ?? ['month' => 0, 'year' => 0, 'month_label' => '-', 'total' => 0, 'utms' => []];
$months = [1=>'Janeiro','Fevereiro','Marco','Abril','Maio','Junho','Julho','Agosto','Setembro','Outubro','Novembro','Dezembro'];
$currentMonth = (int) ($referer['month'] ?? (int) date('m'));
$currentYear = (int) ($referer['year'] ?? (int) date('Y'));
?>

<section class="container py-4">
  <div class="bg-white border rounded-3 p-4 shadow-sm">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
      <h4 class="mb-0">Referer & UTM</h4>
      <div class="d-flex align-items-center gap-2">
        <form method="get" class="d-flex align-items-center gap-2">
          <select name="month" class="form-select form-select-sm" style="width:auto">
            <?php for ($m = 1; $m <= 12; $m++): ?>
              <option value="<?= $m ?>" <?= $m === $currentMonth ? 'selected' : '' ?>><?= $months[$m] ?></option>
            <?php endfor; ?>
          </select>
          <select name="year" class="form-select form-select-sm" style="width:auto">
            <?php for ($y = (int) date('Y'); $y >= 2024; $y--): ?>
              <option value="<?= $y ?>" <?= $y === $currentYear ? 'selected' : '' ?>><?= $y ?></option>
            <?php endfor; ?>
          </select>
          <button type="submit" class="btn btn-primary btn-sm">Filtrar</button>
        </form>
        <div class="btn-group" role="group" aria-label="Relatorios de visitas">
          <a class="btn btn-outline-secondary btn-sm" href="/admin/visitas">Lista</a>
          <a class="btn btn-outline-secondary btn-sm" href="/admin/visitas/mensal">Por mes</a>
          <a class="btn btn-outline-secondary btn-sm" href="/admin/visitas/analytics">Analytics</a>
          <a class="btn btn-outline-secondary btn-sm" href="/admin/visitas/paginas">Por pagina</a>
          <a class="btn btn-outline-secondary btn-sm" href="/admin/visitas/cursos">Cursos</a>
          <a class="btn btn-outline-secondary btn-sm active" href="/admin/visitas/referer">Referer</a>
          <a class="btn btn-outline-secondary btn-sm" href="#utm">UTM</a>
        </div>
      </div>
    </div>

    <div class="card mb-4" id="referer">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="bi bi-link-45deg me-1"></i>Referer</h5>
        <span class="badge text-bg-secondary"><?= $referer['month_label'] ?> <?= $referer['year'] ?></span>
      </div>
      <div class="card-body">
        <div class="alert alert-light border mb-3">
          Total de visitas com referer: <strong><?= (int) $referer['total'] ?></strong>
        </div>

        <?php if (empty($referer['referers'])): ?>
          <p class="text-muted">Nenhum referer registrado neste periodo.</p>
        <?php else: ?>
          <div class="table-responsive">
            <table class="table table-striped table-sm align-middle">
              <thead>
                <tr>
                  <th>#</th>
                  <th>Dominio</th>
                  <th>URL Completa</th>
                  <th>Visitas</th>
                  <th>%</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach (($referer['referers'] ?? []) as $i => $item): ?>
                  <tr>
                    <td><?= $i + 1 ?></td>
                    <td>
                      <strong><?= htmlspecialchars((string) ($item['domain'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></strong>
                    </td>
                    <td>
                      <small class="text-muted text-break" style="max-width:500px;display:inline-block">
                        <?= htmlspecialchars((string) ($item['referer'] ?? '-'), ENT_QUOTES, 'UTF-8') ?>
                      </small>
                    </td>
                    <td><span class="badge text-bg-primary"><?= (int) ($item['total'] ?? 0) ?></span></td>
                    <td><?= number_format((float) ($item['percent'] ?? 0), 1, ',', '.') ?>%</td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>
      </div>
    </div>

    <div class="card" id="utm">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="bi bi-tags me-1"></i>UTM Parameters</h5>
        <span class="badge text-bg-secondary"><?= $utm['month_label'] ?> <?= $utm['year'] ?></span>
      </div>
      <div class="card-body">
        <div class="alert alert-light border mb-3">
          Total de visitas com UTM: <strong><?= (int) $utm['total'] ?></strong>
        </div>

        <?php if (empty($utm['utms'])): ?>
          <p class="text-muted">Nenhum parametro UTM registrado neste periodo.</p>
        <?php else: ?>
          <div class="table-responsive">
            <table class="table table-striped table-sm align-middle">
              <thead>
                <tr>
                  <th>#</th>
                  <th>Source</th>
                  <th>Medium</th>
                  <th>Campaign</th>
                  <th>Visitas</th>
                  <th>%</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach (($utm['utms'] ?? []) as $i => $item): ?>
                  <tr>
                    <td><?= $i + 1 ?></td>
                    <td><span class="badge text-bg-info"><?= htmlspecialchars((string) ($item['source'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></span></td>
                    <td><?= htmlspecialchars((string) ($item['medium'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars((string) ($item['campaign'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                    <td><span class="badge text-bg-primary"><?= (int) ($item['total'] ?? 0) ?></span></td>
                    <td><?= number_format((float) ($item['percent'] ?? 0), 1, ',', '.') ?>%</td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</section>
