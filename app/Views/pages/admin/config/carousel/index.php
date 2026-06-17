<section class="container py-4">
  <div class="bg-white border rounded-3 p-4 shadow-sm">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
      <h4 class="mb-0"><i class="bi bi-images me-2"></i>Carrosséis</h4>
      <a class="btn btn-primary btn-sm" href="/admin/config/carousel/editar"><i class="bi bi-plus-circle me-1"></i>Novo carrossel</a>
    </div>

    <div class="table-responsive">
      <table class="table table-striped table-sm align-middle">
        <thead>
          <tr>
            <th><i class="bi bi-hash"></i></th>
            <th><i class="bi bi-tag me-1"></i>Título</th>
            <th><i class="bi bi-link me-1"></i>Slug</th>
            <th><i class="bi bi-toggle-on me-1"></i>Ativo</th>
            <th><i class="bi bi-calendar me-1"></i>Criado em</th>
            <th><i class="bi bi-gear me-1"></i>Ações</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($carousels ?? [])): ?>
            <tr><td colspan="6" class="text-muted"><i class="bi bi-inbox me-1"></i>Nenhum carrossel encontrado.</td></tr>
          <?php endif; ?>

          <?php foreach (($carousels ?? []) as $c): ?>
            <?php $id = (int) ($c['id'] ?? 0); ?>
            <tr>
              <td><?= $id ?></td>
              <td><?= htmlspecialchars((string) ($c['titulo'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
              <td><code><?= htmlspecialchars((string) ($c['slug'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></code></td>
              <td>
                <?php if (strtoupper(trim((string) ($c['ativo'] ?? 'N'))) === 'S'): ?>
                  <span class="badge bg-success">Sim</span>
                <?php else: ?>
                  <span class="badge bg-secondary">Não</span>
                <?php endif; ?>
              </td>
              <td><?php
                  $raw = (string) ($c['criado_em'] ?? '');
                  $dt = $raw !== '' ? \DateTime::createFromFormat('Y-m-d H:i:s', $raw) : false;
                  echo htmlspecialchars($dt ? $dt->format('d/m/Y H:i') : ($raw ?: '-'), ENT_QUOTES, 'UTF-8');
                  ?></td>
              <td>
                <a class="btn btn-outline-secondary btn-sm" href="/admin/config/carousel/editar?id=<?= $id ?>">
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
