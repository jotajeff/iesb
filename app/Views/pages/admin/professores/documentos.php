<?php
$documentos = $documentos ?? [];
$pasta = $pasta ?? null;
$storageConectado = (bool) ($storageConectado ?? false);
$storageErro = $storageErro ?? null;
$statusLabels = [
    'nao_enviado' => 'Não enviado',
    'enviado' => 'Enviado',
    'em_analise' => 'Em análise',
    'aprovado' => 'Aprovado',
    'rejeitado' => 'Rejeitado',
    'substituido' => 'Substituído',
];
$statusBadges = [
    'nao_enviado' => 'bg-secondary',
    'enviado' => 'bg-info',
    'em_analise' => 'bg-warning text-dark',
    'aprovado' => 'bg-success',
    'rejeitado' => 'bg-danger',
    'substituido' => 'bg-dark',
];
?>

<section class="container py-4">
  <div class="bg-white border rounded-3 p-4 shadow-sm">
    <h4 class="mb-1"><i class="bi bi-folder2-open me-2"></i>Meus Documentos</h4>
    <p class="text-muted mb-4">Envie os documentos solicitados pela secretaria.</p>

    <?php if (!$storageConectado): ?>
      <div class="alert alert-warning border">
        <i class="bi bi-exclamation-triangle me-1"></i>
        <?= htmlspecialchars((string) ($storageErro ?? 'Storage indisponível.'), ENT_QUOTES, 'UTF-8') ?>
        Não é possível enviar documentos no momento. Fale com a secretaria.
      </div>
    <?php endif; ?>

    <?php if (!empty($pasta['folder_name'])): ?>
      <div class="alert alert-light border small mb-4">
        <i class="bi bi-folder me-1"></i>
        Pasta do professor: <strong><?= htmlspecialchars((string) $pasta['folder_name'], ENT_QUOTES, 'UTF-8') ?></strong>
      </div>
    <?php endif; ?>

    <?php if (empty($documentos)): ?>
      <div class="text-center text-muted py-4">
        <i class="bi bi-folder2" style="font-size: 2rem;"></i>
        <p class="mt-2 mb-0">Nenhum documento definido pela secretaria até o momento.</p>
      </div>
    <?php else: ?>
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead>
            <tr>
              <th><i class="bi bi-hash"></i></th>
              <th><i class="bi bi-file-earmark-text me-1"></i>Documento</th>
              <th><i class="bi bi-asterisk me-1"></i>Obrigatório</th>
              <th><i class="bi bi-check2-circle me-1"></i>Status</th>
              <th><i class="bi bi-file-earmark me-1"></i>Arquivo</th>
              <th><i class="bi bi-calendar me-1"></i>Enviado em</th>
              <th><i class="bi bi-sliders me-1"></i>Ações</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($documentos as $doc): ?>
              <?php
              $docId = (int) ($doc['documento_id'] ?? 0);
              $status = (string) ($doc['status'] ?? 'nao_enviado');
              $podeSubstituir = in_array($status, ['nao_enviado', 'enviado', 'rejeitado', 'substituido'], true);
              $podeExcluir = in_array($status, ['enviado', 'nao_enviado', 'substituido'], true);
              ?>
              <tr>
                <td><?= (int) ($doc['ordem'] ?? $doc['tipo_id'] ?? 0) ?></td>
                <td><?= htmlspecialchars((string) ($doc['tipo_descricao'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                <td>
                  <?php if ((int) ($doc['obrigatorio'] ?? 0) === 1): ?>
                    <span class="badge bg-danger"><i class="bi bi-exclamation-circle me-1"></i>Obrigatório</span>
                  <?php else: ?>
                    <span class="badge bg-secondary">Opcional</span>
                  <?php endif; ?>
                </td>
                <td>
                  <span class="badge <?= $statusBadges[$status] ?? 'bg-secondary' ?>">
                    <?= htmlspecialchars($statusLabels[$status] ?? ucfirst($status), ENT_QUOTES, 'UTF-8') ?>
                  </span>
                  <?php if ($status === 'rejeitado' && !empty($doc['observacao'])): ?>
                    <div class="small text-danger mt-1">
                      <i class="bi bi-chat-left-text me-1"></i><?= htmlspecialchars((string) $doc['observacao'], ENT_QUOTES, 'UTF-8') ?>
                    </div>
                  <?php endif; ?>
                </td>
                <td>
                  <?php if ($docId > 0): ?>
                    <i class="bi bi-file-earmark-pdf me-1"></i>
                    <?= htmlspecialchars((string) ($doc['nome_original'] ?? $doc['nome_drive'] ?? '-'), ENT_QUOTES, 'UTF-8') ?>
                    <small class="text-muted">
                      <?php if (!empty($doc['tamanho'])): ?>(<?= number_format((int) $doc['tamanho'], 0, ',', '.') ?> bytes)<?php endif; ?>
                    </small>
                    <?php if ((int) ($doc['versao'] ?? 1) > 1): ?>
                      <span class="badge bg-light text-dark border ms-1">v<?= (int) $doc['versao'] ?></span>
                    <?php endif; ?>
                  <?php else: ?>
                    <span class="text-muted">-</span>
                  <?php endif; ?>
                </td>
                <td class="text-nowrap">
                  <?= $docId > 0 ? htmlspecialchars((string) ($doc['created_at'] ?? '-'), ENT_QUOTES, 'UTF-8') : '<span class="text-muted">-</span>' ?>
                </td>
                <td>
                  <div class="d-flex flex-wrap gap-1">
                    <?php if ($docId > 0): ?>
                      <a class="btn btn-sm btn-outline-success" href="/admin/professores/documentos/visualizar?id=<?= $docId ?>" target="_blank" title="Visualizar">
                        <i class="bi bi-eye"></i>
                      </a>
                      <a class="btn btn-sm btn-outline-primary" href="/admin/professores/documentos/baixar?id=<?= $docId ?>" title="Baixar">
                        <i class="bi bi-download"></i>
                      </a>
                    <?php endif; ?>
                    <?php if ($storageConectado): ?>
                      <?php if ($docId > 0 && $podeSubstituir): ?>
                        <button type="button" class="btn btn-sm btn-outline-warning" data-bs-toggle="modal" data-bs-target="#uploadModal<?= (int) ($doc['tipo_id'] ?? 0) ?>" title="Substituir">
                          <i class="bi bi-arrow-repeat"></i>
                        </button>
                      <?php elseif ($docId === 0): ?>
                        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#uploadModal<?= (int) ($doc['tipo_id'] ?? 0) ?>" title="Enviar">
                          <i class="bi bi-upload"></i>
                        </button>
                      <?php endif; ?>
                      <?php if ($docId > 0 && $podeExcluir): ?>
                        <form method="post" action="/admin/professores/documentos/excluir" class="d-inline" onsubmit="return confirm('Excluir este documento?');">
                          <input type="hidden" name="id" value="<?= $docId ?>">
                          <button type="submit" class="btn btn-sm btn-outline-danger" title="Excluir">
                            <i class="bi bi-trash"></i>
                          </button>
                        </form>
                      <?php endif; ?>
                    <?php endif; ?>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>
</section>

<?php foreach ($documentos as $doc): ?>
  <?php if (!$storageConectado) { break; } ?>
  <?php $tipoIdModal = (int) ($doc['tipo_id'] ?? 0); ?>
  <div class="modal fade" id="uploadModal<?= $tipoIdModal ?>" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <form method="post" action="/admin/professores/documentos/enviar" enctype="multipart/form-data">
          <input type="hidden" name="id_tipo" value="<?= $tipoIdModal ?>">
          <div class="modal-header">
            <h5 class="modal-title">
              <i class="bi bi-upload me-2"></i>Enviar <?= htmlspecialchars((string) ($doc['tipo_descricao'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
            </h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
          </div>
          <div class="modal-body">
            <?php if ((int) ($doc['documento_id'] ?? 0) > 0): ?>
              <div class="alert alert-warning border small">
                <i class="bi bi-arrow-repeat me-1"></i>Já existe um arquivo enviado. Ao enviar, o anterior será marcado como <strong>Substituído</strong>.
              </div>
            <?php endif; ?>
            <div class="mb-3">
              <label class="form-label">Arquivo (PDF, PNG, JPG ou JPEG - máx. 20MB)</label>
              <input type="file" class="form-control" name="arquivo" accept=".pdf,.png,.jpg,.jpeg" required>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
            <button type="submit" class="btn btn-primary"><i class="bi bi-upload me-1"></i>Enviar</button>
          </div>
        </form>
      </div>
    </div>
  </div>
<?php endforeach; ?>
