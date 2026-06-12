<section class="py-4" id="cursos" style="margin-top: 20px;">
  <div class="container">
    <div class="bg-white border rounded-3 p-4 shadow-sm" style="background: var(--bg-card); border-color: var(--border-color);">
      <div class="d-flex align-items-center justify-content-between mb-4" data-aos="fade-up">
        <h4 class="mb-0"><i class="bi bi-book me-2"></i>Meus Cursos</h4>
        <span class="badge bg-primary fs-6"><?= count($cursosMatriculados ?? []) ?> curso(s)</span>
      </div>

      <?php if (empty($cursosMatriculados)): ?>
        <div class="text-center text-muted py-5" data-aos="fade-up">
          <i class="bi bi-journal-bookmark" style="font-size: 3rem;"></i>
          <p class="mt-3 mb-0">Você ainda não está matriculado em nenhum curso.</p>
          <a href="/aluno" class="btn btn-primary mt-3">Ver catálogo de cursos</a>
        </div>
      <?php else: ?>
        <div class="d-flex flex-column gap-3">
          <?php foreach ($cursosMatriculados as $matricula): ?>
            <?php
            $status = (string) ($matricula['status'] ?? '');
            $statusClass = match ($status) {
              'active', 'matriculado' => 'success',
              'concluido' => 'secondary',
              'cancelado' => 'danger',
              default => 'warning',
            };
            $statusLabel = match ($status) {
              'active', 'matriculado' => 'Ativo',
              'concluido' => 'Concluído',
              'cancelado' => 'Cancelado',
              default => ucfirst($status),
            };
            ?>
            <div class="p-4 rounded-4 shadow-sm" style="background: var(--bg-body-alt); border: 1px solid var(--border-color);" data-aos="fade-up">
              <div class="d-flex align-items-start justify-content-between mb-2">
                <h5 class="mb-0" style="color: var(--text-heading);"><?= htmlspecialchars($matricula['curso_nome'] ?? '-', ENT_QUOTES, 'UTF-8') ?></h5>
                <span class="badge bg-<?= $statusClass ?> ms-2"><?= $statusLabel ?></span>
              </div>
              <p class="mb-1" style="color: var(--text-primary);">
                <i class="bi bi-people me-1"></i>
                <strong>Turma:</strong> <?= htmlspecialchars($matricula['turma_nome'] ?? '-', ENT_QUOTES, 'UTF-8') ?>
              </p>
              <p class="mb-0 small" style="color: var(--text-secondary);">
                <i class="bi bi-calendar me-1"></i>
                Matrícula em <?= htmlspecialchars(
                  ($matricula['data_matricula'] ?? '') !== ''
                    ? (new \DateTime($matricula['data_matricula']))->format('d/m/Y')
                    : '-',
                  ENT_QUOTES,
                  'UTF-8'
                ) ?>
              </p>
              <div class="mt-3">
                <a class="btn btn-outline-primary btn-sm" href="/aluno/show?matricula_id=<?= (int) ($matricula['matricula_id'] ?? 0) ?>">
                  <i class="bi bi-eye me-1"></i>Detalhes
                </a>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </div>
</section>
