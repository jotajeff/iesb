<?php
  $protocolo = is_array($protocolo ?? null) ? $protocolo : [];
  $mensagensView = is_array($mensagens ?? null) ? $mensagens : [];
  $id = (int) ($protocolo['id'] ?? 0);
  $statusAtual = (string) ($protocolo['status'] ?? '');
  $prioridadeAtual = (string) ($protocolo['prioridade'] ?? '');
  $responsavelId = (int) ($protocolo['id_usuario_responsavel'] ?? 0);
  $userId = (int) ($authUser['id'] ?? 0);

  $statusInfo = static function (string $status): array {
    return match ($status) {
      'ABERTO' => ['bg-info text-dark', 'Aberto'],
      'EM_ATENDIMENTO' => ['bg-warning text-dark', 'Em atendimento'],
      'RESPONDIDO' => ['bg-success', 'Respondido'],
      'ENCERRADO' => ['bg-secondary', 'Encerrado'],
      default => ['bg-light text-dark border', $status ?: '-'],
    };
  };
  $prioridadeInfo = static function (string $p): array {
    return match ($p) {
      'ALTA' => ['bg-warning text-dark', 'Alta'],
      'URGENTE' => ['bg-danger', 'Urgente'],
      default => ['bg-light text-dark border', 'Normal'],
    };
  };
  [$stClasse, $stLabel] = $statusInfo($statusAtual);
  [$prClasse, $prLabel] = $prioridadeInfo($prioridadeAtual);
  $criado = (string) ($protocolo['created_at'] ?? '');
  $criadoDt = $criado !== '' ? date_create($criado) : false;
  $atualizado = (string) ($protocolo['updated_at'] ?? '');
  $atualizadoDt = $atualizado !== '' ? date_create($atualizado) : false;
?>

<section class="container py-4">
  <div class="bg-white border rounded-3 p-4 shadow-sm">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
      <h4 class="mb-0"><i class="bi bi-chat-left-text me-2"></i>Protocolo #<?= str_pad((string) $id, 6, '0', STR_PAD_LEFT) ?></h4>
      <a class="btn btn-outline-secondary btn-sm" href="/admin/protocolos"><i class="bi bi-arrow-left me-1"></i>Voltar</a>
    </div>

    <div class="row g-3 mb-4">
      <div class="col-md-7">
        <dl class="row mb-0">
          <dt class="col-sm-4 text-muted">Assunto</dt>
          <dd class="col-sm-8"><?= htmlspecialchars((string) ($protocolo['assunto'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></dd>
          <dt class="col-sm-4 text-muted">Aluno</dt>
          <dd class="col-sm-8"><?= htmlspecialchars((string) ($protocolo['aluno_nome'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></dd>
          <dt class="col-sm-4 text-muted">E-mail</dt>
          <dd class="col-sm-8"><?= htmlspecialchars((string) ($protocolo['aluno_email'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></dd>
          <dt class="col-sm-4 text-muted">Matrícula</dt>
          <dd class="col-sm-8"><?= (int) ($protocolo['matricula_id'] ?? 0) > 0 ? '#' . (int) $protocolo['matricula_id'] : '-' ?></dd>
          <dt class="col-sm-4 text-muted">Curso</dt>
          <dd class="col-sm-8"><?= htmlspecialchars((string) ($protocolo['curso_nome'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></dd>
        </dl>
      </div>
      <div class="col-md-5">
        <dl class="row mb-0">
          <dt class="col-sm-5 text-muted">Status</dt>
          <dd class="col-sm-7"><span class="badge <?= $stClasse ?>"><?= htmlspecialchars($stLabel, ENT_QUOTES, 'UTF-8') ?></span></dd>
          <dt class="col-sm-5 text-muted">Prioridade</dt>
          <dd class="col-sm-7"><span class="badge <?= $prClasse ?>"><?= htmlspecialchars($prLabel, ENT_QUOTES, 'UTF-8') ?></span></dd>
          <dt class="col-sm-5 text-muted">Responsável</dt>
          <dd class="col-sm-7"><?= htmlspecialchars((string) ($protocolo['responsavel_nome'] ?? 'Não atribuído'), ENT_QUOTES, 'UTF-8') ?></dd>
          <dt class="col-sm-5 text-muted">Aberto em</dt>
          <dd class="col-sm-7"><?= htmlspecialchars($criadoDt ? $criadoDt->format('d/m/Y H:i') : ($criado ?: '-'), ENT_QUOTES, 'UTF-8') ?></dd>
          <dt class="col-sm-5 text-muted">Atualizado</dt>
          <dd class="col-sm-7"><?= htmlspecialchars($atualizadoDt ? $atualizadoDt->format('d/m/Y H:i') : ($atualizado ?: '-'), ENT_QUOTES, 'UTF-8') ?></dd>
        </dl>
      </div>
    </div>

    <div class="row g-3 mb-4">
      <div class="col-md-6">
        <form method="post" action="/admin/protocolos/status" class="d-flex align-items-end gap-2">
          <input type="hidden" name="id" value="<?= $id ?>">
          <div class="flex-grow-1">
            <label class="form-label small mb-1">Alterar status</label>
            <select class="form-select form-select-sm" name="status">
              <?php foreach (['ABERTO' => 'Aberto', 'EM_ATENDIMENTO' => 'Em atendimento', 'RESPONDIDO' => 'Respondido', 'ENCERRADO' => 'Encerrado'] as $val => $lbl): ?>
                <option value="<?= $val ?>" <?= $statusAtual === $val ? 'selected' : '' ?>><?= $lbl ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <button class="btn btn-sm btn-primary" type="submit">Salvar</button>
        </form>
      </div>
      <div class="col-md-6">
        <form method="post" action="/admin/protocolos/prioridade" class="d-flex align-items-end gap-2">
          <input type="hidden" name="id" value="<?= $id ?>">
          <div class="flex-grow-1">
            <label class="form-label small mb-1">Alterar prioridade</label>
            <select class="form-select form-select-sm" name="prioridade">
              <?php foreach (['NORMAL' => 'Normal', 'ALTA' => 'Alta', 'URGENTE' => 'Urgente'] as $val => $lbl): ?>
                <option value="<?= $val ?>" <?= $prioridadeAtual === $val ? 'selected' : '' ?>><?= $lbl ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <button class="btn btn-sm btn-primary" type="submit">Salvar</button>
        </form>
      </div>
      <?php if ($responsavelId !== $userId): ?>
        <div class="col-12">
          <form method="post" action="/admin/protocolos/assumir">
            <input type="hidden" name="id" value="<?= $id ?>">
            <button class="btn btn-sm btn-outline-success" type="submit"><i class="bi bi-person-check me-1"></i>Assumir protocolo</button>
          </form>
        </div>
      <?php endif; ?>
    </div>

    <h5 class="mb-3"><i class="bi bi-clock-history me-1"></i>Histórico</h5>
    <div class="d-flex flex-column gap-3 mb-4">
      <?php foreach ($mensagensView as $msg): ?>
        <?php
          $tipo = (string) ($msg['tipo'] ?? '');
          $dt = (string) ($msg['created_at'] ?? '');
          $dtFmt = $dt !== '' ? date_create($dt) : false;
          $ehSecretaria = $tipo === 'SECRETARIA';
        ?>
        <div class="d-flex <?= $ehSecretaria ? 'justify-content-end' : 'justify-content-start' ?>">
          <div class="border rounded-3 p-3 <?= $ehSecretaria ? '' : 'bg-light' ?>" style="max-width: 80%;">
            <div class="small text-muted mb-1">
              <strong><?= $ehSecretaria ? 'Secretaria' : ($tipo === 'ALUNO' ? 'Aluno' : 'Sistema') ?></strong>
              <?php if ($ehSecretaria && trim((string) ($msg['usuario_nome'] ?? '')) !== ''): ?>
                (<?= htmlspecialchars((string) $msg['usuario_nome'], ENT_QUOTES, 'UTF-8') ?>)
              <?php endif; ?>
              &middot; <?= htmlspecialchars($dtFmt ? $dtFmt->format('d/m/Y H:i') : ($dt ?: '-'), ENT_QUOTES, 'UTF-8') ?>
            </div>
            <div><?= nl2br(htmlspecialchars((string) ($msg['mensagem'] ?? ''), ENT_QUOTES, 'UTF-8')) ?></div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>

    <?php if ($statusAtual === 'ENCERRADO'): ?>
      <div class="alert alert-secondary mb-0">
        <i class="bi bi-lock me-1"></i>Protocolo encerrado. Altere o status para "Em atendimento" caso precise reabrir.
      </div>
    <?php else: ?>
      <h5 class="mb-3"><i class="bi bi-reply me-1"></i>Responder ao aluno</h5>
      <form method="post" action="/admin/protocolos/responder" class="row g-3">
        <input type="hidden" name="id" value="<?= $id ?>">
        <div class="col-12">
          <textarea class="form-control" name="mensagem" rows="4" required placeholder="Escreva a resposta ao aluno..."></textarea>
        </div>
        <div class="col-12">
          <button class="btn btn-success" type="submit"><i class="bi bi-send me-1"></i>Enviar resposta</button>
        </div>
      </form>
    <?php endif; ?>
  </div>
</section>