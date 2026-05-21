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
  </div>
</section>