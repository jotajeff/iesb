<section class="container py-4">
  <div class="bg-white border rounded-3 p-4 shadow-sm">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
      <h4 class="mb-0"><i class="bi bi-pencil-square me-2"></i>Editar Curso #<?= (int) ($course['id'] ?? 0) ?></h4>
      <a class="btn btn-outline-secondary btn-sm" href="/admin/cursos"><i class="bi bi-arrow-left me-1"></i>Voltar para lista</a>
    </div>

    <form method="post" action="/admin/cursos/atualizar" class="row g-3">
      <input type="hidden" name="id" value="<?= (int) ($course['id'] ?? 0) ?>">
      <?php $cursoCalendarioValue = (string) ($course['curso_calendario'] ?? ''); ?>
      <?php $ativoAtual = strtoupper((string) ($course['ativo'] ?? 'S')); ?>
      <?php $exibirHomeAtual = strtoupper((string) ($course['exibir_home'] ?? 'N')); ?>
      <?php $confirmadoAtual = strtoupper((string) ($course['confirmado'] ?? 'N')); ?>
      <div class="col-md-8">
        <label class="form-label">Nome</label>
        <input class="form-control" type="text" name="nome" value="<?= htmlspecialchars((string) ($course['nome'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" required>
        <small class="text-muted">Ao salvar, o slug será recalculado com base no nome.</small>
      </div>
      <div class="col-md-4">
        <label class="form-label">Modalidade</label>
        <select class="form-select" name="modalidade_id">
          <option value="">Selecione...</option>
          <?php $selMod = (int) ($course['modalidade_id'] ?? 0); ?>
          <?php foreach (($modalidades ?? []) as $m): ?>
            <option value="<?= (int) $m['id'] ?>" <?= (int)$m['id'] === $selMod ? 'selected' : '' ?>>
              <?= htmlspecialchars($m['nome']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-4">
        <label class="form-label">Nível</label>
        <select class="form-select" name="nivel_id">
          <option value="">Selecione...</option>
          <?php $selNiv = (int) ($course['nivel_id'] ?? 0); ?>
          <?php foreach (($niveis ?? []) as $n): ?>
            <option value="<?= (int) $n['id'] ?>" <?= (int)$n['id'] === $selNiv ? 'selected' : '' ?>>
              <?= htmlspecialchars($n['nome']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-4">
        <label class="form-label">Segmento</label>
        <select class="form-select" name="segmento_id">
          <option value="">Selecione...</option>
          <?php $selSeg = (int) ($course['segmento_id'] ?? 0); ?>
          <?php foreach (($segmentos ?? []) as $s): ?>
            <option value="<?= (int) $s['id'] ?>" <?= (int)$s['id'] === $selSeg ? 'selected' : '' ?>>
              <?= htmlspecialchars($s['nome']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-4">
        <label class="form-label">Carga Horária</label>
        <input class="form-control" type="number" name="carga_horaria" value="<?= (int) ($course['carga_horaria'] ?? 0) ?>">
      </div>
      <div class="col-md-4">
        <label class="form-label">Calendário</label>
        <input class="form-control" type="date" name="curso_calendario" value="<?= htmlspecialchars($cursoCalendarioValue, ENT_QUOTES, 'UTF-8') ?>">
      </div>
      <div class="col-md-4">
        <label class="form-label">Ativo</label>
        <select class="form-select" name="ativo">
          <option value="S" <?= $ativoAtual === 'S' ? 'selected' : '' ?>>Sim</option>
          <option value="N" <?= $ativoAtual === 'N' ? 'selected' : '' ?>>Não</option>
        </select>
      </div>
      <div class="col-md-4">
        <label class="form-label">Exibir na home</label>
        <select class="form-select" name="exibir_home">
          <option value="S" <?= $exibirHomeAtual === 'S' ? 'selected' : '' ?>>Sim</option>
          <option value="N" <?= $exibirHomeAtual === 'N' ? 'selected' : '' ?>>Não</option>
        </select>
      </div>
      <div class="col-md-4">
        <label class="form-label">Confirmado</label>
        <select class="form-select" name="confirmado">
          <option value="S" <?= $confirmadoAtual === 'S' ? 'selected' : '' ?>>Sim</option>
          <option value="N" <?= $confirmadoAtual === 'N' ? 'selected' : '' ?>>Não</option>
        </select>
      </div>
      <div class="col-md-4">
        <label class="form-label">Data do curso</label>
        <input class="form-control" type="text" name="data_curso" value="<?= htmlspecialchars((string) ($course['data_curso'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" placeholder="ex: 10/06/2026">
      </div>
      <div class="col-md-4">
        <label class="form-label">Horario</label>
        <input class="form-control" type="text" name="horario" value="<?= htmlspecialchars((string) ($course['horario'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" placeholder="ex: 18h30 as 21h">
      </div>
      <div class="col-md-4">
        <label class="form-label">Local</label>
        <input class="form-control" type="text" name="local_curso" value="<?= htmlspecialchars((string) ($course['local_curso'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" required>
      </div>
      <div class="col-md-8">
        <label class="form-label">Link de ingresso</label>
        <input class="form-control" type="text" name="link_ingresso" value="<?= htmlspecialchars((string) ($course['link_ingresso'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" placeholder="Link de ingresso">
      </div>
      <div class="col-12">
        <button class="btn btn-success" type="submit"><i class="bi bi-check-lg me-1"></i>Atualizar Curso</button>
      </div>
    </form>
  </div>
</section>
