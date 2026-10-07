<?php
  $cursoId = (int) ($course['id'] ?? 0);
  $detalheId = (int) ($detalhe['id'] ?? 0);
  $detalheTexto = (string) ($detalhe['detalhe'] ?? '');
  $isEdit = $detalheId > 0;
?>
<section class="container py-4">
  <div class="bg-white border rounded-3 p-4 shadow-sm">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
      <h4 class="mb-0">
        <i class="bi bi-journal-text me-2"></i>Detalhes do Curso: <?= htmlspecialchars($course['nome'] ?? '-', ENT_QUOTES, 'UTF-8') ?>
      </h4>
      <a class="btn btn-outline-secondary btn-sm" href="/admin/cursos"><i class="bi bi-arrow-left me-1"></i>Voltar para lista</a>
    </div>

    <form method="post" action="/admin/cursos/detalhes/salvar" class="row g-3" id="formDetalhe">
      <input type="hidden" name="curso_id" value="<?= $cursoId ?>">
      <input type="hidden" name="detalhe_id" value="<?= $detalheId ?>">
      <input type="hidden" name="detalhe_b64" id="detalheB64" value="<?= htmlspecialchars(base64_encode($detalheTexto), ENT_QUOTES, 'UTF-8') ?>">

      <div class="col-12">
        <label class="form-label">Conteúdo do Detalhe</label>
        <textarea
          id="detalhe"
          class="form-control"
          rows="12"><?= htmlspecialchars($detalheTexto, ENT_QUOTES, 'UTF-8') ?></textarea>
        <div class="form-text">O conteúdo é enviado codificado (base64) para evitar bloqueio do firewall (ModSecurity).</div>
      </div>

      <div class="col-12">
        <button class="btn btn-success" type="submit">
          <i class="bi bi-check-lg me-1"></i><?= $isEdit ? 'Atualizar Detalhe' : 'Criar Detalhe' ?>
        </button>
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
  (function () {
    var editorInstance = null;

    ClassicEditor
      .create(document.querySelector('#detalhe'))
      .then(function (editor) {
        editorInstance = editor;
      })
      .catch(function (error) {
        console.error(error);
      });

    var form = document.getElementById('formDetalhe');
    if (!form) return;

    function codificarB64(str) {
      try {
        return btoa(unescape(encodeURIComponent(str)));
      } catch (e) {
        return '';
      }
    }

    form.addEventListener('submit', function () {
      var texto = editorInstance
        ? editorInstance.getData()
        : (document.querySelector('#detalhe') || {}).value || '';
      document.getElementById('detalheB64').value = codificarB64(texto);
    });
  })();
</script>
