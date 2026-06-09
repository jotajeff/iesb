<?php
  $matriculasData = $matriculasDB ?? [];
  $totalMatriculas = count($matriculasData);
  $statusCounts = [];
  foreach ($matriculasData as $item) {
      $s = $item['status'] ?? 'desconhecido';
      $statusCounts[$s] = ($statusCounts[$s] ?? 0) + 1;
  }
?>

<section class="py-4" id="home" style="margin-top: 76px;">
  <div class="container">
    <div class="row g-3 mb-4">
      <div class="col-md-4" data-aos="fade-up" data-aos-delay="0">
        <div class="d-flex align-items-center gap-3 p-3 rounded-3 shadow-sm" style="background: linear-gradient(135deg, #0d6efd, #0a58ca); color: #fff;">
          <div class="d-flex align-items-center justify-content-center rounded-circle" style="width: 54px; height: 54px; background: rgba(255,255,255,0.2); font-size: 1.5rem;">
            <i class="bi bi-journal-bookmark-fill"></i>
          </div>
          <div>
            <div class="fs-3 fw-bold"><?= $totalMatriculas ?></div>
            <div class="small opacity-75">Cursos Matriculados</div>
          </div>
        </div>
      </div>
      <div class="col-md-4" data-aos="fade-up" data-aos-delay="100">
        <div class="d-flex align-items-center gap-3 p-3 rounded-3 shadow-sm" style="background: linear-gradient(135deg, #198754, #157347); color: #fff;">
          <div class="d-flex align-items-center justify-content-center rounded-circle" style="width: 54px; height: 54px; background: rgba(255,255,255,0.2); font-size: 1.5rem;">
            <i class="bi bi-check-circle-fill"></i>
          </div>
          <div>
            <div class="fs-3 fw-bold"><?= $statusCounts['active'] ?? 0 ?></div>
            <div class="small opacity-75">Ativos</div>
          </div>
        </div>
      </div>
      <div class="col-md-4" data-aos="fade-up" data-aos-delay="200">
        <div class="d-flex align-items-center gap-3 p-3 rounded-3 shadow-sm" style="background: linear-gradient(135deg, #ffc107, #e0a800); color: #212529;">
          <div class="d-flex align-items-center justify-content-center rounded-circle" style="width: 54px; height: 54px; background: rgba(0,0,0,0.1); font-size: 1.5rem;">
            <i class="bi bi-trophy-fill"></i>
          </div>
          <div>
            <div class="fs-3 fw-bold"><?= $statusCounts['concluido'] ?? 0 ?></div>
            <div class="small opacity-75">Concluídos</div>
          </div>
        </div>
      </div>
    </div>

    <div class="row g-4">
      <div class="col-12" data-aos="fade-up">
        <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 16px; padding: 2rem; box-shadow: var(--card-shadow);">
          <h3 class="mb-3">Cursos Disponíveis</h3>
          <?php if (empty($cursosDisponiveis)): ?>
            <div class="text-center text-muted py-4">
              <i class="bi bi-journal-bookmark" style="font-size: 2rem;"></i>
              <p class="mt-2 mb-0">Nenhum curso disponível no momento.</p>
            </div>
          <?php else: ?>
            <div class="table-responsive">
              <table class="table table-hover align-middle mb-0">
                <thead>
                  <tr>
                    <th style="width: 50px;"></th>
                    <th>Curso</th>
                    <th>Segmento</th>
                    <th>Horário</th>
                    <th style="width: 140px;"></th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($cursosDisponiveis as $curso): ?>
                    <tr>
                      <td>
                        <a href="/aluno/detalhes?id=<?= (int) $curso['id'] ?>" class="btn btn-sm btn-outline-info" title="Detalhes">
                          <i class="bi bi-eye"></i>
                        </a>
                      </td>
                      <td>
                        <strong><?= htmlspecialchars($curso['nome'] ?? '-', ENT_QUOTES, 'UTF-8') ?></strong>
                        <?php if (!empty($curso['local_curso'])): ?>
                          <br><small class="text-muted"><?= htmlspecialchars($curso['local_curso'], ENT_QUOTES, 'UTF-8') ?></small>
                        <?php endif; ?>
                      </td>
                      <td><?= htmlspecialchars($curso['segmento_nome'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
                      <td><?= htmlspecialchars($curso['horario'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
                      <td>
                        <form method="post" action="/aluno/matricular-curso">
                          <input type="hidden" name="curso_id" value="<?= (int) $curso['id'] ?>">
                          <button type="submit" class="btn btn-sm btn-warning w-100">
                            <i class="bi bi-journal-plus me-1"></i>Matricular
                          </button>
                        </form>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</section>
