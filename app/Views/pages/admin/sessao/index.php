<section class="container py-4">
  <div class="bg-white border rounded-3 p-4 shadow-sm">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
      <h4 class="mb-0"><i class="bi bi-layers me-2"></i>Sessões</h4>
      <a class="btn btn-primary btn-sm" href="/admin/sessao/novo"><i class="bi bi-plus-lg me-1"></i>Nova Sessão</a>
    </div>

    <?php if (!empty($flash ?? '')): ?>
      <div class="alert alert-info"><?= htmlspecialchars((string) $flash, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>

    <div class="table-responsive">
      <table class="table table-striped table-sm align-middle">
        <thead>
          <tr>
            <th>#</th>
            <th>Título</th>
            <th>Slug</th>
            <th>Badge</th>
            <th>Mídia</th>
            <th>Criado em</th>
            <th>Ações</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($sessoes ?? [])): ?>
            <tr><td colspan="7" class="text-muted"><i class="bi bi-inbox me-1"></i>Nenhuma sessão encontrada.</td></tr>
          <?php else: ?>
            <?php foreach ($sessoes as $s): ?>
              <tr>
                <td><?= (int) ($s['id'] ?? 0) ?></td>
                <td class="fw-semibold"><?= htmlspecialchars((string) ($s['titulo'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                <td><code><?= htmlspecialchars((string) ($s['slug'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></code></td>
                <td><?= htmlspecialchars((string) ($s['badge'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                <td><?php
                  $midiaMap = [1 => 'C — Carrossel', 0 => 'G — Galeria'];
                  $midiaVal = (int) ($s['midia'] ?? -1);
                  echo $midiaMap[$midiaVal] ?? '—';
                ?></td>
                <td><?php
                  $dt = \DateTime::createFromFormat('Y-m-d H:i:s', (string) ($s['created_at'] ?? ''));
                  echo $dt ? $dt->format('d/m/Y H:i') : '-';
                ?></td>
                <td>
                  <a class="btn btn-sm btn-outline-primary" href="/admin/sessao/editar?id=<?= (int) ($s['id'] ?? 0) ?>" title="Editar"><i class="bi bi-pencil"></i></a>
                  <a class="btn btn-sm btn-outline-success" href="/admin/sessao/imagem?id_fk=<?= (int) ($s['id'] ?? 0) ?>&tabela_fk=<?= htmlspecialchars((string) ($s['slug'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" title="Imagens"><i class="bi bi-image"></i></a>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</section>
