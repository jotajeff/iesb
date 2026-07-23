<?php
$curso = $curso ?? [];
$detalhe = $detalhe ?? null;
$pagamentos = $pagamentos ?? [];
$dateText = $dateText ?? '-';
$isConfirmed = $isConfirmed ?? false;
$linkIngresso = $linkIngresso ?? '';
$isExternalLink = $isExternalLink ?? false;
$img = trim((string) ($curso['imagem_card'] ?? ''));
$nivelSlug = $nivelSlug ?? '';
$disciplinas = $disciplinas ?? [];
$coordenadores = $coordenadores ?? [];
$professores = $professores ?? [];
$isPos = $nivelSlug === 'pos-graduacao';
?>
<section class="hero-section" id="home" style="min-height:50vh;position:relative;overflow:hidden;">
  <canvas id="particlesCanvas" style="position:absolute;inset:0;width:100%;height:100%;pointer-events:none;z-index:1;"></canvas>
  <div class="hero-bg"></div>
  <div class="container hero-content" style="position:relative;z-index:2;">
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

        <?php if ($isPos): ?>

          <div class="accordion" id="accordionCurso">
            <div class="accordion-item">
              <h2 class="accordion-header">
                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseSobre" aria-expanded="true">
                  <i class="bi bi-journal-text me-2"></i>Sobre o curso
                </button>
              </h2>
              <div id="collapseSobre" class="accordion-collapse collapse show" data-bs-parent="#accordionCurso">
                <div class="accordion-body">
                  <?php if ($detalhe && trim((string) ($detalhe['detalhe'] ?? '')) !== ''): ?>
                    <div><?= $detalhe['detalhe'] ?></div>
                  <?php else: ?>
                    <p class="text-muted mb-0">Nenhuma descrição disponível.</p>
                  <?php endif; ?>
                </div>
              </div>
            </div>

            <div class="accordion-item">
              <h2 class="accordion-header">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapsePublico">
                  <i class="bi bi-people me-2"></i>Público-alvo
                </button>
              </h2>
              <div id="collapsePublico" class="accordion-collapse collapse" data-bs-parent="#accordionCurso">
                <div class="accordion-body">
                  <?php $publico = (string) ($curso['publico_alvo'] ?? ''); ?>
                  <?php if (trim($publico) !== ''): ?>
                    <?= nl2br(htmlspecialchars($publico, ENT_QUOTES, 'UTF-8')) ?>
                  <?php else: ?>
                    <p class="text-muted mb-0">Nenhuma informação disponível.</p>
                  <?php endif; ?>
                </div>
              </div>
            </div>

            <div class="accordion-item">
              <h2 class="accordion-header">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseInvestimento">
                  <i class="bi bi-currency-dollar me-2"></i>Investimento
                </button>
              </h2>
              <div id="collapseInvestimento" class="accordion-collapse collapse" data-bs-parent="#accordionCurso">
                <div class="accordion-body">
                  <?php if (!empty($pagamentos)): ?>
                    <?php foreach ($pagamentos as $p): ?>
                      <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-2">
                        <div>
                          <strong><?= htmlspecialchars((string) ($p['descricao'] ?? ''), ENT_QUOTES, 'UTF-8') ?></strong>
                          <span class="badge bg-secondary ms-2" style="font-size:.65rem;"><?= htmlspecialchars((string) ($p['tipo'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span>
                        </div>
                        <div class="text-end">
                          <small class="text-muted"><?= (int) ($p['parcelas'] ?? 1) ?>x</small>
                          <span class="fw-bold ms-2">R$ <?= number_format((float) ($p['valor'] ?? 0), 2, ',', '.') ?></span>
                        </div>
                      </div>
                    <?php endforeach; ?>
                  <?php else: ?>
                    <p class="text-muted mb-0">Entre em contato para saber mais sobre investimento.</p>
                  <?php endif; ?>
                </div>
              </div>
            </div>

            <div class="accordion-item">
              <h2 class="accordion-header">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseCoordenacao">
                  <i class="bi bi-person-badge me-2"></i>Coordenação
                </button>
              </h2>
              <div id="collapseCoordenacao" class="accordion-collapse collapse" data-bs-parent="#accordionCurso">
                <div class="accordion-body">
                  <?php if (!empty($coordenadores)): ?>
                    <div class="row g-3">
                      <?php foreach ($coordenadores as $coord): ?>
                        <div class="col-md-4">
                          <div class="card border shadow-sm h-100 text-center p-3">
                            <?php $fotoCoord = (string) ($coord['foto_path'] ?? ''); ?>
                            <?php if ($fotoCoord !== ''): ?>
                              <img src="/<?= htmlspecialchars($fotoCoord, ENT_QUOTES, 'UTF-8') ?>" alt="" class="rounded-circle border shadow-sm mx-auto mb-3" style="width:100px;height:100px;object-fit:cover;">
                            <?php else: ?>
                              <div class="d-inline-flex align-items-center justify-content-center bg-light rounded-circle border mx-auto mb-3" style="width:100px;height:100px;">
                                <i class="bi bi-person fs-1 text-muted"></i>
                              </div>
                            <?php endif; ?>
                            <h6 class="card-title"><?= htmlspecialchars((string) ($coord['usuario_nome'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></h6>
                            <p class="text-muted small mb-2">Coordenador(a)</p>
                            <?php $resumoCoord = (string) ($coord['curriculo_resumo'] ?? ''); ?>
                            <?php if ($resumoCoord !== ''): ?>
                              <p class="small text-start mb-0"><?= nl2br(htmlspecialchars($resumoCoord, ENT_QUOTES, 'UTF-8')) ?></p>
                            <?php endif; ?>
                          </div>
                        </div>
                      <?php endforeach; ?>
                    </div>
                  <?php else: ?>
                    <p class="text-muted mb-0">Em breve.</p>
                  <?php endif; ?>
                </div>
              </div>
            </div>

            <div class="accordion-item">
              <h2 class="accordion-header">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseDisciplinas">
                  <i class="bi bi-book me-2"></i>Disciplinas
                </button>
              </h2>
              <div id="collapseDisciplinas" class="accordion-collapse collapse" data-bs-parent="#accordionCurso">
                <div class="accordion-body">
                  <?php if (!empty($disciplinas)): ?>
                    <ul class="list-group list-group-flush">
                      <?php foreach ($disciplinas as $d): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                          <?= htmlspecialchars((string) ($d['nome'] ?? '-'), ENT_QUOTES, 'UTF-8') ?>
                          <span class="badge bg-primary rounded-pill"><?= (int) ($d['carga_horaria'] ?? 0) ?>h</span>
                        </li>
                      <?php endforeach; ?>
                    </ul>
                  <?php else: ?>
                    <p class="text-muted mb-0">Grade curricular em atualização.</p>
                  <?php endif; ?>
                </div>
              </div>
            </div>

            <div class="accordion-item">
              <h2 class="accordion-header">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseProfessores">
                  <i class="bi bi-people me-2"></i>Professores
                </button>
              </h2>
              <div id="collapseProfessores" class="accordion-collapse collapse" data-bs-parent="#accordionCurso">
                <div class="accordion-body">
                  <?php if (!empty($professores)): ?>
                    <div class="professores-scroll-wrapper">
                      <div class="professores-scroll-track">
                        <?php for ($r = 0; $r < 3; $r++): ?>
                          <?php foreach ($professores as $prof): ?>
                            <div class="professor-card">
                              <?php $fotoProf = (string) ($prof['foto_path'] ?? ''); ?>
                              <?php if ($fotoProf !== ''): ?>
                                <img src="/<?= htmlspecialchars($fotoProf, ENT_QUOTES, 'UTF-8') ?>" alt="" class="professor-foto">
                              <?php else: ?>
                                <div class="professor-foto-placeholder">
                                  <i class="bi bi-person"></i>
                                </div>
                              <?php endif; ?>
                              <span class="professor-nome"><?= htmlspecialchars((string) ($prof['usuario_nome'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></span>
                            </div>
                          <?php endforeach; ?>
                        <?php endfor; ?>
                      </div>
                    </div>
                  <?php else: ?>
                    <p class="text-muted mb-0">Corpo docente em definição.</p>
                  <?php endif; ?>
                </div>
              </div>
            </div>

          </div>

        <?php else: ?>

          <?php if ($detalhe && trim((string) ($detalhe['detalhe'] ?? '')) !== ''): ?>
            <div class="bg-white border rounded-4 p-4 shadow-sm mb-4">
              <h5 class="mb-3"><i class="bi bi-journal-text me-2"></i>Sobre o curso</h5>
              <div class="curso-detalhe-content" id="detalheCurso"><?= $detalhe['detalhe'] ?></div>
              <button class="btn btn-sm btn-outline-primary mt-3" id="btnVerMais" onclick="toggleDetalhe()">
                <i class="bi bi-chevron-down me-1"></i>Ver mais
              </button>
            </div>
          <?php endif; ?>

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
          <?php if (!empty($pagamentos) && !$isPos): ?>
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

<style>
.accordion-button {
  font-weight: 700;
}
.accordion-button:not(.collapsed) {
  background-color: rgba(var(--primary-rgb, 239, 192, 43), 0.1);
  color: var(--primary, #efc02b);
  box-shadow: inset 0 -1px 0 rgba(var(--primary-rgb, 239, 192, 43), 0.2);
}
.accordion-button:focus {
  box-shadow: none;
  border-color: rgba(var(--primary-rgb, 239, 192, 43), 0.4);
}
.accordion-button::after {
  transition: transform 0.2s ease;
}
.professores-scroll-wrapper {
  overflow: hidden;
  width: 100%;
}
.professores-scroll-track {
  display: flex;
  gap: 1.5rem;
  width: max-content;
  animation: scrollLeft 40s linear infinite;
}
.professor-card {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.5rem;
  min-width: 100px;
}
.professor-foto {
  width: 70px;
  height: 70px;
  border-radius: 50%;
  object-fit: cover;
  border: 2px solid #dee2e6;
}
.professor-foto-placeholder {
  width: 70px;
  height: 70px;
  border-radius: 50%;
  background: #f8f9fa;
  border: 2px solid #dee2e6;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.8rem;
  color: #adb5bd;
}
.professor-nome {
  font-size: 0.8rem;
  text-align: center;
  color: #495057;
  max-width: 100px;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}
@keyframes scrollLeft {
  0% { transform: translateX(0); }
  100% { transform: translateX(-33.333%); }
}
</style>
<script>
(function() {
  var c = document.getElementById('particlesCanvas');
  if (!c) return;
  var ctx = c.getContext('2d');
  var particles = [];
  var w, h;

  function resize() {
    var hero = c.parentElement;
    w = c.width = hero.offsetWidth;
    h = c.height = hero.offsetHeight;
  }

  function createParticles() {
    particles = [];
    var count = Math.min(60, Math.floor(w * h / 8000));
    for (var i = 0; i < count; i++) {
      particles.push({
        x: Math.random() * w,
        y: Math.random() * h,
        r: Math.random() * 2.5 + 0.5,
        dx: (Math.random() - 0.5) * 0.6,
        dy: (Math.random() - 0.5) * 0.6,
        a: Math.random() * 0.4 + 0.1
      });
    }
  }

  function draw() {
    ctx.clearRect(0, 0, w, h);
    for (var i = 0; i < particles.length; i++) {
      var p = particles[i];
      ctx.beginPath();
      ctx.arc(p.x, p.y, p.r, 0, Math.PI * 2);
      ctx.fillStyle = 'rgba(239, 192, 43, ' + p.a + ')';
      ctx.fill();
      p.x += p.dx;
      p.y += p.dy;
      if (p.x < 0 || p.x > w) p.dx *= -1;
      if (p.y < 0 || p.y > h) p.dy *= -1;
    }
    requestAnimationFrame(draw);
  }

  resize();
  createParticles();
  window.addEventListener('resize', function() { resize(); createParticles(); });
  draw();
})();
</script>
<script>
function toggleDetalhe() {
  var el = document.getElementById('detalheCurso');
  var btn = document.getElementById('btnVerMais');
  if (!el || !btn) return;
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
