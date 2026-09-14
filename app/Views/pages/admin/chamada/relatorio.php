<?php
  $turmasView = is_array($turmas ?? null) ? $turmas : [];
  $idTurma = (int) ($idTurma ?? 0);
  $rel = is_array($relatorio ?? null) ? $relatorio : [];
  $turma = is_array($rel['turma'] ?? null) ? $rel['turma'] : null;
  $alunos = is_array($rel['alunos'] ?? null) ? $rel['alunos'] : [];
  $chamadas = is_array($rel['chamadas'] ?? null) ? $rel['chamadas'] : [];
  $presencas = is_array($rel['presencas'] ?? null) ? $rel['presencas'] : [];
?>

<section class="container py-4">
  <div class="bg-white border rounded-3 p-4 shadow-sm">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
      <h4 class="mb-0"><i class="bi bi-clipboard-data me-2"></i>Relatório de Presenças</h4>
      <a class="btn btn-outline-secondary btn-sm" href="/admin/chamadas"><i class="bi bi-arrow-left me-1"></i>Voltar</a>
    </div>

    <form method="get" action="/admin/chamadas/relatorio" class="row g-2 align-items-end mb-4">
      <div class="col-md-5 col-lg-4">
        <label class="form-label">Turma</label>
        <select class="form-select" name="id_turma" required>
          <option value="">Selecione a turma</option>
          <?php foreach ($turmasView as $turmaOpt): ?>
            <?php
              $label = (string) ($turmaOpt['turma_nome'] ?? '-');
              if (trim((string) ($turmaOpt['curso_nome'] ?? '')) !== '') {
                $label .= ' — ' . (string) $turmaOpt['curso_nome'];
              }
            ?>
            <option value="<?= (int) ($turmaOpt['id'] ?? 0) ?>" <?= $idTurma === (int) ($turmaOpt['id'] ?? 0) ? 'selected' : '' ?>>
              <?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-auto">
        <button class="btn btn-primary" type="submit"><i class="bi bi-search me-1"></i>Gerar relatório</button>
      </div>
    </form>

    <?php if ($idTurma > 0): ?>
      <?php if ($turma): ?>
        <div class="alert alert-light border mb-3">
          <i class="bi bi-book me-1"></i><strong>Curso:</strong> <?= htmlspecialchars((string) ($turma['curso_nome'] ?? '-'), ENT_QUOTES, 'UTF-8') ?>
          &middot; <i class="bi bi-people me-1"></i><strong>Turma:</strong> <?= htmlspecialchars((string) ($turma['turma_nome'] ?? '-'), ENT_QUOTES, 'UTF-8') ?>
        </div>
      <?php endif; ?>

      <?php if (empty($alunos) || empty($chamadas)): ?>
        <p class="text-muted mb-0">
          <?= empty($alunos) ? 'Nenhum aluno matriculado nesta turma.' : 'Nenhuma chamada gerada para esta turma.' ?>
        </p>
      <?php else: ?>
        <div class="table-responsive">
          <table class="table table-bordered table-striped table-hover table-sm align-middle">
            <thead class="table-dark">
              <tr>
                <th class="text-start">Aluno</th>
                <?php foreach ($chamadas as $ch): ?>
                  <?php
                    $chData = (string) ($ch['data_aula'] ?? '');
                    $chDt = $chData !== '' ? date_create($chData) : false;
                  ?>
                  <th class="text-center" title="<?= htmlspecialchars((string) ($ch['disciplina_nome'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                    <?= htmlspecialchars($chDt ? $chDt->format('d/m') : ($chData ?: '-'), ENT_QUOTES, 'UTF-8') ?>
                  </th>
                <?php endforeach; ?>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($alunos as $aluno): ?>
                <?php $idMatricula = (int) ($aluno['id_matricula'] ?? 0); ?>
                <tr>
                  <td class="text-start"><?= htmlspecialchars((string) ($aluno['aluno_nome'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                  <?php foreach ($chamadas as $ch): ?>
                    <?php
                      $idChamada = (int) ($ch['id'] ?? 0);
                      $presenca = $presencas[$idChamada . ':' . $idMatricula] ?? '';
                    ?>
                    <td class="text-center">
                      <?php if ($presenca === 'PRESENTE'): ?>
                        <span class="badge bg-success">P</span>
                      <?php elseif ($presenca === 'AUSENTE'): ?>
                        <span class="badge bg-danger">F</span>
                      <?php elseif ($presenca === 'JUSTIFICADA'): ?>
                        <span class="badge bg-warning text-dark">J</span>
                      <?php else: ?>
                        <span class="badge bg-secondary">F</span>
                      <?php endif; ?>
                    </td>
                  <?php endforeach; ?>
                </tr>
              <?php endforeach; ?>
              <tr class="table-light">
                <td class="text-start fw-semibold">Total de alunos da turma</td>
                <?php foreach ($chamadas as $ch): ?>
                  <td class="text-center fw-semibold"><?= count($alunos) ?></td>
                <?php endforeach; ?>
              </tr>
            </tbody>
          </table>
        </div>

        <?php
          $aulasLista = $chamadas;
          usort($aulasLista, static function (array $a, array $b): int {
            return strcmp((string) ($a['data_aula'] ?? ''), (string) ($b['data_aula'] ?? ''))
              ?: ((int) ($a['id'] ?? 0) <=> (int) ($b['id'] ?? 0));
          });
        ?>
        <h6 class="mt-4 mb-2"><i class="bi bi-journal-text me-1"></i>Dias de aula e disciplinas ministradas</h6>
        <div class="table-responsive">
          <table class="table table-sm table-striped table-hover align-middle mb-0">
            <thead>
              <tr>
                <th style="width: 130px;">Dia</th>
                <th>Disciplina</th>
                <th>Horário</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($aulasLista as $aula): ?>
                <?php
                  $aData = (string) ($aula['data_aula'] ?? '');
                  $aDt = $aData !== '' ? date_create($aData) : false;
                  $aInicio = substr((string) ($aula['hora_inicio'] ?? ''), 0, 5);
                  $aFim = substr((string) ($aula['hora_fim'] ?? ''), 0, 5);
                ?>
                <tr>
                  <td><?= htmlspecialchars($aDt ? $aDt->format('d/m/Y') : ($aData ?: '-'), ENT_QUOTES, 'UTF-8') ?></td>
                  <td><?= htmlspecialchars((string) ($aula['disciplina_nome'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                  <td><?= htmlspecialchars(($aInicio !== '' || $aFim !== '') ? ($aInicio !== '' ? $aInicio : '-') . ' às ' . ($aFim !== '' ? $aFim : '-') : '-', ENT_QUOTES, 'UTF-8') ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
            <tfoot>
              <tr class="table-light">
                <td colspan="3" class="fw-semibold">Total de aulas: <?= count($aulasLista) ?></td>
              </tr>
            </tfoot>
          </table>
        </div>

        <div class="d-flex justify-content-end mt-3">
          <a class="btn btn-success" href="/admin/chamadas/relatorio/excel?id_turma=<?= $idTurma ?>">
            <i class="bi bi-file-earmark-excel me-1"></i>Exportar para Excel
          </a>
        </div>
      <?php endif; ?>
    <?php endif; ?>
  </div>
</section>