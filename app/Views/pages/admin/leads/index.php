<?php
$colunas = $colunas ?? [
    1 => [],
    2 => [],
    3 => [],
];

$colunaMeta = [
    1 => [
        'titulo' => 'Recebidos',
        'subtitulo' => 'Novos contatos aguardando atendimento',
        'icone' => 'bi-inbox',
        'classe' => 'leads-column--recebidos',
    ],
    2 => [
        'titulo' => 'Atendidos',
        'subtitulo' => 'Contatos em atendimento ou qualificação',
        'icone' => 'bi-chat-left-text',
        'classe' => 'leads-column--atendidos',
    ],
    3 => [
        'titulo' => 'Encerrados',
        'subtitulo' => 'Atendimentos concluídos',
        'icone' => 'bi-check2-circle',
        'classe' => 'leads-column--encerrados',
    ],
];

$totalLeads = count($colunas[1] ?? []) + count($colunas[2] ?? []) + count($colunas[3] ?? []);

$ativoFiltro = (int) ($ativoFiltro ?? 1);
if (!in_array($ativoFiltro, [1, 2], true)) {
    $ativoFiltro = 1;
}
$statusLabel = $ativoFiltro === 2 ? 'leads não ativos' : 'leads ativos';
?>
<section class="container-fluid py-4 px-3 px-lg-4 leads-page">
  <div class="leads-page__header">
    <div>
      <h4 class="leads-page__title mb-1"><i class="bi bi-person-lines-fill me-2"></i>Leads</h4>
      <p class="leads-page__subtitle mb-0">Acompanhe os contatos recebidos pelo formulário da home e mova cada lead conforme o atendimento avança.</p>
    </div>
    <div class="d-flex flex-wrap align-items-center gap-2">
      <div class="leads-filter" role="group" aria-label="Filtrar leads por status">
        <a class="leads-filter__item <?= $ativoFiltro === 1 ? 'is-active' : '' ?>" href="/admin/leads?ativo=1">
          <i class="bi bi-eye me-1"></i>Leads ativos
        </a>
        <a class="leads-filter__item <?= $ativoFiltro === 2 ? 'is-active' : '' ?>" href="/admin/leads?ativo=2">
          <i class="bi bi-eye-slash me-1"></i>Leads não ativos
        </a>
      </div>
      <span class="leads-total"><strong><?= $totalLeads ?></strong> <?= $statusLabel ?></span>
      <a class="btn btn-outline-secondary btn-sm" href="/admin/leads?ativo=<?= $ativoFiltro ?>"><i class="bi bi-arrow-clockwise me-1"></i>Atualizar</a>
    </div>
  </div>

  <div class="row g-3">
    <?php foreach ([1, 2, 3] as $situacao): ?>
      <?php
      $meta = $colunaMeta[$situacao];
      $items = $colunas[$situacao] ?? [];
      ?>
      <div class="col-12 col-lg-4">
        <div class="leads-column <?= $meta['classe'] ?>">
          <div class="leads-column__header">
            <div class="d-flex align-items-center gap-2">
              <span class="leads-column__icon"><i class="bi <?= $meta['icone'] ?>"></i></span>
              <div>
                <h5 class="leads-column__title mb-0"><?= htmlspecialchars($meta['titulo'], ENT_QUOTES, 'UTF-8') ?></h5>
                <small class="leads-column__subtitle"><?= htmlspecialchars($meta['subtitulo'], ENT_QUOTES, 'UTF-8') ?></small>
              </div>
            </div>
            <span class="leads-column__count"><?= count($items) ?></span>
          </div>

          <div class="leads-column__body">
            <?php if (empty($items)): ?>
              <div class="leads-empty">
                <i class="bi bi-inbox"></i>
                <p class="mb-0">Nenhum lead nesta etapa.</p>
              </div>
            <?php endif; ?>

            <?php foreach ($items as $lead): ?>
              <?php
              $leadId = (int) ($lead['id'] ?? 0);
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
              $comentariosTotal = (int) ($lead['qtd_comentarios'] ?? 0);
              ?>
              <article class="lead-card">
                <div class="lead-card__body">
                  <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                    <div>
                      <span class="lead-card__id">Lead #<?= $leadId ?></span>
                      <h6 class="lead-card__name mb-0"><?= htmlspecialchars($nome !== '' ? $nome : '-', ENT_QUOTES, 'UTF-8') ?></h6>
                    </div>
                  </div>

                  <div class="lead-card__info">
                    <div class="lead-card__info-row">
                      <span class="lead-card__label"><i class="bi bi-whatsapp me-1"></i>Telefone</span>
                      <span class="lead-card__value">
                        <?php if ($whatsapp !== ''): ?>
                          <a href="https://wa.me/<?= htmlspecialchars($whatsapp, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener noreferrer"><?= htmlspecialchars(\App\Helpers\WhatsAppHelper::format($telefone), ENT_QUOTES, 'UTF-8') ?></a>
                        <?php else: ?>
                          <?= htmlspecialchars($telefone !== '' ? $telefone : '-', ENT_QUOTES, 'UTF-8') ?>
                        <?php endif; ?>
                      </span>
                    </div>
                    <div class="lead-card__info-row">
                      <span class="lead-card__label"><i class="bi bi-bookmark-star me-1"></i>Interesse</span>
                      <span class="lead-card__value"><?= htmlspecialchars($interesse !== '' ? $interesse : '-', ENT_QUOTES, 'UTF-8') ?></span>
                    </div>
                    <div class="lead-card__info-row">
                      <span class="lead-card__label"><i class="bi bi-clock me-1"></i>Recebido</span>
                      <span class="lead-card__value"><?= htmlspecialchars($criadoEmFormatado, ENT_QUOTES, 'UTF-8') ?></span>
                    </div>
                    <div class="lead-card__info-row">
                      <span class="lead-card__label"><i class="bi bi-geo-alt me-1"></i>Localização</span>
                      <span class="lead-card__value"><?= htmlspecialchars($bandeira, ENT_QUOTES, 'UTF-8') ?> <?= htmlspecialchars($localizacao, ENT_QUOTES, 'UTF-8') ?></span>
                    </div>
                  </div>
                </div>

                <div class="lead-card__footer">
                  <span class="lead-card__comments">
                    <i class="bi bi-chat-dots me-1"></i>
                    <?= $comentariosTotal > 0 ? $comentariosTotal . ' comentário' . ($comentariosTotal > 1 ? 's' : '') : 'Sem comentários' ?>
                  </span>
                  <a class="btn btn-outline-primary btn-sm" href="/admin/leads/editar?id=<?= $leadId ?>">
                    <i class="bi bi-pencil-square me-1"></i>Editar
                  </a>
                </div>
              </article>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</section>

<style>
.leads-page {
  background:
    radial-gradient(circle at top left, rgba(239, 192, 43, 0.12), transparent 26%),
    radial-gradient(circle at top right, rgba(13, 110, 253, 0.08), transparent 24%);
}

.leads-page__header {
  display: flex;
  flex-wrap: wrap;
  justify-content: space-between;
  align-items: center;
  gap: 1rem;
  margin-bottom: 1rem;
  padding: 1.25rem 1.5rem;
  border: 1px solid rgba(77, 79, 78, 0.12);
  border-radius: 16px;
  background: #fff;
  box-shadow: 0 10px 26px rgba(77, 79, 78, 0.07);
}

.leads-page__title {
  color: var(--text-heading);
  font-weight: 800;
  letter-spacing: -0.02em;
}

.leads-page__subtitle {
  max-width: 65ch;
  color: var(--text-secondary);
  font-size: 0.92rem;
  line-height: 1.6;
}

.leads-filter {
  display: inline-flex;
  padding: 0.25rem;
  border: 1px solid rgba(77, 79, 78, 0.12);
  border-radius: 999px;
  background: #f8f8f2;
}

.leads-filter__item {
  display: inline-flex;
  align-items: center;
  padding: 0.35rem 0.7rem;
  border-radius: 999px;
  color: var(--text-secondary);
  font-size: 0.76rem;
  font-weight: 700;
  text-decoration: none;
  transition: background 0.18s ease, color 0.18s ease;
}

.leads-filter__item:hover {
  background: #fff;
  color: var(--text-heading);
}

.leads-filter__item.is-active {
  background: var(--primary);
  color: #fff;
  box-shadow: 0 4px 12px rgba(77, 79, 78, 0.16);
}

.leads-total {
  display: inline-flex;
  align-items: center;
  gap: 0.3rem;
  padding: 0.45rem 0.8rem;
  border: 1px solid rgba(77, 79, 78, 0.12);
  border-radius: 999px;
  background: #f8f8f2;
  color: var(--text-secondary);
  font-size: 0.8rem;
}

.leads-total strong {
  color: var(--text-heading);
  font-size: 1rem;
}

.leads-column {
  --leads-accent: #9a7400;
  --leads-soft: #fff8df;
  display: flex;
  flex-direction: column;
  height: 100%;
  overflow: hidden;
  border: 1px solid rgba(77, 79, 78, 0.12);
  border-radius: 16px;
  background: #fff;
  box-shadow: 0 12px 30px rgba(77, 79, 78, 0.07);
}

.leads-column--recebidos {
  --leads-accent: #9a7400;
  --leads-soft: #fff8df;
}

.leads-column--atendidos {
  --leads-accent: #0b5ed7;
  --leads-soft: #edf4ff;
}

.leads-column--encerrados {
  --leads-accent: #0f7a4a;
  --leads-soft: #eaf7ef;
}

.leads-column__header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 0.75rem;
  padding: 1rem;
  border-bottom: 1px solid rgba(77, 79, 78, 0.1);
  background: var(--leads-soft);
}

.leads-column__icon {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 40px;
  height: 40px;
  flex-shrink: 0;
  border-radius: 12px;
  background: rgba(255, 255, 255, 0.72);
  color: var(--leads-accent);
  font-size: 1.1rem;
}

.leads-column__title {
  color: #292b2a;
  font-size: 1rem;
  font-weight: 800;
}

.leads-column__subtitle {
  display: block;
  margin-top: 0.15rem;
  color: #5f6462;
  font-size: 0.74rem;
  line-height: 1.4;
}

.leads-column__count {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  min-width: 2rem;
  padding: 0.3rem 0.55rem;
  border-radius: 999px;
  background: var(--leads-accent);
  color: #fff;
  font-size: 0.78rem;
  font-weight: 800;
}

.leads-column__body {
  display: flex;
  flex: 1;
  flex-direction: column;
  gap: 0.85rem;
  min-height: 28rem;
  padding: 1rem;
}

.lead-card {
  overflow: hidden;
  border: 1px solid rgba(77, 79, 78, 0.12);
  border-radius: 14px;
  background: #fff;
  box-shadow: 0 4px 14px rgba(77, 79, 78, 0.06);
  transition: transform 0.18s ease, box-shadow 0.18s ease;
}

.lead-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 12px 26px rgba(77, 79, 78, 0.12);
}

.lead-card__body {
  padding: 1rem;
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

.lead-card__name {
  color: var(--text-heading);
  font-size: 1rem;
  font-weight: 800;
  line-height: 1.3;
  overflow-wrap: anywhere;
}

.lead-card__info {
  display: grid;
  gap: 0.55rem;
  margin-top: 0.85rem;
}

.lead-card__info-row {
  display: grid;
  grid-template-columns: 6.4rem minmax(0, 1fr);
  align-items: baseline;
  gap: 0.55rem;
  font-size: 0.82rem;
}

.lead-card__label {
  color: var(--text-secondary);
  font-weight: 600;
}

.lead-card__value {
  min-width: 0;
  color: var(--text-primary);
  overflow-wrap: anywhere;
}

.lead-card__value a {
  color: #128c7e;
  font-weight: 600;
  text-decoration: none;
}

.lead-card__value a:hover {
  text-decoration: underline;
  text-underline-offset: 2px;
}

.lead-card__footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 0.5rem;
  padding: 0.7rem 1rem;
  border-top: 1px solid rgba(77, 79, 78, 0.1);
  background: #fbfbf7;
}

.lead-card__comments {
  color: var(--text-secondary);
  font-size: 0.76rem;
}

.leads-empty {
  display: flex;
  flex: 1;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  min-height: 11rem;
  padding: 1rem;
  border: 1px dashed rgba(77, 79, 78, 0.18);
  border-radius: 14px;
  background: rgba(255, 255, 255, 0.6);
  color: var(--text-secondary);
  text-align: center;
  font-size: 0.86rem;
}

.leads-empty i {
  font-size: 1.7rem;
  opacity: 0.65;
}

@media (max-width: 991.98px) {
  .leads-column__body {
    min-height: auto;
  }
}

@media (max-width: 575.98px) {
  .leads-page__header {
    padding: 1rem;
  }

  .lead-card__info-row {
    grid-template-columns: 1fr;
    gap: 0.1rem;
  }
}
</style>
