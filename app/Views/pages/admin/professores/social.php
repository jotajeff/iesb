<section class="container py-4">
  <div class="bg-white border rounded-3 p-4 shadow-sm">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
      <h4 class="mb-0"><i class="bi bi-share me-2"></i>Redes Sociais</h4>
      <a class="btn btn-outline-secondary btn-sm" href="/admin/professores/perfil"><i class="bi bi-arrow-left me-1"></i>Voltar para Perfil</a>
    </div>

    <?php if (!empty($social ?? [])): ?>
      <div class="table-responsive mb-4">
        <table class="table table-striped table-sm align-middle">
          <thead>
            <tr>
              <th><i class="bi bi-hash"></i></th>
              <th>Rede</th>
              <th>Link / Perfil</th>
              <th><i class="bi bi-gear me-1"></i>Ações</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($social as $s): ?>
              <tr id="social-row-<?= (int) ($s['id'] ?? 0) ?>">
                <td><?= (int) ($s['id'] ?? 0) ?></td>
                <td><?= htmlspecialchars((string) ($s['rede'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                <td>
                  <a href="<?= htmlspecialchars((string) ($s['link_perfil'] ?? '#'), ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener">
                    <?= htmlspecialchars((string) ($s['link_perfil'] ?? '-'), ENT_QUOTES, 'UTF-8') ?>
                  </a>
                </td>
                <td>
                  <button class="btn btn-outline-danger btn-sm" onclick="excluirSocial(<?= (int) ($s['id'] ?? 0) ?>, this)" title="Excluir">
                    <i class="bi bi-trash"></i>
                  </button>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>

    <hr>
    <h5 class="mb-3"><i class="bi bi-plus-circle me-2"></i>Adicionar Rede Social</h5>

    <form method="post" action="/admin/professores/salvar-social" class="row g-3">
      <div class="col-md-5">
        <label class="form-label">Rede <span class="text-danger">*</span></label>
        <select class="form-select" name="rede" required>
          <option value="">Selecione...</option>
          <option value="Lattes">Lattes</option>
          <option value="LinkedIn">LinkedIn</option>
          <option value="Instagram">Instagram</option>
          <option value="Facebook">Facebook</option>
          <option value="YouTube">YouTube</option>
          <option value="Twitter / X">Twitter / X</option>
          <option value="TikTok">TikTok</option>
          <option value="WhatsApp">WhatsApp</option>
          <option value="Telegram">Telegram</option>
          <option value="GitHub">GitHub</option>
          <option value="Site Pessoal">Site Pessoal</option>
          <option value="ResearchGate">ResearchGate</option>
          <option value="Google Scholar">Google Scholar</option>
          <option value="ORCID">ORCID</option>
          <option value="Academia.edu">Academia.edu</option>
          <option value="Outro">Outro</option>
        </select>
      </div>
      <div class="col-md-7">
        <label class="form-label">Link / Perfil <span class="text-danger">*</span></label>
        <input class="form-control" type="url" name="link_perfil" required placeholder="https:// exemplo.com/perfil">
      </div>
      <div class="col-12 d-flex gap-2">
        <button class="btn btn-success" type="submit" name="action" value="save">
          <i class="bi bi-check-lg me-1"></i>Salvar e voltar
        </button>
        <button class="btn btn-outline-primary" type="submit" name="action" value="add_another">
          <i class="bi bi-plus-lg me-1"></i>Salvar e adicionar outro
        </button>
      </div>
    </form>
  </div>
</section>

<script>
function excluirSocial(id, btn) {
  if (!confirm('Tem certeza que deseja excluir esta rede social?')) {
    return;
  }

  const formData = new FormData();
  formData.append('id', id);

  fetch('/admin/professores/deletar-social', {
    method: 'POST',
    body: formData
  })
  .then(function(r) { return r.json(); })
  .then(function(data) {
    if (data.sucesso) {
      var row = document.getElementById('social-row-' + id);
      if (row) {
        row.style.transition = 'opacity 0.3s';
        row.style.opacity = '0';
        setTimeout(function() { row.remove(); }, 300);
      }
    } else {
      alert('Erro: ' + (data.erro || 'não foi possível excluir.'));
    }
  })
  .catch(function() {
    alert('Erro de comunicação com o servidor.');
  });
}
</script>