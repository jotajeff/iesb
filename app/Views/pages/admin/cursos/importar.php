<?php
$cursoId = (int) ($course['id'] ?? 0);
?>
<section class="container py-4">
  <div class="bg-white border rounded-3 p-4 shadow-sm">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
      <h4 class="mb-0"><i class="bi bi-file-earmark-spreadsheet me-2"></i>Importar disciplinas (Excel)</h4>
      <a class="btn btn-outline-secondary btn-sm" href="/admin/cursos/show?id=<?= $cursoId ?>#disciplinas-curso"><i class="bi bi-arrow-left me-1"></i>Voltar</a>
    </div>

    <p class="text-muted mb-4">Curso: <strong><?= htmlspecialchars((string) ($course['nome'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></strong></p>

    <?php if (!empty($flash ?? '')): ?>
      <div class="alert alert-info"><?= htmlspecialchars((string) $flash, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>

    <div class="alert alert-light border mb-4">
      <p class="mb-2"><i class="bi bi-info-circle me-1"></i>A planilha deve conter um cabeçalho na primeira linha com as colunas:</p>
      <code class="small">id&nbsp;&nbsp;id_curso&nbsp;&nbsp;nome&nbsp;&nbsp;carga_horaria&nbsp;&nbsp;ordem&nbsp;&nbsp;ativo&nbsp;&nbsp;created_at&nbsp;&nbsp;updated_at</code>
      <ul class="mb-0 mt-2 small text-muted">
        <li>A primeira linha é apenas o cabeçalho e <strong>não</strong> será importada.</li>
        <li><strong>id</strong>, <strong>id_curso</strong>, <strong>created_at</strong> e <strong>updated_at</strong> são gerados automaticamente — não informe na planilha.</li>
        <li>As colunas utilizadas são: <strong>nome</strong>, <strong>carga_horaria</strong>, <strong>ordem</strong> e <strong>ativo</strong>.</li>
        <li>Formatos aceitos: <strong>.xlsx</strong> e <strong>.csv</strong>.</li>
      </ul>
    </div>

    <form method="post" action="/admin/cursos/importar-disciplinas" enctype="multipart/form-data" class="row g-3">
      <input type="hidden" name="id_curso" value="<?= $cursoId ?>">
      <div class="col-md-8">
        <label class="form-label">Arquivo da planilha <span class="text-danger">*</span></label>
        <input type="file" name="planilha" class="form-control" accept=".xlsx,.csv" required>
      </div>
      <div class="col-12">
        <button class="btn btn-success" type="submit"><i class="bi bi-upload me-1"></i>Importar</button>
      </div>
    </form>
  </div>
</section>
