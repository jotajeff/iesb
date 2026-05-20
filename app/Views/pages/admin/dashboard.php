<section class="container py-4">
  <div class="row g-3 mb-4">
    <div class="col-md-4">
      <div class="p-3 bg-white border rounded-3 shadow-sm">
        <small class="text-muted">Total de Alunos</small>
        <h2 class="mb-0"><?= (int) ($indicators['total_alunos'] ?? 0) ?></h2>
      </div>
    </div>
    <div class="col-md-4">
      <div class="p-3 bg-white border rounded-3 shadow-sm">
        <small class="text-muted">Total de Cursos</small>
        <h2 class="mb-0"><?= (int) ($indicators['total_cursos'] ?? 0) ?></h2>
      </div>
    </div>
    <div class="col-md-4">
      <div class="p-3 bg-white border rounded-3 shadow-sm">
        <small class="text-muted">Total de Matrículas</small>
        <h2 class="mb-0"><?= (int) ($indicators['total_matriculas'] ?? 0) ?></h2>
      </div>
    </div>
  </div>

  <div class="row g-4">
    <div class="col-lg-5">
      <div class="bg-white border rounded-3 p-4 shadow-sm">
        <h4>Novo Curso</h4>
        <form method="post" action="/admin/cursos" class="d-grid gap-2 mt-3">
          <input type="text" name="name" class="form-control" placeholder="Nome do curso" required>
          <textarea name="description" class="form-control" placeholder="Descrição" rows="3" required></textarea>
          <input type="text" name="duration" class="form-control" placeholder="Duração (ex: 18 meses)" required>
          <input type="number" step="0.01" min="1" name="price" class="form-control" placeholder="Preço mensal" required>
          <button type="submit" class="btn btn-warning">Cadastrar Curso</button>
        </form>
      </div>
    </div>

    <div class="col-lg-7">
      <div class="bg-white border rounded-3 p-4 shadow-sm">
        <h4>Cursos Publicados</h4>
        <div class="d-grid gap-2 mt-3">
          <?php foreach (($courses ?? []) as $course): ?>
            <div class="p-3 border rounded-3">
              <strong><?= htmlspecialchars($course['name'], ENT_QUOTES, 'UTF-8') ?></strong>
              <div><?= htmlspecialchars($course['description'], ENT_QUOTES, 'UTF-8') ?></div>
              <small><?= htmlspecialchars($course['duration'], ENT_QUOTES, 'UTF-8') ?> | R$ <?= number_format((float) $course['price'], 2, ',', '.') ?></small>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>
</section>
