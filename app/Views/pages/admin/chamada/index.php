<?php
  $chamadasView = is_array($chamadas ?? null) ? $chamadas : [];
  $grupos = [];
  foreach ($chamadasView as $chamada) {
    $curso = (string) ($chamada['curso_nome'] ?? 'Curso não informado');
    $turma = (string) ($chamada['turma_nome'] ?? 'Turma não informada');
    $grupos[$curso . '||' . $turma]['curso'] = $curso;
    $grupos[$curso . '||' . $turma]['turma'] = $turma;
    $grupos[$curso . '||' . $turma]['chamadas'][] = $chamada;
  }
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
      <?php foreach ($grupos as $grupo): ?>
        <div class="border rounded-3 overflow-hidden mb-4">
          <div class="bg-light border-bottom px-3 py-2 fw-semibold">
            <i class="bi bi-book me-1"></i><?= htmlspecialchars((string) $grupo['curso'], ENT_QUOTES, 'UTF-8') ?>
            &middot; <i class="bi bi-people me-1"></i><?= htmlspecialchars((string) $grupo['turma'], ENT_QUOTES, 'UTF-8') ?>
            <span class="badge bg-primary ms-1"><?= count($grupo['chamadas']) ?> chamada(s)</span>
          </div>
          <div class="table-responsive">
            <table class="table table-striped table-hover table-sm align-middle mb-0">
              <thead>
                <tr>
                  <th><i class="bi bi-hash"></i></th>
                  <th>Disciplina</th>
                  <th>Professor</th>
                  <th>Data</th>
                  <th>Aula</th>
                  <th>Horário</th>
                  <th>Presenças</th>
                  <th>Status</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($grupo['chamadas'] as $chamada): ?>
                  <?php
                    $statusChamada = (string) ($chamada['status'] ?? 'ABERTA');
                    $totalPresencas = (int) ($chamada['total_presencas'] ?? 0);
                    $totalPresentes = (int) ($chamada['total_presentes'] ?? 0);
                    $rawData = (string) ($chamada['data_aula'] ?? '');
                    $dataChamada = $rawData !== '' ? date_create($rawData) : false;
                  ?>
                  <tr>
                    <td><?= (int) ($chamada['id'] ?? 0) ?></td>
                    <td><?= htmlspecialchars((string) ($chamada['disciplina_nome'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
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
                      <select class="form-select form-select-sm chamada-status-select status-<?= strtolower($statusChamada) ?>" data-chamada-id="<?= (int) ($chamada['id'] ?? 0) ?>" data-current="<?= htmlspecialchars($statusChamada, ENT_QUOTES, 'UTF-8') ?>" aria-label="Alterar status">
                        <option value="ABERTA" <?= $statusChamada === 'ABERTA' ? 'selected' : '' ?>>ABERTA</option>
                        <option value="FECHADA" <?= $statusChamada === 'FECHADA' ? 'selected' : '' ?>>FECHADA</option>
                        <option value="CANCELADA" <?= $statusChamada === 'CANCELADA' ? 'selected' : '' ?>>CANCELADA</option>
                      </select>
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

<style>
  .chamada-status-select {
    font-weight: 600;
    cursor: pointer;
  }
  .status-aberta {
    background-color: #ffc107;
    color: #212529;
    border-color: #ffc107;
  }
  .status-fechada {
    background-color: #198754;
    color: #fff;
    border-color: #198754;
  }
  .status-cancelada {
    background-color: #dc3545;
    color: #fff;
    border-color: #dc3545;
  }
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
  var statusClasses = {
    ABERTA: 'status-aberta',
    FECHADA: 'status-fechada',
    CANCELADA: 'status-cancelada'
  };

  document.querySelectorAll('.chamada-status-select').forEach(function (select) {
    select.addEventListener('change', function () {
      var status = select.value;
      var atual = select.dataset.current;
      var id = select.dataset.chamadaId;
      var body = new URLSearchParams();
      body.append('id', id);
      body.append('status', status);

      fetch('/admin/chamadas/alterar-status', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: body.toString()
      })
        .then(function (r) { return r.json(); })
        .then(function (data) {
          if (data.sucesso) {
            select.dataset.current = status;
            select.className = 'form-select form-select-sm chamada-status-select ' + (statusClasses[status] || '');
            select.value = status;
          } else {
            alert(data.erro || 'Erro ao alterar o status.');
            select.value = atual;
          }
        })
        .catch(function () {
          alert('Erro ao alterar o status.');
          select.value = atual;
        });
    });
  });
});
</script>