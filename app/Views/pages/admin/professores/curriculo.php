<section class="container py-4">
  <div class="bg-white border rounded-3 p-4 shadow-sm">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
      <h4 class="mb-0"><i class="bi bi-file-earmark-text me-2"></i><?= $curriculo ? 'Editar Currículo' : 'Adicionar Currículo' ?></h4>
      <a class="btn btn-outline-secondary btn-sm" href="/admin/professores/perfil"><i class="bi bi-arrow-left me-1"></i>Voltar para Perfil</a>
    </div>

    <form method="post" action="/admin/professores/salvar-curriculo" class="row g-3">
      <div class="col-12">
        <label class="form-label">Conteúdo do Currículo <span class="text-danger">*</span></label>
        <textarea class="form-control" name="conteudo" id="conteudo" rows="20"><?= htmlspecialchars((string) ($curriculo['conteudo'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>
      </div>
      <div class="col-12">
        <button class="btn btn-success" type="submit"><i class="bi bi-check-lg me-1"></i>Salvar</button>
      </div>
    </form>
  </div>
</section>

<script src="https://cdn.ckeditor.com/ckeditor5/41.4.2/classic/ckeditor.js"></script>
<style>
  .ck-editor__editable_inline {
    min-height: 420px;
  }
</style>
<script>
  ClassicEditor
    .create(document.querySelector('#conteudo'))
    .catch(function (error) {
      console.error(error);
    });
</script>
