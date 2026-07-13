<section class="container py-4">
  <div class="bg-white border rounded-3 p-4 shadow-sm">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
      <h4 class="mb-0"><i class="bi bi-diagram-3 me-2"></i>Cursos-turma</h4>
      <a class="btn btn-outline-secondary btn-sm" href="/admin/cursos"><i class="bi bi-list-ul me-1"></i>Listagem de cursos</a>
    </div>

    <?php if (!empty($flash ?? '')): ?>
      <div class="alert alert-info"><?= htmlspecialchars((string) $flash, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>

    <div class="table-responsive">
      <table class="table table-striped table-sm align-middle">
        <thead>
          <tr>
            <th># Curso</th>
            <th>Curso</th>
            <th># Turma</th>
            <th>Turma</th>
            <th>Inscritos</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($cursosTurmas ?? [])): ?>
            <tr><td colspan="5" class="text-muted"><i class="bi bi-inbox me-1"></i>Nenhum vínculo curso-turma encontrado.</td></tr>
          <?php else: ?>
            <?php foreach ($cursosTurmas as $row): ?>
              <tr>
                <td><?= (int) ($row['curso_id'] ?? 0) ?></td>
                <td><?= htmlspecialchars((string) ($row['curso_nome'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= (int) ($row['turma_id'] ?? 0) ?></td>
                <td><?= htmlspecialchars((string) ($row['turma_nome'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= (int) ($row['total_inscritos'] ?? 0) ?></td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</section>
