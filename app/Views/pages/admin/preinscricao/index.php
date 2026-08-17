<section class="container py-4">
  <div class="bg-white border rounded-3 p-4 shadow-sm">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
      <h4 class="mb-0"><i class="bi bi-inbox me-2"></i>Pré-inscrições</h4>
      <div class="d-flex gap-2">
        <a class="btn btn-outline-primary btn-sm" href="/admin/preinscricao/acordos"><i class="bi bi-file-earmark-text me-1"></i>Acordo</a>
        <a class="btn btn-outline-secondary btn-sm" href="/admin/preinscricao/kanban"><i class="bi bi-kanban me-1"></i>Visão em quadro</a>
      </div>
    </div>

    <?php if (!empty($flash ?? '')): ?>
      <div class="alert alert-info"><?= htmlspecialchars((string) $flash, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>

    <form method="get" action="/admin/preinscricao" class="d-flex flex-wrap align-items-center gap-2 mb-3">
      <label class="form-label mb-0 text-muted small"><i class="bi bi-funnel me-1"></i>Situação:</label>
      <div class="btn-group" role="group" aria-label="Filtrar por situação">
        <a class="btn btn-sm <?= (($situacaoFiltro ?? '') === 'recebido') ? 'btn-primary' : 'btn-outline-secondary' ?>" href="/admin/preinscricao?situacao=recebido">Recebido</a>
        <a class="btn btn-sm <?= (($situacaoFiltro ?? '') === 'atendimento') ? 'btn-primary' : 'btn-outline-secondary' ?>" href="/admin/preinscricao?situacao=atendimento">Atendimento</a>
        <a class="btn btn-sm <?= (($situacaoFiltro ?? '') === 'finalizado') ? 'btn-primary' : 'btn-outline-secondary' ?>" href="/admin/preinscricao?situacao=finalizado">Finalizado</a>
        <a class="btn btn-sm <?= (($situacaoFiltro ?? '') === '') ? 'btn-primary' : 'btn-outline-secondary' ?>" href="/admin/preinscricao?situacao=todas">Todas</a>
      </div>
    </form>

    <div class="table-responsive">
      <table class="table table-striped table-sm align-middle">
        <thead>
          <tr>
            <th>#</th>
            <th>Nome</th>
            <th>E-mail</th>
            <th>WhatsApp</th>
            <th>Curso</th>
            <th>Local</th>
            <th>Data</th>
            <th>Situação</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($preInscricoes ?? [])): ?>
            <tr><td colspan="8" class="text-muted"><i class="bi bi-inbox me-1"></i>Nenhuma pré-inscrição encontrada.</td></tr>
          <?php else: ?>
            <?php foreach ($preInscricoes as $p): ?>
              <?php
              $criadoEm = (string) ($p['created_at'] ?? '');
              $dt = $criadoEm !== '' ? \DateTime::createFromFormat('Y-m-d H:i:s', $criadoEm) : false;
              $sit = (string) ($p['situacao'] ?? 'recebido');
              $sitClass = match ($sit) {
                'recebido' => 'bg-warning text-dark',
                'atendimento' => 'bg-info text-dark',
                'finalizado' => 'bg-success text-white',
                default => 'bg-secondary text-white',
              };
              ?>
              <tr>
                <td><a href="/admin/preinscricao/detalhe?id=<?= (int) ($p['id'] ?? 0) ?>" class="text-decoration-none">#<?= (int) ($p['id'] ?? 0) ?></a></td>
                <td><a href="/admin/preinscricao/detalhe?id=<?= (int) ($p['id'] ?? 0) ?>" class="text-decoration-none fw-semibold"><?= htmlspecialchars((string) ($p['nome'] ?? '-'), ENT_QUOTES, 'UTF-8') ?><?php if ((int) ($p['qtd_comentarios'] ?? 0) > 0): ?> <i class="bi bi-chat-dots text-secondary" title="<?= (int) ($p['qtd_comentarios'] ?? 0) ?> comentário(s)"></i><?php endif; ?></a></td>
                <td><?= htmlspecialchars((string) ($p['email'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                <?php $wa = \App\Helpers\WhatsAppHelper::onlyDigits((string) ($p['whatsapp'] ?? '')); ?>
                <td>
                  <?php if ($wa !== ''): ?>
                    <a href="https://wa.me/<?= $wa ?>" target="_blank" class="text-decoration-none"><i class="bi bi-whatsapp me-1" style="color:#128C7E;"></i><?= htmlspecialchars(\App\Helpers\WhatsAppHelper::format((string) ($p['whatsapp'] ?? '-')), ENT_QUOTES, 'UTF-8') ?></a>
                  <?php else: ?>
                    <?= htmlspecialchars(\App\Helpers\WhatsAppHelper::format((string) ($p['whatsapp'] ?? '-')), ENT_QUOTES, 'UTF-8') ?>
                  <?php endif; ?>
                </td>
                <td><?= htmlspecialchars((string) ($p['curso_nome'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                <td class="small"><?= (string) ($p['bandeira'] ?? '') ?> <?= htmlspecialchars((string) ($p['cidade'] ?? '-'), ENT_QUOTES, 'UTF-8') ?>, <?= htmlspecialchars((string) ($p['pais'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= $dt ? $dt->format('d/m/Y H:i') : ($criadoEm ?: '-') ?></td>
                <td>
                  <span class="badge <?= $sitClass ?> situacao-badge" data-id="<?= (int) ($p['id'] ?? 0) ?>" data-situacao="<?= $sit ?>"><?= htmlspecialchars($sit, ENT_QUOTES, 'UTF-8') ?></span>
                  <button class="btn btn-sm btn-outline-secondary border-0 ms-1 editar-situacao-btn" data-id="<?= (int) ($p['id'] ?? 0) ?>" data-situacao="<?= $sit ?>" data-nome="<?= htmlspecialchars((string) ($p['nome'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" title="Alterar situação"><i class="bi bi-pencil"></i></button>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
      <div class="mt-2 text-muted small">
        Total de pré-inscrições: <strong><?= count($preInscricoes ?? []) ?></strong>
      </div>
    </div>
  </div>
</section>

<div class="modal fade" id="modalSituacao" tabindex="-1">
  <div class="modal-dialog modal-sm modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h6 class="modal-title"><i class="bi bi-pencil me-1"></i>Alterar situação</h6>
        <button type="button" class="btn-close" data-bs-dismiss="modal" onclick="fecharModal()"></button>
      </div>
      <div class="modal-body">
        <p class="mb-3" id="modalSituacaoNome"></p>
        <div class="d-grid gap-2">
          <button class="btn btn-warning text-dark opcao-situacao" data-valor="recebido">Recebido</button>
          <button class="btn btn-info text-dark opcao-situacao" data-valor="atendimento">Atendimento</button>
          <button class="btn btn-success opcao-situacao" data-valor="finalizado">Finalizado</button>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
var situacaoEditandoId = null;
var situacaoEditandoBadge = null;

function abrirModal(id, badge, nome) {
  situacaoEditandoId = id;
  situacaoEditandoBadge = badge;
  document.getElementById('modalSituacaoNome').textContent = nome;
  document.getElementById('modalSituacao').classList.add('show');
  document.getElementById('modalSituacao').style.display = 'block';
  document.body.classList.add('modal-open');
}

function fecharModal() {
  document.getElementById('modalSituacao').classList.remove('show');
  document.getElementById('modalSituacao').style.display = '';
  document.body.classList.remove('modal-open');
}

document.addEventListener('DOMContentLoaded', function() {
  document.querySelectorAll('.editar-situacao-btn').forEach(function(btn) {
    btn.addEventListener('click', function() {
      abrirModal(this.dataset.id, this.closest('td').querySelector('.situacao-badge'), this.dataset.nome);
    });
  });

  document.querySelectorAll('.opcao-situacao').forEach(function(btn) {
    btn.addEventListener('click', function() {
      var situacao = this.dataset.valor;
      var formData = new FormData();
      formData.append('id', situacaoEditandoId);
      formData.append('situacao', situacao);

      fetch('/admin/preinscricao/atualizar-situacao', {
        method: 'POST',
        body: formData,
      }).then(function(r) { return r.json(); }).then(function(data) {
        if (data.sucesso) {
          var classes = {
            recebido: 'bg-warning text-dark',
            atendimento: 'bg-info text-dark',
            finalizado: 'bg-success text-white',
          };
          situacaoEditandoBadge.className = 'badge ' + (classes[situacao] || 'bg-secondary text-white') + ' situacao-badge';
          situacaoEditandoBadge.textContent = situacao;
          situacaoEditandoBadge.dataset.situacao = situacao;
        }
      }).catch(function() {
        alert('Erro ao atualizar situação.');
      });

      fecharModal();
    });
  });

  document.getElementById('modalSituacao').addEventListener('click', function(e) {
    if (e.target === this) fecharModal();
  });
});
</script>

<style>
#modalSituacao { background: rgba(0,0,0,.5); }
#modalSituacao.show { display: block; }
</style>