<?php
  $calendario = is_array($calendario ?? null) ? $calendario : [];
  $mesesNomes = [1 => 'Janeiro', 2 => 'Fevereiro', 3 => 'Março', 4 => 'Abril', 5 => 'Maio', 6 => 'Junho', 7 => 'Julho', 8 => 'Agosto', 9 => 'Setembro', 10 => 'Outubro', 11 => 'Novembro', 12 => 'Dezembro'];
  $diasSemana = ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb'];
?>

<section class="py-4" style="margin-top: 20px;">
  <div class="container">
    <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 16px; padding: 2rem; box-shadow: var(--card-shadow);">
      <h4 class="mb-1"><i class="bi bi-calendar3 me-2"></i>Calendário de Aulas</h4>
      <p class="text-muted small mb-4">Meses com aulas agendadas. Dias com aula marcados em branco; a cor indica a sua situação na chamada.</p>

      <div class="d-flex flex-wrap gap-2 mb-4">
        <span class="badge bg-dark text-white" style="font-size: .8rem;">Dia de aula</span>
        <span class="badge bg-success">Presente</span>
        <span class="badge bg-danger">Ausente / Falta</span>
        <span class="badge bg-warning text-dark">Justificada</span>
        <span class="badge bg-secondary">Chamada cancelada</span>
      </div>

      <?php if (empty($calendario)): ?>
        <p class="text-muted mb-0">Nenhuma aula agendada no momento.</p>
      <?php else: ?>
        <?php foreach ($calendario as $ano => $meses): ?>
          <h5 class="mb-3"><i class="bi bi-calendar-week me-1"></i>Ano <?= (int) $ano ?></h5>
          <div class="row g-3 mb-4">
            <?php foreach ($meses as $mes => $dias): ?>
              <div class="col-md-6 col-lg-4">
                <div class="border rounded-3 p-3 h-100">
                  <h6 class="text-center mb-3"><?= htmlspecialchars((string) ($mesesNomes[$mes] ?? $mes), ENT_QUOTES, 'UTF-8') ?></h6>
                  <div class="row g-1 text-center mb-1">
                    <?php foreach ($diasSemana as $ds): ?>
                      <div class="col small text-muted fw-semibold"><?= $ds ?></div>
                    <?php endforeach; ?>
                  </div>
                  <?php
                    $primeiro = (int) date('w', mktime(0, 0, 0, $mes, 1, $ano));
                    $totalDias = (int) date('t', mktime(0, 0, 0, $mes, 1, $ano));
                    $celulas = $primeiro;
                  ?>
                  <div class="row g-1 text-center">
                    <?php for ($i = 0; $i < $primeiro; $i++): ?>
                      <div class="col"></div>
                    <?php endfor; ?>
                    <?php for ($dia = 1; $dia <= $totalDias; $dia++): ?>
                      <?php $dataDia = sprintf('%04d-%02d-%02d', $ano, $mes, $dia); ?>
                      <div class="col">
                        <?php if (!empty($dias[$dia])): ?>
                          <span class="badge bg-dark text-white" style="font-size: .8rem;"><?= $dia ?></span>
                          <?php foreach ($dias[$dia] as $ch): ?>
                            <?php
                              $presenca = (string) ($ch['presenca'] ?? '');
                              $chStatus = (string) ($ch['status'] ?? '');
                              $chIni = (string) ($ch['hora_inicio'] ?? '');
                              $chFim = (string) ($ch['hora_fim'] ?? '');
                              $horaRef = $chFim !== '' ? $chFim : ($chIni !== '' ? $chIni : '23:59:59');
                              $tsCh = strtotime($dataDia . ' ' . $horaRef);
                              $aulaPassou = $tsCh !== false && $tsCh < time();
                              $temRegistro = in_array($presenca, ['PRESENTE', 'AUSENTE', 'JUSTIFICADA'], true);

                              $classe = '';
                              $rotulo = '';
                              $titulo = '';
                              if ($temRegistro) {
                                if ($presenca === 'PRESENTE') {
                                  $classe = 'bg-success';
                                  $rotulo = 'P';
                                  $titulo = 'Presente';
                                } elseif ($presenca === 'JUSTIFICADA') {
                                  $classe = 'bg-warning text-dark';
                                  $rotulo = 'J';
                                  $titulo = 'Justificada';
                                } else {
                                  $classe = 'bg-danger';
                                  $rotulo = 'F';
                                  $titulo = 'Ausente';
                                }
                              } elseif ($chStatus === 'CANCELADA') {
                                $classe = 'bg-secondary';
                                $rotulo = 'C';
                                $titulo = 'Chamada cancelada';
                              } elseif (!$aulaPassou) {
                                $classe = '';
                                $rotulo = '';
                              } else {
                                $classe = 'bg-danger';
                                $rotulo = 'F';
                                $titulo = 'Ausente';
                              }

                              if ($rotulo !== '') {
                                $titulo .= ' - ' . (string) ($ch['disciplina'] ?? '');
                              }
                            ?>
                            <?php if ($rotulo !== ''): ?>
                              <div>
                                <span class="badge <?= $classe ?>" style="font-size: .65rem;" title="<?= htmlspecialchars($titulo, ENT_QUOTES, 'UTF-8') ?>"><?= $rotulo ?></span>
                              </div>
                            <?php endif; ?>
                          <?php endforeach; ?>
                        <?php else: ?>
                          <span class="text-muted" style="font-size: .8rem;"><?= $dia ?></span>
                        <?php endif; ?>
                      </div>
                      <?php $celulas++; ?>
                      <?php if ($celulas % 7 === 0 && $dia < $totalDias): ?>
                  </div>
                  <div class="row g-1 text-center">
                    <?php endif; ?>
                    <?php endfor; ?>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>
</section>