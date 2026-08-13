<?php
$usuario = $usuario ?? [];
$imagens = $imagens ?? [];
$idFk = (int) ($idFk ?? 0);
$tabelaFk = (string) ($tabelaFk ?? '');
$flashUpload = $flash ?? '';
?>
<section class="container py-4">
  <div class="bg-white border rounded-3 p-4 shadow-sm">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
      <h4 class="mb-0"><i class="bi bi-camera me-2"></i>Fotos — <?= htmlspecialchars((string) ($usuario['nome'] ?? ''), ENT_QUOTES, 'UTF-8') ?></h4>
      <a class="btn btn-outline-secondary btn-sm" href="/admin/usuarios"><i class="bi bi-arrow-left me-1"></i>Voltar</a>
    </div>

    <?php if ($flashUpload && (strpos($flashUpload, 'sucesso') !== false || $flashUpload === 'Foto salva com sucesso.')): ?>
      <div class="alert alert-success d-flex justify-content-between align-items-center">
        <span><?= htmlspecialchars($flashUpload, ENT_QUOTES, 'UTF-8') ?></span>
        <span>
          <a class="btn btn-sm btn-outline-primary me-2" href="/admin/usuarios/fotos?id=<?= $idFk ?>"><i class="bi bi-plus-lg me-1"></i>Adicionar outra</a>
          <a class="btn btn-sm btn-outline-secondary" href="/admin/usuarios"><i class="bi bi-list-ul me-1"></i>Voltar à listagem</a>
        </span>
      </div>
    <?php endif; ?>

    <?php if (!empty($imagens)): ?>
      <div class="row g-3 mb-4">
        <?php foreach ($imagens as $img): ?>
          <div class="col-md-3">
            <div class="card border shadow-sm">
              <img src="/<?= htmlspecialchars((string) ($img['path'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" class="img-fluid" alt="<?= htmlspecialchars((string) ($img['legenda'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
              <div class="card-body p-2">
                <small class="text-muted"><?= htmlspecialchars((string) ($img['legenda'] ?? '—'), ENT_QUOTES, 'UTF-8') ?></small>
              </div>
              <div class="card-footer p-2 text-end">
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="deletarFoto(<?= (int) ($img['id'] ?? 0) ?>, <?= $idFk ?>)">
                  <i class="bi bi-trash3"></i>
                </button>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php elseif (!$flashUpload || $flashUpload !== 'Foto salva com sucesso.'): ?>
      <div class="text-muted small mb-4">Nenhuma foto cadastrada.</div>
    <?php endif; ?>

    <hr>
    <h5 class="mb-3"><i class="bi bi-cloud-arrow-up me-1"></i>Upload de foto</h5>
    <form method="post" action="/admin/usuarios/upload-foto" enctype="multipart/form-data">
      <input type="hidden" name="id_fk" value="<?= $idFk ?>">
      <input type="hidden" name="tabela_fk" value="<?= htmlspecialchars($tabelaFk, ENT_QUOTES, 'UTF-8') ?>">

      <div class="row g-3">
        <div class="col-md-4">
          <label class="form-label">Foto</label>
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
function deletarFoto(id, idFk) {
  if (!confirm('Tem certeza que deseja excluir esta foto?')) return;

  const formData = new FormData();
  formData.append('id', id);
  formData.append('id_fk', idFk);
  formData.append('tabela_fk', 'usuarios');

  fetch('/admin/usuarios/deletar-foto', { method: 'POST', body: formData })
    .then(r => r.json())
    .then(data => {
      if (data.sucesso) {
        location.reload();
      } else {
        alert('Erro ao excluir: ' + (data.erro || 'Erro desconhecido'));
      }
    })
    .catch(() => alert('Erro ao excluir foto.'));
}
</script>
