<section class="hero-section" id="home" style="min-height: 100vh;">
  <div class="hero-bg"></div>
  <div class="container hero-content" style="padding-top: 120px;">
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
