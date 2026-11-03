<?php
  $tipoAtual = (string) ($tipo ?? 'video');
  $recursos = is_array($materiais ?? null) ? $materiais : [];
  $totalAcessos = (int) ($totalAcessos ?? 0);
  $totalAlunos = (int) ($totalAlunos ?? 0);

  $tiposRecurso = [
    'todos' => ['rotulo' => 'Todos os recursos', 'icone' => 'bi-collection-play', 'cor' => 'bg-dark'],
    'video' => ['rotulo' => 'Vídeos', 'icone' => 'bi-camera-reels', 'cor' => 'bg-danger'],
    'drive' => ['rotulo' => 'PDFs', 'icone' => 'bi-file-earmark-pdf', 'cor' => 'bg-primary'],
    'pdf' => ['rotulo' => 'PDF', 'icone' => 'bi-file-earmark-pdf', 'cor' => 'bg-primary'],
    'excel' => ['rotulo' => 'Excel', 'icone' => 'bi-file-earmark-excel', 'cor' => 'bg-success'],
    'doc' => ['rotulo' => 'DOC', 'icone' => 'bi-file-earmark-word', 'cor' => 'bg-info text-dark'],
  ];

  $filtros = [
    'video' => 'Vídeos',
    'drive' => 'PDFs',
    'excel' => 'Excel',
    'doc' => 'DOC',
    'todos' => 'Todos os recursos',
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
        <h4 class="mb-0"><i class="bi bi-collection-play me-2"></i>Recursos de aula</h4>
        <p class="text-muted small mb-0">Lista de recursos publicados e dos alunos que os visualizaram.</p>
      </div>
      <div class="d-flex flex-wrap gap-2">
        <a class="btn btn-outline-primary btn-sm" href="/admin/logs/recursos/aluno">
          <i class="bi bi-person-lines-fill me-1"></i>Consulta por aluno
        </a>
        <a class="btn btn-outline-dark btn-sm" href="/admin/logs/recursos/ranking">
          <i class="bi bi-bar-chart-line me-1"></i>Ranking
        </a>
        <a class="btn btn-outline-secondary btn-sm" href="/admin/logs">
          <i class="bi bi-clock-history me-1"></i>Logs
        </a>
      </div>
    </div>

    <form method="get" action="/admin/logs/recursos" class="row g-2 align-items-end mb-4">
      <div class="col-sm-6 col-md-4">
        <label class="form-label" for="tipoRecurso">Tipo de recurso</label>
        <select class="form-select" name="tipo" id="tipoRecurso" onchange="this.form.submit()">
          <?php foreach ($filtros as $valor => $rotulo): ?>
            <option value="<?= htmlspecialchars($valor, ENT_QUOTES, 'UTF-8') ?>" <?= $tipoAtual === $valor ? 'selected' : '' ?>>
              <?= htmlspecialchars($rotulo, ENT_QUOTES, 'UTF-8') ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-auto">
        <button class="btn btn-primary btn-sm" type="submit"><i class="bi bi-funnel me-1"></i>Filtrar</button>
      </div>
    </form>

    <div class="row g-3 mb-4">
      <div class="col-12 col-md-4">
        <div class="border rounded-3 p-3 h-100">
          <div class="d-flex align-items-center gap-2 text-muted small mb-1">
            <i class="bi bi-collection-play"></i>Recursos listados
          </div>
          <div class="fs-4 fw-bold"><?= count($recursos) ?></div>
        </div>
      </div>
      <div class="col-6 col-md-4">
        <div class="border rounded-3 p-3 h-100">
          <div class="d-flex align-items-center gap-2 text-muted small mb-1">
            <i class="bi bi-people"></i>Alunos com acesso
          </div>
          <div class="fs-4 fw-bold"><?= $totalAlunos ?></div>
        </div>
      </div>
      <div class="col-6 col-md-4">
        <div class="border rounded-3 p-3 h-100">
          <div class="d-flex align-items-center gap-2 text-muted small mb-1">
            <i class="bi bi-eye"></i>Visualizações
          </div>
          <div class="fs-4 fw-bold"><?= $totalAcessos ?></div>
        </div>
      </div>
    </div>

    <?php if ($recursos === []): ?>
      <div class="alert alert-light border text-muted mb-0">
        <i class="bi bi-inbox me-1"></i>Nenhum recurso encontrado para o filtro selecionado.
      </div>
    <?php else: ?>
      <div class="accordion" id="recursosAulaAccordion">
        <?php foreach ($recursos as $recurso): ?>
          <?php
            $idRecurso = (int) ($recurso['id'] ?? 0);
            $tipoRecurso = (string) ($recurso['tipo'] ?? '');
            $infoTipo = $tiposRecurso[$tipoRecurso] ?? ['rotulo' => ucfirst($tipoRecurso ?: 'Arquivo'), 'icone' => 'bi-file-earmark', 'cor' => 'bg-secondary'];
            $visualizacoes = is_array($recurso['visualizacoes'] ?? null) ? $recurso['visualizacoes'] : [];
            $acessosRecurso = 0;
            foreach ($visualizacoes as $visualizacao) {
              $acessosRecurso += (int) ($visualizacao['total_visualizacoes'] ?? 0);
            }
            $turmaNome = trim((string) ($recurso['turma_nome'] ?? ''));
            $disciplinaNome = trim((string) ($recurso['disciplina_nome'] ?? ''));
            $urlVisualizar = $tipoRecurso === 'video'
              ? '/admin/turmas/ver-video?id=' . $idRecurso
              : '/admin/turmas/ver-drive?id=' . $idRecurso;
          ?>
          <div class="accordion-item border rounded-3 mb-3 overflow-hidden">
            <h2 class="accordion-header" id="recursoHeading<?= $idRecurso ?>">
              <button class="accordion-button collapsed px-3 py-3" type="button" data-bs-toggle="collapse" data-bs-target="#recursoBody<?= $idRecurso ?>" aria-expanded="false" aria-controls="recursoBody<?= $idRecurso ?>">
                <span class="d-flex align-items-center gap-3 w-100 pe-2">
                  <span class="d-inline-flex align-items-center justify-content-center rounded-circle flex-shrink-0" style="width:44px;height:44px;background:rgba(77,79,78,.08);">
                    <i class="bi <?= htmlspecialchars($infoTipo['icone'], ENT_QUOTES, 'UTF-8') ?> fs-5"></i>
                  </span>
                  <span class="flex-grow-1 text-start">
                    <span class="fw-semibold d-block"><?= htmlspecialchars((string) ($recurso['titulo'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></span>
                    <span class="text-muted small">
                      <?php if ($turmaNome !== ''): ?>
                        <i class="bi bi-people me-1"></i><?= htmlspecialchars($turmaNome, ENT_QUOTES, 'UTF-8') ?>
                      <?php endif; ?>
                      <?php if ($disciplinaNome !== ''): ?>
                        <span class="mx-1">·</span><i class="bi bi-journal-bookmark me-1"></i><?= htmlspecialchars($disciplinaNome, ENT_QUOTES, 'UTF-8') ?>
                      <?php endif; ?>
                      <span class="mx-1">·</span><i class="bi bi-calendar3 me-1"></i><?= htmlspecialchars($formatarData((string) ($recurso['created_at'] ?? '')), ENT_QUOTES, 'UTF-8') ?>
                    </span>
                  </span>
                  <span class="d-flex flex-wrap gap-1 justify-content-end flex-shrink-0">
                    <span class="badge bg-light text-dark border"><i class="bi bi-people me-1"></i><?= count($visualizacoes) ?> aluno(s)</span>
                    <span class="badge bg-dark"><i class="bi bi-eye me-1"></i><?= $acessosRecurso ?> acesso(s)</span>
                  </span>
                </span>
              </button>
            </h2>
            <div id="recursoBody<?= $idRecurso ?>" class="accordion-collapse collapse" aria-labelledby="recursoHeading<?= $idRecurso ?>" data-bs-parent="#recursosAulaAccordion">
              <div class="accordion-body">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                  <span class="badge <?= htmlspecialchars($infoTipo['cor'], ENT_QUOTES, 'UTF-8') ?>">
                    <i class="bi <?= htmlspecialchars($infoTipo['icone'], ENT_QUOTES, 'UTF-8') ?> me-1"></i><?= htmlspecialchars($infoTipo['rotulo'], ENT_QUOTES, 'UTF-8') ?>
                  </span>
                  <a href="<?= htmlspecialchars($urlVisualizar, ENT_QUOTES, 'UTF-8') ?>" class="btn btn-sm btn-outline-primary">
                    <i class="bi bi-box-arrow-up-right me-1"></i>Abrir recurso
                  </a>
                </div>

                <?php if ($visualizacoes === []): ?>
                  <div class="alert alert-light border text-muted mb-0">
                    <i class="bi bi-eye-slash me-1"></i>Nenhum aluno visualizou este recurso.
                  </div>
                <?php else: ?>
                  <div class="table-responsive">
                    <table class="table table-sm table-striped align-middle mb-0">
                      <thead>
                        <tr>
                          <th>Aluno</th>
                          <th>E-mail</th>
                          <th class="text-center">Acessos</th>
                          <th>Primeira visualização</th>
                          <th>Última visualização</th>
                          <th>Localização</th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php foreach ($visualizacoes as $visualizacao): ?>
                          <tr>
                            <td class="fw-semibold"><?= htmlspecialchars((string) ($visualizacao['nome'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars((string) ($visualizacao['email'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                            <td class="text-center"><?= (int) ($visualizacao['total_visualizacoes'] ?? 0) ?></td>
                            <td><?= htmlspecialchars($formatarData((string) ($visualizacao['primeira_visualizacao'] ?? '')), ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($formatarData((string) ($visualizacao['ultima_visualizacao'] ?? '')), ENT_QUOTES, 'UTF-8') ?></td>
                            <?php
                              $local = is_array($visualizacao['localizacao'] ?? null) ? $visualizacao['localizacao'] : [];
                              $cidade = trim((string) ($local['city'] ?? ''));
                              $pais = trim((string) ($local['country'] ?? ''));
                              $localLabel = $pais !== '' && $pais !== '-' ? $pais : '-';
                              if ($cidade !== '' && $cidade !== '-' && $localLabel !== '-') {
                                $localLabel = $cidade . '/' . $localLabel;
                              } elseif ($cidade !== '' && $cidade !== '-') {
                                $localLabel = $cidade;
                              }
                            ?>
                            <td>
                              <span class="text-muted small">
                                <?= htmlspecialchars((string) ($local['flag'] ?? '🏳️'), ENT_QUOTES, 'UTF-8') ?>
                                <?= htmlspecialchars($localLabel, ENT_QUOTES, 'UTF-8') ?>
                              </span>
                            </td>
                          </tr>
                        <?php endforeach; ?>
                      </tbody>
                    </table>
                  </div>
                <?php endif; ?>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</section>
