<?php
$carousel = $carousel ?? null;
$items = $items ?? [];
$isEditing = $carousel !== null;
$id = $isEditing ? (int) ($carousel['id'] ?? 0) : 0;
?>
<section class="container py-4">
  <div class="bg-white border rounded-3 p-4 shadow-sm">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
      <h4 class="mb-0"><i class="bi bi-images me-2"></i><?= $isEditing ? 'Editar Carrossel' : 'Novo Carrossel' ?></h4>
      <a class="btn btn-outline-secondary btn-sm" href="/admin/config/carousel"><i class="bi bi-arrow-left me-1"></i>Voltar</a>
    </div>

    <form method="post" action="/admin/config/carousel/salvar">
      <?php if ($isEditing): ?>
        <input type="hidden" name="id" value="<?= $id ?>">
      <?php endif; ?>

      <div class="row g-3 mb-3">
        <div class="col-md-6">
          <label class="form-label">Título <span class="text-danger">*</span></label>
          <input type="text" name="titulo" class="form-control" required
                 value="<?= htmlspecialchars((string) ($carousel['titulo'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
        </div>
        <div class="col-md-3">
          <label class="form-label">Slug</label>
          <input type="text" name="slug" class="form-control"
                 value="<?= htmlspecialchars((string) ($carousel['slug'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
        </div>
        <div class="col-md-3">
          <label class="form-label">Link</label>
          <input type="url" name="link" class="form-control" placeholder="https://"
                 value="<?= htmlspecialchars((string) ($carousel['link'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
        </div>
        <div class="col-md-3">
          <label class="form-label">Ativo</label>
          <select name="ativo" class="form-select">
            <option value="S" <?= (($carousel['ativo'] ?? 'S') === 'S') ? 'selected' : '' ?>>Sim</option>
            <option value="N" <?= (($carousel['ativo'] ?? 'S') === 'N') ? 'selected' : '' ?>>Não</option>
          </select>
        </div>
        <div class="col-12">
          <label class="form-label">Descrição</label>
          <div id="descricaoEditor" style="height:200px;"><?= (string) ($carousel['descricao'] ?? '') ?></div>
          <input type="hidden" name="descricao" id="descricaoInput"
                 value="<?= htmlspecialchars((string) ($carousel['descricao'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
        </div>
      </div>

      <div class="d-flex justify-content-end">
        <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Salvar</button>
      </div>
    </form>

    <?php if ($isEditing): ?>
    <hr class="my-4">
    <h5 class="mb-3"><i class="bi bi-images me-2"></i>Itens do Carrossel</h5>

    <div class="row g-3 mb-4" id="itemsContainer">
      <?php if (empty($items)): ?>
        <div class="col-12">
          <p class="text-muted"><i class="bi bi-inbox me-1"></i>Nenhum item ainda. Faça upload de imagens abaixo.</p>
        </div>
      <?php else: ?>
        <?php foreach ($items as $item): ?>
          <div class="col-md-4 col-lg-3" data-item-id="<?= (int) ($item['id'] ?? 0) ?>">
            <div class="card border shadow-sm h-100">
              <img src="/<?= htmlspecialchars((string) ($item['imagem'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                   class="card-img-top" style="height:160px;object-fit:cover;" alt="">
              <div class="card-body p-2 text-center">
                <small class="d-block text-truncate"><?= htmlspecialchars((string) ($item['titulo'] ?? 'sem título'), ENT_QUOTES, 'UTF-8') ?></small>
                <small class="text-muted">Ordem: <?= (int) ($item['ordem'] ?? 0) ?></small>
              </div>
              <div class="card-footer p-1 text-center bg-transparent border-top-0">
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="deletarItem(<?= (int) ($item['id'] ?? 0) ?>)">
                  <i class="bi bi-trash"></i>
                </button>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>

    <hr class="my-4">
    <h6 class="mb-2"><i class="bi bi-upload me-1"></i>Upload de nova imagem</h6>
    <form id="uploadForm" enctype="multipart/form-data">
      <input type="hidden" name="id_carousel" value="<?= $id ?>">
      <div class="row g-2 align-items-end">
        <div class="col-md-4">
          <input type="file" name="imagem" class="form-control form-control-sm" accept="image/*" required>
        </div>
        <div class="col-md-2">
          <button type="submit" class="btn btn-success btn-sm"><i class="bi bi-cloud-upload me-1"></i>Enviar</button>
        </div>
      </div>
    </form>
    <?php endif; ?>
  </div>
</section>

<link href="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.snow.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.min.js"></script>

<script>
var quill = new Quill('#descricaoEditor', {
  theme: 'snow',
  placeholder: 'Escreva a descrição do carrossel...',
});
quill.root.innerHTML = <?= json_encode((string) ($carousel['descricao'] ?? ''), JSON_UNESCAPED_UNICODE) ?>;

(function() {
  var input = document.getElementById('descricaoInput');
  function sync() { input.value = quill.root.innerHTML; }
  sync();
  quill.on('text-change', sync);
})();
</script>

<?php if ($isEditing): ?>
<script>
document.getElementById('uploadForm').addEventListener('submit', function(e) {
  e.preventDefault();

  var formData = new FormData(this);

  fetch('/admin/config/carousel/item/upload', {
    method: 'POST',
    body: formData
  })
  .then(function(r) { return r.json(); })
  .then(function(data) {
    if (data.sucesso) {
      location.reload();
    } else {
      alert('Erro: ' + (data.erro || 'não foi possível enviar a imagem.'));
    }
  })
  .catch(function() {
    alert('Erro de rede ao enviar imagem.');
  });
});

function deletarItem(itemId) {
  if (!confirm('Tem certeza que deseja remover este item?')) return;

  var formData = new FormData();
  formData.append('item_id', itemId);

  fetch('/admin/config/carousel/item/deletar', {
    method: 'POST',
    body: formData
  })
  .then(function(r) { return r.json(); })
  .then(function(data) {
    if (data.sucesso) {
      var el = document.querySelector('[data-item-id="' + itemId + '"]');
      if (el) el.remove();
    } else {
      alert('Erro: ' + (data.erro || 'não foi possível remover o item.'));
    }
  })
  .catch(function() {
    alert('Erro de rede ao remover item.');
  });
}
</script>
<?php endif; ?>
