<?php
  $documentosView = is_array($documentos ?? null) ? $documentos : [];

  $formatarTamanho = static function ($bytes): string {
    $bytes = (int) $bytes;
    if ($bytes <= 0) {
      return '-';
    }
    $unidades = ['B', 'KB', 'MB', 'GB'];
    $i = 0;
    $valor = (float) $bytes;
    while ($valor >= 1024 && $i < count($unidades) - 1) {
      $valor /= 1024;
      $i++;
    }
    return number_format($valor, $i === 0 ? 0 : 1, ',', '.') . ' ' . $unidades[$i];
  };
?>

<section class="py-4" style="margin-top: 20px;">
  <div class="container">
    <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 16px; padding: 2rem; box-shadow: var(--card-shadow);">
      <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <h4 class="mb-0"><i class="bi bi-folder2-open me-2"></i>Documentos da Secretaria</h4>
        <a class="btn btn-outline-secondary btn-sm" href="/aluno/documentos"><i class="bi bi-folder2-open me-1"></i>Meus documentos</a>
      </div>

      <p class="text-muted small">Documentos enviados pela Secretaria para você.</p>

      <?php if (empty($documentosView)): ?>
        <p class="text-muted mb-0"><i class="bi bi-inbox me-1"></i>Nenhum documento enviado pela Secretaria.</p>
      <?php else: ?>
        <div class="table-responsive">
          <table class="table table-striped table-hover align-middle mb-0">
            <thead>
              <tr>
                <th><i class="bi bi-tag me-1"></i>Tipo</th>
                <th><i class="bi bi-file-earmark-text me-1"></i>Arquivo</th>
                <th><i class="bi bi-hdd me-1"></i>Tamanho</th>
                <th><i class="bi bi-calendar3 me-1"></i>Enviado em</th>
                <th></th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($documentosView as $doc): ?>
                <?php
                  $dt = (string) ($doc['created_at'] ?? '');
                  $dtFmt = $dt !== '' ? date_create($dt) : false;
                  $fileId = (string) ($doc['file_id'] ?? '');
                ?>
                <tr>
                  <td><?= htmlspecialchars((string) ($doc['tipo_descricao'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                  <td class="text-break" style="max-width: 300px;"><?= htmlspecialchars((string) ($doc['nome_original'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                  <td><?= htmlspecialchars($formatarTamanho($doc['tamanho'] ?? 0), ENT_QUOTES, 'UTF-8') ?></td>
                  <td><?= htmlspecialchars($dtFmt ? $dtFmt->format('d/m/Y H:i') : ($dt ?: '-'), ENT_QUOTES, 'UTF-8') ?></td>
                  <td>
                    <?php if ($fileId !== ''): ?>
                      <a class="btn btn-sm btn-outline-primary" href="https://drive.google.com/file/d/<?= htmlspecialchars($fileId, ENT_QUOTES, 'UTF-8') ?>/view" target="_blank" rel="noopener">
                        <i class="bi bi-box-arrow-up-right me-1"></i>Visualizar
                      </a>
                    <?php else: ?>
                      <button class="btn btn-sm btn-secondary" disabled>Indisponível</button>
                    <?php endif; ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </div>
  </div>
</section>