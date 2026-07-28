<section class="container py-4">
  <div class="bg-white border rounded-3 p-4 shadow-sm">
    <h4 class="mb-1"><i class="bi bi-bell me-2"></i>Notificações</h4>
    <p class="text-muted mb-4"><?= $podeCriar ? 'Gerencie o envio de comunicados para turmas ativas e professores.' : 'Visualize as notificações recebidas.' ?></p>

    <h5 class="mb-3"><i class="bi bi-clock-history me-1"></i>Histórico de Notificações</h5>

    <?php if (empty($notificacoes)): ?>
      <p class="text-muted"><i class="bi bi-inbox me-1"></i>Nenhuma notificação encontrada.</p>
    <?php else: ?>
      <div class="table-responsive">
        <table class="table table-sm table-striped align-middle">
          <thead>
            <tr>
              <th><i class="bi bi-hash"></i></th>
              <th><i class="bi bi-tag me-1"></i>Título</th>
              <th><i class="bi bi-chat-text me-1"></i>Mensagem</th>
              <th><i class="bi bi-bullseye me-1"></i>Destino</th>
              <th><i class="bi bi-person me-1"></i>Origem</th>
              <th><i class="bi bi-calendar me-1"></i>Data</th>
              <?php if ($podeCriar): ?>
                <th><i class="bi bi-eye me-1"></i>Leitura</th>
              <?php endif; ?>
              <?php if ($podeLer): ?>
                <th><i class="bi bi-check2-circle me-1"></i>Status</th>
              <?php endif; ?>
              <?php if ($podeCriar): ?>
                <th><i class="bi bi-gear me-1"></i>Ações</th>
              <?php endif; ?>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($notificacoes as $n): ?>
              <?php $nid = (int) ($n['id'] ?? 0); ?>
              <?php $lida = !empty($n['lida']); ?>
            <tr class="<?= $podeLer && !$lida ? 'fw-semibold' : '' ?>" data-id="<?= $nid ?>" data-titulo="<?= htmlspecialchars((string) ($n['titulo'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" data-mensagem="<?= htmlspecialchars((string) ($n['mensagem'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" data-origem="<?= htmlspecialchars((string) ($n['origem_nome'] ?? '-'), ENT_QUOTES, 'UTF-8') ?>" data-data="<?= htmlspecialchars((string) ($n['created_at'] ?? '-'), ENT_QUOTES, 'UTF-8') ?>" data-lida="<?= $lida ? '1' : '0' ?>">
              <td>
                <?php if ($podeLer): ?>
                  <a href="#" class="text-decoration-none fw-medium link-notificacao" data-bs-toggle="modal" data-bs-target="#notificacaoModal">#<?= $nid ?></a>
                <?php else: ?>
                  <a href="/admin/notificacoes/leitura?id=<?= $nid ?>" class="text-decoration-none fw-medium">#<?= $nid ?></a>
                <?php endif; ?>
              </td>
              <td class="fw-medium"><?= htmlspecialchars((string) ($n['titulo'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
              <td style="max-width:300px;"><?= nl2br(htmlspecialchars(mb_strimwidth((string) ($n['mensagem'] ?? ''), 0, 120, '...'), ENT_QUOTES, 'UTF-8')) ?></td>
              <td>
                <?php
                  $td = (string) ($n['tipo_destino'] ?? '');
                  $tdId = (int) ($n['id_destino'] ?? 0);
                  $destinoNome = $td === 'turma'
                    ? (string) ($n['destino_turma_nome'] ?? '')
                    : (string) ($n['destino_usuario_nome'] ?? '');
                  $labels = ['usuario' => 'Professor', 'turma' => 'Turma', 'curso' => 'Curso', 'aluno' => 'Aluno'];
                  $label = $labels[$td] ?? $td;
                ?>
                <span class="badge bg-secondary"><?= $label ?></span>
                <?php if ($destinoNome !== ''): ?>
                  <span class="small text-muted"><?= htmlspecialchars($destinoNome, ENT_QUOTES, 'UTF-8') ?></span>
                <?php else: ?>
                  <span class="small text-muted">#<?= $tdId ?></span>
                <?php endif; ?>
              </td>
              <td><?= htmlspecialchars((string) ($n['origem_nome'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
              <td class="text-nowrap"><?= htmlspecialchars((string) ($n['created_at'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
              <?php if ($podeCriar): ?>
                <td><a href="/admin/notificacoes/leitura?id=<?= $nid ?>" class="text-decoration-none"><span class="badge <?= (int) ($n['total_leitura'] ?? 0) > 0 ? 'bg-info' : 'bg-warning text-dark' ?>"><?= (int) ($n['total_leitura'] ?? 0) ?></span></a></td>
                <td><a href="/admin/notificacoes/clone?id=<?= $nid ?>" class="btn btn-outline-secondary btn-sm" title="Clonar notificação"><i class="bi bi-copy"></i></a></td>
              <?php endif; ?>
              <?php if ($podeLer): ?>
                <td>
                  <?php if ($lida): ?>
                    <span class="badge bg-success"><i class="bi bi-check2-all me-1"></i>Lida</span>
                  <?php else: ?>
                    <span class="badge bg-warning text-dark"><i class="bi bi-envelope me-1"></i>Nova</span>
                  <?php endif; ?>
                </td>
              <?php endif; ?>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>

    <?php if ($podeCriar): ?>
    <div class="card border-success mt-4">
      <div class="card-header bg-success text-white"><i class="bi bi-send-plus me-1"></i>Nova Notificação</div>
      <div class="card-body">
        <form method="post" action="/admin/notificacoes/salvar">
          <div class="mb-3">
            <label class="form-label">Título <span class="text-danger">*</span></label>
            <input class="form-control" name="titulo" required maxlength="150" placeholder="Ex.: Aviso importante">
          </div>
          <div class="mb-3">
            <label class="form-label">Mensagem <span class="text-danger">*</span></label>
            <textarea class="form-control" name="mensagem" rows="4" required placeholder="Digite o conteúdo da notificação..."></textarea>
          </div>
          <div class="mb-3">
            <label class="form-label">Enviar para <span class="text-danger">*</span></label>
            <select class="form-select" name="destino" required>
              <option value="" disabled selected>Selecione um destino...</option>
              <optgroup label="Turmas ativas">
                <?php foreach ($turmas as $t): ?>
                  <option value="turma:<?= (int) $t['id'] ?>"><?= htmlspecialchars((string) ($t['nome'] ?? ''), ENT_QUOTES, 'UTF-8') ?></option>
                <?php endforeach; ?>
              </optgroup>
              <optgroup label="Professores ativos">
                <?php foreach ($professores as $p): ?>
                  <option value="professor:<?= (int) $p['id'] ?>"><?= htmlspecialchars((string) ($p['nome'] ?? ''), ENT_QUOTES, 'UTF-8') ?></option>
                <?php endforeach; ?>
              </optgroup>
            </select>
          </div>
          <button class="btn btn-success" type="submit"><i class="bi bi-send me-1"></i>Enviar</button>
        </form>
      </div>
    </div>
    <?php endif; ?>
  </div>
</section>

<?php if ($podeLer): ?>
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
  link.addEventListener('click', function(e) {
    var row = this.closest('tr');
    document.getElementById('modalTitulo').textContent = row.getAttribute('data-titulo');
    document.getElementById('modalMensagem').innerHTML = nl2br(row.getAttribute('data-mensagem'));
    document.getElementById('modalOrigem').textContent = row.getAttribute('data-origem');
    document.getElementById('modalData').textContent = row.getAttribute('data-data');
    var btn = document.getElementById('btnMarcarLida');
    btn.setAttribute('data-id', row.getAttribute('data-id'));
    var lida = row.getAttribute('data-lida') === '1';
    btn.style.display = lida ? 'none' : '';
  });
});

document.getElementById('btnMarcarLida').addEventListener('click', function() {
  var id = this.getAttribute('data-id');
  if (!id) return;
  var formData = new FormData();
  formData.append('id', id);
  fetch('/admin/notificacoes/marcar-lida', { method: 'POST', body: formData })
    .then(function(r) { return r.json(); })
    .then(function(data) {
      if (data.sucesso) {
        var row = document.querySelector('tr[data-id="' + id + '"]');
        if (row) {
          row.classList.remove('fw-semibold');
          row.setAttribute('data-lida', '1');
          row.querySelector('.badge.bg-warning').outerHTML = '<span class="badge bg-success"><i class="bi bi-check2-all me-1"></i>Lida</span>';
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
<?php endif; ?>
