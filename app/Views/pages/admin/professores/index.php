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
              <td><?= $profId ?></td>
              <td><?= htmlspecialchars((string) ($prof['nome'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
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
                  <a class="btn btn-outline-secondary btn-sm" href="/admin/professores/editar?id=<?= $profId ?>">
                    <i class="bi bi-pencil-square"></i>
                  </a>
                  <a class="btn btn-outline-info btn-sm" href="/admin/professores/vincular?id=<?= $profId ?>" title="Vincular a turmas">
                    <i class="bi bi-link-45deg"></i>
                  </a>
                  <a class="btn btn-sm <?= $temEndereco ? 'btn-outline-success' : 'btn-outline-warning' ?>" href="/admin/professores/endereco?id=<?= $profId ?>">
                    <i class="bi bi-geo-alt"></i>
                  </a>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</section>
