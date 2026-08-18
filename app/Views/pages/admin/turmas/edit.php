<?php
  $turmaSelecionada = is_array($turma ?? null) ? $turma : null;
  $cursosLista = is_array($cursos ?? null) ? $cursos : [];
  $matrizesLista = is_array($matrizes ?? null) ? $matrizes : [];
?>

<section class="container py-4">
  <div class="bg-white border rounded-3 p-4 shadow-sm">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
      <div>
        <div class="text-uppercase text-muted small fw-semibold mb-1">Cadastro de turmas</div>
        <h4 class="mb-0"><i class="bi bi-pencil-square me-2"></i>Editar turma</h4>
      </div>
      <a class="btn btn-outline-secondary btn-sm" href="/admin/turmas">
        <i class="bi bi-arrow-left-short me-1"></i>Voltar
      </a>
    </div>

    <?php if (!$turmaSelecionada): ?>
      <div class="alert alert-warning mb-0">
        <i class="bi bi-exclamation-triangle me-2"></i>
        Nenhuma turma válida foi carregada para edição. Retorne à lista e selecione novamente.
      </div>
    <?php else: ?>
      <form action="/admin/turmas/atualizar" method="post" class="needs-validation" novalidate>
        <input type="hidden" name="id" value="<?= (int) ($turmaSelecionada['id'] ?? 0) ?>">

        <div class="mb-3">
          <label for="nome" class="form-label">Nome da Turma</label>
          <input type="text" class="form-control" id="nome" name="nome" placeholder="Nome da turma" value="<?= htmlspecialchars((string) ($turmaSelecionada['nome'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" required>
          <div class="invalid-feedback">Por favor, informe o nome da turma.</div>
        </div>

        <div class="mb-3">
          <label for="curso" class="form-label">Curso</label>
          <select class="form-select" id="curso" name="curso" required>
            <option value="">Selecione um curso</option>
            <?php foreach ($cursosLista as $curso): ?>
              <option value="<?= htmlspecialchars((string) ($curso['id'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"<?= (string) ($curso['id'] ?? '') === (string) ($turmaSelecionada['id_curso'] ?? '') ? ' selected' : '' ?>><?= htmlspecialchars((string) ($curso['nome'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></option>
            <?php endforeach; ?>
          </select>
          <div class="invalid-feedback">Por favor, selecione um curso.</div>
        </div>

        <div class="mb-3">
          <label for="id_estrutura" class="form-label">Matriz curricular</label>
          <select class="form-select" id="id_estrutura" name="id_estrutura">
            <option value="">Sem matriz definida</option>
            <?php foreach ($matrizesLista as $matriz): ?>
              <option value="<?= (int) ($matriz['id'] ?? 0) ?>" data-curso="<?= (int) ($matriz['id_curso'] ?? 0) ?>"<?= (int) ($matriz['id'] ?? 0) === (int) ($turmaSelecionada['id_estrutura'] ?? 0) ? ' selected' : '' ?>><?= htmlspecialchars((string) ($matriz['curso_nome'] ?? '') . ' — ' . (string) ($matriz['nome'] ?? ''), ENT_QUOTES, 'UTF-8') ?></option>
            <?php endforeach; ?>
          </select>
          <div class="form-text">A turma utilizará os módulos e disciplinas desta matriz.</div>
        </div>

        <div class="mb-3">
          <label for="data_inicio" class="form-label">Data de Início</label>
          <input type="date" class="form-control" id="data_inicio" name="data_inicio" value="<?= htmlspecialchars((string) ($turmaSelecionada['data_inicio'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
        </div>

        <div class="mb-3 form-check form-switch">
          <input class="form-check-input" type="checkbox" id="ativo" name="ativo" value="1"<?= (int) ($turmaSelecionada['ativo'] ?? 0) === 1 ? ' checked' : '' ?>>
          <label class="form-check-label" for="ativo">Turma Ativa</label>
        </div>

        <button type="submit" class="btn btn-primary">Salvar alterações</button>
      </form>
    <?php endif; ?>
  </div>
</section>

<script>
// Example JavaScript for disabling form submissions if there are invalid fields
(function () {
  'use strict'
  var forms = document.querySelectorAll('.needs-validation')
  Array.prototype.slice.call(forms)
    .forEach(function (form) {
      form.addEventListener('submit', function (event) {
        if (!form.checkValidity()) {
          event.preventDefault()
          event.stopPropagation()
        }
        form.classList.add('was-validated')
      }, false)
    })
})()
</script>
