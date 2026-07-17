<?php
$curso = $curso ?? [];
$detalhe = $detalhe ?? null;
$pagamentos = $pagamentos ?? [];
$dateText = $dateText ?? '-';
$isConfirmed = $isConfirmed ?? false;
$linkIngresso = $linkIngresso ?? '';
$isExternalLink = $isExternalLink ?? false;
$img = trim((string) ($curso['imagem_card'] ?? ''));
?>
<section class="hero-section" id="home" style="min-height:50vh;">
  <div class="hero-bg"></div>
  <div class="container hero-content">
    <div class="row justify-content-center">
      <div class="col-lg-10 text-center courses-hero-copy" data-aos="fade-up">
        <?php if ($isConfirmed): ?>
          <div class="hero-badge"><i class="bi bi-award-fill"></i> Confirmado</div>
        <?php endif; ?>
        <h1 class="hero-title"><?= htmlspecialchars((string) ($curso['nome'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></h1>
        <p class="hero-subtitle"><?= htmlspecialchars((string) ($curso['local_curso'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></p>
      </div>
    </div>
  </div>
</section>

<section class="py-5">
  <div class="container">
    <div class="row g-4">
      <div class="col-lg-8">
        <?php if ($img !== ''): ?>
          <div class="mb-4">
            <img src="/<?= htmlspecialchars($img, ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars((string) ($curso['nome'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" class="img-fluid rounded-4 shadow-sm w-100" style="max-height:400px;object-fit:cover;">
          </div>
        <?php endif; ?>

        <?php if ($detalhe && trim((string) ($detalhe['detalhe'] ?? '')) !== ''): ?>
          <div class="bg-white border rounded-4 p-4 shadow-sm mb-4">
            <h5 class="mb-3"><i class="bi bi-journal-text me-2"></i>Sobre o curso</h5>
            <div class="curso-detalhe-content" id="detalheCurso"><?= $detalhe['detalhe'] ?></div>
            <button class="btn btn-sm btn-outline-primary mt-3" id="btnVerMais" onclick="toggleDetalhe()">
              <i class="bi bi-chevron-down me-1"></i>Ver mais
            </button>
          </div>
        <?php endif; ?>
      </div>

      <div class="col-lg-4">
        <div class="bg-white border rounded-4 p-3 shadow-sm mb-3">
          <h6 class="mb-2 small fw-bold text-uppercase text-muted"><i class="bi bi-info-circle me-1"></i>Informações</h6>
          <ul class="list-unstyled mb-0 small">
            <li class="d-flex align-items-center gap-2 mb-1">
              <i class="bi bi-calendar-event text-primary"></i>
              <span><strong>Data:</strong> <?= htmlspecialchars($dateText, ENT_QUOTES, 'UTF-8') ?></span>
            </li>
            <li class="d-flex align-items-center gap-2 mb-1">
              <i class="bi bi-clock text-primary"></i>
              <span><strong>Horário:</strong> <?= htmlspecialchars((string) ($curso['horario'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></span>
            </li>
            <li class="d-flex align-items-center gap-2 mb-1">
              <i class="bi bi-geo-alt text-primary"></i>
              <span><strong>Local:</strong> <?= htmlspecialchars((string) ($curso['local_curso'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></span>
            </li>
            <li class="d-flex align-items-center gap-2 mb-1">
              <i class="bi bi-clock-history text-primary"></i>
              <span><strong>Carga horária:</strong> <?= (int) ($curso['carga_horaria'] ?? 0) ?>h</span>
            </li>
            <?php $segmentoNome = (string) ($curso['segmento_nome'] ?? ''); ?>
            <?php if ($segmentoNome !== ''): ?>
              <li class="d-flex align-items-center gap-2 mb-1">
                <i class="bi bi-tag text-primary"></i>
                <span><strong>Segmento:</strong> <?= htmlspecialchars($segmentoNome, ENT_QUOTES, 'UTF-8') ?></span>
              </li>
            <?php endif; ?>
            <?php if ($isConfirmed): ?>
              <li class="d-flex align-items-center gap-2">
                <i class="bi bi-check-circle-fill text-success"></i>
                <span class="text-success fw-semibold">Curso confirmado</span>
              </li>
            <?php endif; ?>
          </ul>
        </div>

        <div class="curso-sidebar">
          <?php if (!empty($pagamentos)): ?>
            <div class="curso-pagamento-box">
              <h5 class="curso-pagamento-titulo"><i class="bi bi-currency-dollar me-2"></i>Planos de pagamento</h5>
              <?php foreach ($pagamentos as $p): ?>
                <div class="curso-pagamento-item">
                  <div class="d-flex justify-content-between align-items-center">
                    <strong class="small"><?= htmlspecialchars((string) ($p['descricao'] ?? ''), ENT_QUOTES, 'UTF-8') ?></strong>
                    <span class="badge bg-secondary" style="font-size:.65rem;"><?= htmlspecialchars((string) ($p['tipo'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span>
                  </div>
                  <div class="d-flex justify-content-between align-items-center mt-1">
                    <span class="text-muted small"><?= (int) ($p['parcelas'] ?? 1) ?>x</span>
                    <span class="fw-bold">R$ <?= number_format((float) ($p['valor'] ?? 0), 2, ',', '.') ?></span>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>

          <?php if ($isExternalLink): ?>
            <a class="btn-primary-custom w-100 justify-content-center" href="<?= htmlspecialchars($linkIngresso, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener noreferrer">
              <i class="bi bi-box-arrow-up-right me-1"></i>Inscreva-se
            </a>
          <?php else: ?>
            <a class="btn-primary-custom w-100 justify-content-center" href="/curso/<?= (int) ($curso['id'] ?? 0) ?>/inscricao">
              <i class="bi bi-pencil-square me-1"></i>Garantir minha vaga
            </a>
          <?php endif; ?>
          <a class="btn btn-outline-primary w-100 justify-content-center d-flex align-items-center gap-2 mt-2" href="/pre-inscricao?curso_id=<?= (int) ($curso['id'] ?? 0) ?>">
            <i class="bi bi-info-circle"></i> Quero mais informações
          </a>
        </div>
      </div>
  </div>
</section>

<script>
function toggleDetalhe() {
  var el = document.getElementById('detalheCurso');
  var btn = document.getElementById('btnVerMais');
  el.classList.toggle('collapsed');
  if (el.classList.contains('collapsed')) {
    btn.innerHTML = '<i class="bi bi-chevron-down me-1"></i>Ver mais';
  } else {
    btn.innerHTML = '<i class="bi bi-chevron-up me-1"></i>Ver menos';
  }
}
document.addEventListener('DOMContentLoaded', function() {
  var el = document.getElementById('detalheCurso');
  var btn = document.getElementById('btnVerMais');
  if (el && btn) {
    if (el.scrollHeight > 300) {
      el.classList.add('collapsed');
    } else {
      btn.style.display = 'none';
    }
  }
});
</script>
