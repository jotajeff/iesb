<?php
  $alunosView = is_array($alunos ?? null) ? $alunos : [];
?>

<section class="container py-4">
    <div class="bg-white border rounded-3 p-4 shadow-sm">
      <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <h4 class="mb-0"><i class="bi bi-people me-2"></i>Alunos</h4>
        <div class="d-flex flex-wrap align-items-center gap-2">
          <form method="get" action="/admin/alunos" class="d-flex flex-wrap align-items-center gap-2">
            <div class="input-group input-group-sm" style="max-width: 240px;">
              <span class="input-group-text"><i class="bi bi-search"></i></span>
              <input type="text" class="form-control" name="nome" value="<?= htmlspecialchars($nomeBusca ?? '', ENT_QUOTES, 'UTF-8') ?>" placeholder="Buscar por nome...">
              <?php if (!empty($nomeBusca)): ?>
                <a class="btn btn-outline-secondary" href="/admin/alunos?ativo=<?= (int) ($filtroAtivo ?? 1) ?>" title="Limpar busca"><i class="bi bi-x-lg"></i></a>
              <?php endif; ?>
            </div>
            <label class="form-label mb-0 small text-muted" for="filtroAtivo">Status</label>
            <select class="form-select form-select-sm" name="ativo" id="filtroAtivo" onchange="this.form.submit()">
              <option value="1" <?= ($filtroAtivo ?? 1) === 1 ? 'selected' : '' ?>>Ativos</option>
              <option value="0" <?= ($filtroAtivo ?? 1) === 0 ? 'selected' : '' ?>>Inativos</option>
            </select>
            <button class="btn btn-primary btn-sm" type="submit"><i class="bi bi-funnel me-1"></i>Filtrar</button>
          </form>
          <a class="btn btn-outline-primary btn-sm" href="/admin/alunos/lote"><i class="bi bi-file-earmark-excel me-1"></i>Lote</a>
          <a class="btn btn-primary btn-sm" href="/admin/alunos/novo"><i class="bi bi-plus-circle me-1"></i>Novo aluno</a>
        </div>
      </div>

      <div class="table-responsive">
        <table class="table table-striped table-hover table-sm align-middle">
          <thead>
            <tr>
              <th><i class="bi bi-hash"></i></th>
              <th>Nome</th>
              <th>CPF</th>
              <th>Nascimento</th>
              <th>Telefone</th>
              <th>Email</th>
              <th>Matrículas</th>
              <th>Ativo</th>
              <th><i class="bi bi-gear me-1"></i>Ações</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($alunosView)): ?>
              <tr><td colspan="9" class="text-muted">Sem registro.</td></tr>
            <?php endif; ?>

            <?php foreach ($alunosView as $aluno): ?>
              <tr>
                <td><a class="text-decoration-none fw-medium" href="/admin/alunos/show?id=<?= (int) ($aluno['id'] ?? 0) ?>">#<?= (int) ($aluno['id'] ?? 0) ?></a></td>
                <td><?= htmlspecialchars((string) ($aluno['nome'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars((string) ($aluno['cpf'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                <td><?php
                  $rawDate = (string) ($aluno['data_nascimento'] ?? '');
                  $dt = $rawDate !== '' ? \DateTime::createFromFormat('Y-m-d', $rawDate) : false;
                  echo htmlspecialchars($dt ? $dt->format('d/m/Y') : ($rawDate ?: '-'), ENT_QUOTES, 'UTF-8');
                ?></td>
                <td><?= htmlspecialchars((string) ($aluno['telefone'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars((string) ($aluno['email'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                <td>
                  <?php $totalMatricula = (int) ($aluno['total_matricula'] ?? 0); ?>
                  <span class="badge bg-<?= $totalMatricula > 0 ? 'success' : 'secondary' ?>">
                    <?= $totalMatricula ?>
                  </span>
                </td>
                <td>
                  <?php if (intval($aluno['ativo'] ?? 0) === 1): ?>
                    <span class="badge bg-primary">Sim</span>
                  <?php else: ?>
                    <span class="badge bg-secondary">Não</span>
                  <?php endif; ?>
                </td>
                <td>
                  <div class="d-flex gap-1">
                  <a class="btn btn-outline-success btn-sm" href="/admin/alunos/matricula?id=<?= (int) ($aluno['id'] ?? 0) ?>" title="Matricular aluno">
                    <i class="bi bi-journal-plus"></i>
                  </a>
                  <a class="btn btn-outline-secondary btn-sm" href="/admin/alunos/editar?id=<?= (int) ($aluno['id'] ?? 0) ?>">
                    <i class="bi bi-pencil-square"></i>
                  </a>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>

      <?php
        $page = max(1, (int) ($page ?? 1));
        $totalPages = max(1, (int) ($totalPages ?? 1));
        $total = max(0, (int) ($total ?? 0));
        $perPage = max(1, (int) ($perPage ?? 25));
        $filtroAtivo = (int) ($filtroAtivo ?? 1);
        $deRegistro = $total === 0 ? 0 : (($page - 1) * $perPage) + 1;
        $ateRegistro = min($page * $perPage, $total);
        $baseUrl = '/admin/alunos?ativo=' . $filtroAtivo . '&nome=' . rawurlencode((string) ($nomeBusca ?? '')) . '&page=';
      ?>
      <nav class="d-flex flex-wrap justify-content-between align-items-center gap-2 mt-3">
        <div class="text-muted small">
          Mostrando <strong><?= $deRegistro ?></strong>&ndash;<strong><?= $ateRegistro ?></strong> de <strong><?= $total ?></strong> aluno(s)
        </div>
        <ul class="pagination pagination-sm mb-0">
          <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
            <a class="page-link" href="<?= $baseUrl . ($page - 1) ?>" aria-label="Anterior"><span aria-hidden="true">&laquo;</span></a>
          </li>
          <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <li class="page-item <?= $i === $page ? 'active' : '' ?>">
              <a class="page-link" href="<?= $baseUrl . $i ?>"><?= $i ?></a>
            </li>
          <?php endfor; ?>
          <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
            <a class="page-link" href="<?= $baseUrl . ($page + 1) ?>" aria-label="Próxima"><span aria-hidden="true">&raquo;</span></a>
          </li>
        </ul>
      </nav>
    </div>
  </div>
</section>
