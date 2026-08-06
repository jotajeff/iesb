<?php
$colunas = $colunas ?? [
    'recebido' => [],
    'atendimento' => [],
    'finalizado' => [],
];
$rotulos = [
    'recebido' => ['label' => 'Recebido', 'class' => 'bg-warning text-dark'],
    'atendimento' => ['label' => 'Atendimento', 'class' => 'bg-info text-dark'],
    'finalizado' => ['label' => 'Finalizado', 'class' => 'bg-success text-white'],
];
?>
<section class="container py-4">
  <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <h4 class="mb-0"><i class="bi bi-kanban me-2"></i>Kanban — Pré-inscrições</h4>
    <a class="btn btn-outline-secondary btn-sm" href="/admin/preinscricao"><i class="bi bi-list-ul me-1"></i>Visão em lista</a>
  </div>

  <div class="row g-3">
    <?php foreach (['recebido', 'atendimento', 'finalizado'] as $chave): ?>
      <?php $items = $colunas[$chave] ?? []; ?>
      <div class="col-lg-4 col-12 d-flex flex-column">
        <div class="card border-0 shadow-sm flex-grow-1 d-flex flex-column">
          <div class="card-header border-0 <?= $rotulos[$chave]['class'] ?> fw-semibold py-2">
            <div class="d-flex justify-content-between align-items-center">
              <span><i class="bi bi-circle-fill me-1 small"></i><?= $rotulos[$chave]['label'] ?></span>
              <span class="badge bg-light text-dark"><?= count($items) ?></span>
            </div>
          </div>
          <div class="card-body p-2">
            <?php if (empty($items)): ?>
              <div class="text-muted small text-center py-4">Nenhum item</div>
            <?php else: ?>
              <?php foreach ($items as $p): ?>
                <div class="card border mb-2 shadow-xs">
                  <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-start mb-1">
                      <a href="/admin/preinscricao/detalhe?id=<?= (int) ($p['id'] ?? 0) ?>" class="fw-semibold text-decoration-none small text-truncate"><?= htmlspecialchars((string) ($p['nome'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></a>
                      <div class="d-flex align-items-center gap-2">
                        <?php $qtd = (int) ($p['qtd_comentarios'] ?? 0); ?>
                        <?php if ($qtd > 0): ?>
                          <span class="small text-muted"><i class="bi bi-chat-dots"></i> <?= $qtd ?></span>
                        <?php else: ?>
                          <span class="small text-muted"><i class="bi bi-chat-dots"></i></span>
                        <?php endif; ?>
                        <a href="#" class="badge bg-secondary text-white text-decoration-none editar-situacao-btn-kanban" style="font-size:.65rem;" data-id="<?= (int) ($p['id'] ?? 0) ?>" data-situacao="<?= (string) ($p['situacao'] ?? 'recebido') ?>" data-nome="<?= htmlspecialchars((string) ($p['nome'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">#<?= (int) ($p['id'] ?? 0) ?></a>
                      </div>
                    </div>
                    <?php $wa = preg_replace('/\D/', '', (string) ($p['whatsapp'] ?? '')); ?>
                    <div class="small text-muted mb-1">
                      <i class="bi bi-whatsapp me-1"></i>
                      <?php if ($wa !== ''): ?>
                        <a href="https://wa.me/<?= $wa ?>" target="_blank" class="text-decoration-none text-muted"><?= htmlspecialchars((string) ($p['whatsapp'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></a>
                      <?php else: ?>
                        <?= htmlspecialchars((string) ($p['whatsapp'] ?? '-'), ENT_QUOTES, 'UTF-8') ?>
                      <?php endif; ?>
                    </div>
                    <div class="small text-muted text-truncate">
                      <i class="bi bi-bookmark me-1"></i><?= htmlspecialchars((string) ($p['curso_nome'] ?? '-'), ENT_QUOTES, 'UTF-8') ?>
                    </div>
                  </div>
                </div>
              <?php endforeach; ?>
            <?php endif; ?>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</section>

<div class="modal fade" id="modalSituacaoKanban" tabindex="-1">
  <div class="modal-dialog modal-sm modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h6 class="modal-title"><i class="bi bi-pencil me-1"></i>Alterar situação</h6>
        <button type="button" class="btn-close" data-bs-dismiss="modal" onclick="fecharModalKanban()"></button>
      </div>
      <div class="modal-body">
        <p class="mb-3" id="modalSituacaoNomeKanban"></p>
        <div class="d-grid gap-2">
          <button class="btn btn-warning text-dark opcao-situacao-kanban" data-valor="recebido">Recebido</button>
          <button class="btn btn-info text-dark opcao-situacao-kanban" data-valor="atendimento">Atendimento</button>
          <button class="btn btn-success opcao-situacao-kanban" data-valor="finalizado">Finalizado</button>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
var situacaoEditandoIdKanban = null;

function abrirModalKanban(id, nome) {
  situacaoEditandoIdKanban = id;
  document.getElementById('modalSituacaoNomeKanban').textContent = nome;
  document.getElementById('modalSituacaoKanban').classList.add('show');
  document.getElementById('modalSituacaoKanban').style.display = 'block';
  document.body.classList.add('modal-open');
}

function fecharModalKanban() {
  document.getElementById('modalSituacaoKanban').classList.remove('show');
  document.getElementById('modalSituacaoKanban').style.display = '';
  document.body.classList.remove('modal-open');
}

document.addEventListener('DOMContentLoaded', function() {
  document.querySelectorAll('.editar-situacao-btn-kanban').forEach(function(btn) {
    btn.addEventListener('click', function(e) {
      e.preventDefault();
      abrirModalKanban(this.dataset.id, this.dataset.nome);
    });
  });

  document.querySelectorAll('.opcao-situacao-kanban').forEach(function(btn) {
    btn.addEventListener('click', function() {
      var situacao = this.dataset.valor;
      var formData = new FormData();
      formData.append('id', situacaoEditandoIdKanban);
      formData.append('situacao', situacao);

      fetch('/admin/preinscricao/atualizar-situacao', {
        method: 'POST',
        body: formData,
      }).then(function(r) { return r.json(); }).then(function(data) {
        if (data.sucesso) {
          location.reload();
        }
      }).catch(function() {
        alert('Erro ao atualizar situação.');
      });

      fecharModalKanban();
    });
  });

  document.getElementById('modalSituacaoKanban').addEventListener('click', function(e) {
    if (e.target === this) fecharModalKanban();
  });
});
</script>

<style>
#modalSituacaoKanban { background: rgba(0,0,0,.5); }
#modalSituacaoKanban.show { display: block; }
</style>
