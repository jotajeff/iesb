<?php
$tarefaId = (int) ($tarefa['id'] ?? 0);
$situacaoLabel = (string) ($tarefa['situacao_label'] ?? 'Criada');
$situacaoClass = (string) ($tarefa['situacao_class'] ?? 'bg-secondary');
$prioridadeLabel = (string) ($tarefa['prioridade_label'] ?? 'Baixa');
$prioridadeClass = (string) ($tarefa['prioridade_class'] ?? 'bg-success');
$comentariosTotal = (int) ($tarefa['comentarios_total'] ?? 0);
$criadoEm = trim((string) ($tarefa['criado_em'] ?? ''));
$criadoEmFormatado = $criadoEm !== '' ? date('d/m/Y H:i', strtotime($criadoEm)) : '-';
?>
<section class="container py-4">
  <div class="bg-white border rounded-3 p-4 shadow-sm">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
      <div>
        <h4 class="mb-1"><i class="bi bi-card-text me-2"></i>Detalhes da Tarefa #<?= $tarefaId ?></h4>
        <p class="text-muted mb-0">Visualização completa com comentários e dados de acompanhamento.</p>
      </div>
      <div class="d-flex flex-wrap gap-2">
        <a class="btn btn-outline-secondary btn-sm" href="/admin/tarefas"><i class="bi bi-arrow-left me-1"></i>Voltar para lista</a>
        <a class="btn btn-primary btn-sm" href="/admin/tarefas/editar?id=<?= $tarefaId ?>"><i class="bi bi-pencil-square me-1"></i>Editar tarefa</a>
      </div>
    </div>

    <div class="row g-3 mb-4">
      <div class="col-lg-8">
        <div class="card border-0 shadow-sm h-100">
          <div class="card-body">
            <div class="d-flex flex-wrap gap-2 mb-3">
              <span class="badge <?= $situacaoClass ?> text-uppercase"><?= htmlspecialchars($situacaoLabel, ENT_QUOTES, 'UTF-8') ?></span>
              <span class="badge <?= $prioridadeClass ?>">Prioridade: <?= htmlspecialchars($prioridadeLabel, ENT_QUOTES, 'UTF-8') ?></span>
              <span class="badge bg-dark">#<?= $tarefaId ?></span>
            </div>

            <h5 class="mb-3"><?= htmlspecialchars((string) ($tarefa['tarefa'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></h5>

            <div class="row g-3">
              <div class="col-md-6">
                <div class="p-3 rounded-3 bg-light border">
                  <div class="text-muted small text-uppercase">Setor</div>
                  <div class="fw-semibold"><?= htmlspecialchars((string) ($tarefa['setor_nome'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></div>
                </div>
              </div>
              <div class="col-md-6">
                <div class="p-3 rounded-3 bg-light border">
                  <div class="text-muted small text-uppercase">Criado por</div>
                  <div class="fw-semibold"><?= htmlspecialchars((string) ($tarefa['criado_por_nome'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></div>
                </div>
              </div>
              <div class="col-md-6">
                <div class="p-3 rounded-3 bg-light border">
                  <div class="text-muted small text-uppercase">Responsável</div>
                  <div class="fw-semibold"><?= htmlspecialchars((string) ($tarefa['responsavel_nome'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></div>
                </div>
              </div>
              <div class="col-md-6">
                <div class="p-3 rounded-3 bg-light border">
                  <div class="text-muted small text-uppercase">Criado em</div>
                  <div class="fw-semibold"><?= htmlspecialchars($criadoEmFormatado, ENT_QUOTES, 'UTF-8') ?></div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="col-lg-4">
        <div class="card border-0 shadow-sm h-100">
          <div class="card-body">
            <div class="text-uppercase text-muted small fw-semibold mb-2">Resumo</div>
            <div class="d-grid gap-3">
              <div class="p-3 rounded-3 bg-light border">
                <div class="text-muted small">Comentários</div>
                <div class="fw-bold fs-4"><?= $comentariosTotal ?></div>
                <div class="text-muted small"><?= $comentariosTotal > 0 ? 'Já existem comentários registrados.' : 'Incluir primeiro comentário.' ?></div>
              </div>
              <div class="p-3 rounded-3 bg-light border">
                <div class="text-muted small">Situação</div>
                <div class="fw-semibold"><?= htmlspecialchars($situacaoLabel, ENT_QUOTES, 'UTF-8') ?></div>
              </div>
              <div class="p-3 rounded-3 bg-light border">
                <div class="text-muted small">Prioridade</div>
                <div class="fw-semibold"><?= htmlspecialchars($prioridadeLabel, ENT_QUOTES, 'UTF-8') ?></div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="row g-3">
      <div class="col-lg-7">
        <div class="card border-0 shadow-sm h-100">
          <div class="card-header bg-white border-0 pt-3 px-3">
            <div class="d-flex align-items-center justify-content-between">
              <h5 class="mb-0"><i class="bi bi-chat-dots me-2"></i>Comentários</h5>
              <span class="badge bg-secondary"><?= $comentariosTotal ?></span>
            </div>
          </div>
          <div class="card-body pt-0">
            <?php if (empty($comentarios)): ?>
              <div class="alert alert-info mb-0">
                <i class="bi bi-chat-left-text me-1"></i>Incluir primeiro comentário.
              </div>
            <?php else: ?>
              <div class="list-group list-group-flush">
                <?php foreach ($comentarios as $comentario): ?>
                  <?php
                    $comentarioTexto = (string) ($comentario['comentario'] ?? '');
                    $comentarioData = (string) ($comentario['criado_em'] ?? '');
                    $comentarioDataFormatada = $comentarioData !== '' ? date('d/m/Y H:i', strtotime($comentarioData)) : '-';
                  ?>
                  <div class="list-group-item px-0">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                      <strong class="small text-muted">Comentário #<?= (int) ($comentario['id'] ?? 0) ?></strong>
                      <small class="text-muted"><?= htmlspecialchars($comentarioDataFormatada, ENT_QUOTES, 'UTF-8') ?></small>
                    </div>
                    <div><?= nl2br(htmlspecialchars($comentarioTexto, ENT_QUOTES, 'UTF-8')) ?></div>
                  </div>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>
          </div>
        </div>
      </div>

      <div class="col-lg-5">
        <div class="card border-0 shadow-sm h-100">
          <div class="card-header bg-white border-0 pt-3 px-3">
            <h5 class="mb-0"><i class="bi bi-plus-circle me-2"></i>Adicionar comentário</h5>
          </div>
          <div class="card-body">
            <form method="post" action="/admin/tarefas/comentario" class="d-grid gap-3">
              <input type="hidden" name="tarefa_id" value="<?= $tarefaId ?>">
              <div>
                <label class="form-label">Comentário</label>
                <textarea class="form-control" name="comentario" rows="7" maxlength="100" required placeholder="Escreva um comentário curto para a tarefa"></textarea>
                <small class="text-muted">A tabela `comentarios` é reaproveitável via `tabela_fg`.</small>
              </div>
              <div>
                <button class="btn btn-success" type="submit"><i class="bi bi-send me-1"></i>Salvar comentário</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
