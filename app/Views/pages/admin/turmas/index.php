<?php
$turmasView = is_array($turmas ?? null) ? $turmas : [];
$isProfessor = (string) ($authUser['role'] ?? $authUser['tipo'] ?? '') === 'professor';
?>

<section class="container py-4">
    <div class="bg-white border rounded-3 p-4 shadow-sm">
      <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <h4 class="mb-0"><i class="bi bi-people me-2"></i>Turmas</h4>
        <div class="d-flex flex-wrap align-items-center gap-2">
          <form method="get" action="/admin/turmas" class="d-flex align-items-center gap-2">
            <label class="form-label mb-0 small text-muted" for="filtroAtivo">Status</label>
            <select class="form-select form-select-sm" name="ativo" id="filtroAtivo" onchange="this.form.submit()">
              <option value="">Todos</option>
              <option value="1" <?= ($filtroAtivo ?? -1) === 1 ? 'selected' : '' ?>>Ativas</option>
              <option value="0" <?= ($filtroAtivo ?? -1) === 0 ? 'selected' : '' ?>>Inativas</option>
            </select>
          </form>
          <?php if (!$isProfessor): ?>
            <a class="btn btn-outline-secondary btn-sm" href="/admin/alunos/troca-historico"><i class="bi bi-arrow-left-right me-1"></i>Troca de Turmas</a>
            <a class="btn btn-outline-primary btn-sm" href="/admin/turmas/geracao"><i class="bi bi-magic me-1"></i>Geração de turmas</a>
            <a class="btn btn-primary btn-sm" href="/admin/turmas/novo"><i class="bi bi-plus-circle me-1"></i>Nova turma</a>
          <?php endif; ?>
        </div>
      </div>

      <div class="table-responsive">
        <table class="table table-striped table-sm align-middle">
          <thead>
            <tr>
              <th><i class="bi bi-hash"></i></th>
              <th>Nome</th>
              <th>Curso</th>
              <th>Matriz curricular</th>
              <th>Nível</th>
              <th>Data Início</th>
              <th>Inscritos</th>
              <th>Ativa</th>
              <th><i class="bi bi-gear me-1"></i>Ações</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($turmasView)): ?>
              <tr><td colspan="9" class="text-muted">Sem registro.</td></tr>
            <?php endif; ?>

            <?php foreach ($turmasView as $turma): ?>
              <tr>
                <td><a class="text-decoration-none fw-medium" href="/admin/turmas/show?id=<?= (int) ($turma['id'] ?? 0) ?>">#<?= (int) ($turma['id'] ?? 0) ?></a></td>
                <td><?= htmlspecialchars((string) ($turma['nome'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars((string) ($turma['curso_nome'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                <td><?php if ((int) ($turma['id_estrutura'] ?? 0) > 0): ?><?= htmlspecialchars((string) ($turma['estrutura_nome'] ?? '-'), ENT_QUOTES, 'UTF-8') ?><span class="text-muted small"> (v<?= htmlspecialchars((string) ($turma['estrutura_versao'] ?? '1.0'), ENT_QUOTES, 'UTF-8') ?>)</span><?php else: ?><span class="text-muted">Sem matriz definida</span><?php endif; ?></td>
                <td><?= htmlspecialchars((string) ($turma['nivel_nome'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                <td><?php
                  $rawDate = (string) ($turma['data_inicio'] ?? '');
                  $dt = $rawDate !== '' ? \DateTime::createFromFormat('Y-m-d', $rawDate) : false;
                  echo htmlspecialchars($dt ? $dt->format('d/m/Y') : ($rawDate ?: '-'), ENT_QUOTES, 'UTF-8');
                ?></td>
                <td class="text-center"><span class="badge bg-info"><?= (int) ($turma['total_inscritos'] ?? 0) ?></span></td>
                <td>
                  <?php if (intval($turma['ativo'] ?? 0) === 1): ?>
                    <span class="badge bg-primary">Sim</span>
                  <?php else: ?>
                    <span class="badge bg-secondary">Não</span>
                  <?php endif; ?>
                </td>
                <td>
                  <a class="btn btn-outline-info btn-sm" href="/admin/turmas/show?id=<?= (int) ($turma['id'] ?? 0) ?>">
                    <i class="bi bi-eye me-1"></i>Visualizar
                  </a>
                  <?php if (!$isProfessor): ?>
                    <a class="btn btn-outline-secondary btn-sm" href="/admin/turmas/editar?id=<?= (int) ($turma['id'] ?? 0) ?>">
                      <i class="bi bi-pencil-square me-1"></i>Editar
                    </a>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
</section>
