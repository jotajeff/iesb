<?php
  $turmasView = is_array($turmas ?? null) ? $turmas : [];
?>

<section class="container py-4">
    <div class="bg-white border rounded-3 p-4 shadow-sm">
      <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <h4 class="mb-0"><i class="bi bi-people me-2"></i>Turmas</h4>
        <div class="d-flex flex-wrap align-items-center gap-2">
          <a class="btn btn-primary btn-sm" href="/admin/turmas/novo"><i class="bi bi-plus-circle me-1"></i>Nova turma</a>
        </div>
      </div>

      <div class="table-responsive">
        <table class="table table-striped table-sm align-middle">
          <thead>
            <tr>
              <th><i class="bi bi-hash"></i></th>
              <th>Nome</th>
              <th>Curso</th>
              <th>Nível</th>
              <th>Data Início</th>
              <th>Inscritos</th>
              <th>Ativa</th>
              <th><i class="bi bi-gear me-1"></i>Ações</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($turmasView)): ?>
              <tr><td colspan="8" class="text-muted">Sem registro.</td></tr>
            <?php endif; ?>

            <?php foreach ($turmasView as $turma): ?>
              <tr>
                <td><a class="text-decoration-none fw-medium" href="/admin/turmas/show?id=<?= (int) ($turma['id'] ?? 0) ?>"><i class="bi bi-box-arrow-up-right me-1"></i><?= (int) ($turma['id'] ?? 0) ?></a></td>
                <td><?= htmlspecialchars((string) ($turma['nome'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars((string) ($turma['curso_nome'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars((string) ($turma['nivel_nome'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                <td><?php
                  $rawDate = (string) ($turma['data_inicio'] ?? '');
                  $dt = $rawDate !== '' ? \DateTime::createFromFormat('Y-m-d', $rawDate) : false;
                  echo htmlspecialchars($dt ? $dt->format('d/m/Y') : ($rawDate ?: '-'), ENT_QUOTES, 'UTF-8');
                ?></td>
                <td class="text-center"><span class="badge bg-info"><?= (int) ($turma['total_inscritos'] ?? 0) ?></span></td>
                <td>
                  <?php if (strtoupper(trim((string) ($turma['ativa'] ?? 'N'))) === 'S'): ?>
                    <span class="badge bg-primary">Sim</span>
                  <?php else: ?>
                    <span class="badge bg-secondary">Não</span>
                  <?php endif; ?>
                </td>
                <td>
                  <a class="btn btn-outline-secondary btn-sm" href="/admin/turmas/editar?id=<?= (int) ($turma['id'] ?? 0) ?>">
                    <i class="bi bi-pencil-square me-1"></i>Editar
                  </a>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
</section>
