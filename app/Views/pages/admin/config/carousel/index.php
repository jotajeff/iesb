<section class="container py-4">
  <div class="bg-white border rounded-3 p-4 shadow-sm">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
      <h4 class="mb-0"><i class="bi bi-images me-2"></i>Itens do Carrossel</h4>
      <a class="btn btn-primary btn-sm" href="/admin/config/carousel/editar"><i class="bi bi-plus-circle me-1"></i>Novo item</a>
    </div>

    <?php if (!empty($flash ?? '')): ?>
      <div class="alert alert-info"><?= htmlspecialchars((string) $flash, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>

    <?php if (empty($items ?? [])): ?>
      <p class="text-muted"><i class="bi bi-inbox me-1"></i>Nenhum item encontrado.</p>
    <?php else: ?>
      <div class="table-responsive">
        <table class="table table-striped table-sm align-middle">
          <thead>
            <tr>
              <th>ID</th>
              <th>Imagem</th>
              <th>Título</th>
              <th>Link</th>
              <th>Ordem</th>
              <th>Ativo</th>
              <th>Ações</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($items as $item): ?>
              <?php $itemId = (int) ($item['id'] ?? 0); ?>
              <tr>
                <td><?= $itemId ?></td>
                <td>
                  <?php $img = (string) ($item['imagem'] ?? ''); ?>
                  <?php if ($img !== ''): ?>
                    <img src="/<?= htmlspecialchars($img, ENT_QUOTES, 'UTF-8') ?>"
                         style="width:80px;height:50px;object-fit:cover;border-radius:6px;"
                         alt="thumb">
                  <?php else: ?>
                    <span class="text-muted">—</span>
                  <?php endif; ?>
                </td>
                <td><?= htmlspecialchars((string) ($item['titulo'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                <td>
                  <?php $link = (string) ($item['link'] ?? ''); ?>
                  <?php if ($link !== ''): ?>
                    <a href="<?= htmlspecialchars($link, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener" class="text-truncate d-inline-block" style="max-width:200px;">
                      <?= htmlspecialchars($link, ENT_QUOTES, 'UTF-8') ?>
                    </a>
                  <?php else: ?>
                    <span class="text-muted">—</span>
                  <?php endif; ?>
                </td>
                <td><?= (int) ($item['ordem'] ?? 0) ?></td>
                <td>
                  <?php if (intval($item['ativo'] ?? 0) === 1): ?>
                    <span class="badge bg-success">Sim</span>
                  <?php else: ?>
                    <span class="badge bg-secondary">Não</span>
                  <?php endif; ?>
                </td>
                <td>
                  <a class="btn btn-outline-secondary btn-sm" href="/admin/config/carousel/editar?id=<?= $itemId ?>">
                    <i class="bi bi-pencil-square"></i>
                  </a>
                  <button type="button" class="btn btn-outline-danger btn-sm" onclick="deletarItem(<?= $itemId ?>)">
                    <i class="bi bi-trash"></i>
                  </button>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>
</section>

<script>
function deletarItem(itemId) {
  if (!confirm('Tem certeza que deseja remover este item?')) return;

  var formData = new FormData();
  formData.append('item_id', itemId);

  fetch('/admin/config/carousel/deletar', {
    method: 'POST',
    body: formData
  })
  .then(function(r) { return r.json(); })
  .then(function(data) {
    if (data.sucesso) {
      location.reload();
    } else {
      alert('Erro: ' + (data.erro || 'não foi possível remover.'));
    }
  })
  .catch(function() {
    alert('Erro de rede ao remover item.');
  });
}
</script>
