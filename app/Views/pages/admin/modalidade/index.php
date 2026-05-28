<section class="container py-4">
  <div class="bg-white border rounded-3 p-4 shadow-sm">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
      <h4 class="mb-0"><i class="bi bi-journal-bookmark-fill me-2"></i>Modalidades</h4>
      <a class="btn btn-primary btn-sm" href="/admin/modalidade/edit"><i class="bi bi-plus-circle me-1"></i>Nova modalidade</a>
    </div>

    <div class="table-responsive">
      <table class="table table-striped table-sm align-middle">
        <thead>
          <tr>
            <th><i class="bi bi-hash"></i></th>
            <th><i class="bi bi-tag me-1"></i>Nome</th>
            <th><i class="bi bi-toggle-on me-1"></i>Ativo</th>
            <th><i class="bi bi-gear me-1"></i>Ações</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($modalidades ?? [])): ?>
            <tr><td colspan="4" class="text-muted"><i class="bi bi-inbox me-1"></i>Nenhuma modalidade encontrada.</td></tr>
          <?php endif; ?>

          <?php foreach (($modalidades ?? []) as $m): ?>
            <?php $id = (int) ($m['id'] ?? 0); ?>
            <?php $ativo = (int) ($m['ativo'] ?? 0); ?>
            <tr>
              <td><?= $id ?></td>
              <td><?= htmlspecialchars((string) ($m['nome'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
              <td>
                <?php if ($ativo === 1): ?>
                  <span class="badge bg-success">Sim</span>
                <?php else: ?>
                  <span class="badge bg-secondary">Não</span>
                <?php endif; ?>
              </td>
              <td>
                <a class="btn btn-outline-secondary btn-sm" href="/admin/modalidade/edit?id=<?= $id ?>">
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
