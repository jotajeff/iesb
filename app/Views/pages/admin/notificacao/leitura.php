<section class="container py-4">
  <div class="bg-white border rounded-3 p-4 shadow-sm">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
      <h4 class="mb-0"><i class="bi bi-eye me-2"></i>Leituras da Notificação</h4>
      <a class="btn btn-outline-secondary btn-sm" href="/admin/notificacoes"><i class="bi bi-arrow-left me-1"></i>Voltar</a>
    </div>

    <div class="card bg-light mb-4">
      <div class="card-body">
        <dl class="row mb-0">
          <dt class="col-sm-2 text-muted">Título</dt>
          <dd class="col-sm-10"><?= htmlspecialchars((string) ($notificacao['titulo'] ?? ''), ENT_QUOTES, 'UTF-8') ?></dd>
          <dt class="col-sm-2 text-muted">Mensagem</dt>
          <dd class="col-sm-10"><?= nl2br(htmlspecialchars((string) ($notificacao['mensagem'] ?? ''), ENT_QUOTES, 'UTF-8')) ?></dd>
          <dt class="col-sm-2 text-muted">Destino</dt>
          <dd class="col-sm-10">
            <?php
              $td = (string) ($notificacao['tipo_destino'] ?? '');
              $labels = ['usuario' => 'Professor', 'turma' => 'Turma', 'curso' => 'Curso', 'aluno' => 'Aluno'];
              $label = $labels[$td] ?? $td;
              $destinoNome = $td === 'turma'
                ? (string) ($notificacao['destino_turma_nome'] ?? '')
                : (string) ($notificacao['destino_usuario_nome'] ?? '');
            ?>
            <span class="badge bg-secondary me-1"><?= $label ?></span>
            <?= htmlspecialchars($destinoNome, ENT_QUOTES, 'UTF-8') ?>
          </dd>
          <dt class="col-sm-2 text-muted">Origem</dt>
          <dd class="col-sm-10"><?= htmlspecialchars((string) ($notificacao['origem_nome'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></dd>
          <dt class="col-sm-2 text-muted">Data</dt>
          <dd class="col-sm-10"><?= htmlspecialchars((string) ($notificacao['created_at'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></dd>
        </dl>
      </div>
    </div>

    <h5 class="mb-3"><i class="bi bi-people me-1"></i>Quem leu (<?= count($leituras) ?>)</h5>

    <?php if (empty($leituras)): ?>
      <p class="text-muted"><i class="bi bi-inbox me-1"></i>Nenhuma leitura registrada.</p>
    <?php else: ?>
      <div class="table-responsive">
        <table class="table table-sm table-striped align-middle">
          <thead>
            <tr>
              <th><i class="bi bi-hash"></i></th>
              <th><i class="bi bi-person me-1"></i>Nome</th>
              <th><i class="bi bi-envelope me-1"></i>Email</th>
              <th><i class="bi bi-calendar me-1"></i>Data da Leitura</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($leituras as $l): ?>
            <tr>
              <td><?= (int) ($l['id'] ?? 0) ?></td>
              <td><?= htmlspecialchars((string) ($l['nome'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
              <td><?= htmlspecialchars((string) ($l['email'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
              <td class="text-nowrap"><?= htmlspecialchars((string) ($l['lida_em'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>
</section>
