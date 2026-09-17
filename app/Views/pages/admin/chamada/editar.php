<?php
  $chamada = is_array($chamada ?? null) ? $chamada : [];
  $professoresView = is_array($professores ?? null) ? $professores : [];
  $id = (int) ($chamada['id'] ?? 0);
  $statusAtual = (string) ($chamada['status'] ?? 'ABERTA');
  $modoAtual = (int) ($chamada['modo'] ?? 1);
  $professorAtual = (int) ($chamada['id_usuario_professor'] ?? 0);
  $dataAula = (string) ($chamada['data_aula'] ?? '');
  $horaInicio = substr((string) ($chamada['hora_inicio'] ?? ''), 0, 5);
  $horaFim = substr((string) ($chamada['hora_fim'] ?? ''), 0, 5);
?>

<section class="container py-4">
  <div class="bg-white border rounded-3 p-4 shadow-sm">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
      <h4 class="mb-0"><i class="bi bi-pencil-square me-2"></i>Editar Chamada #<?= str_pad((string) $id, 6, '0', STR_PAD_LEFT) ?></h4>
      <div class="d-flex flex-wrap gap-2">
        <a class="btn btn-outline-primary btn-sm" href="/admin/chamadas/upload?id=<?= $id ?>"><i class="bi bi-cloud-upload me-1"></i>Anexar origem da chamada</a>
        <a class="btn btn-outline-secondary btn-sm" href="/admin/chamadas"><i class="bi bi-arrow-left me-1"></i>Voltar</a>
      </div>
    </div>

    <div class="alert alert-light border mb-3">
      <i class="bi bi-people me-1"></i><strong>Turma:</strong> <?= htmlspecialchars((string) ($chamada['turma_nome'] ?? '-'), ENT_QUOTES, 'UTF-8') ?>
      &middot; <i class="bi bi-journal-bookmark me-1"></i><strong>Disciplina:</strong> <?= htmlspecialchars((string) ($chamada['disciplina_nome'] ?? '-'), ENT_QUOTES, 'UTF-8') ?>
      <?php $origemChamada = trim((string) ($chamada['origem_chamada'] ?? '')); ?>
      <?php if ($origemChamada !== ''): ?>
        <br><i class="bi bi-file-earmark-check me-1"></i><strong>Origem:</strong>
        <a href="<?= htmlspecialchars($origemChamada, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener">Ver documento</a>
      <?php endif; ?>
    </div>

    <form method="post" action="/admin/chamadas/atualizar" class="row g-3">
      <input type="hidden" name="id" value="<?= $id ?>">

      <div class="col-md-6">
        <label class="form-label">Professor</label>
        <select class="form-select" name="id_usuario_professor">
          <option value="">Sem professor</option>
          <?php foreach ($professoresView as $prof): ?>
            <option value="<?= (int) ($prof['id'] ?? 0) ?>" <?= $professorAtual === (int) ($prof['id'] ?? 0) ? 'selected' : '' ?>>
              <?= htmlspecialchars((string) ($prof['nome'] ?? '-'), ENT_QUOTES, 'UTF-8') ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="col-md-6">
        <label class="form-label">Data da aula <span class="text-danger">*</span></label>
        <input type="date" class="form-control" name="data_aula" value="<?= htmlspecialchars($dataAula, ENT_QUOTES, 'UTF-8') ?>" required>
      </div>

      <div class="col-md-4">
        <label class="form-label">Número da aula</label>
        <input type="number" class="form-control" name="numero_aula" min="1" max="999" value="<?= (int) ($chamada['numero_aula'] ?? 0) > 0 ? (int) $chamada['numero_aula'] : '' ?>">
      </div>

      <div class="col-md-4">
        <label class="form-label">Hora de início</label>
        <input type="time" class="form-control" name="hora_inicio" value="<?= htmlspecialchars($horaInicio, ENT_QUOTES, 'UTF-8') ?>">
      </div>

      <div class="col-md-4">
        <label class="form-label">Hora de fim</label>
        <input type="time" class="form-control" name="hora_fim" value="<?= htmlspecialchars($horaFim, ENT_QUOTES, 'UTF-8') ?>">
      </div>

      <div class="col-md-4">
        <label class="form-label">Status</label>
        <select class="form-select" name="status">
          <option value="ABERTA" <?= $statusAtual === 'ABERTA' ? 'selected' : '' ?>>ABERTA</option>
          <option value="FECHADA" <?= $statusAtual === 'FECHADA' ? 'selected' : '' ?>>FECHADA</option>
          <option value="CANCELADA" <?= $statusAtual === 'CANCELADA' ? 'selected' : '' ?>>CANCELADA</option>
        </select>
      </div>

      <div class="col-md-4">
        <label class="form-label">Modo</label>
        <select class="form-select" name="modo">
          <option value="1" <?= $modoAtual === 1 ? 'selected' : '' ?>>Manual</option>
          <option value="2" <?= $modoAtual === 2 ? 'selected' : '' ?>>Automática</option>
        </select>
      </div>

      <div class="col-12">
        <label class="form-label">Conteúdo da aula</label>
        <textarea class="form-control" name="conteudo" rows="3" placeholder="Conteúdo ministrado (opcional)"><?= htmlspecialchars((string) ($chamada['conteudo'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>
      </div>

      <div class="col-12">
        <label class="form-label">Observação</label>
        <textarea class="form-control" name="observacao" rows="2" placeholder="Observações (opcional)"><?= htmlspecialchars((string) ($chamada['observacao'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>
      </div>

      <div class="col-12">
        <button class="btn btn-success" type="submit"><i class="bi bi-check-lg me-1"></i>Salvar alterações</button>
      </div>
    </form>
  </div>
</section>