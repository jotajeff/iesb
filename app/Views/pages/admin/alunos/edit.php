<?php
  $alunoSelecionado = is_array($aluno ?? null) ? $aluno : null;
?>

<section class="container py-4">
  <div class="bg-white border rounded-3 p-4 shadow-sm">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
      <div>
        <div class="text-uppercase text-muted small fw-semibold mb-1">Cadastro de alunos</div>
        <h4 class="mb-0"><i class="bi bi-pencil-square me-2"></i>Editar aluno</h4>
      </div>
      <a class="btn btn-outline-secondary btn-sm" href="/admin/alunos">
        <i class="bi bi-arrow-left-short me-1"></i>Voltar
      </a>
    </div>

    <?php if (!$alunoSelecionado): ?>
      <div class="alert alert-warning mb-0">
        <i class="bi bi-exclamation-triangle me-2"></i>
        Nenhum aluno válido foi carregado para edição. Retorne à lista e selecione novamente.
      </div>
    <?php else: ?>
      <form action="/admin/alunos/atualizar" method="post" class="needs-validation" novalidate>
        <input type="hidden" name="id" value="<?= (int) ($alunoSelecionado['id'] ?? 0) ?>">

        <div class="row g-3">
          <div class="col-md-6">
            <label for="nome" class="form-label">Nome <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="nome" name="nome" placeholder="Nome completo" value="<?= htmlspecialchars((string) ($alunoSelecionado['nome'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" required>
            <div class="invalid-feedback">Por favor, informe o nome.</div>
          </div>

          <div class="col-md-3">
            <label for="cpf" class="form-label">CPF</label>
            <input type="text" class="form-control" id="cpf" name="cpf" placeholder="000.000.000-00" value="<?= htmlspecialchars((string) ($alunoSelecionado['cpf'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
          </div>

          <div class="col-md-3">
            <label for="data_nascimento" class="form-label">Data de Nascimento</label>
            <input type="date" class="form-control" id="data_nascimento" name="data_nascimento" value="<?= htmlspecialchars((string) ($alunoSelecionado['data_nascimento'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
          </div>

          <div class="col-md-6">
            <label for="email" class="form-label">Email</label>
            <input type="email" class="form-control" id="email" name="email" placeholder="email@exemplo.com" value="<?= htmlspecialchars((string) ($alunoSelecionado['email'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
          </div>

          <div class="col-md-3">
            <label for="telefone" class="form-label">Telefone</label>
            <input type="text" class="form-control" id="telefone" name="telefone" placeholder="(61) 99999-9999" value="<?= htmlspecialchars((string) ($alunoSelecionado['telefone'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
          </div>

          <div class="col-md-3 d-flex align-items-end">
            <div class="form-check form-switch mb-2">
              <input class="form-check-input" type="checkbox" id="ativo" name="ativo" value="1"<?= (int) ($alunoSelecionado['ativo'] ?? 0) === 1 ? ' checked' : '' ?>>
              <label class="form-check-label" for="ativo">Ativo</label>
            </div>
          </div>

          <div class="col-12">
            <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Salvar alterações</button>
          </div>
        </div>
      </form>
    <?php endif; ?>
  </div>
</section>

<script>
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
