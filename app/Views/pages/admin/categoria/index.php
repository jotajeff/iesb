<section class="container py-4">
  <div class="bg-white border rounded-3 p-4 shadow-sm">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
      <h4 class="mb-0"><i class="bi bi-newspaper me-2"></i>Categorias</h4>
      <a class="btn btn-primary btn-sm" href="/admin/config/categoria/edit"><i class="bi bi-plus-circle me-1"></i>Nova categoria</a>
    </div>

    <div class="table-responsive">
      <table class="table table-striped table-sm align-middle">
        <thead>
          <tr>
            <th><i class="bi bi-hash"></i></th>
            <th><i class="bi bi-tag me-1"></i>Nome</th>
            <th><i class="bi bi-link me-1"></i>Slug</th>
            <th><i class="bi bi-toggle-on me-1"></i>Ativo</th>
            <th><i class="bi bi-gear me-1"></i>Ações</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($categorias ?? [])): ?>
            <tr><td colspan="5" class="text-muted"><i class="bi bi-inbox me-1"></i>Nenhuma categoria encontrada.</td></tr>
          <?php endif; ?>

          <?php foreach (($categorias ?? []) as $c): ?>
            <?php $id = (int) ($c['id'] ?? 0); ?>
            <?php $ativo = (int) ($c['ativo'] ?? 0); ?>
            <tr>
              <td><?= $id ?></td>
              <td><?= htmlspecialchars((string) ($c['nome'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
              <td><code><?= htmlspecialchars((string) ($c['slug'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></code></td>
              <td>
                <?php if ($ativo === 1): ?>
                  <span class="badge bg-success">Sim</span>
                <?php else: ?>
                  <span class="badge bg-secondary">Não</span>
                <?php endif; ?>
              </td>
              <td>
                <a class="btn btn-outline-secondary btn-sm" href="/admin/config/categoria/edit?id=<?= $id ?>">
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
