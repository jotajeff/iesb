<section class="container py-4">
    <div class="bg-white border rounded-3 p-4 shadow-sm">
      <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <h4 class="mb-0"><i class="bi bi-geo-alt me-2"></i>Endereço do Professor</h4>
        <a class="btn btn-outline-secondary btn-sm" href="<?= htmlspecialchars((string) ($backRoute ?? '/admin/professores'), ENT_QUOTES, 'UTF-8') ?>"><i class="bi bi-arrow-left me-1"></i>Voltar</a>
      </div>

      <p class="mb-3"><strong>Professor:</strong> <?= htmlspecialchars((string) ($professor['nome'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p>

    <form method="post" action="/admin/professores/salvar-endereco" class="row g-3">
      <input type="hidden" name="id" value="<?= (int) ($professor['id'] ?? 0) ?>">

      <div class="col-md-4">
        <label class="form-label">CEP <span class="text-danger">*</span></label>
        <input class="form-control" type="text" name="cep" id="cepInput" required value="<?= htmlspecialchars((string) ($endereco['cep'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" placeholder="00000-000" maxlength="9">
        <div id="cepFeedback" class="invalid-feedback"></div>
      </div>
      <div class="col-md-6">
        <label class="form-label">Logradouro <span class="text-danger">*</span></label>
        <input class="form-control" type="text" name="logradouro" id="logradouroInput" required value="<?= htmlspecialchars((string) ($endereco['logradouro'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
      </div>
      <div class="col-md-2">
        <label class="form-label">Número</label>
        <input class="form-control" type="text" name="numero" value="<?= htmlspecialchars((string) ($endereco['numero'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
      </div>
      <div class="col-md-5">
        <label class="form-label">Cidade <span class="text-danger">*</span></label>
        <input class="form-control" type="text" name="cidade" id="cidadeInput" required value="<?= htmlspecialchars((string) ($endereco['cidade'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
      </div>
      <div class="col-md-3">
        <label class="form-label">UF <span class="text-danger">*</span></label>
        <select class="form-select" name="uf" id="ufSelect" required>
          <option value="">Selecione</option>
          <?php $ufs = ['AC','AL','AP','AM','BA','CE','DF','ES','GO','MA','MT','MS','MG','PA','PB','PR','PE','PI','RJ','RN','RS','RO','RR','SC','SP','SE','TO']; ?>
          <?php foreach ($ufs as $uf): ?>
            <option value="<?= $uf ?>" <?= ((string) ($endereco['uf'] ?? '') === $uf) ? 'selected' : '' ?>><?= $uf ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="col-12">
        <button class="btn btn-success" type="submit"><i class="bi bi-check-lg me-1"></i>Salvar Endereço</button>
      </div>
    </form>
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

  fetch('/admin/professores/endereco?ajax=1&cep=' + cep)
    .then(function (res) {
      console.log('Response status:', res.status);
      console.log('Response headers:', [...res.headers.entries()]);
      return res.text().then(function(text) {
        console.log('Response body:', text);
        try {
          var json = JSON.parse(text);
          if (!res.ok) throw new Error(json.erro || 'Erro ao buscar CEP');
          return json;
        } catch (e) {
          throw new Error('Resposta inválida do servidor: ' + text.substring(0, 100));
        }
      });
    })
    .then(function (data) {
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
      console.error('Erro completo:', err);
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
