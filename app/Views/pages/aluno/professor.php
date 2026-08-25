<section class="py-4" id="detalhes-professor" style="margin-top: 20px;">
  <div class="container">
    <div class="bg-white border rounded-3 p-4 shadow-sm" style="background: var(--bg-card); border-color: var(--border-color);">
      <div class="d-flex align-items-center justify-content-between mb-4" data-aos="fade-up">
        <h4 class="mb-0"><i class="bi bi-person-badge me-2"></i><?= htmlspecialchars($professor['nome'] ?? '-', ENT_QUOTES, 'UTF-8') ?></h4>
        <a class="btn btn-outline-secondary btn-sm" href="/aluno/cursos"><i class="bi bi-arrow-left me-1"></i>Voltar</a>
      </div>

      <div class="d-flex align-items-center gap-3 mb-4">
        <div class="flex-shrink-0">
          <?php
            $professorFoto = trim((string) ($professor['foto'] ?? ''));
            $professorFotoSrc = '';
            if ($professorFoto !== '') {
              $professorFotoSrc = (str_starts_with($professorFoto, 'http') || str_starts_with($professorFoto, '/'))
                ? $professorFoto
                : '/' . $professorFoto;
            }
          ?>
          <?php if ($professorFotoSrc !== ''): ?>
            <img src="<?= htmlspecialchars($professorFotoSrc, ENT_QUOTES, 'UTF-8') ?>" alt="Foto de <?= htmlspecialchars((string) ($professor['nome'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" class="rounded-circle border shadow-sm" width="96" height="96" style="object-fit: cover;">
          <?php else: ?>
            <div class="rounded-circle bg-secondary d-flex align-items-center justify-content-center text-white" style="width:96px;height:96px;font-size:2rem;">
              <i class="bi bi-person"></i>
            </div>
          <?php endif; ?>
        </div>
        <div>
          <h5 class="mb-1"><?= htmlspecialchars($professor['nome'] ?? '-', ENT_QUOTES, 'UTF-8') ?></h5>
          <p class="mb-1 small text-muted">
            <i class="bi bi-envelope me-1"></i><?= htmlspecialchars($professor['email'] ?? '-', ENT_QUOTES, 'UTF-8') ?>
          </p>
          <?php if (!empty($professor['telefone'])): ?>
            <p class="mb-0 small text-muted"><i class="bi bi-telephone me-1"></i><?= htmlspecialchars($professor['telefone'], ENT_QUOTES, 'UTF-8') ?></p>
          <?php endif; ?>
        </div>
      </div>

      <div class="accordion" id="accordionProfessor">
        <div class="accordion-item">
          <h2 class="accordion-header">
            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseResumo" aria-expanded="true">
              <i class="bi bi-card-text me-2"></i>Resumo
            </button>
          </h2>
          <div id="collapseResumo" class="accordion-collapse collapse show" data-bs-parent="#accordionProfessor">
            <div class="accordion-body">
              <?php if ($curriculo && trim((string) ($curriculo['resumo'] ?? '')) !== ''): ?>
                <p class="mb-0"><?= nl2br(htmlspecialchars((string) $curriculo['resumo'], ENT_QUOTES, 'UTF-8')) ?></p>
              <?php else: ?>
                <p class="text-muted mb-0"><i class="bi bi-inbox me-1"></i>Nenhum resumo disponível.</p>
              <?php endif; ?>
            </div>
          </div>
        </div>

        <div class="accordion-item">
          <h2 class="accordion-header">
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseCurriculo" aria-expanded="false">
              <i class="bi bi-file-earmark-text me-2"></i>Currículo
            </button>
          </h2>
          <div id="collapseCurriculo" class="accordion-collapse collapse" data-bs-parent="#accordionProfessor">
            <div class="accordion-body">
              <?php if ($curriculo && trim((string) ($curriculo['conteudo'] ?? '')) !== ''): ?>
                <div><?= ($curriculo['conteudo'] ?? '') ?></div>
              <?php else: ?>
                <p class="text-muted mb-0"><i class="bi bi-inbox me-1"></i>Nenhum currículo disponível.</p>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>