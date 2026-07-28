<section class="container py-4">
    <div class="bg-white border rounded-3 p-4 shadow-sm">
      <?php
        $currentRole = (string) ($authUser['role'] ?? '');
        $isAdmin = $currentRole === 'admin';
      ?>
      <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <h4 class="mb-0"><i class="bi bi-person-badge me-2"></i>Professores</h4>
        <div class="d-flex flex-wrap align-items-center gap-2">
          <?php if ($isAdmin): ?>
            <a class="btn btn-primary btn-sm" href="/admin/professores/novo"><i class="bi bi-plus-circle me-1"></i>Novo Professor</a>
          <?php endif; ?>
        </div>
      </div>

    <div class="table-responsive">
      <table class="table table-striped table-sm align-middle">
        <thead>
          <tr>
            <th><i class="bi bi-hash"></i></th>
            <th><i class="bi bi-person me-1"></i>Nome</th>
            <th><i class="bi bi-telephone me-1"></i>Telefone</th>
            <th><i class="bi bi-toggle-on me-1"></i>Ativo</th>
            <th><i class="bi bi-geo-alt me-1"></i>Endereço</th>
            <th><i class="bi bi-link-45deg me-1"></i>Turmas</th>
            <th><i class="bi bi-gear me-1"></i>Ações</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($professores ?? [])): ?>
            <tr><td colspan="7" class="text-muted"><i class="bi bi-inbox me-1"></i>Nenhum professor encontrado.</td></tr>
          <?php endif; ?>

          <?php foreach ($professores ?? [] as $prof): ?>
            <?php $profId = (int) ($prof['id'] ?? 0); ?>
            <?php $temEndereco = isset($enderecos[$profId]) && $enderecos[$profId] !== null; ?>
            <tr>
              <td><a href="/admin/professores/detalhe?id=<?= $profId ?>" class="text-decoration-none fw-medium">#<?= $profId ?></a></td>
              <td class="d-flex align-items-center gap-2">
                <?php $fotoPath = $fotos[$profId] ?? null; ?>
                <?php if ($fotoPath): ?>
                  <img src="/<?= htmlspecialchars($fotoPath, ENT_QUOTES, 'UTF-8') ?>" alt="" class="rounded-circle" style="width:32px;height:32px;object-fit:cover;">
                <?php endif; ?>
                <?= htmlspecialchars((string) ($prof['nome'] ?? '-'), ENT_QUOTES, 'UTF-8') ?>
              </td>
              <td><?= htmlspecialchars((string) ($prof['telefone'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
              <td>
                <?php $ativo = (int) ($prof['ativo'] ?? 1); ?>
                <?php if ($ativo === 1): ?>
                  <span class="badge bg-success">Sim</span>
                <?php else: ?>
                  <span class="badge bg-secondary">Não</span>
                <?php endif; ?>
              </td>
              <td>
                <?php if ($temEndereco): ?>
                  <span class="badge bg-success">Cadastrado</span>
                <?php else: ?>
                  <span class="badge bg-danger">Pendente</span>
                <?php endif; ?>
              </td>
              <td>
                <?php $qtd = (int) ($vinculoCounts[$profId] ?? 0); ?>
                <span class="badge bg-primary"><?= $qtd ?></span>
              </td>
              <td>
                <div class="d-flex gap-1">
                  <a class="btn btn-outline-secondary btn-sm" href="/admin/professores/editar?id=<?= $profId ?>" title="Editar professor">
                    <i class="bi bi-pencil-square"></i>
                  </a>
                  <a class="btn btn-outline-info btn-sm" href="/admin/professores/vincular?id=<?= $profId ?>" title="Vincular a turmas">
                    <i class="bi bi-link-45deg"></i>
                  </a>
                  <a class="btn btn-sm <?= $temEndereco ? 'btn-outline-success' : 'btn-outline-warning' ?>" href="/admin/professores/endereco?id=<?= $profId ?>" title="<?= $temEndereco ? 'Ver endereço' : 'Cadastrar endereço' ?>">
                    <i class="bi bi-geo-alt"></i>
                  </a>
                  <a class="btn btn-outline-primary btn-sm" href="/admin/professores/fotos?id=<?= $profId ?>" title="Fotos do professor">
                    <i class="bi bi-camera"></i>
                  </a>
                  <a class="btn btn-outline-dark btn-sm" href="/admin/professores/curriculo?id=<?= $profId ?>" title="Currículo do professor">
                    <i class="bi bi-mortarboard"></i>
                  </a>
                  <?php if (isset($curriculos[$profId]) && trim((string) ($curriculos[$profId]['resumo'] ?? '')) !== ''): ?>
                    <button class="btn btn-outline-secondary btn-sm btn-toggle-resumo" data-target="curriculo-<?= $profId ?>" title="Ver resumo do currículo">
                      <i class="bi bi-card-text"></i>
                    </button>
                  <?php endif; ?>
                </div>
              </td>
            </tr>
            <?php
              $curriculoResumo = isset($curriculos[$profId]) ? trim((string) ($curriculos[$profId]['resumo'] ?? '')) : '';
              if ($curriculoResumo !== ''):
            ?>
              <tr class="curriculo-resumo-row" id="curriculo-<?= $profId ?>" style="display:none;">
                <td colspan="7" class="bg-light">
                  <div class="p-3 border rounded">
                    <strong><i class="bi bi-file-earmark-text me-1"></i>Resumo:</strong>
                    <p class="mb-0 mt-1"><?= nl2br(htmlspecialchars($curriculoResumo, ENT_QUOTES, 'UTF-8')) ?></p>
                  </div>
                </td>
              </tr>
            <?php endif; ?>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <div class="mt-3 pt-3 border-top text-muted small">
      <strong>Legenda das ações:</strong>
      <span class="ms-2"><i class="bi bi-pencil-square"></i> Editar</span>
      <span class="ms-2"><i class="bi bi-link-45deg"></i> Vincular turmas</span>
      <span class="ms-2"><i class="bi bi-geo-alt"></i> Endereço</span>
      <span class="ms-2"><i class="bi bi-camera"></i> Fotos</span>
      <span class="ms-2"><i class="bi bi-mortarboard"></i> Currículo</span>
      <span class="ms-2"><i class="bi bi-card-text"></i> Resumo</span>
    </div>
  </div>
</section>

<script>
document.querySelectorAll('.btn-toggle-resumo').forEach(function(btn) {
  btn.addEventListener('click', function() {
    var targetId = this.getAttribute('data-target');
    var row = document.getElementById(targetId);
    if (row) {
      var isVisible = row.style.display !== 'none';
      row.style.display = isVisible ? 'none' : '';
    }
  });
});
</script>
