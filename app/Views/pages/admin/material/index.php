<?php
  $materiaisView = is_array($materiais ?? null) ? $materiais : [];
  $turmasView = is_array($turmas ?? null) ? $turmas : [];
  $idTurma = (int) ($idTurma ?? 0);

  $grupos = [];
  if ($idTurma > 0) {
    $grupos[''] = $materiaisView;
  } else {
    foreach ($materiaisView as $mat) {
      $turmaNome = (string) ($mat['turma_nome'] ?? 'Turma não informada');
      $grupos[$turmaNome][] = $mat;
    }
  }
?>

<section class="container py-4">
  <div class="bg-white border rounded-3 p-4 shadow-sm">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
      <h4 class="mb-0"><i class="bi bi-folder2-open me-2"></i>Materiais Publicados</h4>
      <div class="d-flex flex-wrap align-items-center gap-2">
        <a class="btn btn-primary btn-sm" href="/admin/material/novo"><i class="bi bi-plus-circle me-1"></i>Novo material</a>
      </div>
    </div>

    <form method="get" action="/admin/material" class="row g-2 align-items-end mb-4">
      <div class="col-md-5 col-lg-4">
        <label class="form-label">Turma</label>
        <select class="form-select" name="id_turma" onchange="this.form.submit()">
          <option value="">Todas as ativas</option>
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
          <a class="btn btn-outline-secondary btn-sm" href="/admin/material"><i class="bi bi-x-lg"></i></a>
        <?php endif; ?>
      </div>
    </form>

    <?php if (empty($materiaisView)): ?>
      <p class="text-muted mb-0">Nenhum material encontrado.</p>
    <?php else: ?>
      <?php foreach ($grupos as $turmaNome => $itens): ?>
        <div class="border rounded-3 overflow-hidden mb-4">
          <?php if ($idTurma === 0): ?>
            <div class="bg-light border-bottom px-3 py-2 fw-semibold">
              <i class="bi bi-people me-1"></i><?= htmlspecialchars((string) $turmaNome, ENT_QUOTES, 'UTF-8') ?>
              <span class="badge bg-primary ms-1"><?= count($itens) ?> material(is)</span>
            </div>
          <?php endif; ?>
          <div class="table-responsive">
            <table class="table table-striped table-hover table-sm align-middle mb-0">
              <thead>
                <tr>
                  <th><i class="bi bi-hash"></i></th>
                  <th>Tipo</th>
                  <th>Título</th>
                  <?php if ($idTurma > 0): ?>
                    <th>Turma</th>
                  <?php endif; ?>
                  <th>Disciplina</th>
                  <th>Publicado em</th>
                  <th><i class="bi bi-gear me-1"></i>Ações</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($itens as $mat): ?>
                  <?php
                    $tipoMaterial = (string) ($mat['tipo'] ?? '');
                    $rawData = (string) ($mat['created_at'] ?? '');
                    $dtMaterial = $rawData !== '' ? date_create($rawData) : false;
                  ?>
                  <tr>
                    <td><?= (int) ($mat['id'] ?? 0) ?></td>
                    <td>
                      <span class="badge <?= $tipoMaterial === 'video' ? 'bg-danger' : ($tipoMaterial === 'drive' ? 'bg-primary' : 'bg-secondary') ?>">
                        <i class="bi <?= $tipoMaterial === 'video' ? 'bi-camera-reels' : 'bi-google' ?> me-1"></i>
                        <?= htmlspecialchars($tipoMaterial ?: '-', ENT_QUOTES, 'UTF-8') ?>
                      </span>
                    </td>
                    <td><?= htmlspecialchars((string) ($mat['titulo'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                    <?php if ($idTurma > 0): ?>
                      <td><?= htmlspecialchars((string) ($mat['turma_nome'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                    <?php endif; ?>
                    <td>
                      <?php if ((int) ($mat['id_disciplina'] ?? 0) > 0): ?>
                        <span class="badge bg-info text-dark"><i class="bi bi-journal-bookmark me-1"></i><?= htmlspecialchars((string) ($mat['disciplina_nome'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></span>
                      <?php else: ?>
                        <span class="badge bg-secondary"><i class="bi bi-briefcase me-1"></i>Secretaria</span>
                      <?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars($dtMaterial ? $dtMaterial->format('d/m/Y H:i') : ($rawData ?: '-'), ENT_QUOTES, 'UTF-8') ?></td>
                    <td>
                      <div class="d-flex gap-1">
                        <a class="btn btn-outline-secondary btn-sm" href="<?= htmlspecialchars((string) ($mat['link'] ?? '#'), ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener" title="Abrir">
                          <i class="bi bi-box-arrow-up-right"></i>
                        </a>
                        <form method="post" action="/admin/material/deletar" class="d-inline"
                              onsubmit="return confirm('Tem certeza que deseja remover este material?');">
                          <input type="hidden" name="id" value="<?= (int) ($mat['id'] ?? 0) ?>">
                          <button type="submit" class="btn btn-outline-danger btn-sm" title="Remover">
                            <i class="bi bi-trash"></i>
                          </button>
                        </form>
                      </div>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>
</section>