<?php
$course = $course ?? [];
$imagens = $imagens ?? [];
$cursoId = (int) ($course['id'] ?? 0);
?>
<section class="container py-4">
  <div class="bg-white border rounded-3 p-4 shadow-sm">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
      <h4 class="mb-0"><i class="bi bi-images me-2"></i>Galeria — <?= htmlspecialchars((string) ($course['nome'] ?? ''), ENT_QUOTES, 'UTF-8') ?></h4>
      <a class="btn btn-outline-secondary btn-sm" href="/admin/cursos/show?id=<?= $cursoId ?>"><i class="bi bi-arrow-left me-1"></i>Voltar</a>
    </div>

    <?php if (!empty($imagens)): ?>
      <div class="row g-3 mb-4">
        <?php foreach ($imagens as $img): ?>
          <div class="col-md-3">
            <div class="card border shadow-sm">
              <img src="/<?= htmlspecialchars((string) ($img['path'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" class="card-img-top" alt="<?= htmlspecialchars((string) ($img['legenda'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" style="height:180px;object-fit:cover;">
              <div class="card-body p-2">
                <small class="text-muted"><?= htmlspecialchars((string) ($img['legenda'] ?? '—'), ENT_QUOTES, 'UTF-8') ?></small>
              </div>
              <div class="card-footer p-2 text-end">
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="deletarImagem(<?= (int) ($img['id'] ?? 0) ?>)">
                  <i class="bi bi-trash3"></i>
                </button>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <div class="text-center text-muted p-4 mb-4 border rounded bg-light">
        <i class="bi bi-images" style="font-size:2rem;"></i>
        <p class="mt-2 mb-0">Nenhuma imagem na galeria deste curso.</p>
      </div>
    <?php endif; ?>

    <hr>
    <h5 class="mb-3"><i class="bi bi-cloud-arrow-up me-1"></i>Upload de imagem</h5>
    <form method="post" action="/admin/cursos/upload-galeria" enctype="multipart/form-data">
      <input type="hidden" name="id_curso" value="<?= $cursoId ?>">

      <div class="row g-3">
        <div class="col-md-4">
          <label class="form-label">Imagem</label>
          <input type="file" name="imagem" class="form-control" accept="image/*" required>
        </div>
        <div class="col-md-4">
          <label class="form-label">Legenda</label>
          <input type="text" name="legenda" class="form-control" maxlength="150" placeholder="Opcional">
        </div>
        <div class="col-md-4 d-flex align-items-end">
          <button type="submit" class="btn btn-primary w-100"><i class="bi bi-cloud-arrow-up me-1"></i>Enviar</button>
        </div>
      </div>
    </form>
  </div>
</section>

<script>
function deletarImagem(id) {
  if (!confirm('Tem certeza que deseja excluir esta imagem?')) return;
  const formData = new FormData();
  formData.append('id', id);
  fetch('/admin/cursos/deletar-galeria', { method: 'POST', body: formData })
    .then(r => r.json())
    .then(data => {
      if (data.sucesso) {
        location.reload();
      } else {
        alert('Erro ao excluir: ' + (data.erro || 'Erro desconhecido'));
      }
    })
    .catch(() => alert('Erro ao excluir imagem.'));
}
</script>
