<?php
  $alunosView = is_array($alunos ?? null) ? $alunos : [];
?>

<section class="container py-4">
    <div class="bg-white border rounded-3 p-4 shadow-sm">
      <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <h4 class="mb-0"><i class="bi bi-people me-2"></i>Alunos</h4>
        <div class="d-flex flex-wrap align-items-center gap-2">
          <form method="get" action="/admin/alunos" class="d-flex align-items-center gap-2">
            <label class="form-label mb-0 small text-muted" for="filtroAtivo">Status</label>
            <select class="form-select form-select-sm" name="ativo" id="filtroAtivo" onchange="this.form.submit()">
              <option value="1" <?= ($filtroAtivo ?? 1) === 1 ? 'selected' : '' ?>>Ativos</option>
              <option value="0" <?= ($filtroAtivo ?? 1) === 0 ? 'selected' : '' ?>>Inativos</option>
            </select>
          </form>
          <a class="btn btn-primary btn-sm" href="/admin/alunos/novo"><i class="bi bi-plus-circle me-1"></i>Novo aluno</a>
        </div>
      </div>

      <div class="table-responsive">
        <table class="table table-striped table-sm align-middle">
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
                <td><a class="text-decoration-none fw-medium" href="/admin/alunos/show?id=<?= (int) ($aluno['id'] ?? 0) ?>"><i class="bi bi-box-arrow-up-right me-1"></i><?= (int) ($aluno['id'] ?? 0) ?></a></td>
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
                  <?php $totalMatriculas = (int) ($aluno['total_matriculas'] ?? 0); ?>
                  <span class="badge bg-<?= $totalMatriculas > 0 ? 'success' : 'secondary' ?>">
                    <?= $totalMatriculas ?>
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
                  <a class="btn btn-outline-secondary btn-sm" href="/admin/alunos/editar?id=<?= (int) ($aluno['id'] ?? 0) ?>">
                    <i class="bi bi-pencil-square"></i>
                  </a>
                  <a class="btn btn-outline-success btn-sm" href="/admin/alunos/matricula?id=<?= (int) ($aluno['id'] ?? 0) ?>">
                    <i class="bi bi-journal-plus"></i> Matricular
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
