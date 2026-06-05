<?php
$tarefas = $tarefas ?? [];
$isAdmin = (bool) ($isAdmin ?? false);
$situacoes = $situacoes ?? [];
$filtroSituacao = (string) ($filtroSituacao ?? '');
?>
<section class="container py-4">
  <div class="bg-white border rounded-3 p-4 shadow-sm">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
      <div>
        <div class="text-uppercase text-muted small fw-semibold mb-1">Gestão de tarefas</div>
        <h4 class="mb-1"><i class="bi bi-list-task me-2"></i>Lista de tarefas</h4>
        <p class="mb-0 text-muted">Visualize todas as tarefas em formato de tabela e acesse rapidamente as edições.</p>
      </div>
      <div class="d-flex flex-wrap align-items-center gap-2">
        <a class="btn btn-outline-secondary btn-sm" href="/admin/tarefas">
          <i class="bi bi-kanban me-1"></i>Voltar ao Kanban
        </a>
        <a class="btn btn-primary btn-sm" href="/admin/tarefas/novo">
          <i class="bi bi-plus-circle me-1"></i>Nova tarefa
        </a>
      </div>
    </div>

    <?php if (!$isAdmin): ?>
      <div class="alert alert-info mb-3">
        <i class="bi bi-info-circle me-1"></i>Você está visualizando apenas as tarefas atribuídas ao seu usuário.
      </div>
    <?php endif; ?>

    <form method="get" action="/admin/tarefas/lista" class="row g-3 mb-3">
      <div class="col-12 col-md-4 col-lg-3">
        <label for="situacaoSelect" class="form-label small text-uppercase text-muted">Filtrar por situação</label>
        <div class="input-group">
          <select class="form-select" id="situacaoSelect" name="situacao">
            <option value="" <?= $filtroSituacao === '' ? 'selected' : '' ?>>Todas</option>
            <?php foreach ($situacoes as $slug => $label): ?>
              <option value="<?= htmlspecialchars($slug, ENT_QUOTES, 'UTF-8') ?>" <?= $filtroSituacao === $slug ? 'selected' : '' ?>>
                <?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?>
              </option>
            <?php endforeach; ?>
          </select>
          <button class="btn btn-outline-primary" type="submit"><i class="bi bi-filter"></i></button>
        </div>
      </div>
    </form>

    <div class="table-responsive">
      <table class="table table-hover align-middle">
        <thead class="table-light">
          <tr>
            <th class="text-uppercase small text-muted">ID</th>
            <th class="text-uppercase small text-muted">Setor</th>
            <th class="text-uppercase small text-muted">Tarefa</th>
            <th class="text-uppercase small text-muted">Criado por</th>
            <th class="text-uppercase small text-muted">Responsável</th>
            <th class="text-uppercase small text-muted">Situação</th>
            <th class="text-uppercase small text-muted">Prioridade</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($tarefas)): ?>
            <tr>
              <td colspan="7" class="text-center text-muted py-4">
                <i class="bi bi-inbox me-1"></i>Nenhuma tarefa encontrada.
              </td>
            </tr>
          <?php endif; ?>
          <?php foreach ($tarefas as $tarefa): ?>
            <?php
              $tarefaId = (int) ($tarefa['id'] ?? 0);
              $setorNome = trim((string) ($tarefa['setor_nome'] ?? ''));
              $descricao = trim((string) ($tarefa['tarefa'] ?? ''));
              $criadoPorNome = trim((string) ($tarefa['criado_por_nome'] ?? ''));
              $responsavelNome = trim((string) ($tarefa['responsavel_nome'] ?? ''));
              $situacaoLabel = (string) ($tarefa['situacao_label'] ?? 'Criada');
              $situacaoClass = (string) ($tarefa['situacao_class'] ?? 'bg-secondary');
              $prioridadeLabel = (string) ($tarefa['prioridade_label'] ?? 'Baixa');
              $prioridadeClass = (string) ($tarefa['prioridade_class'] ?? 'bg-success');
            ?>
            <tr>
              <td>
                <a class="fw-semibold text-decoration-none" href="/admin/tarefas/editar?id=<?= $tarefaId ?>">
                  #<?= $tarefaId ?>
                </a>
              </td>
              <td><?= htmlspecialchars($setorNome !== '' ? $setorNome : '-', ENT_QUOTES, 'UTF-8') ?></td>
              <td><?= htmlspecialchars($descricao !== '' ? $descricao : '-', ENT_QUOTES, 'UTF-8') ?></td>
              <td><?= htmlspecialchars($criadoPorNome !== '' ? $criadoPorNome : '-', ENT_QUOTES, 'UTF-8') ?></td>
              <td><?= htmlspecialchars($responsavelNome !== '' ? $responsavelNome : '-', ENT_QUOTES, 'UTF-8') ?></td>
              <td>
                <span class="badge <?= htmlspecialchars($situacaoClass, ENT_QUOTES, 'UTF-8') ?> text-uppercase">
                  <?= htmlspecialchars($situacaoLabel, ENT_QUOTES, 'UTF-8') ?>
                </span>
              </td>
              <td>
                <span class="badge <?= htmlspecialchars($prioridadeClass, ENT_QUOTES, 'UTF-8') ?>">
                  <?= htmlspecialchars($prioridadeLabel, ENT_QUOTES, 'UTF-8') ?>
                </span>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</section>
