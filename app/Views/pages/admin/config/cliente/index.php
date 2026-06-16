<section class="container py-4">
  <div class="bg-white border rounded-3 p-4 shadow-sm">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
      <h4 class="mb-0"><i class="bi bi-building-gear me-2"></i>Instituições</h4>
    </div>

    <?php if (empty($instituicoes ?? [])): ?>
      <p class="text-muted"><i class="bi bi-inbox me-1"></i>Nenhuma instituição encontrada.</p>
    <?php endif; ?>

    <?php foreach (($instituicoes ?? []) as $i): ?>
      <?php $id = (int) ($i['id'] ?? 0); ?>
      <div class="border rounded-3 p-3 mb-3">
        <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-2">
          <h5 class="mb-0">#<?= $id ?> — <?= htmlspecialchars((string) ($i['razao_social'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></h5>
          <a class="btn btn-outline-secondary btn-sm" href="/admin/config/cliente/editar?id=<?= $id ?>">
            <i class="bi bi-pencil-square me-1"></i>Editar
          </a>
        </div>
        <div class="row g-2 mb-1">
          <div class="col-md-3"><small class="text-muted">Nome Fantasia</small><br><?= htmlspecialchars((string) ($i['nome_fantasia'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></div>
          <div class="col-md-3"><small class="text-muted">Documento</small><br><?= htmlspecialchars((string) ($i['documento'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></div>
          <div class="col-md-3"><small class="text-muted">Telefone</small><br><?= htmlspecialchars((string) ($i['telefone'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></div>
          <div class="col-md-3"><small class="text-muted">Email</small><br><?= htmlspecialchars((string) ($i['email'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></div>
          <div class="col-md-3"><small class="text-muted">Responsável</small><br><?= htmlspecialchars((string) ($i['responsavel_nome'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></div>
          <div class="col-md-3"><small class="text-muted">Status</small><br><?= htmlspecialchars((string) ($i['status'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</section>
