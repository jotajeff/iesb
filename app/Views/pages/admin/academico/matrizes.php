<?php
$matrizes = $matrizes ?? [];
$cursos = $cursos ?? [];
$idCursoFiltro = (int) ($idCursoFiltro ?? 0);
$statusFiltro = (string) ($statusFiltro ?? '');

$cursosAtivos = array_values(array_filter(
    $cursos,
    static fn (array $c): bool => (int) ($c['ativo'] ?? 0) === 1
));

usort($cursosAtivos, static function (array $a, array $b): int {
    $ta = (int) ($a['nivel_id'] ?? 0);
    $tb = (int) ($b['nivel_id'] ?? 0);
    if ($ta !== $tb) {
        return $ta <=> $tb;
    }
    return strcasecmp((string) ($a['nome'] ?? ''), (string) ($b['nome'] ?? ''));
});

$cursosPorTipo = [];
foreach ($cursosAtivos as $curso) {
    $tipoId = (int) ($curso['nivel_id'] ?? 0);
    $tipoNome = trim((string) ($curso['nivel_nome'] ?? ''));
    if ($tipoId <= 0) {
        $tipoId = 0;
        $tipoNome = $tipoNome !== '' ? $tipoNome : 'Outros';
    }
    if (!isset($cursosPorTipo[$tipoId])) {
        $cursosPorTipo[$tipoId] = ['nome' => $tipoNome, 'cursos' => []];
    }
    $cursosPorTipo[$tipoId]['cursos'][] = $curso;
}
?>
<section class="container py-4">
  <div class="bg-white border rounded-3 p-4 shadow-sm">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
      <h4 class="mb-0"><i class="bi bi-diagram-3 me-2"></i>Matrizes Curriculares</h4>
      <a class="btn btn-primary btn-sm" href="/admin/academico/matrizes/form"><i class="bi bi-plus-circle me-1"></i>Nova matriz</a>
    </div>

    <?php if (!empty($flash ?? '')): ?>
      <div class="alert alert-info"><?= htmlspecialchars((string) $flash, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>

    <form method="get" action="/admin/academico/matrizes" class="d-flex flex-wrap align-items-end gap-2 mb-3">
      <div>
        <label class="form-label small text-muted mb-1">Curso</label>
        <select name="id_curso" class="form-select form-select-sm">
          <option value="">Todos os cursos</option>
          <?php foreach ($cursosPorTipo as $grupo): ?>
            <optgroup label="<?= htmlspecialchars((string) ($grupo['nome'] ?? 'Outros'), ENT_QUOTES, 'UTF-8') ?>">
              <?php foreach ($grupo['cursos'] as $curso): ?>
                <option value="<?= (int) ($curso['id'] ?? 0) ?>" <?= $idCursoFiltro === (int) ($curso['id'] ?? 0) ? 'selected' : '' ?>><?= htmlspecialchars((string) ($curso['nome'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></option>
              <?php endforeach; ?>
            </optgroup>
          <?php endforeach; ?>
        </select>
      </div>
      <div>
        <label class="form-label small text-muted mb-1">Status</label>
        <select name="status" class="form-select form-select-sm">
          <option value="">Todos</option>
          <option value="ativo" <?= $statusFiltro === 'ativo' ? 'selected' : '' ?>>Ativo</option>
          <option value="inativo" <?= $statusFiltro === 'inativo' ? 'selected' : '' ?>>Inativo</option>
        </select>
      </div>
      <button type="submit" class="btn btn-sm btn-outline-secondary"><i class="bi bi-funnel me-1"></i>Filtrar</button>
    </form>

    <div class="table-responsive">
      <table class="table table-striped table-sm align-middle">
        <thead>
          <tr>
            <th>#</th>
            <th>Curso</th>
            <th>Nome da matriz</th>
            <th>Versão</th>
            <th>Carga horária</th>
            <th>Módulos</th>
            <th>Status</th>
            <th>Criado em</th>
            <th>Ações</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($matrizes)): ?>
            <tr><td colspan="9" class="text-muted"><i class="bi bi-inbox me-1"></i>Nenhuma matriz encontrada.</td></tr>
          <?php endif; ?>
          <?php foreach ($matrizes as $m): ?>
            <?php $id = (int) ($m['id'] ?? 0); ?>
            <?php $criadoEm = (string) ($m['created_at'] ?? ''); ?>
            <tr>
              <td><?= $id ?></td>
              <td><?= htmlspecialchars((string) ($m['curso_nome'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
              <td class="fw-semibold"><?= htmlspecialchars((string) ($m['nome'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
              <td><span class="badge bg-light text-dark"><?= htmlspecialchars((string) ($m['versao'] ?? '1.0'), ENT_QUOTES, 'UTF-8') ?></span></td>
              <td><?= (int) ($m['carga_horaria'] ?? 0) > 0 ? (int) ($m['carga_horaria'] ?? 0) . 'h' : '-' ?></td>
              <td><span class="badge bg-primary"><?= (int) ($m['total_modulos'] ?? 0) ?></span></td>
              <td>
                <?php if ((int) ($m['ativo'] ?? 0) === 1): ?>
                  <span class="badge bg-success">Ativo</span>
                <?php else: ?>
                  <span class="badge bg-secondary">Inativo</span>
                <?php endif; ?>
              </td>
              <td><?= $criadoEm !== '' ? date('d/m/Y H:i', strtotime($criadoEm)) : '-' ?></td>
              <td>
                <div class="d-flex gap-1">
                  <a class="btn btn-outline-primary btn-sm" href="/admin/academico/matrizes/detalhe?id=<?= $id ?>" title="Visualizar"><i class="bi bi-eye"></i></a>
                  <a class="btn btn-outline-secondary btn-sm" href="/admin/academico/matrizes/form?id=<?= $id ?>" title="Editar"><i class="bi bi-pencil"></i></a>
                  <?php if ((int) ($m['ativo'] ?? 0) === 1): ?>
                    <button type="button" class="btn btn-outline-danger btn-sm btn-desativar-matriz" data-id="<?= $id ?>" title="Desativar"><i class="bi bi-toggle-off"></i></button>
                  <?php endif; ?>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function () {
  document.querySelectorAll('.btn-desativar-matriz').forEach(function (btn) {
    btn.addEventListener('click', function () {
      if (!confirm('Desativar esta matriz curricular?')) return;
      var body = new URLSearchParams();
      body.set('id', this.dataset.id);
      fetch('/admin/academico/matrizes/desativar', { method: 'POST', body: body })
        .then(function (r) { return r.json(); })
        .then(function (data) {
          if (data.sucesso) location.reload();
          else alert(data.erro || 'Erro ao desativar.');
        })
        .catch(function () { alert('Erro ao desativar.'); });
    });
  });
});
</script>
