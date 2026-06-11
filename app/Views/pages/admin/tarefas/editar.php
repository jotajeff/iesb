<?php
$situacoes = $situacoes ?? [
    'criada' => 'Criada',
    'execucao' => 'Execução',
    'finalizada' => 'Finalizada',
    'revisao' => 'Revisão',
];
$prioridades = $prioridades ?? [
    1 => 'Baixa',
    2 => 'Média',
    3 => 'Alta',
];
$tarefaId = (int) ($tarefa['id'] ?? 0);
$setorAtual = (string) ($tarefa['setor_id'] ?? '');
$responsavelAtual = (string) ($tarefa['responsavel_id'] ?? '');
$situacaoAtual = (string) ($tarefa['situacao'] ?? 'criada');
$criadoPorNome = (string) ($tarefa['criado_por_nome'] ?? '');
$prioridadeAtual = (int) ($tarefa['prioridade'] ?? 1);
?>
<section class="container py-4">
  <div class="bg-white border rounded-3 p-4 shadow-sm">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
      <div>
        <h4 class="mb-1"><i class="bi bi-pencil-square me-2"></i>Editar Tarefa #<?= $tarefaId ?></h4>
        <p class="text-muted mb-0">Atualize os dados da tarefa e altere a situação para movê-la entre as colunas.</p>
      </div>
      <a class="btn btn-outline-secondary btn-sm" href="/admin/tarefas"><i class="bi bi-arrow-left me-1"></i>Voltar para lista</a>
    </div>

    <form method="post" action="/admin/tarefas/atualizar" class="row g-3">
      <input type="hidden" name="id" value="<?= $tarefaId ?>">

      <div class="col-md-6">
        <label class="form-label">Setor <span class="text-danger">*</span></label>
        <select class="form-select" name="setor" required>
          <option value="">Selecione</option>
          <?php foreach ($setores ?? [] as $setor): ?>
            <?php $setorId = (int) ($setor['id'] ?? 0); ?>
            <option value="<?= $setorId ?>" <?= (string) $setorId === $setorAtual ? 'selected' : '' ?>>
              <?= htmlspecialchars((string) ($setor['setor'] ?? '-'), ENT_QUOTES, 'UTF-8') ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="col-md-6">
        <label class="form-label">Responsável</label>
        <select class="form-select" name="responsavel">
          <option value="0">Sem responsável</option>
          <?php foreach ($usuarios ?? [] as $usuario): ?>
            <?php $usuarioId = (int) ($usuario['id'] ?? 0); ?>
            <option value="<?= $usuarioId ?>" <?= (string) $usuarioId === $responsavelAtual ? 'selected' : '' ?>>
              <?= htmlspecialchars((string) ($usuario['nome'] ?? '-'), ENT_QUOTES, 'UTF-8') ?> (<?= htmlspecialchars((string) ($usuario['tipo'] ?? '-'), ENT_QUOTES, 'UTF-8') ?>)
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="col-12">
        <label class="form-label">Tarefa <span class="text-danger">*</span></label>
        <textarea class="form-control" name="tarefa" rows="4" maxlength="1000" required><?= htmlspecialchars((string) ($tarefa['tarefa'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>
      </div>

      <div class="col-md-4">
        <label class="form-label">Situação <span class="text-danger">*</span></label>
        <select class="form-select" name="situacao" required>
          <?php foreach ($situacoes as $valor => $label): ?>
            <option value="<?= htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8') ?>" <?= $valor === $situacaoAtual ? 'selected' : '' ?>>
              <?= htmlspecialchars((string) $label, ENT_QUOTES, 'UTF-8') ?>
            </option>
          <?php endforeach; ?>
        </select>
        <small class="text-muted">`revisao` volta para a coluna de execução no Kanban.</small>
      </div>

      <div class="col-md-4">
        <label class="form-label">Prioridade <span class="text-danger">*</span></label>
        <select class="form-select" name="prioridade" required>
          <?php foreach ($prioridades as $valor => $label): ?>
            <option value="<?= (int) $valor ?>" <?= (int) $valor === $prioridadeAtual ? 'selected' : '' ?>>
              <?= htmlspecialchars((string) $label, ENT_QUOTES, 'UTF-8') ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="col-md-8">
        <label class="form-label">Criado por</label>
        <input class="form-control" type="text" value="<?= htmlspecialchars($criadoPorNome !== '' ? $criadoPorNome : '-', ENT_QUOTES, 'UTF-8') ?>" readonly>
      </div>

      <div class="col-12">
        <button class="btn btn-success" type="submit"><i class="bi bi-check-lg me-1"></i>Atualizar tarefa</button>
        <a class="btn btn-outline-secondary ms-2" href="/admin/tarefas">Cancelar</a>
      </div>
    </form>
  </div>
</section>
