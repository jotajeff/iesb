<section class="container py-4">
  <div class="bg-white border rounded-3 p-4 shadow-sm">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
      <div>
        <div class="text-uppercase text-muted small fw-semibold mb-1">Cadastro de alunos</div>
        <h4 class="mb-0"><i class="bi bi-people me-2"></i>Novo aluno</h4>
      </div>
      <a class="btn btn-outline-secondary btn-sm" href="/admin/alunos">
        <i class="bi bi-arrow-left-short me-1"></i>Voltar
      </a>
    </div>

    <form action="/admin/alunos/salvar" method="post" class="needs-validation" novalidate>
      <div class="alert alert-info d-flex align-items-center gap-2 py-2 mb-3">
        <i class="bi bi-info-circle-fill"></i>
        <span>A senha será gerada automaticamente com o prefixo do email + "#" + ano atual.</span>
      </div>
      <div class="row g-3">
        <div class="col-md-6">
          <label for="nome" class="form-label">Nome <span class="text-danger">*</span></label>
          <input type="text" class="form-control" id="nome" name="nome" placeholder="Nome completo" required>
          <div class="invalid-feedback">Por favor, informe o nome.</div>
        </div>

        <div class="col-md-3">
          <label for="cpf" class="form-label">CPF</label>
          <input type="text" class="form-control" id="cpf" name="cpf" placeholder="000.000.000-00">
        </div>

        <div class="col-md-3">
          <label for="data_nascimento" class="form-label">Data de Nascimento</label>
          <input type="date" class="form-control" id="data_nascimento" name="data_nascimento">
        </div>

        <div class="col-md-6">
          <label for="email" class="form-label">Email</label>
          <input type="email" class="form-control" id="email" name="email" placeholder="email@exemplo.com">
        </div>

        <div class="col-md-3">
          <label for="telefone" class="form-label">Telefone</label>
          <input type="text" class="form-control" id="telefone" name="telefone" placeholder="(61) 99999-9999">
        </div>

        <div class="col-md-3 d-flex align-items-end">
          <div class="form-check form-switch mb-2">
            <input class="form-check-input" type="checkbox" id="ativo" name="ativo" value="1" checked>
            <label class="form-check-label" for="ativo">Ativo</label>
          </div>
        </div>

        <div class="col-12">
          <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Salvar</button>
        </div>
      </div>
    </form>
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
