<?php
  $chamadasView = is_array($chamadas ?? null) ? $chamadas : [];
?>

<section class="container py-4">
  <div class="bg-white border rounded-3 p-4 shadow-sm">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
      <h4 class="mb-0"><i class="bi bi-clipboard-check me-2"></i>Chamadas</h4>
      <div class="d-flex flex-wrap align-items-center gap-2">
        <a class="btn btn-primary btn-sm" href="/admin/chamadas/novo"><i class="bi bi-plus-circle me-1"></i>Gerar chamada</a>
      </div>
    </div>

    <?php if (empty($chamadasView)): ?>
      <p class="text-muted mb-0">Nenhuma chamada gerada.</p>
    <?php else: ?>
      <div class="table-responsive">
        <table class="table table-striped table-hover table-sm align-middle">
          <thead>
            <tr>
              <th><i class="bi bi-hash"></i></th>
              <th>Disciplina</th>
              <th>Turma</th>
              <th>Professor</th>
              <th>Data</th>
              <th>Aula</th>
              <th>Horário</th>
              <th>Presenças</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($chamadasView as $chamada): ?>
              <?php
                $statusChamada = (string) ($chamada['status'] ?? 'ABERTA');
                $totalPresencas = (int) ($chamada['total_presencas'] ?? 0);
                $totalPresentes = (int) ($chamada['total_presentes'] ?? 0);
                $rawData = (string) ($chamada['data_aula'] ?? '');
                $dataChamada = $rawData !== '' ? date_create($rawData) : false;
                $statusClass = match ($statusChamada) {
                  'ABERTA' => 'bg-warning text-dark',
                  'FECHADA' => 'bg-success',
                  'CANCELADA' => 'bg-danger',
                  default => 'bg-secondary',
                };
              ?>
              <tr>
                <td><?= (int) ($chamada['id'] ?? 0) ?></td>
                <td><?= htmlspecialchars((string) ($chamada['disciplina_nome'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars((string) ($chamada['turma_nome'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars((string) ($chamada['professor_nome'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars($dataChamada ? $dataChamada->format('d/m/Y') : ($rawData ?: '-'), ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= (int) ($chamada['numero_aula'] ?? 0) > 0 ? (int) $chamada['numero_aula'] : '-' ?></td>
                <td>
                  <?php
                    $hInicio = (string) ($chamada['hora_inicio'] ?? '');
                    $hFim = (string) ($chamada['hora_fim'] ?? '');
                    echo htmlspecialchars(($hInicio !== '' ? $hInicio : '-') . ($hInicio !== '' && $hFim !== '' ? ' às ' : '') . ($hFim !== '' ? $hFim : ''), ENT_QUOTES, 'UTF-8');
                  ?>
                </td>
                <td>
                  <span class="badge bg-primary"><?= $totalPresentes ?>/<?= $totalPresencas ?></span>
                </td>
                <td>
                  <span class="badge <?= $statusClass ?>"><?= htmlspecialchars(ucfirst(strtolower($statusChamada)), ENT_QUOTES, 'UTF-8') ?></span>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>
</section>