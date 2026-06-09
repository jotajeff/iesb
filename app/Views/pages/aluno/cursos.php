<section class="py-4" id="cursos" style="margin-top: 76px;">
  <div class="container">
    <div class="d-flex align-items-center justify-content-between mb-4" data-aos="fade-up">
      <h2 class="mb-0">Meus Cursos</h2>
      <span class="badge bg-primary fs-6"><?= count($cursosMatriculados ?? []) ?> curso(s)</span>
    </div>

    <?php if (empty($cursosMatriculados)): ?>
      <div class="text-center text-muted py-5" data-aos="fade-up">
        <i class="bi bi-journal-bookmark" style="font-size: 3rem;"></i>
        <p class="mt-3 mb-0">Você ainda não está matriculado em nenhum curso.</p>
        <a href="/aluno" class="btn btn-primary mt-3">Ver catálogo de cursos</a>
      </div>
    <?php else: ?>
      <div class="row g-4">
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
          <div class="col-lg-6" data-aos="fade-up">
            <div class="p-4 rounded-4 shadow-sm h-100" style="background: var(--bg-card); border: 1px solid var(--border-color);">
              <div class="d-flex align-items-start justify-content-between mb-2">
                <h4 class="mb-0"><?= htmlspecialchars($matricula['curso_nome'] ?? '-', ENT_QUOTES, 'UTF-8') ?></h4>
                <span class="badge bg-<?= $statusClass ?> ms-2"><?= $statusLabel ?></span>
              </div>
              <p class="mb-1">
                <i class="bi bi-people me-1"></i>
                <strong>Turma:</strong> <?= htmlspecialchars($matricula['turma_nome'] ?? '-', ENT_QUOTES, 'UTF-8') ?>
              </p>
              <p class="mb-0 text-muted small">
                <i class="bi bi-calendar me-1"></i>
                Matrícula em <?= htmlspecialchars(
                  \DateTime::createFromFormat('Y-m-d', $matricula['data_matricula'] ?? '')
                    ?->format('d/m/Y') ?? ($matricula['data_matricula'] ?? '-'),
                  ENT_QUOTES,
                  'UTF-8'
                ) ?>
              </p>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</section>
