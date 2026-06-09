<?php
  $curso = $curso ?? [];
  $nome = htmlspecialchars($curso['nome'] ?? '-', ENT_QUOTES, 'UTF-8');
  $segmento = htmlspecialchars($curso['segmento_nome'] ?? '-', ENT_QUOTES, 'UTF-8');
  $nivel = htmlspecialchars($curso['nivel_nome'] ?? '-', ENT_QUOTES, 'UTF-8');
  $horario = htmlspecialchars($curso['horario'] ?? '-', ENT_QUOTES, 'UTF-8');
  $local = htmlspecialchars($curso['local_curso'] ?? '-', ENT_QUOTES, 'UTF-8');
  $dataCurso = htmlspecialchars($curso['data_curso'] ?? '-', ENT_QUOTES, 'UTF-8');
  $linkIngresso = trim((string) ($curso['link_ingresso'] ?? ''));
  $imagem = trim((string) ($curso['imagem_card'] ?? ''));
  $isConfirmed = strtoupper(trim((string) ($curso['confirmado'] ?? 'N'))) === 'S';
?>

<section class="py-4" id="detalhes" style="margin-top: 76px;">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-lg-8" data-aos="fade-up">
        <a href="/aluno" class="btn btn-outline-secondary btn-sm mb-3">
          <i class="bi bi-arrow-left me-1"></i>Voltar
        </a>

        <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 16px; box-shadow: var(--card-shadow); overflow: hidden;">

          <?php if ($imagem !== ''): ?>
            <img src="/<?= htmlspecialchars($imagem, ENT_QUOTES, 'UTF-8') ?>" alt="<?= $nome ?>" style="width: 100%; height: 260px; object-fit: cover; display: block;">
          <?php else: ?>
            <div style="width: 100%; height: 160px; background: linear-gradient(135deg, #2c3e50, #0f172a); display: flex; align-items: center; justify-content: center;">
              <i class="bi bi-journal-bookmark" style="font-size: 3rem; color: rgba(255,255,255,0.3);"></i>
            </div>
          <?php endif; ?>

          <div class="p-4">
            <div class="d-flex align-items-start justify-content-between mb-3">
              <div>
                <h2 class="mb-1"><?= $nome ?></h2>
                <?php if ($isConfirmed): ?>
                  <span class="badge bg-success"><i class="bi bi-award-fill me-1"></i>Confirmado</span>
                <?php endif; ?>
              </div>
            </div>

            <div class="row g-3 mb-3">
              <div class="col-sm-6">
                <div class="d-flex align-items-center gap-2 p-3 rounded-3" style="background: var(--color-body); border: 1px solid var(--border-color);">
                  <i class="bi bi-tag fs-4 text-primary"></i>
                  <div>
                    <small class="text-muted d-block">Segmento</small>
                    <strong><?= $segmento ?></strong>
                  </div>
                </div>
              </div>
              <div class="col-sm-6">
                <div class="d-flex align-items-center gap-2 p-3 rounded-3" style="background: var(--color-body); border: 1px solid var(--border-color);">
                  <i class="bi bi-bar-chart fs-4 text-primary"></i>
                  <div>
                    <small class="text-muted d-block">Nível</small>
                    <strong><?= $nivel ?></strong>
                  </div>
                </div>
              </div>
              <div class="col-sm-6">
                <div class="d-flex align-items-center gap-2 p-3 rounded-3" style="background: var(--color-body); border: 1px solid var(--border-color);">
                  <i class="bi bi-clock fs-4 text-primary"></i>
                  <div>
                    <small class="text-muted d-block">Horário</small>
                    <strong><?= $horario ?></strong>
                  </div>
                </div>
              </div>
              <div class="col-sm-6">
                <div class="d-flex align-items-center gap-2 p-3 rounded-3" style="background: var(--color-body); border: 1px solid var(--border-color);">
                  <i class="bi bi-geo-alt fs-4 text-primary"></i>
                  <div>
                    <small class="text-muted d-block">Local</small>
                    <strong><?= $local ?></strong>
                  </div>
                </div>
              </div>
              <?php if ($dataCurso !== '-'): ?>
              <div class="col-sm-6">
                <div class="d-flex align-items-center gap-2 p-3 rounded-3" style="background: var(--color-body); border: 1px solid var(--border-color);">
                  <i class="bi bi-calendar-event fs-4 text-primary"></i>
                  <div>
                    <small class="text-muted d-block">Data</small>
                    <strong><?= $dataCurso ?></strong>
                  </div>
                </div>
              </div>
              <?php endif; ?>
            </div>

            <div class="d-flex gap-2 mt-4">
              <form method="post" action="/aluno/matricular-curso">
                <input type="hidden" name="curso_id" value="<?= (int) ($curso['id'] ?? 0) ?>">
                <button type="submit" class="btn btn-warning">
                  <i class="bi bi-journal-plus me-1"></i>Matricular
                </button>
              </form>
              <?php if ($linkIngresso !== ''): ?>
                <a href="<?= htmlspecialchars($linkIngresso, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener noreferrer" class="btn btn-outline-primary">
                  <i class="bi bi-box-arrow-up-right me-1"></i>Página externa
                </a>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
