<?php
$conectado = (bool) ($conectado ?? false);
$info = $connectionInfo ?? [];
$connectUrl = (string) ($connectUrl ?? '');
?>

<section class="container py-4">
  <div class="bg-white border rounded-3 p-4 shadow-sm">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
      <h4 class="mb-0"><i class="bi bi-cloud-drive me-2"></i>Storage (Google Drive)</h4>
      <?php if ($conectado): ?>
        <form method="post" action="/admin/storage/disconnect" class="d-inline">
          <button type="submit" class="btn btn-outline-danger btn-sm"><i class="bi bi-x-circle me-1"></i>Desconectar</button>
        </form>
      <?php endif; ?>
    </div>

    <div class="alert <?= $conectado ? 'alert-success' : 'alert-warning' ?> border">
      <strong>Status:</strong>
      <?= $conectado ? 'Conectado' : 'Desconectado' ?>
      <?php if (!empty($info['email_workspace'])): ?>
        <span class="ms-2 text-muted"><?= htmlspecialchars((string) $info['email_workspace'], ENT_QUOTES, 'UTF-8') ?></span>
      <?php endif; ?>
    </div>

    <?php if (!$conectado): ?>
      <p class="text-muted">Para armazenar documentos no Google Drive da instituição, conecte a conta Google do Workspace.</p>
      <a class="btn btn-primary" href="<?= htmlspecialchars($connectUrl, ENT_QUOTES, 'UTF-8') ?>"><i class="bi bi-google me-1"></i>Conectar Google</a>
    <?php else: ?>
      <div class="row g-3 mb-4">
        <div class="col-md-6">
          <div class="border rounded-3 p-3 h-100">
            <h6 class="text-muted mb-2"><i class="bi bi-envelope me-1"></i>Conta Workspace</h6>
            <p class="mb-0"><?= htmlspecialchars((string) ($info['email_workspace'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></p>
          </div>
        </div>
        <div class="col-md-6">
          <div class="border rounded-3 p-3 h-100">
            <h6 class="text-muted mb-2"><i class="bi bi-folder me-1"></i>Pasta Raiz</h6>
            <p class="mb-0">
              <?= htmlspecialchars((string) ($info['root_folder_nome'] ?? '-'), ENT_QUOTES, 'UTF-8') ?>
              <?php if (!empty($info['root_folder_id'])): ?>
                <span class="text-muted small">(<?= htmlspecialchars((string) $info['root_folder_id'], ENT_QUOTES, 'UTF-8') ?>)</span>
              <?php endif; ?>
            </p>
          </div>
        </div>
      </div>

      <form method="post" action="/admin/storage/estrutura" class="d-inline">
        <button type="submit" class="btn btn-outline-primary"><i class="bi bi-diagram-3 me-1"></i>Criar/Verificar Estrutura de Pastas</button>
      </form>
    <?php endif; ?>
  </div>
</section>
