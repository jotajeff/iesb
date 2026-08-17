<?php
$cursos = $cursos ?? [];
$turmas = $turmas ?? [];
$matriculas = $matriculas ?? [];
$termo = (string) ($termo ?? '');
$idCurso = (int) ($idCurso ?? 0);
$idTurma = (int) ($idTurma ?? 0);
$matriculaDetalhe = $matriculaDetalhe ?? null;
$matriculaId = (int) ($matriculaId ?? 0);
$disciplinasMatricula = $disciplinasMatricula ?? [];
?>
<section class="container py-4">
  <div class="bg-white border rounded-3 p-4 shadow-sm">
    <h4 class="mb-3"><i class="bi bi-clipboard-check me-2"></i>Situação Acadêmica</h4>

    <?php if (!empty($flash ?? '')): ?>
      <div class="alert alert-info"><?= htmlspecialchars((string) $flash, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>

    <form method="get" action="/admin/academico/situacao" class="row g-2 align-items-end mb-3">
      <div class="col-md-4">
        <label class="form-label small text-muted mb-1">Buscar (aluno, CPF ou matrícula)</label>
        <input type="text" name="termo" class="form-control form-control-sm" value="<?= htmlspecialchars($termo, ENT_QUOTES, 'UTF-8') ?>" placeholder="Nome, CPF ou nº de matrícula">
      </div>
      <div class="col-md-3">
        <label class="form-label small text-muted mb-1">Curso</label>
        <select name="id_curso" class="form-select form-select-sm">
          <option value="">Todos</option>
          <?php foreach ($cursos as $curso): ?>
            <option value="<?= (int) ($curso['id'] ?? 0) ?>" <?= $idCurso === (int) ($curso['id'] ?? 0) ? 'selected' : '' ?>><?= htmlspecialchars((string) ($curso['nome'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-3">
        <label class="form-label small text-muted mb-1">Turma</label>
        <select name="id_turma" class="form-select form-select-sm">
          <option value="">Todas</option>
          <?php foreach ($turmas as $turma): ?>
            <option value="<?= (int) ($turma['id'] ?? 0) ?>" <?= $idTurma === (int) ($turma['id'] ?? 0) ? 'selected' : '' ?>><?= htmlspecialchars((string) ($turma['nome'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-2">
        <button type="submit" class="btn btn-sm btn-primary w-100"><i class="bi bi-search me-1"></i>Buscar</button>
      </div>
    </form>

    <?php if ($matriculaDetalhe !== null): ?>
      <div class="border rounded-3 p-3 mb-3 bg-light">
        <div class="row g-3">
          <div class="col-md-3">
            <div class="text-muted small text-uppercase">Aluno</div>
            <div class="fw-semibold"><?= htmlspecialchars((string) ($matriculaDetalhe['aluno_nome'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></div>
            <div class="text-muted small"><?= htmlspecialchars((string) ($matriculaDetalhe['cpf'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></div>
          </div>
          <div class="col-md-3">
            <div class="text-muted small text-uppercase">Matrícula</div>
            <div class="fw-semibold">#<?= htmlspecialchars((string) ($matriculaDetalhe['numero'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></div>
          </div>
          <div class="col-md-3">
            <div class="text-muted small text-uppercase">Curso</div>
            <div class="fw-semibold"><?= htmlspecialchars((string) ($matriculaDetalhe['curso_nome'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></div>
          </div>
          <div class="col-md-3">
            <div class="text-muted small text-uppercase">Turma</div>
            <div class="fw-semibold"><?= htmlspecialchars((string) ($matriculaDetalhe['turma_nome'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></div>
          </div>
        </div>
      </div>
    <?php endif; ?>

    <?php if ($matriculaDetalhe !== null): ?>
      <h5 class="mb-3">Disciplinas da matrícula</h5>
      <?php if (empty($disciplinasMatricula)): ?>
        <div class="alert alert-light border text-muted">
          <i class="bi bi-info-circle me-1"></i>Esta matrícula ainda não possui disciplinas vinculadas. Vincule na tela da turma (seção "Alunos inscritos").
        </div>
      <?php else: ?>
        <div class="table-responsive">
          <table class="table table-striped table-sm align-middle">
            <thead>
              <tr>
                <th>Disciplina</th>
                <th>Professor</th>
                <th>Situação</th>
                <th>Nota</th>
                <th>Frequência</th>
                <th>Ações</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($disciplinasMatricula as $md): ?>
                <tr>
                  <td><?= htmlspecialchars((string) ($md['disciplina_nome'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                  <td><?= htmlspecialchars((string) ($md['professor_nome'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                  <td>
                    <?php
                      $sit = (string) ($md['situacao'] ?? 'MATRICULADO');
                      $sitLabel = match ($sit) {
                        'MATRICULADO' => 'Matriculado',
                        'CURSANDO' => 'Cursando',
                        'APROVADO' => 'Aprovado',
                        'REPROVADO' => 'Reprovado',
                        'DISPENSADO' => 'Dispensado',
                        'TRANCADO' => 'Trancado',
                        'CANCELADO' => 'Cancelado',
                        default => $sit,
                      };
                      $sitClass = match ($sit) {
                        'APROVADO' => 'bg-success',
                        'REPROVADO' => 'bg-danger',
                        'CURSANDO' => 'bg-info text-dark',
                        'DISPENSADO' => 'bg-primary',
                        'TRANCADO', 'CANCELADO' => 'bg-secondary',
                        default => 'bg-warning text-dark',
                      };
                    ?>
                    <span class="badge <?= $sitClass ?>"><?= htmlspecialchars($sitLabel, ENT_QUOTES, 'UTF-8') ?></span>
                  </td>
                  <td><?= $md['nota'] !== null ? number_format((float) $md['nota'], 2, ',', '.') : '--' ?></td>
                  <td><?= $md['frequencia'] !== null ? number_format((float) $md['frequencia'], 2, ',', '.') . '%' : '--' ?></td>
                  <td>
                    <button type="button" class="btn btn-sm btn-outline-primary btn-editar-situacao"
                            data-id="<?= (int) ($md['id'] ?? 0) ?>"
                            data-matricula="<?= $matriculaId ?>"
                            data-disciplina="<?= htmlspecialchars((string) ($md['disciplina_nome'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                            data-situacao="<?= htmlspecialchars($sit, ENT_QUOTES, 'UTF-8') ?>"
                            data-nota="<?= $md['nota'] !== null ? (string) $md['nota'] : '' ?>"
                            data-frequencia="<?= $md['frequencia'] !== null ? (string) $md['frequencia'] : '' ?>"
                            data-conclusao="<?= htmlspecialchars((string) ($md['data_conclusao'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                            data-observacao="<?= htmlspecialchars((string) ($md['observacao'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"><i class="bi bi-pencil"></i></button>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    <?php endif; ?>

    <h5 class="mt-4 mb-3">Matrículas encontradas (<?= count($matriculas) ?>)</h5>
    <div class="table-responsive">
      <table class="table table-striped table-sm align-middle">
        <thead>
          <tr>
            <th>#</th>
            <th>Matrícula</th>
            <th>Aluno</th>
            <th>CPF</th>
            <th>Curso</th>
            <th>Turma</th>
            <th>Disciplinas</th>
            <th>Ações</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($matriculas)): ?>
            <tr><td colspan="8" class="text-muted"><i class="bi bi-inbox me-1"></i>Nenhuma matrícula encontrada.</td></tr>
          <?php endif; ?>
          <?php foreach ($matriculas as $m): ?>
            <tr>
              <td><?= (int) ($m['id_matricula'] ?? 0) ?></td>
              <td><?= htmlspecialchars((string) ($m['numero'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
              <td><?= htmlspecialchars((string) ($m['aluno_nome'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
              <td><?= htmlspecialchars((string) ($m['cpf'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
              <td><?= htmlspecialchars((string) ($m['curso_nome'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
              <td><?= htmlspecialchars((string) ($m['turma_nome'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
              <td><span class="badge bg-primary"><?= (int) ($m['total_disciplinas'] ?? 0) ?></span></td>
              <td>
                <a class="btn btn-sm btn-outline-primary" href="/admin/academico/situacao?matricula=<?= (int) ($m['id_matricula'] ?? 0) ?>&termo=<?= urlencode($termo) ?>&id_curso=<?= $idCurso ?>&id_turma=<?= $idTurma ?>"><i class="bi bi-eye me-1"></i>Abrir</a>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</section>

<div class="modal fade" id="modalSituacaoDisciplina" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form id="formSituacaoDisciplina">
        <input type="hidden" name="id" id="sitId" value="0">
        <input type="hidden" name="id_matricula" id="sitMatriculaId" value="0">
        <div class="modal-header">
          <h5 class="modal-title">Situação acadêmica</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <p class="text-muted small mb-3">Disciplina: <strong id="sitDisciplina"></strong></p>
          <div class="mb-3">
            <label class="form-label">Situação</label>
            <select name="situacao" id="sitSituacao" class="form-select">
              <option value="MATRICULADO">Matriculado</option>
              <option value="CURSANDO">Cursando</option>
              <option value="APROVADO">Aprovado</option>
              <option value="REPROVADO">Reprovado</option>
              <option value="DISPENSADO">Dispensado</option>
              <option value="TRANCADO">Trancado</option>
              <option value="CANCELADO">Cancelado</option>
            </select>
          </div>
          <div class="row g-3">
            <div class="col-6">
              <label class="form-label">Nota (0 a 10)</label>
              <input type="number" name="nota" id="sitNota" class="form-control" step="0.01" min="0" max="10">
            </div>
            <div class="col-6">
              <label class="form-label">Frequência (0 a 100)</label>
              <input type="number" name="frequencia" id="sitFrequencia" class="form-control" step="0.01" min="0" max="100">
            </div>
            <div class="col-12">
              <label class="form-label">Data de conclusão</label>
              <input type="date" name="data_conclusao" id="sitDataConclusao" class="form-control">
            </div>
            <div class="col-12">
              <label class="form-label">Observação</label>
              <textarea name="observacao" id="sitObservacao" class="form-control" rows="2"></textarea>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Salvar</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
  var form = document.getElementById('formSituacaoDisciplina');
  var modal = document.getElementById('modalSituacaoDisciplina');

  document.querySelectorAll('.btn-editar-situacao').forEach(function (btn) {
    btn.addEventListener('click', function () {
      document.getElementById('sitId').value = btn.dataset.id;
      document.getElementById('sitMatriculaId').value = btn.dataset.matricula;
      document.getElementById('sitDisciplina').textContent = btn.dataset.disciplina || '';
      document.getElementById('sitSituacao').value = btn.dataset.situacao || 'MATRICULADO';
      document.getElementById('sitNota').value = btn.dataset.nota || '';
      document.getElementById('sitFrequencia').value = btn.dataset.frequencia || '';
      document.getElementById('sitDataConclusao').value = btn.dataset.conclusao || '';
      document.getElementById('sitObservacao').value = btn.dataset.observacao || '';
      bootstrap.Modal.getOrCreateInstance(modal).show();
    });
  });

  if (form) {
    form.addEventListener('submit', function (e) {
      e.preventDefault();
      var data = new URLSearchParams(new FormData(form));
      fetch('/admin/academico/situacao/salvar', { method: 'POST', body: data })
        .then(function (r) { return r.json(); })
        .then(function (res) {
          if (res.sucesso) { bootstrap.Modal.getInstance(modal).hide(); location.reload(); }
          else alert(res.erro || 'Erro ao salvar situação.');
        })
        .catch(function () { alert('Erro ao salvar situação.'); });
    });
  }
});
</script>
