<?php
  $chamada = is_array($chamada ?? null) ? $chamada : [];
  $inscritosView = is_array($inscritos ?? null) ? $inscritos : [];
  $presencasView = is_array($presencas ?? null) ? $presencas : [];
  $idChamada = (int) ($chamada['id'] ?? 0);
  $aberta = (string) ($chamada['status'] ?? '') === 'ABERTA';
  $chData = (string) ($chamada['data_aula'] ?? '');
  $chDt = $chData !== '' ? date_create($chData) : false;
  $chInicio = substr((string) ($chamada['hora_inicio'] ?? ''), 0, 5);
  $chFim = substr((string) ($chamada['hora_fim'] ?? ''), 0, 5);
  $statusInfo = match ((string) ($chamada['status'] ?? '')) {
    'ABERTA' => ['bg-warning text-dark', 'Aberta'],
    'FECHADA' => ['bg-success', 'Fechada'],
    'CANCELADA' => ['bg-danger', 'Cancelada'],
    default => ['bg-secondary', (string) ($chamada['status'] ?? '-')],
  };
?>

<section class="container py-4">
  <div class="bg-white border rounded-3 p-4 shadow-sm">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
      <h4 class="mb-0"><i class="bi bi-clipboard-check me-2"></i>Lançamento de Presença #<?= str_pad((string) $idChamada, 6, '0', STR_PAD_LEFT) ?></h4>
      <div class="d-flex gap-2">
        <a class="btn btn-outline-secondary btn-sm" href="/admin/chamadas"><i class="bi bi-arrow-left me-1"></i>Voltar</a>
        <?php if ($aberta): ?>
          <form method="post" action="/admin/chamadas/lancamento/encerrar" onsubmit="return confirm('Encerrar esta chamada? Não será possível lançar novas presenças.');">
            <input type="hidden" name="id" value="<?= $idChamada ?>">
            <button class="btn btn-danger btn-sm" type="submit"><i class="bi bi-lock me-1"></i>Encerrar chamada</button>
          </form>
        <?php endif; ?>
      </div>
    </div>

    <div class="alert alert-light border mb-3">
      <i class="bi bi-people me-1"></i><strong>Turma:</strong> <?= htmlspecialchars((string) ($chamada['turma_nome'] ?? '-'), ENT_QUOTES, 'UTF-8') ?>
      &middot; <i class="bi bi-journal-bookmark me-1"></i><strong>Disciplina:</strong> <?= htmlspecialchars((string) ($chamada['disciplina_nome'] ?? '-'), ENT_QUOTES, 'UTF-8') ?>
      <?php if (trim((string) ($chamada['professor_nome'] ?? '')) !== ''): ?>
        &middot; <i class="bi bi-person-badge me-1"></i><strong>Professor:</strong> <?= htmlspecialchars((string) $chamada['professor_nome'], ENT_QUOTES, 'UTF-8') ?>
      <?php endif; ?>
      <br>
      <i class="bi bi-calendar3 me-1"></i><strong>Data:</strong> <?= htmlspecialchars($chDt ? $chDt->format('d/m/Y') : ($chData ?: '-'), ENT_QUOTES, 'UTF-8') ?>
      <?php if ($chInicio !== '' || $chFim !== ''): ?>
        &middot; <i class="bi bi-clock me-1"></i><strong>Horário:</strong> <?= htmlspecialchars(($chInicio ?: '-') . ' às ' . ($chFim ?: '-'), ENT_QUOTES, 'UTF-8') ?>
      <?php endif; ?>
      &middot; <strong>Status:</strong> <span class="badge <?= $statusInfo[0] ?>"><?= htmlspecialchars($statusInfo[1], ENT_QUOTES, 'UTF-8') ?></span>
    </div>

    <?php if (!$aberta): ?>
      <div class="alert alert-secondary"><i class="bi bi-lock me-1"></i>Esta chamada não está aberta. Somente consulta.</div>
    <?php endif; ?>

    <?php if (empty($inscritosView)): ?>
      <p class="text-muted mb-0">Nenhum aluno matriculado nesta turma.</p>
    <?php else: ?>
      <div class="mb-3">
        <div class="input-group input-group-sm" style="max-width: 340px;">
          <span class="input-group-text"><i class="bi bi-search"></i></span>
          <input type="text" class="form-control" id="filtroAluno" placeholder="Filtrar por nome do aluno..." autocomplete="off">
          <button type="button" class="btn btn-outline-secondary" id="limparFiltroAluno" title="Limpar"><i class="bi bi-x-lg"></i></button>
        </div>
      </div>
      <div class="table-responsive">
        <table class="table table-striped table-hover table-sm align-middle">
          <thead>
            <tr>
              <th style="min-width: 220px;">Aluno</th>
              <th>E-mail</th>
              <th>Registro de presença (presença · entrada · permanência · observação)</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($inscritosView as $aluno): ?>
              <?php
                $idMatricula = (int) ($aluno['id_matricula'] ?? 0);
                $pres = $presencasView[$idMatricula] ?? null;
                $presencaAtual = (string) ($pres['presenca'] ?? 'PRESENTE');
                $entradaAtual = substr((string) ($pres['entrada'] ?? ''), 0, 5);
                $permanenciaAtual = substr((string) ($pres['permanencia'] ?? ''), 0, 5);
                $observacaoAtual = (string) ($pres['observacao'] ?? '');
                $registrado = $pres !== null;
              ?>
<tr data-aluno="<?= htmlspecialchars(mb_strtolower((string) ($aluno['aluno_nome'] ?? '')), ENT_QUOTES, 'UTF-8') ?>">
                <td>
                  <?= htmlspecialchars((string) ($aluno['aluno_nome'] ?? '-'), ENT_QUOTES, 'UTF-8') ?>
                  <?php if ($registrado): ?>
                    <span class="badge bg-success ms-1"><i class="bi bi-check-lg"></i></span>
                  <?php endif; ?>
                </td>
                <td class="small text-muted"><?= htmlspecialchars((string) ($aluno['aluno_email'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                <td class="p-0">
                  <form method="post" action="/admin/chamadas/lancamento/registrar" class="d-flex flex-wrap gap-1 align-items-center p-1">
                    <input type="hidden" name="id_chamada" value="<?= $idChamada ?>">
                    <input type="hidden" name="id_matricula" value="<?= $idMatricula ?>">
                    <select class="form-select form-select-sm" name="presenca" style="max-width: 150px;" <?= $aberta ? '' : 'disabled' ?>>
                      <option value="PRESENTE" <?= $presencaAtual === 'PRESENTE' ? 'selected' : '' ?>>Presente</option>
                      <option value="AUSENTE" <?= $presencaAtual === 'AUSENTE' ? 'selected' : '' ?>>Ausente</option>
                      <option value="JUSTIFICADA" <?= $presencaAtual === 'JUSTIFICADA' ? 'selected' : '' ?>>Justificada</option>
                    </select>
                    <input type="time" class="form-control form-control-sm" name="entrada" style="max-width: 120px;" value="<?= htmlspecialchars($entradaAtual, ENT_QUOTES, 'UTF-8') ?>" <?= $aberta ? '' : 'disabled' ?>>
                    <input type="time" class="form-control form-control-sm" name="permanencia" style="max-width: 120px;" value="<?= htmlspecialchars($permanenciaAtual, ENT_QUOTES, 'UTF-8') ?>" <?= $aberta ? '' : 'disabled' ?>>
                    <input type="text" class="form-control form-control-sm flex-grow-1" name="observacao" style="min-width: 140px;" maxlength="255" placeholder="Observação" value="<?= htmlspecialchars($observacaoAtual, ENT_QUOTES, 'UTF-8') ?>" <?= $aberta ? '' : 'disabled' ?>>
                    <?php if ($aberta): ?>
                      <button class="btn btn-sm <?= $registrado ? 'btn-outline-primary' : 'btn-success' ?>" type="submit">
                        <i class="bi <?= $registrado ? 'bi-arrow-repeat' : 'bi-check-lg' ?> me-1"></i><?= $registrado ? 'Atualizar' : 'Registrar' ?>
                      </button>
                    <?php endif; ?>
                  </form>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <div class="text-muted small mt-2">Total de alunos inscritos: <strong><?= count($inscritosView) ?></strong> <span id="contadorFiltro"></span></div>
    <?php endif; ?>
  </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function () {
  var input = document.getElementById('filtroAluno');
  var limpar = document.getElementById('limparFiltroAluno');
  var contador = document.getElementById('contadorFiltro');
  if (!input) return;

  var linhas = Array.prototype.slice.call(document.querySelectorAll('tr[data-aluno]'));
  var total = linhas.length;

  function aplicar() {
    var termo = input.value.trim().toLowerCase();
    var visiveis = 0;
    linhas.forEach(function (tr) {
      var nome = tr.getAttribute('data-aluno') || '';
      var mostra = termo === '' || nome.indexOf(termo) !== -1;
      tr.style.display = mostra ? '' : 'none';
      if (mostra) visiveis++;
    });
    if (contador) {
      contador.textContent = termo === '' ? '' : '(exibindo ' + visiveis + ' de ' + total + ')';
    }
  }

  input.addEventListener('input', aplicar);
  if (limpar) {
    limpar.addEventListener('click', function () {
      input.value = '';
      aplicar();
      input.focus();
    });
  }
});
</script>