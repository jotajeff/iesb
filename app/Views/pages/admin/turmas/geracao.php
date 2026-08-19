<?php
$cursosPorTipo = is_array($cursosPorTipo ?? null) ? $cursosPorTipo : [];
?>

<section class="container py-4">
  <div class="bg-white border rounded-3 p-4 shadow-sm">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
      <h4 class="mb-0"><i class="bi bi-magic me-2"></i>Geração de Turmas</h4>
      <a class="btn btn-outline-secondary btn-sm" href="/admin/turmas"><i class="bi bi-arrow-left me-1"></i>Voltar</a>
    </div>

    <?php if (!empty($flash ?? '')): ?>
      <div class="alert alert-info"><?= htmlspecialchars((string) $flash, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>

    <?php if (empty($cursosPorTipo)): ?>
      <div class="alert alert-light border text-muted mb-0">
        <i class="bi bi-inbox me-1"></i>Nenhum curso ativo encontrado.
      </div>
    <?php else: ?>
      <div class="table-responsive">
        <table class="table table-striped table-sm align-middle">
          <thead>
            <tr>
              <th>Tipo</th>
              <th>Curso</th>
              <th>Nome da Turma</th>
              <th>Ações</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($cursosPorTipo as $grupo): ?>
              <?php $qtd = count($grupo['cursos']); ?>
              <?php foreach ($grupo['cursos'] as $i => $curso): ?>
                <tr>
                  <?php if ($i === 0): ?>
                    <td rowspan="<?= $qtd ?>" class="fw-semibold"><?= htmlspecialchars((string) ($grupo['nome'] ?? 'Outros'), ENT_QUOTES, 'UTF-8') ?></td>
                  <?php endif; ?>
                  <td><?= htmlspecialchars((string) ($curso['nome'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                  <td>
                    <?php if (!empty($curso['tem_turma'])): ?>
                      <div>
                        <?php foreach (($curso['turmas_existentes'] ?? []) as $nomeTurmaExistente): ?>
                          <span class="badge bg-light text-dark border me-1 mb-1"><i class="bi bi-people me-1"></i><?= htmlspecialchars((string) $nomeTurmaExistente, ENT_QUOTES, 'UTF-8') ?></span>
                        <?php endforeach; ?>
                      </div>
                    <?php else: ?>
                      <input type="text" class="form-control form-control-sm geracao-nome" value="<?= htmlspecialchars((string) ($curso['nome_turma'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" data-curso="<?= (int) ($curso['id'] ?? 0) ?>">
                    <?php endif; ?>
                  </td>
                  <td>
                    <?php if (!empty($curso['tem_turma'])): ?>
                      <button type="button" class="btn btn-outline-secondary btn-sm" disabled title="Este curso já possui turma(s)">
                        <i class="bi bi-check2-all me-1"></i>Já possui turma
                      </button>
                    <?php else: ?>
                      <button type="button" class="btn btn-success btn-sm text-nowrap btn-confirmar-geracao"
                        data-curso="<?= (int) ($curso['id'] ?? 0) ?>"
                        data-nome="<?= htmlspecialchars((string) ($curso['nome'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                        data-tipo="<?= htmlspecialchars((string) ($grupo['nome'] ?? 'Outros'), ENT_QUOTES, 'UTF-8') ?>">
                        <i class="bi bi-check-lg me-1"></i>Confirmar
                      </button>
                    <?php endif; ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>
</section>

<div class="modal fade" id="confirmarGeracaoModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="bi bi-magic me-2"></i>Confirmar geração de turma</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
      </div>
      <div class="modal-body">
        <dl class="row mb-3">
          <dt class="col-sm-3 text-muted">Tipo</dt>
          <dd class="col-sm-9" id="modalTipo"></dd>
          <dt class="col-sm-3 text-muted">Curso</dt>
          <dd class="col-sm-9" id="modalCurso"></dd>
        </dl>
        <form method="post" action="/admin/turmas/geracao/confirmar" id="formConfirmarGeracao">
          <input type="hidden" name="curso_id" id="modalCursoId" value="">
          <div>
            <label class="form-label small fw-semibold">Nome da turma</label>
            <input type="text" name="nome" id="modalNome" class="form-control" required>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="submit" form="formConfirmarGeracao" class="btn btn-success"><i class="bi bi-check-lg me-1"></i>Confirmar</button>
      </div>
    </div>
  </div>
</div>

<script>
document.querySelectorAll('.btn-confirmar-geracao').forEach(function (btn) {
  btn.addEventListener('click', function () {
    var cursoId = this.getAttribute('data-curso');
    var nome = this.getAttribute('data-nome');
    var tipo = this.getAttribute('data-tipo');

    var inputNome = document.querySelector('.geracao-nome[data-curso="' + cursoId + '"]');
    var nomeSugerido = inputNome ? inputNome.value : nome;

    document.getElementById('modalTipo').textContent = tipo;
    document.getElementById('modalCurso').textContent = nome;
    document.getElementById('modalCursoId').value = cursoId;
    document.getElementById('modalNome').value = nomeSugerido;

    var modalEl = document.getElementById('confirmarGeracaoModal');
    var modal = bootstrap.Modal.getOrCreateInstance(modalEl);
    modal.show();
  });
});
</script>