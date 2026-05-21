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
  <div class="bg-white border rounded-3 p-4 shadow-sm">
    <h5 class="mb-2">Gestao de cursos</h5>
    <p class="text-muted mb-3">Use o modulo de cursos para listar, criar e editar registros da tabela <code>cursos_iesb</code>.</p>
    <a class="btn btn-primary btn-sm" href="/admin/cursos">Abrir cursos</a>
  </div>
</section>
