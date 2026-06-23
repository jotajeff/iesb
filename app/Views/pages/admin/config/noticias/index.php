<section class="container py-4">
  <div class="bg-white border rounded-3 p-4 shadow-sm">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
      <h4 class="mb-0"><i class="bi bi-newspaper me-2"></i>Notícias</h4>
      <a class="btn btn-primary btn-sm" href="/admin/config/noticias/editar"><i class="bi bi-plus-circle me-1"></i>Nova notícia</a>
    </div>

    <div class="table-responsive">
      <table class="table table-striped table-sm align-middle">
        <thead>
          <tr>
            <th><i class="bi bi-hash"></i></th>
            <th><i class="bi bi-card-heading me-1"></i>Título</th>
            <th><i class="bi bi-tag me-1"></i>Categoria</th>
            <th><i class="bi bi-toggle-on me-1"></i>Status</th>
            <th><i class="bi bi-star me-1"></i>Destaque</th>
            <th><i class="bi bi-calendar me-1"></i>Publicação</th>
            <th><i class="bi bi-gear me-1"></i>Ações</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($noticias ?? [])): ?>
            <tr><td colspan="7" class="text-muted"><i class="bi bi-inbox me-1"></i>Nenhuma notícia encontrada.</td></tr>
          <?php endif; ?>

          <?php foreach (($noticias ?? []) as $n): ?>
            <?php $id = (int) ($n['id'] ?? 0); ?>
            <tr>
              <td><?= $id ?></td>
              <td><?= htmlspecialchars((string) ($n['titulo'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
              <td><?= htmlspecialchars((string) ($n['categoria_nome'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
              <td>
                <?php $status = (string) ($n['status'] ?? 'rascunho'); ?>
                <?php if ($status === 'publicado'): ?>
                  <span class="badge bg-success">Publicado</span>
                <?php elseif ($status === 'arquivado'): ?>
                  <span class="badge bg-secondary">Arquivado</span>
                <?php else: ?>
                  <span class="badge bg-warning text-dark">Rascunho</span>
                <?php endif; ?>
              </td>
              <td>
                <?php if ((int) ($n['destaque'] ?? 0) === 1): ?>
                  <span class="badge bg-danger"><i class="bi bi-star-fill"></i></span>
                <?php else: ?>
                  <span class="badge bg-light text-muted"><i class="bi bi-star"></i></span>
                <?php endif; ?>
              </td>
              <td><?php
                  $raw = (string) ($n['data_publicacao'] ?? '');
                  $dt = $raw !== '' ? \DateTime::createFromFormat('Y-m-d H:i:s', $raw) : false;
                  echo htmlspecialchars($dt ? $dt->format('d/m/Y H:i') : ($raw ?: '-'), ENT_QUOTES, 'UTF-8');
                  ?></td>
              <td>
                <a class="btn btn-outline-secondary btn-sm" href="/admin/config/noticias/editar?id=<?= $id ?>">
                  <i class="bi bi-pencil-square me-1"></i>Editar
                </a>
                <button type="button" class="btn btn-outline-danger btn-sm" onclick="deletarNoticia(<?= $id ?>)">
                  <i class="bi bi-trash"></i>
                </button>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</section>

<script>
function deletarNoticia(id) {
  if (!confirm('Tem certeza que deseja excluir esta notícia?')) return;

  var formData = new FormData();
  formData.append('id', id);

  fetch('/admin/config/noticias/deletar', {
    method: 'POST',
    body: formData
  })
  .then(function(r) { return r.json(); })
  .then(function(data) {
    if (data.sucesso) {
      location.reload();
    } else {
      alert('Erro: ' + (data.erro || 'não foi possível excluir.'));
    }
  })
  .catch(function() {
    alert('Erro de rede ao excluir notícia.');
  });
}
</script>
