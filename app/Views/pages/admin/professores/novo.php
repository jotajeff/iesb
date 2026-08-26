<section class="container py-4">
    <div class="bg-white border rounded-3 p-4 shadow-sm">
      <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <h4 class="mb-0"><i class="bi bi-plus-circle me-2"></i>Novo Professor</h4>
        <a class="btn btn-outline-secondary btn-sm" href="/admin/professores"><i class="bi bi-arrow-left me-1"></i>Voltar para lista</a>
      </div>

    <form method="post" action="/admin/professores/salvar" class="row g-3">
      <div class="col-md-6">
        <label class="form-label">Nome <span class="text-danger">*</span></label>
        <input class="form-control" type="text" name="nome" required>
      </div>
      <div class="col-md-6">
        <label class="form-label">Email <span class="text-danger">*</span></label>
        <input class="form-control" type="email" name="email" id="emailInput" required oninput="gerarSenha()">
      </div>
      <div class="col-md-6">
        <label class="form-label">Senha inicial <span class="text-danger">*</span></label>
        <div class="input-group">
          <input class="form-control font-monospace" type="text" name="senha" id="senhaInput" required>
          <button type="button" class="btn btn-outline-secondary" onclick="gerarSenha(true)" title="Gerar nova senha">⟳</button>
          <button type="button" class="btn btn-outline-secondary" onclick="copiarSenha()" title="Copiar senha">📋</button>
        </div>
        <small class="text-danger">Anote esta senha agora! Ela não será mostrada novamente.</small>
      </div>
      <div class="col-md-6">
        <label class="form-label">Telefone</label>
        <input class="form-control" type="text" name="telefone" placeholder="(61) 99999-9999">
      </div>
      <div class="col-md-6">
        <label class="form-label">Titulação</label>
        <input class="form-control" type="text" name="titulacao" placeholder="Especialista, Mestre, Doutor...">
      </div>
      <div class="col-md-3">
        <label class="form-label">Ativo</label>
        <select class="form-select" name="ativo">
          <option value="1" selected>Sim</option>
          <option value="0">Não</option>
        </select>
      </div>

      <div class="col-12">
        <button class="btn btn-success" type="submit"><i class="bi bi-check-lg me-1"></i>Criar Professor</button>
      </div>
    </form>

<script>
function gerarSenha(overwrite = false) {
  var emailInput = document.getElementById('emailInput');
  var senhaInput = document.getElementById('senhaInput');
  if (!emailInput || !senhaInput) return;

  var email = emailInput.value.trim();
  if (!email) return;

  var ano = new Date().getFullYear();
  var senha = email.split('@')[0] + '#' + ano;

  if (overwrite || !senhaInput.value) {
    senhaInput.value = senha;
  }
}

document.addEventListener('DOMContentLoaded', function() {
  gerarSenha();
});

function copiarSenha() {
  var senhaInput = document.getElementById('senhaInput');
  if (!senhaInput) return;
  navigator.clipboard.writeText(senhaInput.value).then(() => {
    alert('Senha copiada!');
  });
}
</script>
  </div>
</section>
