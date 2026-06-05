<section class="container py-4">
  <div class="bg-white border rounded-3 p-4 shadow-sm">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
      <h4 class="mb-0"><i class="bi bi-building me-2"></i>Setores</h4>
      <a class="btn btn-primary btn-sm" href="/admin/setor/edit"><i class="bi bi-plus-circle me-1"></i>Novo setor</a>
    </div>

    <div class="table-responsive">
      <table class="table table-striped table-sm align-middle">
        <thead>
          <tr>
            <th><i class="bi bi-hash"></i></th>
            <th><i class="bi bi-tag me-1"></i>Setor</th>
            <th><i class="bi bi-gear me-1"></i>Ações</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($setores ?? [])): ?>
            <tr><td colspan="3" class="text-muted"><i class="bi bi-inbox me-1"></i>Nenhum setor encontrado.</td></tr>
          <?php endif; ?>

          <?php foreach (($setores ?? []) as $s): ?>
            <?php $id = (int) ($s['id'] ?? 0); ?>
            <tr>
              <td><?= $id ?></td>
              <td><?= htmlspecialchars((string) ($s['setor'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
              <td>
                <a class="btn btn-outline-secondary btn-sm" href="/admin/setor/edit?id=<?= $id ?>">
                  <i class="bi bi-pencil-square me-1"></i>Editar
                </a>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</section>
