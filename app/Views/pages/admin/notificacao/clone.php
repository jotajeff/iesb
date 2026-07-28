<section class="container py-4">
  <div class="bg-white border rounded-3 p-4 shadow-sm">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
      <h4 class="mb-0"><i class="bi bi-copy me-2"></i>Clonar Notificação</h4>
      <a class="btn btn-outline-secondary btn-sm" href="/admin/notificacoes"><i class="bi bi-arrow-left me-1"></i>Voltar</a>
    </div>

    <div class="card bg-light mb-4">
      <div class="card-body">
        <p class="mb-1 text-muted"><strong>Original:</strong> <?= htmlspecialchars((string) ($notificacao['titulo'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p>
        <p class="mb-0 text-muted small">Os dados abaixo foram pré-preenchidos. Escolha um novo destino para enviar.</p>
      </div>
    </div>

    <form method="post" action="/admin/notificacoes/salvar">
      <div class="mb-3">
        <label class="form-label">Título <span class="text-danger">*</span></label>
        <input class="form-control" name="titulo" required maxlength="150" value="<?= htmlspecialchars((string) ($notificacao['titulo'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
      </div>
      <div class="mb-3">
        <label class="form-label">Mensagem <span class="text-danger">*</span></label>
        <textarea class="form-control" name="mensagem" rows="4" required><?= htmlspecialchars((string) ($notificacao['mensagem'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>
      </div>
      <div class="mb-3">
        <label class="form-label">Novo destino <span class="text-danger">*</span></label>
        <select class="form-select" name="destino" required>
          <option value="" disabled selected>Selecione um destino...</option>
          <?php if (!empty($turmas)): ?>
          <optgroup label="Turmas ativas">
            <?php foreach ($turmas as $t): ?>
              <option value="turma:<?= (int) $t['id'] ?>"><?= htmlspecialchars((string) ($t['nome'] ?? ''), ENT_QUOTES, 'UTF-8') ?></option>
            <?php endforeach; ?>
          </optgroup>
          <?php endif; ?>
          <?php if (!empty($professores)): ?>
          <optgroup label="Professores ativos">
            <?php foreach ($professores as $p): ?>
              <option value="professor:<?= (int) $p['id'] ?>"><?= htmlspecialchars((string) ($p['nome'] ?? ''), ENT_QUOTES, 'UTF-8') ?></option>
            <?php endforeach; ?>
          </optgroup>
          <?php endif; ?>
        </select>
        <?php
          $tipoOrig = (string) ($notificacao['tipo_destino'] ?? '');
          $labels = ['usuario' => 'Professor', 'turma' => 'Turma'];
        ?>
        <div class="form-text">O destino original (<?= $labels[$tipoOrig] ?? $tipoOrig ?>) foi removido das opções.</div>
      </div>
      <button class="btn btn-success" type="submit"><i class="bi bi-send me-1"></i>Enviar cópia</button>
    </form>
  </div>
</section>
