<?php
$arquivos = $arquivos ?? [];
?>
<section class="py-4">
  <div class="container-fluid">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
      <div>
        <h1 class="h3 mb-1">Logs do Webhook Asaas</h1>
        <p class="text-muted mb-0">Arquivos de log dos eventos recebidos do webhook. Clique em um arquivo para conferir os detalhes.</p>
      </div>
      <div class="d-flex gap-2">
        <a href="/admin/asaas" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Voltar às cobranças</a>
      </div>
    </div>

    <?php if (empty($arquivos)): ?>
      <div class="card border-0 shadow-sm">
        <div class="card-body text-center py-5">
          <div class="mb-2 text-muted"><i class="bi bi-journal-x" style="font-size:3rem;"></i></div>
          <h5 class="mb-1">Nenhum arquivo de log encontrado</h5>
          <p class="text-muted mb-0">Os logs são gerados em <code>storage/logs/asaas</code> à medida que o webhook recebe eventos.</p>
        </div>
      </div>
    <?php else: ?>
      <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
              <thead class="table-light">
                <tr>
                  <th>Arquivo</th>
                  <th>Data</th>
                  <th>Linhas</th>
                  <th>Tamanho</th>
                  <th>Última modificação</th>
                  <th class="text-end">Ações</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($arquivos as $arquivo): ?>
                  <tr>
                    <td>
                      <i class="bi bi-file-earmark-text me-2 text-warning"></i>
                      <span class="fw-semibold font-monospace"><?= htmlspecialchars((string) ($arquivo['arquivo'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span>
                    </td>
                    <td><?= htmlspecialchars((string) ($arquivo['data'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= number_format((int) ($arquivo['linhas'] ?? 0), 0, ',', '.') ?></td>
                    <td><?= htmlspecialchars((string) ($arquivo['tamanho_formatado'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars((string) ($arquivo['modificado_formatado'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                    <td class="text-end">
                      <a class="btn btn-outline-primary btn-sm" href="/admin/asaas/webhook-log-detalhe?arquivo=<?= htmlspecialchars((string) ($arquivo['arquivo'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                        <i class="bi bi-eye me-1"></i>Ver detalhes
                      </a>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    <?php endif; ?>
  </div>
</section>
