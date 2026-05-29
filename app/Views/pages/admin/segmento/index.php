<section class="container py-4">
  <div class="bg-white border rounded-3 p-4 shadow-sm">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
      <h4 class="mb-0"><i class="bi bi-diagram-3 me-2"></i>Segmentos</h4>
      <a class="btn btn-primary btn-sm" href="/admin/segmento/edit"><i class="bi bi-plus-circle me-1"></i>Novo segmento</a>
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
          <?php if (empty($segmentos ?? [])): ?>
            <tr><td colspan="4" class="text-muted"><i class="bi bi-inbox me-1"></i>Nenhum segmento encontrado.</td></tr>
          <?php endif; ?>

          <?php foreach (($segmentos ?? []) as $s): ?>
            <?php $id = (int) ($s['id'] ?? 0); ?>
            <?php $ativo = strtoupper((string) ($s['ativo'] ?? 'N')); ?>
            <tr>
              <td><?= $id ?></td>
              <td><?= htmlspecialchars((string) ($s['nome'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
              <td>
                <?php if ($ativo === 'S'): ?>
                  <span class="badge bg-success">Sim</span>
                <?php else: ?>
                  <span class="badge bg-secondary">Não</span>
                <?php endif; ?>
              </td>
              <td>
                <a class="btn btn-outline-secondary btn-sm" href="/admin/segmento/edit?id=<?= $id ?>">
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
