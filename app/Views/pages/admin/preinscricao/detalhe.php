<?php
$preId = (int) ($pre['id'] ?? 0);
$comentariosTotal = (int) ($comentariosTotal ?? 0);
$criadoEm = trim((string) ($pre['created_at'] ?? ''));
$criadoEmFormatado = $criadoEm !== '' ? date('d/m/Y H:i', strtotime($criadoEm)) : '-';
?>
<section class="container py-4">
  <div class="bg-white border rounded-3 p-4 shadow-sm">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
      <div>
        <h4 class="mb-1"><i class="bi bi-inbox me-2"></i>Pré-inscrição #<?= $preId ?></h4>
        <p class="text-muted mb-0">Dados do formulário de pré-inscrição.</p>
      </div>
      <a class="btn btn-outline-secondary btn-sm" href="/admin/preinscricao"><i class="bi bi-arrow-left me-1"></i>Voltar</a>
    </div>

    <div class="row g-3 mb-4">
      <div class="col-lg-8">
        <div class="card border-0 shadow-sm h-100">
          <div class="card-body">
            <h5 class="mb-3"><i class="bi bi-person-fill me-2"></i>Dados do candidato</h5>
            <div class="row g-3">
              <div class="col-md-6">
                <div class="p-3 rounded-3 bg-light border">
                  <div class="text-muted small text-uppercase">Nome</div>
                  <div class="fw-semibold"><?= htmlspecialchars((string) ($pre['nome'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></div>
                </div>
              </div>
              <div class="col-md-6">
                <div class="p-3 rounded-3 bg-light border">
                  <div class="text-muted small text-uppercase">E-mail</div>
                  <div class="fw-semibold"><?= htmlspecialchars((string) ($pre['email'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></div>
                </div>
              </div>
              <div class="col-md-6">
                <div class="p-3 rounded-3 bg-light border">
                  <div class="text-muted small text-uppercase">WhatsApp</div>
                  <div class="fw-semibold">
                    <?php $wa = \App\Helpers\WhatsAppHelper::onlyDigits((string) ($pre['whatsapp'] ?? '')); ?>
                    <?php if ($wa !== ''): ?>
                      <a href="https://wa.me/<?= $wa ?>" target="_blank" class="text-decoration-none"><i class="bi bi-whatsapp me-1" style="color:#128C7E;"></i><?= htmlspecialchars(\App\Helpers\WhatsAppHelper::format((string) ($pre['whatsapp'] ?? '-')), ENT_QUOTES, 'UTF-8') ?></a>
                    <?php else: ?>
                      <?= htmlspecialchars(\App\Helpers\WhatsAppHelper::format((string) ($pre['whatsapp'] ?? '-')), ENT_QUOTES, 'UTF-8') ?>
                    <?php endif; ?>
                  </div>
                </div>
              </div>
              <div class="col-md-6">
                <div class="p-3 rounded-3 bg-light border">
                  <div class="text-muted small text-uppercase">Curso de interesse</div>
                  <div class="fw-semibold"><?= htmlspecialchars((string) ($pre['curso_nome'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></div>
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
                <div class="text-muted small">Situação</div>
                <div class="fw-semibold"><span class="badge bg-warning text-dark"><?= htmlspecialchars((string) ($pre['situacao'] ?? 'recebido'), ENT_QUOTES, 'UTF-8') ?></span></div>
              </div>
              <div class="p-3 rounded-3 bg-light border">
                <div class="text-muted small">Recebido em</div>
                <div class="fw-semibold"><?= htmlspecialchars($criadoEmFormatado, ENT_QUOTES, 'UTF-8') ?></div>
              </div>
              <div class="p-3 rounded-3 bg-light border">
                <div class="text-muted small">Localização</div>
                <div class="fw-semibold"><?= (string) ($bandeira ?? '') ?> <?= htmlspecialchars((string) ($cidade ?? '-'), ENT_QUOTES, 'UTF-8') ?>, <?= htmlspecialchars((string) ($pais ?? '-'), ENT_QUOTES, 'UTF-8') ?></div>
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
                <i class="bi bi-chat-left-text me-1"></i>Nenhum comentário ainda.
              </div>
            <?php else: ?>
              <div class="list-group list-group-flush">
                <?php foreach ($comentarios as $comentario): ?>
                  <?php
                    $comentarioTexto = (string) ($comentario['comentario'] ?? '');
                    $comentarioData = (string) ($comentario['created_at'] ?? '');
                    $comentarioDataFormatada = $comentarioData !== '' ? date('d/m/Y H:i', strtotime($comentarioData)) : '-';
                  ?>
                  <div class="list-group-item px-0">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                      <strong class="small text-muted">#<?= (int) ($comentario['id'] ?? 0) ?></strong>
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
            <form method="post" action="/admin/preinscricao/comentario" class="d-grid gap-3">
              <input type="hidden" name="pre_id" value="<?= $preId ?>">
              <div>
                <textarea class="form-control" name="comentario" rows="5" maxlength="100" required placeholder="Escreva um comentário..."></textarea>
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
