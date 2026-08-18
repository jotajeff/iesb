<?php
$matriz = $matriz ?? [];
$modulos = $modulos ?? [];
$disciplinasDoCurso = $disciplinasDoCurso ?? [];
$idMatriz = (int) ($matriz['id'] ?? 0);
?>
<section class="container py-4">
  <div class="bg-white border rounded-3 p-4 shadow-sm">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
      <h4 class="mb-0"><i class="bi bi-diagram-3 me-2"></i><?= htmlspecialchars((string) ($matriz['nome'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></h4>
      <div class="d-flex gap-2">
        <a class="btn btn-outline-secondary btn-sm" href="/admin/academico/matrizes/form?id=<?= $idMatriz ?>"><i class="bi bi-pencil me-1"></i>Editar</a>
        <a class="btn btn-outline-secondary btn-sm" href="/admin/academico/matrizes"><i class="bi bi-arrow-left me-1"></i>Voltar</a>
      </div>
    </div>

    <div class="row g-3 mb-4">
      <div class="col-md-3">
        <div class="p-3 rounded-3 bg-light border">
          <div class="text-muted small text-uppercase">Curso</div>
          <div class="fw-semibold"><?= htmlspecialchars((string) ($matriz['curso_nome'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="p-3 rounded-3 bg-light border">
          <div class="text-muted small text-uppercase">Versão</div>
          <div class="fw-semibold"><?= htmlspecialchars((string) ($matriz['versao'] ?? '1.0'), ENT_QUOTES, 'UTF-8') ?></div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="p-3 rounded-3 bg-light border">
          <div class="text-muted small text-uppercase">Carga horária</div>
          <div class="fw-semibold"><?= (int) ($matriz['carga_horaria'] ?? 0) > 0 ? (int) ($matriz['carga_horaria'] ?? 0) . 'h' : '-' ?></div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="p-3 rounded-3 bg-light border">
          <div class="text-muted small text-uppercase">Status</div>
          <div class="fw-semibold">
            <?php if ((int) ($matriz['ativo'] ?? 0) === 1): ?>
              <span class="badge bg-success">Ativo</span>
            <?php else: ?>
              <span class="badge bg-secondary">Inativo</span>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>

    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
      <h5 class="mb-0"><i class="bi bi-collection me-2"></i>Módulos</h5>
      <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalModulo"><i class="bi bi-plus-circle me-1"></i>Novo módulo</button>
    </div>

    <div id="moduloFeedback" class="alert d-none"></div>

    <?php if (empty($modulos)): ?>
      <div class="alert alert-light border"><i class="bi bi-info-circle me-1"></i>Nenhum módulo cadastrado. Clique em "Novo módulo" para começar.</div>
    <?php else: ?>
      <?php foreach ($modulos as $modulo): ?>
        <?php $idModulo = (int) ($modulo['id'] ?? 0); ?>
        <?php $moduloAtivo = (int) ($modulo['ativo'] ?? 1) === 1; ?>
        <div id="modulo-<?= $idModulo ?>" class="card border mb-3 <?= $moduloAtivo ? '' : 'opacity-50' ?>">
          <div class="card-header bg-light d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
              <strong><?= htmlspecialchars((string) ($modulo['nome'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></strong>
              <span class="badge bg-light text-dark ms-2"><?= (int) ($modulo['ordem'] ?? 0) ?></span>
              <?php if (!empty($modulo['descricao'])): ?>
                <div class="text-muted small"><?= htmlspecialchars((string) $modulo['descricao'], ENT_QUOTES, 'UTF-8') ?></div>
              <?php endif; ?>
              <?php if ((int) ($modulo['carga_horaria'] ?? 0) > 0): ?>
                <span class="badge bg-info text-dark ms-1"><?= (int) ($modulo['carga_horaria'] ?? 0) ?>h</span>
              <?php endif; ?>
              <span class="badge bg-primary ms-1"><?= (int) ($modulo['total_disciplinas'] ?? 0) ?> disciplina(s)</span>
              <?php if (!$moduloAtivo): ?>
                <span class="badge bg-secondary ms-1">Inativo</span>
              <?php endif; ?>
            </div>
            <div class="d-flex gap-1">
              <button type="button" class="btn btn-sm btn-outline-secondary btn-editar-modulo" data-id="<?= $idModulo ?>" data-nome="<?= htmlspecialchars((string) ($modulo['nome'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" data-descricao="<?= htmlspecialchars((string) ($modulo['descricao'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" data-ordem="<?= (int) ($modulo['ordem'] ?? 0) ?>" data-carga="<?= (int) ($modulo['carga_horaria'] ?? 0) ?>" data-ativo="<?= (int) ($modulo['ativo'] ?? 1) ?>"><i class="bi bi-pencil"></i></button>
              <button type="button" class="btn btn-sm btn-outline-primary btn-add-disciplina" data-modulo-id="<?= $idModulo ?>" data-modulo-nome="<?= htmlspecialchars((string) ($modulo['nome'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"><i class="bi bi-plus-circle me-1"></i>Disciplinas</button>
              <?php if ($moduloAtivo): ?>
                <button type="button" class="btn btn-sm btn-outline-danger btn-desativar-modulo" data-id="<?= $idModulo ?>"><i class="bi bi-toggle-off"></i></button>
              <?php endif; ?>
            </div>
          </div>
          <?php $disciplinas = $modulo['disciplinas'] ?? []; ?>
          <?php if (!empty($disciplinas)): ?>
            <div class="card-body p-0">
              <table class="table table-sm align-middle mb-0">
                <thead class="table-light">
                  <tr>
                    <th style="width:60px;">Ordem</th>
                    <th>Disciplina</th>
                    <th>Carga horária</th>
                    <th>Obrigatória</th>
                    <th>Status</th>
                    <th style="width:90px;">Ações</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($disciplinas as $d): ?>
                    <tr>
                      <td><?= (int) ($d['ordem'] ?? 0) ?></td>
                      <td><?= htmlspecialchars((string) ($d['disciplina_nome'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                      <td><?= (int) ($d['disciplina_carga_horaria'] ?? 0) > 0 ? (int) ($d['disciplina_carga_horaria'] ?? 0) . 'h' : '-' ?></td>
                      <td>
                        <?php if ((int) ($d['obrigatoria'] ?? 1) === 1): ?>
                          <span class="badge bg-success">Sim</span>
                        <?php else: ?>
                          <span class="badge bg-secondary">Não</span>
                        <?php endif; ?>
                      </td>
                      <td>
                        <?php if ((int) ($d['ativo'] ?? 1) === 1): ?>
                          <span class="badge bg-success">Ativo</span>
                        <?php else: ?>
                          <span class="badge bg-secondary">Inativo</span>
                        <?php endif; ?>
                      </td>
                      <td>
                        <?php if ((int) ($d['ativo'] ?? 1) === 1): ?>
                          <button type="button" class="btn btn-sm btn-outline-danger btn-desativar-disciplina" data-id="<?= (int) ($d['id'] ?? 0) ?>"><i class="bi bi-toggle-off"></i></button>
                        <?php endif; ?>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          <?php else: ?>
            <div class="card-body"><div class="text-muted small">Nenhuma disciplina vinculada.</div></div>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>
</section>

<div class="modal fade" id="modalModulo" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form id="formModulo">
        <input type="hidden" name="id" id="moduloId" value="0">
        <input type="hidden" name="id_estrutura" value="<?= $idMatriz ?>">
        <div class="modal-header">
          <h5 class="modal-title" id="modalModuloTitulo">Novo módulo</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Nome <span class="text-danger">*</span></label>
            <input type="text" name="nome" id="moduloNome" class="form-control" maxlength="150" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Descrição</label>
            <textarea name="descricao" id="moduloDescricao" class="form-control" rows="2"></textarea>
          </div>
          <div class="row g-3">
            <div class="col-6">
              <label class="form-label">Ordem</label>
              <input type="number" name="ordem" id="moduloOrdem" class="form-control" min="0" value="0">
            </div>
            <div class="col-6">
              <label class="form-label">Carga horária</label>
              <input type="number" name="carga_horaria" id="moduloCarga" class="form-control" min="0" value="0">
            </div>
            <div class="col-12">
              <label class="form-label">Ativo</label>
              <select name="ativo" id="moduloAtivo" class="form-select">
                <option value="1">Sim</option>
                <option value="0">Não</option>
              </select>
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

<div class="modal fade" id="modalDisciplina" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form id="formDisciplina">
        <input type="hidden" name="id" id="discId" value="0">
        <input type="hidden" name="id_modulo" id="discModuloId" value="0">
        <div class="modal-header">
          <h5 class="modal-title" id="modalDisciplinaTitulo">Adicionar disciplina</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <p class="text-muted small mb-3">Módulo: <strong id="discModuloNome"></strong></p>
          <div class="mb-3">
            <label class="form-label">Disciplina <span class="text-danger">*</span></label>
            <select name="id_disciplina" id="discDisciplina" class="form-select" required>
              <option value="">Selecione...</option>
              <?php foreach ($disciplinasDoCurso as $d): ?>
                <option value="<?= (int) ($d['id'] ?? 0) ?>"><?= htmlspecialchars((string) ($d['nome'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></option>
              <?php endforeach; ?>
            </select>
            <div class="form-text">Somente disciplinas do curso desta matriz.</div>
          </div>
          <div class="row g-3">
            <div class="col-6">
              <label class="form-label">Ordem</label>
              <input type="number" name="ordem" id="discOrdem" class="form-control" min="0" value="0">
            </div>
            <div class="col-6">
              <label class="form-label">Obrigatória</label>
              <select name="obrigatoria" id="discObrigatoria" class="form-select">
                <option value="1">Sim</option>
                <option value="0">Não</option>
              </select>
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
  var modalModulo = document.getElementById('modalModulo');
  var formModulo = document.getElementById('formModulo');
  var feedback = document.getElementById('moduloFeedback');

  function showFeedback(mensagem, tipo) {
    feedback.textContent = mensagem;
    feedback.className = 'alert mb-3 ' + (tipo === 'danger' ? 'alert-danger' : 'alert-success');
  }

  document.querySelectorAll('.btn-editar-modulo').forEach(function (btn) {
    btn.addEventListener('click', function () {
      document.getElementById('moduloId').value = btn.dataset.id;
      document.getElementById('moduloNome').value = btn.dataset.nome;
      document.getElementById('moduloDescricao').value = btn.dataset.descricao || '';
      document.getElementById('moduloOrdem').value = btn.dataset.ordem || '0';
      document.getElementById('moduloCarga').value = btn.dataset.carga || '0';
      document.getElementById('moduloAtivo').value = btn.dataset.ativo || '1';
      document.getElementById('modalModuloTitulo').textContent = 'Editar módulo';
      bootstrap.Modal.getOrCreateInstance(modalModulo).show();
    });
  });

  document.querySelectorAll('.btn-desativar-modulo').forEach(function (btn) {
    btn.addEventListener('click', function () {
      if (!confirm('Desativar este módulo?')) return;
      var body = new URLSearchParams();
      body.set('id', this.dataset.id);
      fetch('/admin/academico/modulos/desativar', { method: 'POST', body: body })
        .then(function (r) { return r.json(); })
        .then(function (data) { if (data.sucesso) location.reload(); else alert(data.erro || 'Erro.'); });
    });
  });

  formModulo.addEventListener('submit', function (e) {
    e.preventDefault();
    var data = new URLSearchParams(new FormData(formModulo));
    fetch('/admin/academico/modulos/salvar', { method: 'POST', body: data })
      .then(function (r) { return r.json(); })
      .then(function (res) {
        if (res.sucesso) { bootstrap.Modal.getInstance(modalModulo).hide(); location.reload(); }
        else alert(res.erro || 'Erro ao salvar módulo.');
      })
      .catch(function () { alert('Erro ao salvar módulo.'); });
  });

  var modalDisciplina = document.getElementById('modalDisciplina');
  var formDisciplina = document.getElementById('formDisciplina');

  document.querySelectorAll('.btn-add-disciplina').forEach(function (btn) {
    btn.addEventListener('click', function () {
      document.getElementById('discId').value = '0';
      document.getElementById('discModuloId').value = btn.dataset.moduloId;
      document.getElementById('discModuloNome').textContent = btn.dataset.moduloNome;
      document.getElementById('discDisciplina').value = '';
      document.getElementById('discOrdem').value = '0';
      document.getElementById('discObrigatoria').value = '1';
      document.getElementById('modalDisciplinaTitulo').textContent = 'Adicionar disciplina';
      bootstrap.Modal.getOrCreateInstance(modalDisciplina).show();
    });
  });

  formDisciplina.addEventListener('submit', function (e) {
    e.preventDefault();
    var data = new URLSearchParams(new FormData(formDisciplina));
    fetch('/admin/academico/disciplinas/salvar', { method: 'POST', body: data })
      .then(function (r) { return r.json(); })
      .then(function (res) {
        if (res.sucesso) { bootstrap.Modal.getInstance(modalDisciplina).hide(); location.reload(); }
        else alert(res.erro || 'Erro ao salvar disciplina.');
      })
      .catch(function () { alert('Erro ao salvar disciplina.'); });
  });

  document.querySelectorAll('.btn-desativar-disciplina').forEach(function (btn) {
    btn.addEventListener('click', function () {
      if (!confirm('Desativar esta disciplina da matriz?')) return;
      var body = new URLSearchParams();
      body.set('id', this.dataset.id);
      fetch('/admin/academico/disciplinas/desativar', { method: 'POST', body: body })
        .then(function (r) { return r.json(); })
        .then(function (data) { if (data.sucesso) location.reload(); else alert(data.erro || 'Erro.'); });
    });
  });
});
</script>
