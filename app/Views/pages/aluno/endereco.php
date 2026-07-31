<?php
  $enderecoData = is_array($endereco ?? null) ? $endereco : [];
  $campos = [
    'cep' => ['label' => 'CEP', 'icon' => 'bi-geo', 'required' => true, 'maxlength' => 9, 'placeholder' => '00000-000'],
    'logradouro' => ['label' => 'Logradouro', 'icon' => 'bi-signpost-2', 'required' => true, 'maxlength' => 255, 'placeholder' => 'Rua, avenida, etc.'],
    'numero' => ['label' => 'Número', 'icon' => 'bi-house', 'required' => false, 'maxlength' => 20, 'placeholder' => 'Nº'],
    'cidade' => ['label' => 'Cidade', 'icon' => 'bi-building', 'required' => true, 'maxlength' => 100, 'placeholder' => 'Cidade'],
    'uf' => ['label' => 'UF', 'icon' => 'bi-flag', 'required' => true, 'maxlength' => 2, 'placeholder' => 'UF'],
  ];
  $ufs = ['AC','AL','AP','AM','BA','CE','DF','ES','GO','MA','MT','MS','MG','PA','PB','PR','PE','PI','RJ','RN','RS','RO','RR','SC','SP','SE','TO'];
  $ufAtual = (string) ($enderecoData['uf'] ?? '');
?>
<section class="py-4" id="endereco" style="margin-top: 20px;">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-lg-8" data-aos="fade-up">
        <div class="bg-white border rounded-3 p-4 shadow-sm" style="background: var(--bg-card); border-color: var(--border-color);">
          <div class="d-flex align-items-center gap-3 mb-4">
            <i class="bi bi-geo-alt-fill fs-2 text-primary"></i>
            <div>
              <h4 class="mb-0" style="color: var(--text-heading);">Meu Endereço</h4>
              <small style="color: var(--text-secondary);">Atualize seus dados de localização</small>
            </div>
          </div>

          <?php if (!empty($flash ?? '')): ?>
            <div class="alert alert-info"><?= htmlspecialchars((string) $flash, ENT_QUOTES, 'UTF-8') ?></div>
          <?php endif; ?>

          <form method="post" action="/aluno/endereco/atualizar" class="needs-validation" novalidate>
            <div class="row g-3">
              <div class="col-md-4">
                <label for="cepInput" class="form-label d-flex align-items-center gap-2">
                  <i class="bi bi-geo"></i>CEP <span class="text-danger">*</span>
                </label>
                <input type="text" class="form-control" id="cepInput" name="cep" value="<?= htmlspecialchars((string) ($enderecoData['cep'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" maxlength="9" placeholder="00000-000" required>
                <div id="cepFeedback" class="invalid-feedback"></div>
              </div>
              <div class="col-md-6">
                <label for="logradouroInput" class="form-label d-flex align-items-center gap-2">
                  <i class="bi bi-signpost-2"></i>Logradouro <span class="text-danger">*</span>
                </label>
                <input type="text" class="form-control" id="logradouroInput" name="logradouro" value="<?= htmlspecialchars((string) ($enderecoData['logradouro'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" maxlength="255" required>
              </div>
              <div class="col-md-2">
                <label for="numero" class="form-label d-flex align-items-center gap-2">
                  <i class="bi bi-house"></i>Número
                </label>
                <input type="text" class="form-control" id="numero" name="numero" value="<?= htmlspecialchars((string) ($enderecoData['numero'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" maxlength="20">
              </div>
              <div class="col-md-5">
                <label for="cidadeInput" class="form-label d-flex align-items-center gap-2">
                  <i class="bi bi-building"></i>Cidade <span class="text-danger">*</span>
                </label>
                <input type="text" class="form-control" id="cidadeInput" name="cidade" value="<?= htmlspecialchars((string) ($enderecoData['cidade'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" maxlength="100" required>
              </div>
              <div class="col-md-3">
                <label for="ufSelect" class="form-label d-flex align-items-center gap-2">
                  <i class="bi bi-flag"></i>UF <span class="text-danger">*</span>
                </label>
                <select class="form-select" name="uf" id="ufSelect" required>
                  <option value="">Selecione</option>
                  <?php foreach ($ufs as $uf): ?>
                    <option value="<?= $uf ?>" <?= $ufAtual === $uf ? 'selected' : '' ?>><?= $uf ?></option>
                  <?php endforeach; ?>
                </select>
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
document.getElementById('cepInput').addEventListener('blur', function () {
  var cep = this.value.replace(/\D/g, '');
  var feedback = document.getElementById('cepFeedback');

  if (cep.length !== 8) {
    this.classList.add('is-invalid');
    feedback.textContent = 'CEP deve ter 8 dígitos.';
    return;
  }

  this.classList.remove('is-invalid');
  feedback.textContent = '';

  fetch('/aluno/buscar-cep?cep=' + cep)
    .then(function (res) { return res.json(); })
    .then(function (data) {
      if (data.erro) {
        throw new Error(data.erro);
      }
      document.getElementById('logradouroInput').value = data.logradouro || '';
      document.getElementById('cidadeInput').value = data.cidade || '';
      var ufSelect = document.getElementById('ufSelect');
      if (data.uf) {
        for (var i = 0; i < ufSelect.options.length; i++) {
          if (ufSelect.options[i].value === data.uf) {
            ufSelect.selectedIndex = i;
            break;
          }
        }
      }
    })
    .catch(function (err) {
      document.getElementById('cepInput').classList.add('is-invalid');
      feedback.textContent = err.message || 'Erro ao buscar CEP.';
    });
});

document.getElementById('cepInput').addEventListener('input', function () {
  var raw = this.value.replace(/\D/g, '');
  if (raw.length > 5) {
    this.value = raw.slice(0, 5) + '-' + raw.slice(5, 8);
  } else {
    this.value = raw;
  }
  this.classList.remove('is-invalid');
  document.getElementById('cepFeedback').textContent = '';
});
</script>
