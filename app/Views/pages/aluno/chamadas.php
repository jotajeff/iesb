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
                <form method="post" action="/aluno/chamada/presenca" class="d-inline form-presenca">
                  <input type="hidden" name="id_chamada" value="<?= (int) ($chamadaAberta['id'] ?? 0) ?>">
                  <input type="hidden" name="presenca" value="PRESENTE">
                  <button type="submit" value="PRESENTE" class="btn btn-sm btn-success" <?= $dentroHorario ? '' : 'disabled' ?>><i class="bi bi-check-lg me-1"></i>Presente</button>
                </form>
                <form method="post" action="/aluno/chamada/presenca" class="d-inline form-presenca">
                  <input type="hidden" name="id_chamada" value="<?= (int) ($chamadaAberta['id'] ?? 0) ?>">
                  <input type="hidden" name="presenca" value="AUSENTE">
                  <button type="submit" value="AUSENTE" class="btn btn-sm btn-outline-danger" <?= $dentroHorario ? '' : 'disabled' ?>><i class="bi bi-x-lg me-1"></i>Ausente</button>
                </form>
                <form method="post" action="/aluno/chamada/presenca" class="d-inline form-presenca">
                  <input type="hidden" name="id_chamada" value="<?= (int) ($chamadaAberta['id'] ?? 0) ?>">
                  <input type="hidden" name="presenca" value="JUSTIFICADA">
                  <button type="submit" value="JUSTIFICADA" class="btn btn-sm btn-outline-warning" <?= $dentroHorario ? '' : 'disabled' ?>><i class="bi bi-shield-check me-1"></i>Justificada</button>
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
        <div class="list-group">
          <?php foreach ($historico as $h): ?>
            <?php
              $hData = (string) ($h['data_aula'] ?? '');
              $hDt = $hData !== '' ? date_create($hData) : false;
              $hPresenca = (string) ($h['presenca'] ?? '');
              $hStatus = (string) ($h['chamada_status'] ?? '');
              $hInicio = substr((string) ($h['hora_inicio'] ?? ''), 0, 5);
              $hFim = substr((string) ($h['hora_fim'] ?? ''), 0, 5);
            ?>
            <div class="list-group-item py-3">
              <div class="d-flex flex-wrap align-items-center gap-2">
                <i class="bi bi-check-circle-fill text-success" aria-hidden="true"></i>
                <strong><?= htmlspecialchars((string) ($h['disciplina_nome'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></strong>
                <span class="badge <?= $hPresenca === 'PRESENTE' ? 'bg-success' : ($hPresenca === 'JUSTIFICADA' ? 'bg-warning text-dark' : 'bg-danger') ?> ms-auto">
                  <?= htmlspecialchars(ucfirst(strtolower($hPresenca ?: '-')), ENT_QUOTES, 'UTF-8') ?>
                </span>
              </div>
              <div class="ps-4 mt-2 text-muted small">
                <div>
                  <i class="bi bi-book me-1"></i><?= htmlspecialchars((string) ($h['curso_nome'] ?? '-'), ENT_QUOTES, 'UTF-8') ?>
                  &middot; <i class="bi bi-people me-1"></i><?= htmlspecialchars((string) ($h['turma_nome'] ?? '-'), ENT_QUOTES, 'UTF-8') ?>
                </div>
                <?php if (trim((string) ($h['professor_nome'] ?? '')) !== ''): ?>
                  <div><i class="bi bi-person-badge me-1"></i>Professor: <?= htmlspecialchars((string) $h['professor_nome'], ENT_QUOTES, 'UTF-8') ?></div>
                <?php endif; ?>
                <div>
                  <i class="bi bi-calendar3 me-1"></i><?= htmlspecialchars($hDt ? $hDt->format('d/m/Y') : ($hData ?: '-'), ENT_QUOTES, 'UTF-8') ?>
                  <?php if ($hInicio !== '' || $hFim !== ''): ?>
                    &middot; <i class="bi bi-clock me-1"></i><?= htmlspecialchars(($hInicio !== '' ? $hInicio : '-') . ' às ' . ($hFim !== '' ? $hFim : '-'), ENT_QUOTES, 'UTF-8') ?>
                  <?php endif; ?>
                  &middot; <i class="bi bi-tag me-1"></i>Chamada: <?= htmlspecialchars(ucfirst(strtolower($hStatus ?: '-')), ENT_QUOTES, 'UTF-8') ?>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </div>
</section>

<div class="modal fade" id="modalConfirmarPresenca" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-primary-subtle">
        <h5 class="modal-title"><i class="bi bi-clipboard-check me-2"></i>Confirmar presença</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
      </div>
      <div class="modal-body">
        <p class="text-muted small mb-3">Revise os dados antes de confirmar sua presença:</p>
        <dl class="row mb-0">
          <dt class="col-sm-5 text-muted">Aluno</dt>
          <dd class="col-sm-7" id="cpAluno">-</dd>
          <dt class="col-sm-5 text-muted">Curso</dt>
          <dd class="col-sm-7" id="cpCurso">-</dd>
          <dt class="col-sm-5 text-muted">Turma</dt>
          <dd class="col-sm-7" id="cpTurma">-</dd>
          <dt class="col-sm-5 text-muted">Disciplina</dt>
          <dd class="col-sm-7" id="cpDisciplina">-</dd>
          <dt class="col-sm-5 text-muted">Data / Horário</dt>
          <dd class="col-sm-7" id="cpData">-</dd>
          <dt class="col-sm-5 text-muted">Professor</dt>
          <dd class="col-sm-7" id="cpProfessor">-</dd>
          <dt class="col-sm-5 text-muted">Presença</dt>
          <dd class="col-sm-7" id="cpPresenca">-</dd>
        </dl>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal"><i class="bi bi-x me-1"></i>Cancelar</button>
        <button type="button" class="btn btn-success" id="btnConfirmarPresenca"><i class="bi bi-check-lg me-1"></i>Confirmar</button>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
  <?php
    $chDados = (string) ($chamadaAberta['data_aula'] ?? '');
    $chDtDados = $chDados !== '' ? date_create($chDados) : false;
    $chHIni = substr((string) ($chamadaAberta['hora_inicio'] ?? ''), 0, 5);
    $chHFim = substr((string) ($chamadaAberta['hora_fim'] ?? ''), 0, 5);
    $chDataLabel = ($chDtDados ? $chDtDados->format('d/m/Y') : $chDados)
      . ($chHIni !== '' || $chHFim !== '' ? ' ' . $chHIni . ' às ' . $chHFim : '');
  ?>
  var chamadaDados = {
    aluno: <?= json_encode((string) ($alunoNome ?? ''), JSON_UNESCAPED_UNICODE) ?>,
    curso: <?= json_encode((string) ($chamadaAberta['curso_nome'] ?? ''), JSON_UNESCAPED_UNICODE) ?>,
    turma: <?= json_encode((string) ($chamadaAberta['turma_nome'] ?? ''), JSON_UNESCAPED_UNICODE) ?>,
    disciplina: <?= json_encode((string) ($chamadaAberta['disciplina_nome'] ?? ''), JSON_UNESCAPED_UNICODE) ?>,
    data: <?= json_encode($chDataLabel, JSON_UNESCAPED_UNICODE) ?>,
    professor: <?= json_encode((string) ($chamadaAberta['professor_nome'] ?? ''), JSON_UNESCAPED_UNICODE) ?>
  };
  var formConfirmar = null;
  var modalEl = document.getElementById('modalConfirmarPresenca');
  if (!modalEl) return;

  function textoPresenca(v) {
    return v.charAt(0) + v.slice(1).toLowerCase();
  }

  document.querySelectorAll('.form-presenca').forEach(function (form) {
    form.addEventListener('submit', function (event) {
      event.preventDefault();
      var presencaInput = form.querySelector('input[name="presenca"]');
      var presenca = presencaInput ? presencaInput.value : '';
      document.getElementById('cpAluno').textContent = chamadaDados.aluno || '-';
      document.getElementById('cpCurso').textContent = chamadaDados.curso || '-';
      document.getElementById('cpTurma').textContent = chamadaDados.turma || '-';
      document.getElementById('cpDisciplina').textContent = chamadaDados.disciplina || '-';
      document.getElementById('cpData').textContent = chamadaDados.data || '-';
      document.getElementById('cpProfessor').textContent = chamadaDados.professor || '-';
      document.getElementById('cpPresenca').textContent = textoPresenca(presenca);
      formConfirmar = form;
      bootstrap.Modal.getOrCreateInstance(modalEl).show();
    });
  });

  document.getElementById('btnConfirmarPresenca').addEventListener('click', function () {
    var modal = bootstrap.Modal.getInstance(modalEl);
    if (modal) modal.hide();
    if (formConfirmar) formConfirmar.submit();
  });
});
</script>