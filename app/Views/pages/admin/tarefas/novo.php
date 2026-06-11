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
$currentUser = $authUser ?? [];
?>
<section class="container py-4">
  <div class="bg-white border rounded-3 p-4 shadow-sm">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
      <div>
        <h4 class="mb-1"><i class="bi bi-plus-circle me-2"></i>Nova Tarefa</h4>
        <p class="text-muted mb-0">A tarefa será criada já com o status escolhido e aparecerá no Kanban.</p>
      </div>
      <a class="btn btn-outline-secondary btn-sm" href="/admin/tarefas"><i class="bi bi-arrow-left me-1"></i>Voltar para lista</a>
    </div>

    <form method="post" action="/admin/tarefas/salvar" class="row g-3">
      <div class="col-md-6">
        <label class="form-label">Setor <span class="text-danger">*</span></label>
        <select class="form-select" name="setor" required>
          <option value="">Selecione</option>
          <?php foreach ($setores ?? [] as $setor): ?>
            <option value="<?= (int) ($setor['id'] ?? 0) ?>"><?= htmlspecialchars((string) ($setor['setor'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="col-md-6">
        <label class="form-label">Responsável</label>
        <select class="form-select" name="responsavel">
          <option value="0">Sem responsável</option>
          <?php foreach ($usuarios ?? [] as $usuario): ?>
            <option value="<?= (int) ($usuario['id'] ?? 0) ?>"><?= htmlspecialchars((string) ($usuario['nome'] ?? '-'), ENT_QUOTES, 'UTF-8') ?> (<?= htmlspecialchars((string) ($usuario['tipo'] ?? '-'), ENT_QUOTES, 'UTF-8') ?>)</option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="col-12">
        <label class="form-label">Tarefa <span class="text-danger">*</span></label>
        <textarea class="form-control" name="tarefa" rows="4" maxlength="1000" required placeholder="Descreva a tarefa com o máximo de clareza possível"></textarea>
      </div>

      <div class="col-md-4">
        <label class="form-label">Situação inicial <span class="text-danger">*</span></label>
        <select class="form-select" name="situacao" required>
          <?php foreach ($situacoes as $valor => $label): ?>
            <option value="<?= htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8') ?>" <?= $valor === 'criada' ? 'selected' : '' ?>>
              <?= htmlspecialchars((string) $label, ENT_QUOTES, 'UTF-8') ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="col-md-4">
        <label class="form-label">Prioridade <span class="text-danger">*</span></label>
        <select class="form-select" name="prioridade" required>
          <?php foreach ($prioridades as $valor => $label): ?>
            <option value="<?= (int) $valor ?>" <?= (int) $valor === 1 ? 'selected' : '' ?>>
              <?= htmlspecialchars((string) $label, ENT_QUOTES, 'UTF-8') ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="col-md-8">
        <label class="form-label">Criado por</label>
        <input class="form-control" type="text" value="<?= htmlspecialchars((string) ($currentUser['name'] ?? 'Usuário logado'), ENT_QUOTES, 'UTF-8') ?>" readonly>
        <small class="text-muted">O vínculo com `usuarios.id` é gravado automaticamente no envio.</small>
      </div>

      <div class="col-12">
        <button class="btn btn-success" type="submit"><i class="bi bi-check-lg me-1"></i>Criar Tarefa</button>
      </div>
    </form>
  </div>
</section>
