<section class="container py-4">
  <div class="bg-white border rounded-3 p-4 shadow-sm">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
      <h4 class="mb-0">Ranking de Visitas — Cursos</h4>
      <div class="btn-group" role="group" aria-label="Relatorios de visitas">
        <a class="btn btn-outline-secondary btn-sm" href="/admin/visitas">Lista</a>
        <a class="btn btn-outline-secondary btn-sm" href="/admin/visitas/mensal">Por mes</a>
        <a class="btn btn-outline-secondary btn-sm" href="/admin/visitas/analytics">Analytics</a>
        <a class="btn btn-outline-secondary btn-sm" href="/admin/visitas/paginas">Por pagina</a>
        <a class="btn btn-outline-secondary btn-sm active" href="/admin/visitas/cursos">Cursos</a>
      </div>
    </div>

    <?php
    $s = $coursesStats ?? ['total' => 0, 'pages' => []];
    ?>

    <div class="alert alert-light border mb-4">
      Total de visitas em páginas de curso: <strong><?= (int) $s['total'] ?></strong>
    </div>

    <?php if (empty($s['pages'])): ?>
      <p class="text-muted">Nenhuma visita registrada em páginas de curso.</p>
    <?php endif; ?>

    <div class="table-responsive">
      <table class="table table-striped table-sm align-middle">
        <thead>
          <tr>
            <th>#</th>
            <th>Curso</th>
            <th>Slug</th>
            <th>Visitas</th>
            <th>%</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach (($s['pages'] ?? []) as $i => $page): ?>
            <tr>
              <td><?= $i + 1 ?></td>
              <td><?= htmlspecialchars((string) ($page['name'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
              <td><small class="text-muted">/<?= htmlspecialchars((string) ($page['slug'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></small></td>
              <td><span class="badge text-bg-primary"><?= (int) ($page['total'] ?? 0) ?></span></td>
              <td><?= number_format((float) ($page['percent'] ?? 0), 1, ',', '.') ?>%</td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</section>
