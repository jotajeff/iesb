<?php use App\Support\UiIconHelper; ?>
<section class="container py-4">
  <div class="bg-white border rounded-3 p-4 shadow-sm">
    <h4 class="mb-3">Visitas de Páginas</h4>
    <div class="table-responsive">
      <table class="table table-striped table-sm align-middle">
        <thead>
          <tr>
            <th>#</th>
            <th>Página</th>
            <th>Agente</th>
            <th>País / Cidade</th>
            <th>Data</th>
            <th>Hora</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($visits)): ?>
            <tr><td colspan="6" class="text-muted">Sem registros de visitas.</td></tr>
          <?php endif; ?>
          <?php foreach (($visits ?? []) as $visit): ?>
            <tr>
              <td><?= (int) $visit['id'] ?></td>
              <td><?= htmlspecialchars((string) $visit['pagina_nome'], ENT_QUOTES, 'UTF-8') ?></td>
              <td>
                <?php $ua = $visit['user_agent'] ?? []; ?>
                <span class="me-2"><i class="bi <?= UiIconHelper::device((string) ($ua['device'] ?? '')) ?>"></i> <?= htmlspecialchars((string) ($ua['device'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></span>
                <span class="me-2"><i class="bi <?= UiIconHelper::os((string) ($ua['os'] ?? '')) ?>"></i> <?= htmlspecialchars((string) ($ua['os'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></span>
                <span><i class="bi <?= UiIconHelper::browser((string) ($ua['browser'] ?? '')) ?>"></i> <?= htmlspecialchars((string) ($ua['browser'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></span>
              </td>
              <td>
                <?= htmlspecialchars((string) (($visit['location']['flag'] ?? '🏳️') . ' ' . ($visit['location']['country'] ?? '-')), ENT_QUOTES, 'UTF-8') ?>
                <br>
                <small class="text-muted"><?= htmlspecialchars((string) ($visit['location']['city'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></small>
              </td>
              <td><?= htmlspecialchars((string) ($visit['data_visita_formatada'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
              <td><?= htmlspecialchars((string) $visit['hora_visita'], ENT_QUOTES, 'UTF-8') ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</section>
