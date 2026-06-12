<?php
  $alunoData = is_array($aluno ?? null) ? $aluno : [];
  $fotoAtual = (string) ($alunoData['foto'] ?? '');

  $fields = [
    'nome' => ['label' => 'Nome completo', 'icon' => 'bi-person', 'required' => true],
    'cpf' => ['label' => 'CPF', 'icon' => 'bi-credit-card', 'required' => false],
    'data_nascimento' => ['label' => 'Data de Nascimento', 'icon' => 'bi-calendar', 'required' => false],
    'telefone' => ['label' => 'Telefone', 'icon' => 'bi-telephone', 'required' => false],
    'email' => ['label' => 'Email', 'icon' => 'bi-envelope', 'required' => false],
  ];
?>

<section class="py-4" id="perfil" style="margin-top: 20px;">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-lg-8" data-aos="fade-up">
        <div class="bg-white border rounded-3 p-4 shadow-sm" style="background: var(--bg-card); border-color: var(--border-color);">
          <div class="d-flex align-items-center gap-3 mb-4">
            <div class="position-relative" style="width: 80px; height: 80px;">
              <label for="fotoInput" class="d-block rounded-circle overflow-hidden cursor-pointer" style="width:80px;height:80px;cursor:pointer;" title="Clique para alterar a foto">
                <?php if ($fotoAtual !== ''): ?>
                  <img src="/<?= htmlspecialchars($fotoAtual, ENT_QUOTES, 'UTF-8') ?>" alt="Foto" id="fotoPreview" class="w-100 h-100" style="object-fit:cover;">
                <?php else: ?>
                  <div id="fotoPreview" class="d-flex align-items-center justify-content-center rounded-circle bg-secondary text-white" style="width:80px;height:80px;font-size:2rem;">
                    <i class="bi bi-person"></i>
                  </div>
                <?php endif; ?>
                <div class="position-absolute bottom-0 end-0 rounded-circle bg-primary d-flex align-items-center justify-content-center" style="width:28px;height:28px;border:2px solid var(--bg-card);">
                  <i class="bi bi-camera-fill text-white" style="font-size:.8rem;"></i>
                </div>
              </label>
              <form id="fotoForm" method="post" action="/aluno/foto" enctype="multipart/form-data">
                <input type="file" id="fotoInput" name="foto" accept="image/jpg,image/jpeg,image/png" style="display:none;">
              </form>
            </div>
            <div>
              <h4 class="mb-0" style="color: var(--text-heading);">Meu Perfil</h4>
              <small style="color: var(--text-secondary);">Mantenha seus dados sempre atualizados</small>
            </div>
          </div>

          <form method="post" action="/aluno/perfil/atualizar" class="needs-validation" novalidate>
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
          </form>
        </div>
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
})();

document.getElementById('fotoInput').addEventListener('change', function() {
  if (this.files && this.files[0]) {
    var reader = new FileReader();
    reader.onload = function(e) {
      var preview = document.getElementById('fotoPreview');
      if (preview.tagName === 'IMG') {
        preview.src = e.target.result;
      } else {
        var img = document.createElement('img');
        img.id = 'fotoPreview';
        img.className = 'w-100 h-100 rounded-circle';
        img.style.objectFit = 'cover';
        img.src = e.target.result;
        preview.parentNode.replaceChild(img, preview);
      }
    };
    reader.readAsDataURL(this.files[0]);
    document.getElementById('fotoForm').submit();
  }
});
</script>
