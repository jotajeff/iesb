<?php
  $chamada = is_array($chamada ?? null) ? $chamada : [];
  $id = (int) ($chamada['id'] ?? 0);
  $origem = trim((string) ($chamada['origem_chamada'] ?? ''));
  $dataAula = (string) ($chamada['data_aula'] ?? '');
  $dtAula = $dataAula !== '' ? date_create($dataAula) : false;
?>

<section class="container py-4">
  <div class="bg-white border rounded-3 p-4 shadow-sm">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
      <h4 class="mb-0"><i class="bi bi-cloud-upload me-2"></i>Origem da Chamada #<?= str_pad((string) $id, 6, '0', STR_PAD_LEFT) ?></h4>
      <a class="btn btn-outline-secondary btn-sm" href="/admin/chamadas/editar?id=<?= $id ?>"><i class="bi bi-arrow-left me-1"></i>Voltar</a>
    </div>

    <div class="alert alert-light border mb-3">
      <i class="bi bi-people me-1"></i><strong>Turma:</strong> <?= htmlspecialchars((string) ($chamada['turma_nome'] ?? '-'), ENT_QUOTES, 'UTF-8') ?>
      &middot; <i class="bi bi-journal-bookmark me-1"></i><strong>Disciplina:</strong> <?= htmlspecialchars((string) ($chamada['disciplina_nome'] ?? '-'), ENT_QUOTES, 'UTF-8') ?>
      &middot; <i class="bi bi-calendar3 me-1"></i><strong>Data:</strong> <?= htmlspecialchars($dtAula ? $dtAula->format('d/m/Y') : ($dataAula ?: '-'), ENT_QUOTES, 'UTF-8') ?>
    </div>

    <?php if ($origem !== ''): ?>
      <div class="alert alert-success d-flex align-items-center gap-2">
        <i class="bi bi-file-earmark-check fs-4"></i>
        <div>
          Documento de origem já enviado.
          <a href="<?= htmlspecialchars($origem, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener" class="alert-link fw-semibold ms-1">
            <i class="bi bi-box-arrow-up-right me-1"></i>Ver documento
          </a>
        </div>
      </div>
    <?php endif; ?>

    <p class="text-muted small">
      Envie o documento que originou esta chamada (arquivo físico/digitalizado). O arquivo será armazenado no Google Drive em
      <strong>IESB-Uploads &gt; Chamadas</strong>, com o prefixo <code><?= $id ?>_<?= date('Ymd') ?></code>.
      <?= $origem !== '' ? 'Enviar um novo arquivo substituirá o atual.' : '' ?>
    </p>

    <form method="post" action="/admin/chamadas/upload-origem" enctype="multipart/form-data" class="row g-3">
      <input type="hidden" name="id" value="<?= $id ?>">
      <div class="col-12">
        <label class="form-label">Arquivo <span class="text-danger">*</span></label>
        <input type="file" class="form-control" name="arquivo" required>
      </div>
      <div class="col-12">
        <button class="btn btn-success" type="submit"><i class="bi bi-cloud-upload me-1"></i>Enviar documento</button>
      </div>
    </form>
  </div>
</section>