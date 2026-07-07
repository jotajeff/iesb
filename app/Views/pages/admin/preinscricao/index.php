<section class="container py-4">
  <div class="bg-white border rounded-3 p-4 shadow-sm">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
      <h4 class="mb-0"><i class="bi bi-inbox me-2"></i>Pré-inscrições</h4>
    </div>

    <?php if (!empty($flash ?? '')): ?>
      <div class="alert alert-info"><?= htmlspecialchars((string) $flash, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>

    <div class="table-responsive">
      <table class="table table-striped table-sm align-middle">
        <thead>
          <tr>
            <th>#</th>
            <th>Nome</th>
            <th>E-mail</th>
            <th>WhatsApp</th>
            <th>Curso</th>
            <th>Local</th>
            <th>Data</th>
            <th>Situação</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($preInscricoes ?? [])): ?>
            <tr><td colspan="8" class="text-muted"><i class="bi bi-inbox me-1"></i>Nenhuma pré-inscrição recebida.</td></tr>
          <?php else: ?>
            <?php foreach ($preInscricoes as $p): ?>
              <?php
              $criadoEm = (string) ($p['criado_em'] ?? '');
              $dt = $criadoEm !== '' ? \DateTime::createFromFormat('Y-m-d H:i:s', $criadoEm) : false;
              ?>
              <tr>
                <td><?= (int) ($p['id'] ?? 0) ?></td>
                <td><?= htmlspecialchars((string) ($p['nome'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars((string) ($p['email'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars((string) ($p['whatsapp'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars((string) ($p['curso_nome'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                <td class="small"><?= (string) ($p['bandeira'] ?? '') ?> <?= htmlspecialchars((string) ($p['cidade'] ?? '-'), ENT_QUOTES, 'UTF-8') ?>, <?= htmlspecialchars((string) ($p['pais'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= $dt ? $dt->format('d/m/Y H:i') : ($criadoEm ?: '-') ?></td>
                <td><span class="badge bg-warning text-dark"><?= htmlspecialchars((string) ($p['situacao'] ?? 'recebido'), ENT_QUOTES, 'UTF-8') ?></span></td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</section>
