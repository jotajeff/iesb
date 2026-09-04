<?php
  $chamadaAberta = is_array($chamadaAberta ?? null) ? $chamadaAberta : null;
  $historico = is_array($historico ?? null) ? $historico : [];
?>

<section class="py-4" style="margin-top: 20px;">
  <div class="container">
    <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 16px; padding: 2rem; box-shadow: var(--card-shadow);">
      <h4 class="mb-4"><i class="bi bi-clipboard-check me-2"></i>Chamadas</h4>

      <?php if (!empty($chamadaAberta) && trim((string) ($chamadaAberta['presenca_atual'] ?? '')) === ''): ?>
        <?php
          $presencaAtual = (string) ($chamadaAberta['presenca_atual'] ?? '');
          $chData = (string) ($chamadaAberta['data_aula'] ?? '');
          $chDataFmt = $chData !== '' ? date_create($chData) : false;
          $chInicio = substr((string) ($chamadaAberta['hora_inicio'] ?? ''), 0, 5);
          $chFim = substr((string) ($chamadaAberta['hora_fim'] ?? ''), 0, 5);
          $chInicioFull = (string) ($chamadaAberta['hora_inicio'] ?? '');
          $chFimFull = (string) ($chamadaAberta['hora_fim'] ?? '');
          $dentroHorario = false;
          if ($chData !== '' && $chInicioFull !== '' && $chFimFull !== '') {
            $inicioTs = strtotime($chData . ' ' . $chInicioFull);
            $fimTs = strtotime($chData . ' ' . $chFimFull);
            $agoraTs = time();
            $dentroHorario = $inicioTs !== false && $fimTs !== false && $agoraTs >= $inicioTs && $agoraTs <= $fimTs;
          }
        ?>
        <div class="alert alert-primary" role="alert">
          <div class="d-flex align-items-start gap-3">
            <i class="bi bi-bell-fill fs-4 mt-1" aria-hidden="true"></i>
            <div class="flex-grow-1">
              <div class="fw-semibold">Chamada em andamento</div>
              <div class="small mt-1">
                <strong>Disciplina:</strong> <?= htmlspecialchars((string) ($chamadaAberta['disciplina_nome'] ?? '-'), ENT_QUOTES, 'UTF-8') ?>
                &middot; <strong>Turma:</strong> <?= htmlspecialchars((string) ($chamadaAberta['turma_nome'] ?? '-'), ENT_QUOTES, 'UTF-8') ?>
                &middot; <strong>Data:</strong> <?= htmlspecialchars($chDataFmt ? $chDataFmt->format('d/m/Y') : ($chData ?: '-'), ENT_QUOTES, 'UTF-8') ?>
              </div>
              <?php if (!$dentroHorario): ?>
                <div class="alert alert-warning py-2 px-3 mb-2 mt-2 small">
                  <i class="bi bi-exclamation-triangle me-1"></i>Registro de presença permitido apenas entre <strong><?= htmlspecialchars($chInicio, ENT_QUOTES, 'UTF-8') ?></strong> e <strong><?= htmlspecialchars($chFim, ENT_QUOTES, 'UTF-8') ?></strong>.
                </div>
              <?php endif; ?>
              <div class="d-flex flex-wrap gap-2 mt-3">
                <form method="post" action="/aluno/chamada/presenca" class="d-inline">
                  <input type="hidden" name="id_chamada" value="<?= (int) ($chamadaAberta['id'] ?? 0) ?>">
                  <button type="submit" name="presenca" value="PRESENTE" class="btn btn-sm btn-success" <?= $dentroHorario ? '' : 'disabled' ?>><i class="bi bi-check-lg me-1"></i>Presente</button>
                </form>
                <form method="post" action="/aluno/chamada/presenca" class="d-inline">
                  <input type="hidden" name="id_chamada" value="<?= (int) ($chamadaAberta['id'] ?? 0) ?>">
                  <button type="submit" name="presenca" value="AUSENTE" class="btn btn-sm btn-outline-danger" <?= $dentroHorario ? '' : 'disabled' ?>><i class="bi bi-x-lg me-1"></i>Ausente</button>
                </form>
                <form method="post" action="/aluno/chamada/presenca" class="d-inline">
                  <input type="hidden" name="id_chamada" value="<?= (int) ($chamadaAberta['id'] ?? 0) ?>">
                  <button type="submit" name="presenca" value="JUSTIFICADA" class="btn btn-sm btn-outline-warning" <?= $dentroHorario ? '' : 'disabled' ?>><i class="bi bi-shield-check me-1"></i>Justificada</button>
                </form>
              </div>
            </div>
          </div>
        </div>
      <?php endif; ?>

      <h5 class="mb-3"><i class="bi bi-list-ul me-2"></i>Histórico de presenças</h5>
      <?php if (empty($historico)): ?>
        <p class="text-muted mb-0">Nenhuma chamada registrada para você.</p>
      <?php else: ?>
        <div class="table-responsive">
          <table class="table table-striped table-sm align-middle mb-0">
            <thead>
              <tr>
                <th>Disciplina</th>
                <th>Turma</th>
                <th>Data</th>
                <th>Presença</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($historico as $h): ?>
                <?php
                  $hData = (string) ($h['data_aula'] ?? '');
                  $hDt = $hData !== '' ? date_create($hData) : false;
                  $hPresenca = (string) ($h['presenca'] ?? '');
                ?>
                <tr>
                  <td><?= htmlspecialchars((string) ($h['disciplina_nome'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                  <td><?= htmlspecialchars((string) ($h['turma_nome'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                  <td><?= htmlspecialchars($hDt ? $hDt->format('d/m/Y') : ($hData ?: '-'), ENT_QUOTES, 'UTF-8') ?></td>
                  <td>
                    <span class="badge <?= $hPresenca === 'PRESENTE' ? 'bg-success' : ($hPresenca === 'JUSTIFICADA' ? 'bg-warning text-dark' : 'bg-danger') ?>">
                      <?= htmlspecialchars(ucfirst(strtolower($hPresenca ?: '-')), ENT_QUOTES, 'UTF-8') ?>
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
</section>