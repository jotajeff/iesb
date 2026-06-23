<?php
$noticia = $noticia ?? null;
$isEditing = $noticia !== null;
$id = $isEditing ? (int) ($noticia['id'] ?? 0) : 0;
$categorias = $categorias ?? [];
?>
<section class="container py-4">
  <div class="bg-white border rounded-3 p-4 shadow-sm">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
      <h4 class="mb-0"><i class="bi bi-newspaper me-2"></i><?= $isEditing ? 'Editar Notícia' : 'Nova Notícia' ?></h4>
      <a class="btn btn-outline-secondary btn-sm" href="/admin/config/noticias"><i class="bi bi-arrow-left me-1"></i>Voltar</a>
    </div>

    <form method="post" action="/admin/config/noticias/salvar" enctype="multipart/form-data">
      <?php if ($isEditing): ?>
        <input type="hidden" name="id" value="<?= $id ?>">
      <?php endif; ?>

      <div class="row g-3 mb-3">
        <div class="col-md-8">
          <label class="form-label">Título <span class="text-danger">*</span></label>
          <input type="text" name="titulo" class="form-control" required
                 value="<?= htmlspecialchars((string) ($noticia['titulo'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
        </div>
        <div class="col-md-4">
          <label class="form-label">Slug</label>
          <input type="text" name="slug" class="form-control"
                 value="<?= htmlspecialchars((string) ($noticia['slug'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
          <div class="form-text">Deixe em branco para gerar automaticamente.</div>
        </div>
      </div>

      <div class="row g-3 mb-3">
        <div class="col-md-4">
          <label class="form-label">Categoria</label>
          <select name="id_categoria" class="form-select">
            <option value="">— Selecione —</option>
            <?php foreach ($categorias as $cat): ?>
              <?php $catId = (int) ($cat['id'] ?? 0); ?>
              <option value="<?= $catId ?>" <?= $catId === (int) ($noticia['id_categoria'] ?? 0) ? 'selected' : '' ?>>
                <?= htmlspecialchars((string) ($cat['nome'] ?? '-'), ENT_QUOTES, 'UTF-8') ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-4">
          <label class="form-label">Autor</label>
          <input type="text" name="autor" class="form-control"
                 value="<?= htmlspecialchars((string) ($noticia['autor'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
        </div>
        <div class="col-md-4">
          <label class="form-label">Data de Publicação</label>
          <input type="datetime-local" name="data_publicacao" class="form-control"
                 value="<?php
                   $raw = (string) ($noticia['data_publicacao'] ?? '');
                   $dt = $raw !== '' ? \DateTime::createFromFormat('Y-m-d H:i:s', $raw) : false;
                   echo $dt ? $dt->format('Y-m-d\TH:i') : '';
                 ?>">
        </div>
      </div>

      <div class="row g-3 mb-3">
        <div class="col-md-4">
          <label class="form-label">Status</label>
          <select name="status" class="form-select">
            <?php $status = (string) ($noticia['status'] ?? 'rascunho'); ?>
            <option value="rascunho" <?= $status === 'rascunho' ? 'selected' : '' ?>>Rascunho</option>
            <option value="publicado" <?= $status === 'publicado' ? 'selected' : '' ?>>Publicado</option>
            <option value="arquivado" <?= $status === 'arquivado' ? 'selected' : '' ?>>Arquivado</option>
          </select>
        </div>
        <div class="col-md-2">
          <label class="form-label">Destaque</label>
          <div class="form-check form-switch mt-2">
            <input class="form-check-input" type="checkbox" name="destaque" value="1" role="switch"
                   id="destaqueSwitch" <?= (int) ($noticia['destaque'] ?? 0) === 1 ? 'checked' : '' ?>>
            <label class="form-check-label" for="destaqueSwitch">Sim</label>
          </div>
        </div>
        <div class="col-md-3">
          <label class="form-label">Data do Evento</label>
          <input type="datetime-local" name="data_evento" class="form-control"
                 value="<?php
                   $raw = (string) ($noticia['data_evento'] ?? '');
                   $dt = $raw !== '' ? \DateTime::createFromFormat('Y-m-d H:i:s', $raw) : false;
                   echo $dt ? $dt->format('Y-m-d\TH:i') : '';
                 ?>">
        </div>
      </div>

      <div class="mb-3">
        <label class="form-label">Resumo</label>
        <textarea name="resumo" class="form-control" rows="3" maxlength="500"><?= htmlspecialchars((string) ($noticia['resumo'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>
      </div>

      <div class="mb-3">
        <label class="form-label">Conteúdo</label>
        <div id="conteudoEditor" style="height:400px;"><?= (string) ($noticia['conteudo'] ?? '') ?></div>
        <input type="hidden" name="conteudo" id="conteudoInput"
               value="<?= htmlspecialchars((string) ($noticia['conteudo'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
      </div>

      <div class="row g-3 mb-3">
        <div class="col-md-6">
          <label class="form-label">Imagem de Capa</label>
          <?php if ($isEditing && (string) ($noticia['imagem_capa'] ?? '') !== ''): ?>
            <div class="mb-2">
              <img src="/<?= htmlspecialchars((string) ($noticia['imagem_capa'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                   class="img-thumbnail" style="max-height:150px;" alt="Capa atual">
            </div>
          <?php endif; ?>
          <input type="file" name="imagem_capa" class="form-control" accept="image/*">
        </div>
        <div class="col-md-6">
          <label class="form-label">Legenda da Imagem</label>
          <input type="text" name="legenda_imagem" class="form-control"
                 value="<?= htmlspecialchars((string) ($noticia['legenda_imagem'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
        </div>
      </div>

      <hr>
      <h6 class="mb-3"><i class="bi bi-search me-1"></i>SEO</h6>
      <div class="row g-3 mb-3">
        <div class="col-md-6">
          <label class="form-label">Meta Title</label>
          <input type="text" name="meta_title" class="form-control"
                 value="<?= htmlspecialchars((string) ($noticia['meta_title'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
        </div>
        <div class="col-md-6">
          <label class="form-label">Meta Description</label>
          <input type="text" name="meta_description" class="form-control" maxlength="300"
                 value="<?= htmlspecialchars((string) ($noticia['meta_description'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
        </div>
      </div>

      <div class="d-flex justify-content-end gap-2">
        <a class="btn btn-outline-secondary" href="/admin/config/noticias">Cancelar</a>
        <button type="submit" class="btn btn-success"><i class="bi bi-check-lg me-1"></i><?= $isEditing ? 'Atualizar Notícia' : 'Criar Notícia' ?></button>
      </div>
    </form>
  </div>
</section>

<link href="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.snow.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.min.js"></script>

<script>
var quill = new Quill('#conteudoEditor', {
  theme: 'snow',
  placeholder: 'Escreva o conteúdo da notícia...',
});
quill.root.innerHTML = <?= json_encode((string) ($noticia['conteudo'] ?? ''), JSON_UNESCAPED_UNICODE) ?>;

(function() {
  var input = document.getElementById('conteudoInput');
  function sync() { input.value = quill.root.innerHTML; }
  sync();
  quill.on('text-change', sync);
})();
</script>
