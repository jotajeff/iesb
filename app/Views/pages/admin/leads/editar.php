<?php
$leadId = (int) ($lead['id'] ?? 0);
$situacaoAtual = (int) ($lead['situacao'] ?? 1);
$situacoes = [
    1 => ['label' => 'Recebido', 'classe' => 'lead-situacao--recebido'],
    2 => ['label' => 'Atendido', 'classe' => 'lead-situacao--atendido'],
    3 => ['label' => 'Encerrado', 'classe' => 'lead-situacao--encerrado'],
];
if (!isset($situacoes[$situacaoAtual])) {
    $situacaoAtual = 1;
}
$nome = trim((string) ($lead['nome'] ?? ''));
$telefone = trim((string) ($lead['telefone'] ?? ''));
$interesse = trim((string) ($lead['interesse'] ?? ''));
$cidade = trim((string) ($lead['cidade'] ?? ''));
$pais = trim((string) ($lead['pais'] ?? ''));
$bandeira = trim((string) ($lead['bandeira'] ?? '🏳️'));
$localizacao = $pais !== '' && $pais !== '-' ? $pais : 'Não identificado';
if ($cidade !== '' && $cidade !== '-') {
    $localizacao = $cidade . ($pais !== '' && $pais !== '-' ? '/' . $pais : '');
}
$criadoEm = trim((string) ($lead['created_at'] ?? ''));
$criadoEmFormatado = $criadoEm !== '' ? date('d/m/Y H:i', strtotime($criadoEm)) : '-';
$whatsapp = \App\Helpers\WhatsAppHelper::onlyDigits($telefone);
$ativoAtual = (int) ($lead['ativo'] ?? 1);
if (!in_array($ativoAtual, [1, 2], true)) {
    $ativoAtual = 1;
}
$comentarios = $comentarios ?? [];
$comentariosTotal = (int) ($comentariosTotal ?? count($comentarios));
?>
<section class="container py-4 leads-edit-page">
  <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
      <h4 class="leads-edit-page__title mb-1"><i class="bi bi-pencil-square me-2"></i>Editar lead #<?= $leadId ?></h4>
      <p class="leads-edit-page__subtitle mb-0">Atualize a situação do atendimento e registre o histórico de contato.</p>
    </div>
    <div class="d-flex flex-wrap gap-2">
      <a class="btn btn-outline-secondary btn-sm" href="/admin/leads?ativo=<?= $ativoAtual ?>"><i class="bi bi-arrow-left me-1"></i>Voltar ao kanban</a>
      <?php if ($whatsapp !== ''): ?>
        <a class="btn btn-success btn-sm" href="https://wa.me/<?= htmlspecialchars($whatsapp, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener noreferrer">
          <i class="bi bi-whatsapp me-1"></i>Chamar no WhatsApp
        </a>
      <?php endif; ?>
    </div>
  </div>

  <div class="row g-4">
    <div class="col-12 col-lg-7">
      <div class="card border-0 shadow-sm leads-edit-card mb-4">
        <div class="card-body p-4">
          <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-3">
            <div>
              <span class="lead-card__id">Lead #<?= $leadId ?></span>
              <h5 class="leads-edit-card__name mb-0"><?= htmlspecialchars($nome !== '' ? $nome : '-', ENT_QUOTES, 'UTF-8') ?></h5>
            </div>
            <div class="d-flex flex-wrap align-items-center gap-2">
              <span class="lead-situacao <?= $situacoes[$situacaoAtual]['classe'] ?>">
                <?= htmlspecialchars($situacoes[$situacaoAtual]['label'], ENT_QUOTES, 'UTF-8') ?>
              </span>
              <?php if ($ativoAtual === 2): ?>
                <span class="badge bg-secondary"><i class="bi bi-eye-slash me-1"></i>Não ativo</span>
              <?php endif; ?>
            </div>
          </div>

          <div class="row g-3">
            <div class="col-md-6">
              <div class="lead-data-box">
                <span class="lead-data-box__label">Telefone</span>
                <span class="lead-data-box__value">
                  <?php if ($whatsapp !== ''): ?>
                    <a href="https://wa.me/<?= htmlspecialchars($whatsapp, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener noreferrer">
                      <i class="bi bi-whatsapp me-1"></i><?= htmlspecialchars(\App\Helpers\WhatsAppHelper::format($telefone), ENT_QUOTES, 'UTF-8') ?>
                    </a>
                  <?php else: ?>
                    <?= htmlspecialchars($telefone !== '' ? $telefone : '-', ENT_QUOTES, 'UTF-8') ?>
                  <?php endif; ?>
                </span>
              </div>
            </div>
            <div class="col-md-6">
              <div class="lead-data-box">
                <span class="lead-data-box__label">Interesse</span>
                <span class="lead-data-box__value"><?= htmlspecialchars($interesse !== '' ? $interesse : '-', ENT_QUOTES, 'UTF-8') ?></span>
              </div>
            </div>
            <div class="col-md-6">
              <div class="lead-data-box">
                <span class="lead-data-box__label">Recebido em</span>
                <span class="lead-data-box__value"><?= htmlspecialchars($criadoEmFormatado, ENT_QUOTES, 'UTF-8') ?></span>
              </div>
            </div>
            <div class="col-md-6">
              <div class="lead-data-box">
                <span class="lead-data-box__label">Localização</span>
                <span class="lead-data-box__value"><?= htmlspecialchars($bandeira, ENT_QUOTES, 'UTF-8') ?> <?= htmlspecialchars($localizacao, ENT_QUOTES, 'UTF-8') ?></span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="card border-0 shadow-sm leads-edit-card">
        <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center px-4 pt-4 pb-2">
          <h5 class="mb-0"><i class="bi bi-chat-dots me-2"></i>Histórico de comentários</h5>
          <span class="badge bg-secondary"><?= $comentariosTotal ?></span>
        </div>
        <div class="card-body px-4 pb-4">
          <?php if (empty($comentarios)): ?>
            <div class="alert alert-light border mb-0">
              <i class="bi bi-chat-left-text me-1"></i>Nenhum comentário registrado ainda.
            </div>
          <?php else: ?>
            <div class="lead-comments">
              <?php foreach ($comentarios as $comentario): ?>
                <?php
                $comentarioTexto = (string) ($comentario['comentario'] ?? '');
                $comentarioData = (string) ($comentario['created_at'] ?? '');
                $comentarioDataFormatada = $comentarioData !== '' ? date('d/m/Y H:i', strtotime($comentarioData)) : '-';
                ?>
                <div class="lead-comment">
                  <div class="lead-comment__marker" aria-hidden="true"></div>
                  <div class="lead-comment__content">
                    <time class="lead-comment__date"><?= htmlspecialchars($comentarioDataFormatada, ENT_QUOTES, 'UTF-8') ?></time>
                    <div class="lead-comment__text"><?= nl2br(htmlspecialchars($comentarioTexto, ENT_QUOTES, 'UTF-8')) ?></div>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <div class="col-12 col-lg-5">
      <div class="card border-0 shadow-sm leads-edit-card leads-update-card">
        <div class="card-body p-4">
          <h5 class="mb-1"><i class="bi bi-arrow-repeat me-2"></i>Atualizar atendimento</h5>
          <p class="text-muted small mb-4">A mudança de situação move o lead para a coluna correspondente no kanban. Leads não ativos ficam ocultos do filtro padrão.</p>

          <form method="post" action="/admin/leads/atualizar" class="d-grid gap-3">
            <input type="hidden" name="id" value="<?= $leadId ?>">

            <div>
              <label class="form-label fw-semibold" for="leadSituacao">Situação</label>
              <select class="form-select" id="leadSituacao" name="situacao" required>
                <?php foreach ($situacoes as $valor => $situacao): ?>
                  <option value="<?= (int) $valor ?>" <?= (int) $valor === $situacaoAtual ? 'selected' : '' ?>>
                    <?= htmlspecialchars($situacao['label'], ENT_QUOTES, 'UTF-8') ?>
                  </option>
                <?php endforeach; ?>
              </select>
              <div class="form-text">Recebido: 1 · Atendido: 2 · Encerrado: 3</div>
            </div>

            <div>
              <label class="form-label fw-semibold" for="leadAtivo">Cadastro do lead</label>
              <select class="form-select" id="leadAtivo" name="ativo" required>
                <option value="1" <?= $ativoAtual === 1 ? 'selected' : '' ?>>Ativo (exibido no kanban)</option>
                <option value="2" <?= $ativoAtual === 2 ? 'selected' : '' ?>>Não ativo (oculto do kanban)</option>
              </select>
              <div class="form-text">Leads não ativos continuam disponíveis no filtro “Leads não ativos”.</div>
            </div>

            <div>
              <label class="form-label fw-semibold" for="leadComentario">Novo comentário <span class="text-muted fw-normal">(opcional)</span></label>
              <textarea class="form-control" id="leadComentario" name="comentario" rows="6" maxlength="2000" placeholder="Registre o retorno do contato, próximos passos ou observações..."></textarea>
              <div class="form-text">O comentário será anexado ao histórico deste lead.</div>
            </div>

            <div class="d-grid gap-2">
              <button class="btn btn-success" type="submit"><i class="bi bi-check-lg me-1"></i>Salvar alterações</button>
              <a class="btn btn-outline-secondary" href="/admin/leads?ativo=<?= $ativoAtual ?>">Cancelar</a>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</section>

<style>
.leads-edit-page__title {
  color: var(--text-heading);
  font-weight: 800;
  letter-spacing: -0.02em;
}

.leads-edit-page__subtitle {
  color: var(--text-secondary);
  font-size: 0.92rem;
}

.leads-edit-card {
  border-radius: 16px;
  background: #fff;
}

.lead-card__id {
  display: block;
  margin-bottom: 0.2rem;
  color: var(--text-secondary);
  font-size: 0.68rem;
  font-weight: 800;
  letter-spacing: 0.07em;
  text-transform: uppercase;
}

.leads-edit-card__name {
  color: var(--text-heading);
  font-size: 1.2rem;
  font-weight: 800;
  line-height: 1.3;
}

.lead-situacao {
  display: inline-flex;
  align-items: center;
  padding: 0.4rem 0.75rem;
  border-radius: 999px;
  font-size: 0.76rem;
  font-weight: 800;
  letter-spacing: 0.02em;
}

.lead-situacao--recebido {
  background: #fff3c4;
  color: #7a5a00;
}

.lead-situacao--atendido {
  background: #dbeafe;
  color: #0b4db3;
}

.lead-situacao--encerrado {
  background: #d8f3e2;
  color: #0b6a40;
}

.lead-data-box {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
  height: 100%;
  padding: 0.9rem;
  border: 1px solid rgba(77, 79, 78, 0.1);
  border-radius: 12px;
  background: #f8f8f2;
}

.lead-data-box__label {
  color: var(--text-secondary);
  font-size: 0.7rem;
  font-weight: 800;
  letter-spacing: 0.06em;
  text-transform: uppercase;
}

.lead-data-box__value {
  color: var(--text-primary);
  font-size: 0.9rem;
  overflow-wrap: anywhere;
}

.lead-data-box__value a {
  color: #128c7e;
  font-weight: 600;
  text-decoration: none;
}

.lead-data-box__value a:hover {
  text-decoration: underline;
  text-underline-offset: 2px;
}

.lead-comments {
  display: grid;
  gap: 1rem;
}

.lead-comment {
  position: relative;
  display: grid;
  grid-template-columns: 0.85rem minmax(0, 1fr);
  gap: 0.75rem;
}

.lead-comment__marker {
  width: 0.7rem;
  height: 0.7rem;
  margin-top: 0.28rem;
  border: 2px solid var(--primary);
  border-radius: 50%;
  background: #fff;
}

.lead-comment:not(:last-child) .lead-comment__marker::after {
  content: "";
  position: absolute;
  top: 1rem;
  left: 0.25rem;
  width: 2px;
  height: calc(100% + 0.3rem);
  background: rgba(77, 79, 78, 0.12);
}

.lead-comment__content {
  padding: 0.8rem 0.95rem;
  border: 1px solid rgba(77, 79, 78, 0.1);
  border-radius: 12px;
  background: #fbfbf7;
}

.lead-comment__date {
  display: block;
  margin-bottom: 0.35rem;
  color: var(--text-secondary);
  font-size: 0.72rem;
  font-weight: 700;
}

.lead-comment__text {
  color: var(--text-primary);
  font-size: 0.9rem;
  line-height: 1.6;
  overflow-wrap: anywhere;
}

.leads-update-card {
  position: sticky;
  top: 1rem;
}

@media (max-width: 991.98px) {
  .leads-update-card {
    position: static;
  }
}
</style>
