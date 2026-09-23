<?php
  $documentosView = is_array($documentos ?? null) ? $documentos : [];
  $turmasView = is_array($turmas ?? null) ? $turmas : [];
  $idTurma = (int) ($idTurma ?? 0);

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

<section class="container py-4">
  <div class="bg-white border rounded-3 p-4 shadow-sm">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
      <h4 class="mb-0"><i class="bi bi-folder2-open me-2"></i>Documentos da Secretaria</h4>
      <a class="btn btn-primary btn-sm" href="/admin/documentos/novo"><i class="bi bi-plus-circle me-1"></i>Novo documento</a>
    </div>

    <form method="get" action="/admin/documentos" class="row g-2 align-items-end mb-4">
      <div class="col-md-5 col-lg-4">
        <label class="form-label">Turma (ativas)</label>
        <select class="form-select" name="id_turma" onchange="this.form.submit()">
          <option value="">Todas as turmas ativas</option>
          <?php foreach ($turmasView as $turma): ?>
            <?php
              $label = (string) ($turma['turma_nome'] ?? '-');
              if (trim((string) ($turma['curso_nome'] ?? '')) !== '') {
                $label .= ' — ' . (string) $turma['curso_nome'];
              }
            ?>
            <option value="<?= (int) ($turma['id'] ?? 0) ?>" <?= $idTurma === (int) ($turma['id'] ?? 0) ? 'selected' : '' ?>>
              <?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-auto">
        <button class="btn btn-primary btn-sm" type="submit"><i class="bi bi-funnel me-1"></i>Filtrar</button>
        <?php if ($idTurma > 0): ?>
          <a class="btn btn-outline-secondary btn-sm" href="/admin/documentos"><i class="bi bi-x-lg"></i></a>
        <?php endif; ?>
      </div>
    </form>

    <?php if (empty($documentosView)): ?>
      <p class="text-muted mb-0">Nenhum documento encontrado.</p>
    <?php else: ?>
      <div class="table-responsive">
        <table class="table table-striped table-hover table-sm align-middle">
          <thead>
            <tr>
              <th><i class="bi bi-hash"></i></th>
              <th>Aluno</th>
              <th>Tipo</th>
              <th>Arquivo</th>
              <th>Tamanho</th>
              <th>Status</th>
              <th>Enviado em</th>
              <th><i class="bi bi-gear me-1"></i>Ações</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($documentosView as $doc): ?>
              <?php
                $dt = (string) ($doc['created_at'] ?? '');
                $dtFmt = $dt !== '' ? date_create($dt) : false;
                $fileId = (string) ($doc['file_id'] ?? '');
                $status = (string) ($doc['status'] ?? '');
                $statusClasse = match ($status) {
                  'aprovado' => 'bg-success',
                  'rejeitado' => 'bg-danger',
                  'em_analise' => 'bg-warning text-dark',
                  'substituido' => 'bg-secondary',
                  default => 'bg-info text-dark',
                };
              ?>
              <tr>
                <td><?= (int) ($doc['id'] ?? 0) ?></td>
                <td>
                  <?= htmlspecialchars((string) ($doc['aluno_nome'] ?? ('#' . (int) ($doc['id_aluno'] ?? 0))), ENT_QUOTES, 'UTF-8') ?>
                </td>
                <td><?= htmlspecialchars((string) ($doc['tipo_descricao'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                <td class="text-break" style="max-width: 260px;"><?= htmlspecialchars((string) ($doc['nome_original'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars($formatarTamanho($doc['tamanho'] ?? 0), ENT_QUOTES, 'UTF-8') ?></td>
                <td><span class="badge <?= $statusClasse ?>"><?= htmlspecialchars($status ?: '-', ENT_QUOTES, 'UTF-8') ?></span></td>
                <td><?= htmlspecialchars($dtFmt ? $dtFmt->format('d/m/Y H:i') : ($dt ?: '-'), ENT_QUOTES, 'UTF-8') ?></td>
                <td>
                  <?php if ($fileId !== ''): ?>
                    <a class="btn btn-outline-primary btn-sm" href="https://drive.google.com/file/d/<?= htmlspecialchars($fileId, ENT_QUOTES, 'UTF-8') ?>/view" target="_blank" rel="noopener" title="Ver documento">
                      <i class="bi bi-box-arrow-up-right"></i>
                    </a>
                  <?php else: ?>
                    <button class="btn btn-secondary btn-sm" disabled><i class="bi bi-box-arrow-up-right"></i></button>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>
</section>