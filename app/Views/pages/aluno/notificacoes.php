<section class="py-4" id="notificacoes" style="margin-top: 20px;">
  <div class="container">
    <div class="p-4 rounded-3 shadow-sm" style="background: var(--bg-card); border: 1px solid var(--border-color);">
      <h4 class="mb-1"><i class="bi bi-bell me-2"></i>Notificações</h4>
      <p class="text-muted mb-4">Comunicados para você e suas turmas.</p>

      <?php if (empty($notificacoes)): ?>
        <div class="text-center text-muted py-4">
          <i class="bi bi-inbox" style="font-size: 2rem;"></i>
          <p class="mt-2 mb-0">Nenhuma notificação encontrada.</p>
        </div>
      <?php else: ?>
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0">
            <thead>
              <tr>
                <th><i class="bi bi-hash"></i></th>
                <th><i class="bi bi-tag me-1"></i>Título</th>
                <th><i class="bi bi-chat-text me-1"></i>Mensagem</th>
                <th><i class="bi bi-person me-1"></i>Origem</th>
                <th><i class="bi bi-calendar me-1"></i>Data</th>
                <th><i class="bi bi-check2-circle me-1"></i>Status</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($notificacoes as $n): ?>
                <?php $nid = (int) ($n['id'] ?? 0); ?>
                <?php $lida = !empty($n['lida']); ?>
              <tr class="<?= !$lida ? 'fw-semibold' : '' ?>" data-id="<?= $nid ?>" data-titulo="<?= htmlspecialchars((string) ($n['titulo'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" data-mensagem="<?= htmlspecialchars((string) ($n['mensagem'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" data-origem="<?= htmlspecialchars((string) ($n['origem_nome'] ?? '-'), ENT_QUOTES, 'UTF-8') ?>" data-data="<?= htmlspecialchars((string) ($n['created_at'] ?? '-'), ENT_QUOTES, 'UTF-8') ?>" data-lida="<?= $lida ? '1' : '0' ?>">
                <td><a href="#" class="text-decoration-none fw-medium link-notificacao" data-bs-toggle="modal" data-bs-target="#notificacaoModal">#<?= $nid ?></a></td>
                <td><?= htmlspecialchars((string) ($n['titulo'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                <td style="max-width:300px;"><?= nl2br(htmlspecialchars(mb_strimwidth((string) ($n['mensagem'] ?? ''), 0, 120, '...'), ENT_QUOTES, 'UTF-8')) ?></td>
                <td><?= htmlspecialchars((string) ($n['origem_nome'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                <td class="text-nowrap"><?= htmlspecialchars((string) ($n['created_at'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                <td>
                  <?php if ($lida): ?>
                    <span class="badge bg-success"><i class="bi bi-check2-all me-1"></i>Lida</span>
                  <?php else: ?>
                    <span class="badge bg-warning text-dark"><i class="bi bi-envelope me-1"></i>Nova</span>
                  <?php endif; ?>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </div>
  </div>
</section>

<div class="modal fade" id="notificacaoModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalTitulo"><i class="bi bi-bell me-2"></i></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
      </div>
      <div class="modal-body">
        <div id="modalMensagem" class="mb-3"></div>
        <hr>
        <dl class="row mb-0 small text-muted">
          <dt class="col-sm-2">Origem</dt>
          <dd class="col-sm-4" id="modalOrigem"></dd>
          <dt class="col-sm-2">Data</dt>
          <dd class="col-sm-4" id="modalData"></dd>
        </dl>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-success" id="btnMarcarLida" data-id="">
          <i class="bi bi-check2-circle me-1"></i>Marcar como lida
        </button>
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Fechar</button>
      </div>
    </div>
  </div>
</div>

<script>
document.querySelectorAll('.link-notificacao').forEach(function(link) {
  link.addEventListener('click', function() {
    var row = this.closest('tr');
    document.getElementById('modalTitulo').textContent = row.getAttribute('data-titulo');
    document.getElementById('modalMensagem').innerHTML = nl2br(row.getAttribute('data-mensagem'));
    document.getElementById('modalOrigem').textContent = row.getAttribute('data-origem');
    document.getElementById('modalData').textContent = row.getAttribute('data-data');
    var btn = document.getElementById('btnMarcarLida');
    btn.setAttribute('data-id', row.getAttribute('data-id'));
    btn.style.display = row.getAttribute('data-lida') === '1' ? 'none' : '';
  });
});

document.getElementById('btnMarcarLida').addEventListener('click', function() {
  var id = this.getAttribute('data-id');
  if (!id) return;
  var formData = new FormData();
  formData.append('id', id);
  fetch('/aluno/notificacoes/marcar-lida', { method: 'POST', body: formData })
    .then(function(r) { return r.json(); })
    .then(function(data) {
      if (data.sucesso) {
        var row = document.querySelector('tr[data-id="' + id + '"]');
        if (row) {
          row.classList.remove('fw-semibold');
          row.setAttribute('data-lida', '1');
          var badge = row.querySelector('.badge');
          if (badge) {
            badge.outerHTML = '<span class="badge bg-success"><i class="bi bi-check2-all me-1"></i>Lida</span>';
          }
        }
        document.getElementById('btnMarcarLida').style.display = 'none';
      }
    });
});

function nl2br(str) {
  if (!str) return '';
  return str.replace(/\n/g, '<br>');
}
</script>
