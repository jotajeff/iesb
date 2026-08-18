<?php
$modulos = is_array($modulos ?? null) ? $modulos : [];
$matrizes = is_array($matrizes ?? null) ? $matrizes : [];
$turmas = is_array($turmas ?? null) ? $turmas : [];
$idEstrutura = (int) ($idEstrutura ?? 0);
$idTurma = (int) ($idTurma ?? 0);
?>
<section class="container py-4">
  <div class="bg-white border rounded-3 p-4 shadow-sm">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
      <div>
        <div class="text-uppercase text-muted small fw-semibold mb-1">Acadêmico</div>
        <h4 class="mb-0"><i class="bi bi-grid-3x3-gap me-2"></i>Módulos</h4>
      </div>
      <a class="btn btn-outline-secondary btn-sm" href="/admin/academico/matrizes"><i class="bi bi-diagram-3 me-1"></i>Ver matrizes</a>
    </div>

    <p class="text-muted">Os módulos pertencem à matriz curricular. A turma utiliza os módulos da matriz selecionada no cadastro da turma.</p>

    <form method="get" action="/admin/academico/modulos" class="row g-2 align-items-end mb-4">
      <div class="col-md-5">
        <label class="form-label small text-muted">Matriz curricular</label>
        <select name="id_estrutura" class="form-select">
          <option value="">Todas as matrizes</option>
          <?php foreach ($matrizes as $matriz): ?>
            <option value="<?= (int) ($matriz['id'] ?? 0) ?>" <?= $idEstrutura === (int) ($matriz['id'] ?? 0) ? 'selected' : '' ?>>
              <?= htmlspecialchars((string) ($matriz['curso_nome'] ?? '') . ' — ' . (string) ($matriz['nome'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-5">
        <label class="form-label small text-muted">Turma</label>
        <select name="id_turma" class="form-select">
          <option value="">Todas as turmas</option>
          <?php foreach ($turmas as $turma): ?>
            <option value="<?= (int) ($turma['id'] ?? 0) ?>" <?= $idTurma === (int) ($turma['id'] ?? 0) ? 'selected' : '' ?>>
              <?= htmlspecialchars((string) ($turma['curso_nome'] ?? '') . ' — ' . (string) ($turma['nome'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-2"><button class="btn btn-primary w-100" type="submit"><i class="bi bi-search me-1"></i>Filtrar</button></div>
    </form>

    <div class="table-responsive">
      <table class="table table-striped table-sm align-middle">
        <thead><tr><th>Módulo</th><th>Matriz</th><th>Curso</th><th>Turma</th><th>Disciplinas</th><th>Status</th><th>Ações</th></tr></thead>
        <tbody>
        <?php if (empty($modulos)): ?>
          <tr><td colspan="7" class="text-muted"><i class="bi bi-inbox me-1"></i>Nenhum módulo encontrado.</td></tr>
        <?php endif; ?>
        <?php foreach ($modulos as $modulo): ?>
          <tr>
            <td><span class="badge bg-light text-dark me-1"><?= (int) ($modulo['ordem'] ?? 0) ?></span><?= htmlspecialchars((string) ($modulo['nome'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
            <td><?= htmlspecialchars((string) ($modulo['estrutura_nome'] ?? '-'), ENT_QUOTES, 'UTF-8') ?> <small class="text-muted">v<?= htmlspecialchars((string) ($modulo['estrutura_versao'] ?? ''), ENT_QUOTES, 'UTF-8') ?></small></td>
            <td><?= htmlspecialchars((string) ($modulo['curso_nome'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
            <td><?= htmlspecialchars((string) ($modulo['turma_nome'] ?? 'Sem turma vinculada'), ENT_QUOTES, 'UTF-8') ?></td>
            <td><span class="badge bg-primary"><?= (int) ($modulo['total_disciplinas'] ?? 0) ?></span></td>
            <td><?= (int) ($modulo['ativo'] ?? 0) === 1 ? '<span class="badge bg-success">Ativo</span>' : '<span class="badge bg-secondary">Inativo</span>' ?></td>
            <td class="text-nowrap"><a class="btn btn-sm btn-outline-primary" href="/admin/academico/matrizes/detalhe?id=<?= (int) ($modulo['id_estrutura'] ?? 0) ?>#modulo-<?= (int) ($modulo['id'] ?? 0) ?>" title="Gerenciar módulo e disciplinas"><i class="bi bi-pencil-square"></i></a><?php if ((int) ($modulo['id_turma'] ?? 0) > 0): ?> <a class="btn btn-sm btn-outline-secondary" href="/admin/turmas/show?id=<?= (int) $modulo['id_turma'] ?>" title="Abrir turma"><i class="bi bi-people"></i></a><?php endif; ?></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</section>
