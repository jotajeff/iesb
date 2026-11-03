<?php
  $rankingView = is_array($ranking ?? null) ? $ranking : [];
  $totalAcessos = (int) ($totalAcessos ?? 0);
  $maiorAcessos = 0;
  foreach ($rankingView as $alunoRanking) {
    $maiorAcessos = max($maiorAcessos, (int) ($alunoRanking['total_acessos'] ?? 0));
  }

  $medalhas = [
    1 => ['cor' => '#c9a227', 'rotulo' => '1º'],
    2 => ['cor' => '#8c959d', 'rotulo' => '2º'],
    3 => ['cor' => '#b87333', 'rotulo' => '3º'],
  ];
?>

<section class="container py-4">
  <div class="bg-white border rounded-3 p-4 shadow-sm">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
      <div>
        <h4 class="mb-0"><i class="bi bi-bar-chart-line me-2"></i>Ranking de acessos</h4>
        <p class="text-muted small mb-0">Alunos ativos ordenados do maior para o menor número de acessos aos recursos de aula.</p>
      </div>
      <div class="d-flex flex-wrap gap-2">
        <a class="btn btn-outline-primary btn-sm" href="/admin/logs/recursos/aluno">
          <i class="bi bi-person-lines-fill me-1"></i>Consulta por aluno
        </a>
        <a class="btn btn-outline-secondary btn-sm" href="/admin/logs/recursos">
          <i class="bi bi-arrow-left me-1"></i>Recursos de aula
        </a>
      </div>
    </div>

    <div class="row g-3 mb-4">
      <div class="col-6 col-md-4">
        <div class="border rounded-3 p-3 h-100">
          <div class="d-flex align-items-center gap-2 text-muted small mb-1">
            <i class="bi bi-people"></i>Alunos ativos
          </div>
          <div class="fs-4 fw-bold"><?= count($rankingView) ?></div>
        </div>
      </div>
      <div class="col-6 col-md-4">
        <div class="border rounded-3 p-3 h-100">
          <div class="d-flex align-items-center gap-2 text-muted small mb-1">
            <i class="bi bi-eye"></i>Acessos no total
          </div>
          <div class="fs-4 fw-bold"><?= $totalAcessos ?></div>
        </div>
      </div>
    </div>

    <?php if ($rankingView === []): ?>
      <div class="alert alert-light border text-muted mb-0">
        <i class="bi bi-inbox me-1"></i>Nenhum aluno ativo encontrado.
      </div>
    <?php else: ?>
      <div class="border rounded-3 overflow-hidden">
        <?php foreach ($rankingView as $indice => $alunoRanking): ?>
          <?php
            $posicao = $indice + 1;
            $acessos = (int) ($alunoRanking['total_acessos'] ?? 0);
            $recursos = (int) ($alunoRanking['total_recursos'] ?? 0);
            $largura = $maiorAcessos > 0 ? round(($acessos / $maiorAcessos) * 100, 1) : 0;
            $medalha = $medalhas[$posicao] ?? null;
            $corBarra = $medalha['cor'] ?? '#1f2937';
          ?>
          <div class="px-3 py-3 <?= $posicao < count($rankingView) ? 'border-bottom' : '' ?>">
            <div class="d-flex align-items-center gap-3">
              <span class="d-inline-flex align-items-center justify-content-center rounded-circle flex-shrink-0 fw-bold small" style="width:38px;height:38px;background:<?= $medalha['cor'] ?? 'rgba(77,79,78,.08)' ?>;color:<?= $medalha !== null ? '#fff' : 'inherit' ?>;">
                <?= htmlspecialchars($medalha['rotulo'] ?? ($posicao . 'º'), ENT_QUOTES, 'UTF-8') ?>
              </span>
              <div class="flex-grow-1 min-w-0">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-1">
                  <div class="text-truncate">
                    <span class="fw-semibold"><?= htmlspecialchars((string) ($alunoRanking['nome'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></span>
                    <span class="text-muted small ms-1"><?= htmlspecialchars((string) ($alunoRanking['email'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span>
                  </div>
                  <div class="d-flex flex-wrap gap-1">
                    <span class="badge bg-light text-dark border"><i class="bi bi-collection-play me-1"></i><?= $recursos ?> recurso(s)</span>
                    <span class="badge bg-dark"><i class="bi bi-eye me-1"></i><?= $acessos ?> acesso(s)</span>
                  </div>
                </div>
                <div class="progress" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="<?= $largura ?>" style="height:10px;background:rgba(77,79,78,.08);">
                  <div class="progress-bar" style="width:<?= $largura ?>%;background:<?= $corBarra ?>;"></div>
                </div>
              </div>
              <div class="text-end flex-shrink-0" style="width:58px;">
                <span class="fw-semibold"><?= number_format($largura, 1, ',', '.') ?>%</span>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</section>
