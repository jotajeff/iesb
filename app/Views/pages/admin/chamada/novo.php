<?php
  $turmasView = is_array($turmas ?? null) ? $turmas : [];
?>

<section class="container py-4">
  <div class="bg-white border rounded-3 p-4 shadow-sm">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
      <h4 class="mb-0"><i class="bi bi-clipboard-plus me-2"></i>Gerar Chamada</h4>
      <a class="btn btn-outline-secondary btn-sm" href="/admin/chamadas"><i class="bi bi-arrow-left me-1"></i>Voltar</a>
    </div>

    <p class="text-muted small">
      Selecione a turma, o professor e a disciplina. A chamada será aberta para todos os alunos matriculados na disciplina selecionada (presença inicial: Presente).
    </p>

    <form method="post" action="/admin/chamadas/gerar" class="row g-3">
      <div class="col-md-4">
        <label class="form-label">Turma <span class="text-danger">*</span></label>
        <select class="form-select" name="id_turma" id="chamadaTurma" required>
          <option value="">Selecione a turma</option>
          <?php foreach ($turmasView as $turma): ?>
            <option value="<?= (int) ($turma['id'] ?? 0) ?>"><?= htmlspecialchars((string) ($turma['nome'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="col-md-4">
        <label class="form-label">Professor <span class="text-danger">*</span></label>
        <select class="form-select" name="id_usuario_professor" id="chamadaProfessor" required disabled>
          <option value="">Primeiro selecione a turma</option>
        </select>
      </div>

      <div class="col-md-4">
        <label class="form-label">Disciplina <span class="text-danger">*</span></label>
        <select class="form-select" name="id_turma_disciplina" id="chamadaDisciplina" required disabled>
          <option value="">Primeiro selecione o professor</option>
        </select>
      </div>

      <div class="col-md-6">
        <label class="form-label">Data da aula <span class="text-danger">*</span></label>
        <input type="date" class="form-control" name="data_aula" value="<?= date('Y-m-d') ?>" required>
      </div>

      <div class="col-md-6">
        <label class="form-label">Número da aula</label>
        <input type="number" class="form-control" name="numero_aula" min="1" max="999">
      </div>

      <div class="col-md-4">
        <label class="form-label">Hora de início</label>
        <input type="time" class="form-control" name="hora_inicio">
      </div>

      <div class="col-md-4">
        <label class="form-label">Hora de fim</label>
        <input type="time" class="form-control" name="hora_fim">
      </div>

      <div class="col-md-4">
        <label class="form-label">Status</label>
        <select class="form-select" name="status">
          <option value="ABERTA" selected>ABERTA</option>
          <option value="FECHADA">FECHADA</option>
          <option value="CANCELADA">CANCELADA</option>
        </select>
      </div>

      <div class="col-12">
        <label class="form-label">Conteúdo da aula</label>
        <textarea class="form-control" name="conteudo" rows="3" placeholder="Conteúdo ministrado (opcional)"></textarea>
      </div>

      <div class="col-12">
        <label class="form-label">Observação</label>
        <textarea class="form-control" name="observacao" rows="2" placeholder="Observações (opcional)"></textarea>
      </div>

      <div class="col-12">
        <button class="btn btn-success" type="submit"><i class="bi bi-check-lg me-1"></i>Gerar chamada</button>
      </div>
    </form>
  </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function () {
  var selTurma = document.getElementById('chamadaTurma');
  var selProfessor = document.getElementById('chamadaProfessor');
  var selDisciplina = document.getElementById('chamadaDisciplina');

  function limpar(select, texto) {
    select.innerHTML = '';
    var opt = document.createElement('option');
    opt.value = '';
    opt.textContent = texto;
    select.appendChild(opt);
  }

  function carregarProfessores(idTurma) {
    limpar(selProfessor, 'Carregando professores...');
    limpar(selDisciplina, 'Primeiro selecione o professor');
    selProfessor.disabled = true;
    selDisciplina.disabled = true;
    if (!idTurma) {
      limpar(selProfessor, 'Primeiro selecione a turma');
      return;
    }
    fetch('/admin/chamadas/ajax-professores?id_turma=' + encodeURIComponent(idTurma))
      .then(function (r) {
        if (!r.ok) {
          throw new Error('Falha ao carregar professores');
        }
        return r.json();
      })
      .then(function (itens) {
        if (!Array.isArray(itens)) {
          throw new Error('Resposta inválida');
        }

        limpar(selProfessor, itens.length ? 'Selecione o professor' : 'Nenhum professor vinculado');
        itens.forEach(function (p) {
          var opt = document.createElement('option');
          opt.value = p.id;
          opt.textContent = p.nome;
          selProfessor.appendChild(opt);
        });
        selProfessor.disabled = itens.length === 0;
      })
      .catch(function () {
        limpar(selProfessor, 'Erro ao carregar professores');
        selProfessor.disabled = true;
      });
  }

  function carregarDisciplinas() {
    var idTurma = selTurma.value;
    var idProfessor = selProfessor.value;
    limpar(selDisciplina, 'Carregando disciplinas...');
    selDisciplina.disabled = true;
    if (!idTurma || !idProfessor) {
      limpar(selDisciplina, 'Primeiro selecione o professor');
      return;
    }
    fetch('/admin/chamadas/ajax-disciplinas?id_turma=' + encodeURIComponent(idTurma) + '&id_professor=' + encodeURIComponent(idProfessor))
      .then(function (r) {
        if (!r.ok) {
          throw new Error('Falha ao carregar disciplinas');
        }
        return r.json();
      })
      .then(function (itens) {
        if (!Array.isArray(itens)) {
          throw new Error('Resposta inválida');
        }

        limpar(selDisciplina, itens.length ? 'Selecione a disciplina' : 'Nenhuma disciplina vinculada');
        itens.forEach(function (d) {
          var opt = document.createElement('option');
          opt.value = d.id;
          opt.textContent = d.disciplina_nome;
          selDisciplina.appendChild(opt);
        });
        selDisciplina.disabled = itens.length === 0;
      })
      .catch(function () {
        limpar(selDisciplina, 'Erro ao carregar disciplinas');
        selDisciplina.disabled = true;
      });
  }

  selTurma.addEventListener('change', function () { carregarProfessores(selTurma.value); });
  selProfessor.addEventListener('change', carregarDisciplinas);
});
</script>
