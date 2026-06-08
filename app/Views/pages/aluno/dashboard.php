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
      <div class="col-lg-6" data-aos="fade-right">
        <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 16px; padding: 2rem; box-shadow: var(--card-shadow);">
          <h3>Catálogo de Cursos</h3>
          <div class="d-grid gap-2 mt-3">
            <?php foreach (($courses ?? []) as $course): ?>
              <div class="p-3" style="border: 1px solid var(--border-color); border-radius: 12px;">
                <strong><?= htmlspecialchars($course['name'], ENT_QUOTES, 'UTF-8') ?></strong>
                <div><?= htmlspecialchars($course['description'], ENT_QUOTES, 'UTF-8') ?></div>
                <small><?= htmlspecialchars($course['duration'], ENT_QUOTES, 'UTF-8') ?> | R$ <?= number_format((float) $course['price'], 2, ',', '.') ?></small>
                <form method="post" action="/aluno/matricular" class="mt-2">
                  <input type="hidden" name="course_id" value="<?= (int) $course['id'] ?>">
                  <button type="submit" class="btn btn-sm btn-warning">Matricular</button>
                </form>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
      <div class="col-lg-6" data-aos="fade-left">
        <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 16px; padding: 2rem; box-shadow: var(--card-shadow);">
          <h3>Meus Cursos</h3>
          <div class="d-grid gap-2 mt-3">
            <?php if (empty($enrollments)): ?>
              <div class="p-3" style="border: 1px dashed var(--border-color); border-radius: 12px;">Você ainda não possui matrículas.</div>
            <?php endif; ?>
            <?php foreach (($enrollments ?? []) as $item): ?>
              <div class="p-3" style="border: 1px solid var(--border-color); border-radius: 12px;">
                <strong><?= htmlspecialchars($item['name'], ENT_QUOTES, 'UTF-8') ?></strong>
                <div>Status: <?= htmlspecialchars($item['status'], ENT_QUOTES, 'UTF-8') ?></div>
                <div>Progresso: <?= (int) $item['progress'] ?>%</div>
                <small>Matrícula em <?= htmlspecialchars($item['created_at'], ENT_QUOTES, 'UTF-8') ?></small>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
