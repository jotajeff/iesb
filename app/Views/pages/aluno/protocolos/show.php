<?php
  $protocolo = is_array($protocolo ?? null) ? $protocolo : [];
  $mensagensView = is_array($mensagens ?? null) ? $mensagens : [];
  $id = (int) ($protocolo['id'] ?? 0);
  $statusAtual = (string) ($protocolo['status'] ?? '');
  $encerrado = $statusAtual === 'ENCERRADO';

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
  [$prClasse, $prLabel] = $prioridadeInfo((string) ($protocolo['prioridade'] ?? ''));
  $criado = (string) ($protocolo['created_at'] ?? '');
  $criadoDt = $criado !== '' ? date_create($criado) : false;
  $atualizado = (string) ($protocolo['updated_at'] ?? '');
  $atualizadoDt = $atualizado !== '' ? date_create($atualizado) : false;
?>

<section class="py-4" style="margin-top: 20px;">
  <div class="container">
    <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 16px; padding: 2rem; box-shadow: var(--card-shadow);">
      <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <h4 class="mb-0"><i class="bi bi-chat-left-text me-2"></i>Protocolo #<?= str_pad((string) $id, 6, '0', STR_PAD_LEFT) ?></h4>
        <a class="btn btn-outline-secondary btn-sm" href="/aluno/protocolos"><i class="bi bi-arrow-left me-1"></i>Voltar</a>
      </div>

      <div class="row g-3 mb-4">
        <div class="col-md-8">
          <dl class="row mb-0">
            <dt class="col-sm-3 text-muted">Assunto</dt>
            <dd class="col-sm-9"><?= htmlspecialchars((string) ($protocolo['assunto'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></dd>
            <dt class="col-sm-3 text-muted">Aluno</dt>
            <dd class="col-sm-9"><?= htmlspecialchars((string) ($protocolo['aluno_nome'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></dd>
            <dt class="col-sm-3 text-muted">Matrícula</dt>
            <dd class="col-sm-9"><?= (int) ($protocolo['matricula_id'] ?? 0) > 0 ? '#' . (int) $protocolo['matricula_id'] : '-' ?></dd>
            <dt class="col-sm-3 text-muted">Curso</dt>
            <dd class="col-sm-9"><?= htmlspecialchars((string) ($protocolo['curso_nome'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></dd>
          </dl>
        </div>
        <div class="col-md-4">
          <dl class="row mb-0">
            <dt class="col-sm-5 text-muted">Status</dt>
            <dd class="col-sm-7"><span class="badge <?= $stClasse ?>"><?= htmlspecialchars($stLabel, ENT_QUOTES, 'UTF-8') ?></span></dd>
            <dt class="col-sm-5 text-muted">Prioridade</dt>
            <dd class="col-sm-7"><span class="badge <?= $prClasse ?>"><?= htmlspecialchars($prLabel, ENT_QUOTES, 'UTF-8') ?></span></dd>
            <dt class="col-sm-5 text-muted">Aberto em</dt>
            <dd class="col-sm-7"><?= htmlspecialchars($criadoDt ? $criadoDt->format('d/m/Y H:i') : ($criado ?: '-'), ENT_QUOTES, 'UTF-8') ?></dd>
            <dt class="col-sm-5 text-muted">Atualizado</dt>
            <dd class="col-sm-7"><?= htmlspecialchars($atualizadoDt ? $atualizadoDt->format('d/m/Y H:i') : ($atualizado ?: '-'), ENT_QUOTES, 'UTF-8') ?></dd>
          </dl>
        </div>
      </div>

      <h5 class="mb-3"><i class="bi bi-clock-history me-1"></i>Histórico</h5>
      <div class="d-flex flex-column gap-3 mb-4">
        <?php foreach ($mensagensView as $msg): ?>
          <?php
            $tipo = (string) ($msg['tipo'] ?? '');
            $dt = (string) ($msg['created_at'] ?? '');
            $dtFmt = $dt !== '' ? date_create($dt) : false;
            $ehAluno = $tipo === 'ALUNO';
          ?>
          <div class="d-flex <?= $ehAluno ? 'justify-content-end' : 'justify-content-start' ?>">
            <div class="border rounded-3 p-3 <?= $ehAluno ? '' : 'bg-light' ?>" style="max-width: 80%;">
              <div class="small text-muted mb-1">
                <strong><?= $ehAluno ? 'Você' : ($tipo === 'SECRETARIA' ? 'Secretaria' : 'Sistema') ?></strong>
                &middot; <?= htmlspecialchars($dtFmt ? $dtFmt->format('d/m/Y H:i') : ($dt ?: '-'), ENT_QUOTES, 'UTF-8') ?>
              </div>
              <div><?= nl2br(htmlspecialchars((string) ($msg['mensagem'] ?? ''), ENT_QUOTES, 'UTF-8')) ?></div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>

      <?php if ($encerrado): ?>
        <div class="alert alert-secondary mb-0">
          <i class="bi bi-lock me-1"></i>Este protocolo está encerrado. Caso necessário, abra um novo protocolo.
        </div>
      <?php else: ?>
        <h5 class="mb-3"><i class="bi bi-reply me-1"></i>Nova mensagem</h5>
        <form method="post" action="/aluno/protocolos/mensagem" class="row g-3">
          <input type="hidden" name="id" value="<?= $id ?>">
          <div class="col-12">
            <textarea class="form-control" name="mensagem" rows="4" required placeholder="Escreva sua mensagem..."></textarea>
          </div>
          <div class="col-12">
            <button class="btn btn-primary" type="submit"><i class="bi bi-send me-1"></i>Enviar mensagem</button>
          </div>
        </form>
      <?php endif; ?>
    </div>
  </div>
</section>