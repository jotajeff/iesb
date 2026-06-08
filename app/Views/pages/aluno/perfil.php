<?php
  $alunoData = is_array($aluno ?? null) ? $aluno : [];

  $fields = [
    'nome' => ['label' => 'Nome completo', 'icon' => 'bi-person', 'required' => true],
    'cpf' => ['label' => 'CPF', 'icon' => 'bi-credit-card', 'required' => false],
    'data_nascimento' => ['label' => 'Data de Nascimento', 'icon' => 'bi-calendar', 'required' => false],
    'telefone' => ['label' => 'Telefone', 'icon' => 'bi-telephone', 'required' => false],
    'email' => ['label' => 'Email', 'icon' => 'bi-envelope', 'required' => false],
  ];
?>

<section class="py-4" id="perfil" style="margin-top: 76px;">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-lg-8" data-aos="fade-up">
        <div class="d-flex align-items-center gap-3 mb-4">
          <div class="d-flex align-items-center justify-content-center rounded-circle" style="width: 64px; height: 64px; background: linear-gradient(135deg, #0d6efd, #0a58ca); color: #fff; font-size: 1.75rem;">
            <i class="bi bi-person-fill"></i>
          </div>
          <div>
            <h4 class="mb-0">Meu Perfil</h4>
            <small class="text-muted">Mantenha seus dados sempre atualizados</small>
          </div>
        </div>

        <form method="post" action="/aluno/perfil/atualizar" class="needs-validation" novalidate>
          <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 16px; padding: 1.5rem; box-shadow: var(--card-shadow);">
            <div class="row g-3">
              <?php foreach ($fields as $key => $field):
                $value = (string) ($alunoData[$key] ?? '');
                $isEmpty = $value === '' || $value === '-' || $value === '000.000.000-00';
                $inputType = match ($key) {
                  'data_nascimento' => 'date',
                  'email' => 'email',
                  default => 'text',
                };
              ?>
                <div class="col-md-<?= $key === 'nome' ? '12' : '6' ?>">
                  <label for="<?= $key ?>" class="form-label d-flex align-items-center gap-2">
                    <i class="bi <?= $field['icon'] ?>"></i><?= $field['label'] ?>
                    <?php if ($field['required']): ?><span class="text-danger">*</span><?php endif; ?>
                    <?php if ($isEmpty): ?>
                      <span class="badge bg-warning text-dark ms-auto"><i class="bi bi-exclamation-triangle-fill me-1"></i>Pendente</span>
                    <?php endif; ?>
                  </label>
                  <input type="<?= $inputType ?>" class="form-control <?= $isEmpty ? 'border-warning' : '' ?>" id="<?= $key ?>" name="<?= $key ?>" value="<?= htmlspecialchars($value, ENT_QUOTES, 'UTF-8') ?>" <?= $field['required'] ? 'required' : '' ?> placeholder="<?= $field['label'] ?>">
                  <?php if ($field['required']): ?>
                    <div class="invalid-feedback">Informe <?= lcfirst($field['label']) ?>.</div>
                  <?php endif; ?>
                </div>
              <?php endforeach; ?>

              <div class="col-12">
                <hr>
                <label for="senha" class="form-label d-flex align-items-center gap-2">
                  <i class="bi bi-lock"></i>Nova senha
                  <small class="text-muted fw-normal">(deixe em branco para manter a atual)</small>
                </label>
                <input type="password" class="form-control" id="senha" name="senha" placeholder="Nova senha">
              </div>
            </div>

            <div class="d-flex gap-2 mt-4">
              <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Salvar</button>
              <a href="/aluno" class="btn btn-outline-secondary"><i class="bi bi-x-lg me-1"></i>Cancelar</a>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</section>

<script>
(function () {
  'use strict'
  var forms = document.querySelectorAll('.needs-validation')
  Array.prototype.slice.call(forms).forEach(function (form) {
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
