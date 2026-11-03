<?php
  $alunosView = is_array($alunos ?? null) ? $alunos : [];
  $alunoIdAtual = (int) ($alunoId ?? 0);
  $alunoSelecionado = is_array($alunoSelecionado ?? null) ? $alunoSelecionado : null;
  $recursosView = is_array($recursos ?? null) ? $recursos : [];
  $totalAcessos = (int) ($totalAcessos ?? 0);

  $tiposRecurso = [
    'video' => ['rotulo' => 'Vídeo', 'icone' => 'bi-camera-reels', 'cor' => 'bg-danger'],
    'drive' => ['rotulo' => 'PDF', 'icone' => 'bi-file-earmark-pdf', 'cor' => 'bg-primary'],
    'pdf' => ['rotulo' => 'PDF', 'icone' => 'bi-file-earmark-pdf', 'cor' => 'bg-primary'],
    'excel' => ['rotulo' => 'Excel', 'icone' => 'bi-file-earmark-excel', 'cor' => 'bg-success'],
    'doc' => ['rotulo' => 'DOC', 'icone' => 'bi-file-earmark-word', 'cor' => 'bg-info text-dark'],
  ];

  $formatarData = static function (?string $valor): string {
    if ($valor === null || trim($valor) === '') {
      return '-';
    }
    try {
      return (new DateTime($valor))->format('d/m/Y H:i');
    } catch (Throwable) {
      return $valor;
    }
  };
?>

<section class="container py-4">
  <div class="bg-white border rounded-3 p-4 shadow-sm">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
      <div>
        <h4 class="mb-0"><i class="bi bi-person-lines-fill me-2"></i>Recursos por aluno</h4>
        <p class="text-muted small mb-0">Selecione um aluno para ver todos os recursos acessados por ele.</p>
      </div>
      <div class="d-flex flex-wrap gap-2">
        <a class="btn btn-outline-dark btn-sm" href="/admin/logs/recursos/ranking">
          <i class="bi bi-bar-chart-line me-1"></i>Ranking
        </a>
        <a class="btn btn-outline-secondary btn-sm" href="/admin/logs/recursos">
          <i class="bi bi-arrow-left me-1"></i>Recursos de aula
        </a>
      </div>
    </div>

    <form method="get" action="/admin/logs/recursos/aluno" class="row g-2 align-items-end mb-4">
      <div class="col-sm-8 col-md-6">
        <label class="form-label" for="alunoRecurso">Aluno</label>
        <select class="form-select" name="aluno_id" id="alunoRecurso" onchange="this.form.submit()">
          <option value="">Selecione um aluno</option>
          <?php foreach ($alunosView as $alunoItem): ?>
            <option value="<?= (int) ($alunoItem['id'] ?? 0) ?>" <?= $alunoIdAtual === (int) ($alunoItem['id'] ?? 0) ? 'selected' : '' ?>>
              <?= htmlspecialchars((string) ($alunoItem['nome'] ?? '-'), ENT_QUOTES, 'UTF-8') ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-auto">
        <button class="btn btn-primary btn-sm" type="submit"><i class="bi bi-search me-1"></i>Consultar</button>
      </div>
    </form>

    <?php if ($alunoIdAtual <= 0 || $alunoSelecionado === null): ?>
      <div class="alert alert-light border text-muted mb-0">
        <i class="bi bi-person me-1"></i>Selecione um aluno para visualizar os recursos acessados.
      </div>
    <?php else: ?>
      <div class="alert alert-light border d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <div>
          <div class="fw-semibold"><?= htmlspecialchars((string) ($alunoSelecionado['nome'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></div>
          <div class="text-muted small"><?= htmlspecialchars((string) ($alunoSelecionado['email'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></div>
        </div>
        <div class="d-flex flex-wrap gap-2">
          <span class="badge bg-light text-dark border"><i class="bi bi-collection-play me-1"></i><?= count($recursosView) ?> recurso(s)</span>
          <span class="badge bg-dark"><i class="bi bi-eye me-1"></i><?= $totalAcessos ?> acesso(s)</span>
        </div>
      </div>

      <?php if ($recursosView === []): ?>
        <div class="alert alert-light border text-muted mb-0">
          <i class="bi bi-eye-slash me-1"></i>Nenhum recurso acessado por este aluno.
        </div>
      <?php else: ?>
        <div class="table-responsive">
          <table class="table table-sm table-striped table-hover align-middle mb-0">
            <thead>
              <tr>
                <th class="text-center" style="width: 44px;" title="Visualizado"><i class="bi bi-check2-square"></i></th>
                <th>Recurso</th>
                <th>Tipo</th>
                <th>Turma / Disciplina</th>
                <th class="text-center">Acessos</th>
                <th>Primeiro acesso</th>
                <th>Último acesso</th>
                <th>Localização</th>
                <th><i class="bi bi-gear me-1"></i>Ações</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($recursosView as $recurso): ?>
                <?php
                  $tipoRecurso = (string) ($recurso['tipo'] ?? '');
                  $infoTipo = $tiposRecurso[$tipoRecurso] ?? ['rotulo' => ucfirst($tipoRecurso ?: 'Arquivo'), 'icone' => 'bi-file-earmark', 'cor' => 'bg-secondary'];
                  $turmaNome = trim((string) ($recurso['turma_nome'] ?? ''));
                  $disciplinaNome = trim((string) ($recurso['disciplina_nome'] ?? ''));
                  $urlVisualizar = $tipoRecurso === 'video'
                    ? '/admin/turmas/ver-video?id=' . (int) ($recurso['id'] ?? 0)
                    : '/admin/turmas/ver-drive?id=' . (int) ($recurso['id'] ?? 0);
                  $local = is_array($recurso['localizacao'] ?? null) ? $recurso['localizacao'] : [];
                  $cidade = trim((string) ($local['city'] ?? ''));
                  $pais = trim((string) ($local['country'] ?? ''));
                  $localLabel = $pais !== '' && $pais !== '-' ? $pais : '-';
                  if ($cidade !== '' && $cidade !== '-' && $localLabel !== '-') {
                    $localLabel = $cidade . '/' . $localLabel;
                  } elseif ($cidade !== '' && $cidade !== '-') {
                    $localLabel = $cidade;
                  }
                ?>
                <tr>
                  <td class="text-center">
                    <?php if ((int) ($recurso['visualizado'] ?? 0) === 1): ?>
                      <i class="bi bi-check2-square text-primary fs-5" title="Visualizado"></i>
                    <?php endif; ?>
                  </td>
                  <td>
                    <span class="fw-semibold"><?= htmlspecialchars((string) ($recurso['titulo'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></span>
                    <?php if ((int) ($recurso['ativo'] ?? 1) !== 1): ?>
                      <span class="badge bg-secondary ms-1">Inativo</span>
                    <?php endif; ?>
                  </td>
                  <td>
                    <span class="badge <?= htmlspecialchars($infoTipo['cor'], ENT_QUOTES, 'UTF-8') ?>">
                      <i class="bi <?= htmlspecialchars($infoTipo['icone'], ENT_QUOTES, 'UTF-8') ?> me-1"></i><?= htmlspecialchars($infoTipo['rotulo'], ENT_QUOTES, 'UTF-8') ?>
                    </span>
                  </td>
                  <td>
                    <?php if ($turmaNome !== ''): ?>
                      <span class="d-block"><?= htmlspecialchars($turmaNome, ENT_QUOTES, 'UTF-8') ?></span>
                    <?php endif; ?>
                    <?php if ($disciplinaNome !== ''): ?>
                      <span class="text-muted small"><?= htmlspecialchars($disciplinaNome, ENT_QUOTES, 'UTF-8') ?></span>
                    <?php endif; ?>
                    <?php if ($turmaNome === '' && $disciplinaNome === ''): ?>
                      -
                    <?php endif; ?>
                  </td>
                  <td class="text-center"><?= (int) ($recurso['total_acessos'] ?? 0) ?></td>
                  <td><?= htmlspecialchars($formatarData((string) ($recurso['primeiro_acesso'] ?? '')), ENT_QUOTES, 'UTF-8') ?></td>
                  <td><?= htmlspecialchars($formatarData((string) ($recurso['ultimo_acesso'] ?? '')), ENT_QUOTES, 'UTF-8') ?></td>
                  <td>
                    <span class="text-muted small">
                      <?= htmlspecialchars((string) ($local['flag'] ?? '🏳️'), ENT_QUOTES, 'UTF-8') ?>
                      <?= htmlspecialchars($localLabel, ENT_QUOTES, 'UTF-8') ?>
                    </span>
                  </td>
                  <td>
                    <a href="<?= htmlspecialchars($urlVisualizar, ENT_QUOTES, 'UTF-8') ?>" class="btn btn-sm btn-outline-primary" title="Abrir recurso">
                      <i class="bi bi-box-arrow-up-right"></i>
                    </a>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    <?php endif; ?>
  </div>
</section>
