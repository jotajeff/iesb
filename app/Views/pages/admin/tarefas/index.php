<?php
$colunas = $colunas ?? [
    'tarefa' => [],
    'execucao' => [],
    'finalizado' => [],
];

$colunaMeta = [
    'tarefa' => [
        'titulo' => 'Tarefa',
        'subtitulo' => 'Novas demandas e itens ainda não iniciados',
        'classe' => 'kanban-coluna-tarefa',
        'badge' => 'bg-warning text-dark',
        'icone' => 'bi-inbox',
    ],
    'execucao' => [
        'titulo' => 'Execução',
        'subtitulo' => 'Em andamento e também em revisão',
        'classe' => 'kanban-coluna-execucao',
        'badge' => 'bg-primary',
        'icone' => 'bi-gear-wide-connected',
    ],
    'finalizado' => [
        'titulo' => 'Finalizado',
        'subtitulo' => 'Tarefas concluídas',
        'classe' => 'kanban-coluna-finalizado',
        'badge' => 'bg-success',
        'icone' => 'bi-check2-circle',
    ],
];
$authUser = $authUser ?? [];
$isAdmin = (bool) ($isAdmin ?? ((string) ($authUser['role'] ?? $authUser['type'] ?? '') === 'admin'));
?>
<section class="container-fluid py-4 px-3 px-lg-4 tarefas-kanban-page">
  <div class="bg-white border rounded-4 p-4 shadow-sm mb-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
      <div>
        <div class="text-uppercase text-muted small fw-semibold mb-1">Gestão de tarefas</div>
        <h4 class="mb-1"><i class="bi bi-kanban me-2"></i>Kanban de tarefas</h4>
        <p class="mb-0 text-muted">Crie, acompanhe e mova as tarefas entre tarefa, execução e finalizado.</p>
      </div>
      <div class="d-flex flex-wrap align-items-center gap-2">
        <a class="btn btn-outline-secondary btn-sm" href="/admin/tarefas">
          <i class="bi bi-arrow-clockwise me-1"></i>Atualizar lista
        </a>
        <a class="btn btn-outline-primary btn-sm" href="/admin/tarefas/lista">
          <i class="bi bi-list-ul me-1"></i>Ver como lista
        </a>
        <a class="btn btn-primary btn-sm" href="/admin/tarefas/novo">
          <i class="bi bi-plus-circle me-1"></i>Nova tarefa
        </a>
      </div>
    </div>

    <?php if (!$isAdmin): ?>
      <div class="alert alert-info mt-3 mb-0">
        <i class="bi bi-info-circle me-1"></i>Você está vendo apenas as tarefas atribuídas ao seu usuário.
      </div>
    <?php endif; ?>
  </div>

  <div class="row g-3 kanban-board">
    <?php foreach (['tarefa', 'execucao', 'finalizado'] as $coluna): ?>
      <?php $meta = $colunaMeta[$coluna]; ?>
      <div class="col-12 col-lg-4">
        <div class="kanban-column <?= $meta['classe'] ?> h-100">
          <div class="kanban-column-header">
            <div class="d-flex align-items-center gap-2">
              <span class="kanban-column-icon"><i class="bi <?= $meta['icone'] ?>"></i></span>
              <div>
                <h5 class="mb-0 text-white"><?= htmlspecialchars($meta['titulo'], ENT_QUOTES, 'UTF-8') ?></h5>
                <small class="text-white-50"><?= htmlspecialchars($meta['subtitulo'], ENT_QUOTES, 'UTF-8') ?></small>
              </div>
            </div>
            <span class="badge rounded-pill <?= $meta['badge'] ?>"><?= count($colunas[$coluna] ?? []) ?></span>
          </div>

          <div class="kanban-column-body">
            <?php if (empty($colunas[$coluna])): ?>
              <div class="kanban-empty">
                <i class="bi bi-inbox"></i>
                <p class="mb-0">Nenhuma tarefa nesta etapa.</p>
              </div>
            <?php endif; ?>

            <?php foreach ($colunas[$coluna] as $tarefa): ?>
              <?php
                $tarefaId = (int) ($tarefa['id'] ?? 0);
                $setorNome = trim((string) ($tarefa['setor_nome'] ?? ''));
                $criadoPorNome = trim((string) ($tarefa['criado_por_nome'] ?? ''));
                $responsavelNome = trim((string) ($tarefa['responsavel_nome'] ?? ''));
                $situacaoLabel = (string) ($tarefa['situacao_label'] ?? 'Criada');
                $situacaoClass = (string) ($tarefa['situacao_class'] ?? 'bg-secondary');
                $prioridadeLabel = (string) ($tarefa['prioridade_label'] ?? 'Baixa');
                $prioridadeClass = (string) ($tarefa['prioridade_class'] ?? 'bg-success');
                $comentariosTotal = (int) ($tarefa['comentarios_total'] ?? 0);
                $criadoEm = trim((string) ($tarefa['created_at'] ?? ''));
                $criadoEmFormatado = $criadoEm !== '' ? date('d/m/Y H:i', strtotime($criadoEm)) : '-';
              ?>
              <article class="card kanban-card shadow-sm border-0 mb-3">
                <div class="card-body p-3">
                  <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                    <div>
                      <a class="text-decoration-none text-muted small fw-semibold" href="/admin/tarefas/show?id=<?= $tarefaId ?>">
                        Tarefa #<?= $tarefaId ?>
                      </a>
                      <h6 class="card-title mb-1"><?= htmlspecialchars((string) ($tarefa['tarefa'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></h6>
                    </div>
                    <div class="d-flex flex-column align-items-end gap-2">
                      <span class="badge <?= $situacaoClass ?> text-uppercase"><?= htmlspecialchars($situacaoLabel, ENT_QUOTES, 'UTF-8') ?></span>
                      <span class="badge <?= $prioridadeClass ?>">Prioridade: <?= htmlspecialchars($prioridadeLabel, ENT_QUOTES, 'UTF-8') ?></span>
                    </div>
                  </div>

                  <div class="kanban-meta">
                    <div class="kanban-meta-item">
                      <span class="kanban-meta-label">Setor</span>
                      <strong><?= htmlspecialchars($setorNome !== '' ? $setorNome : '-', ENT_QUOTES, 'UTF-8') ?></strong>
                    </div>
                    <div class="kanban-meta-item">
                      <span class="kanban-meta-label">Criado por</span>
                      <strong><?= htmlspecialchars($criadoPorNome !== '' ? $criadoPorNome : '-', ENT_QUOTES, 'UTF-8') ?></strong>
                    </div>
                    <div class="kanban-meta-item">
                      <span class="kanban-meta-label">Responsável</span>
                      <strong><?= htmlspecialchars($responsavelNome !== '' ? $responsavelNome : '-', ENT_QUOTES, 'UTF-8') ?></strong>
                    </div>
                  </div>

                  <div class="kanban-card-actions mt-3">
                    <a class="btn btn-outline-primary btn-sm w-100 mb-2" href="/admin/tarefas/editar?id=<?= $tarefaId ?>">
                      <i class="bi bi-pencil-square me-1"></i>Atualizar tarefa
                    </a>
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 text-muted small">
                      <span><i class="bi bi-clock me-1"></i><?= htmlspecialchars($criadoEmFormatado, ENT_QUOTES, 'UTF-8') ?></span>
                      <a class="text-decoration-none small" href="/admin/tarefas/show?id=<?= $tarefaId ?>">
                        <i class="bi bi-chat-dots me-1"></i>
                        <?= $comentariosTotal > 0 ? $comentariosTotal . ' comentários' : 'Incluir primeiro comentário' ?>
                      </a>
                    </div>
                  </div>
                </div>
              </article>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</section>

<style>
.tarefas-kanban-page {
  background:
    radial-gradient(circle at top left, rgba(239, 192, 43, 0.12), transparent 28%),
    radial-gradient(circle at top right, rgba(13, 110, 253, 0.11), transparent 25%),
    linear-gradient(180deg, rgba(255, 255, 255, 0.18), rgba(255, 255, 255, 0));
}

.kanban-column {
  border-radius: 1.25rem;
  border: 1px solid rgba(77, 79, 78, 0.12);
  background: rgba(255, 255, 255, 0.72);
  backdrop-filter: blur(6px);
  overflow: hidden;
  box-shadow: 0 12px 30px rgba(77, 79, 78, 0.08);
}

.kanban-coluna-tarefa {
  background: linear-gradient(180deg, rgba(255, 248, 220, 0.92), rgba(255, 255, 255, 0.92));
}

.kanban-coluna-execucao {
  background: linear-gradient(180deg, rgba(226, 238, 255, 0.92), rgba(255, 255, 255, 0.92));
}

.kanban-coluna-finalizado {
  background: linear-gradient(180deg, rgba(227, 247, 232, 0.92), rgba(255, 255, 255, 0.92));
}

.kanban-column-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 1rem;
  padding: 1rem 1rem 0.9rem;
  background: rgba(31, 41, 55, 0.92);
  border-bottom: 1px solid rgba(255, 255, 255, 0.08);
}

.kanban-column-icon {
  width: 42px;
  height: 42px;
  border-radius: 12px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  color: #fff;
  background: rgba(255, 255, 255, 0.12);
  flex-shrink: 0;
}

.kanban-column-body {
  padding: 1rem;
  min-height: 24rem;
}

.kanban-card {
  border-radius: 1rem;
  border-left: 5px solid var(--primary);
  transition: transform 0.18s ease, box-shadow 0.18s ease;
}

.kanban-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 12px 28px rgba(77, 79, 78, 0.12);
}

.kanban-meta {
  display: grid;
  gap: 0.55rem;
}

.kanban-meta-item {
  display: flex;
  flex-direction: column;
  gap: 0.15rem;
  padding: 0.65rem 0.75rem;
  border-radius: 0.85rem;
  background: rgba(77, 79, 78, 0.035);
}

.kanban-meta-label {
  font-size: 0.72rem;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  color: var(--text-secondary);
}

.kanban-empty {
  min-height: 11rem;
  border: 1px dashed rgba(77, 79, 78, 0.16);
  border-radius: 1rem;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  color: var(--text-secondary);
  background: rgba(255, 255, 255, 0.45);
}

.kanban-empty i {
  font-size: 1.7rem;
  opacity: 0.7;
}

@media (max-width: 991.98px) {
  .kanban-column-body {
    min-height: auto;
  }
}
</style>
