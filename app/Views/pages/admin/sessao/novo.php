<section class="container py-4">
  <div class="bg-white border rounded-3 p-4 shadow-sm">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
      <h4 class="mb-0"><i class="bi bi-plus-lg me-2"></i>Nova Sessão</h4>
      <a class="btn btn-outline-secondary btn-sm" href="/admin/sessao"><i class="bi bi-arrow-left me-1"></i>Voltar</a>
    </div>

    <form method="post" action="/admin/sessao/salvar" enctype="multipart/form-data">
      <div class="row g-3">
        <div class="col-md-6">
          <label class="form-label">Título <span class="text-danger">*</span></label>
          <input type="text" name="titulo" class="form-control" maxlength="150" required>
        </div>
        <div class="col-md-3">
          <label class="form-label">Slug</label>
          <select name="slug" class="form-select">
            <option value="">Selecione...</option>
            <option value="eventos">eventos</option>
            <option value="parcerias">parcerias</option>
            <option value="sobre">sobre</option>
          </select>
        </div>
        <div class="col-md-3">
          <label class="form-label">Badge</label>
          <input type="text" name="badge" class="form-control" maxlength="50">
        </div>
        <div class="col-12">
          <label class="form-label">Apresenta</label>
          <input type="text" name="apresenta" class="form-control" maxlength="255" placeholder="Subtítulo ou chamada">
        </div>
        <div class="col-12">
          <label class="form-label">Banner (imagem)</label>
          <input type="file" name="banner" class="form-control" accept="image/*">
        </div>
        <div class="col-12">
          <label class="form-label">Texto</label>
          <textarea name="texto" id="texto" class="form-control" rows="6"></textarea>
        </div>
        <div class="col-md-3">
          <label class="form-label">Mídia</label>
          <select name="midia" class="form-select">
            <option value="">—</option>
            <option value="C">C — Carrossel</option>
            <option value="G">G — Galeria</option>
          </select>
        </div>
      </div>
      <div class="mt-4">
        <button type="submit" class="btn btn-primary" id="btnSalvar"><span id="spinnerSalvar" class="spinner-border spinner-border-sm d-none me-1" role="status" aria-hidden="true"></span><i class="bi bi-check-lg me-1"></i>Salvar</button>
        <a class="btn btn-outline-secondary ms-2" href="/admin/sessao">Cancelar</a>
      </div>
    </form>
  </div>
</section>

<script src="https://cdn.ckeditor.com/ckeditor5/41.4.2/classic/ckeditor.js"></script>
<style>
  .ck-editor__editable_inline { min-height: 280px; }
</style>
<script>
  ClassicEditor.create(document.querySelector('#texto')).catch(function (error) { console.error(error); });
  document.getElementById('btnSalvar').addEventListener('click', function () {
    document.getElementById('spinnerSalvar').classList.remove('d-none');
  });
</script>
