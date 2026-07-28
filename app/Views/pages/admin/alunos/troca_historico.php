<?php
  $trocas = is_array($trocas ?? null) ? $trocas : [];
?>

<section class="container py-4">
  <div class="bg-white border rounded-3 p-4 shadow-sm">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
      <h4 class="mb-0"><i class="bi bi-arrow-left-right me-2"></i>Histórico de Trocas de Turma</h4>
      <a class="btn btn-outline-secondary btn-sm" href="/admin/alunos">
        <i class="bi bi-arrow-left me-1"></i>Voltar para Alunos
      </a>
    </div>

    <?php if (empty($trocas)): ?>
      <div class="alert alert-light border text-muted">
        <i class="bi bi-inbox me-1"></i>Nenhuma troca registrada.
      </div>
    <?php else: ?>
      <div class="table-responsive">
        <table class="table table-striped table-hover table-sm align-middle mb-0">
          <thead>
            <tr>
              <th>#</th>
              <th>Aluno</th>
              <th>Turma Origem</th>
              <th>Turma Destino</th>
              <th>Motivo</th>
              <th>Data</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($trocas as $t): ?>
              <tr>
                <td><?= (int) ($t['id'] ?? 0) ?></td>
                <td>
                  <a href="/admin/alunos/show?id=<?= (int) ($t['id_aluno'] ?? 0) ?>">
                    <?= htmlspecialchars((string) ($t['aluno_nome'] ?? '-'), ENT_QUOTES, 'UTF-8') ?>
                  </a>
                </td>
                <td>
                  <small class="text-muted"><?= htmlspecialchars((string) ($t['curso_origem_nome'] ?? ''), ENT_QUOTES, 'UTF-8') ?></small><br>
                  <?= htmlspecialchars((string) ($t['turma_origem_nome'] ?? '-'), ENT_QUOTES, 'UTF-8') ?>
                </td>
                <td>
                  <small class="text-muted"><?= htmlspecialchars((string) ($t['curso_destino_nome'] ?? ''), ENT_QUOTES, 'UTF-8') ?></small><br>
                  <?= htmlspecialchars((string) ($t['turma_destino_nome'] ?? '-'), ENT_QUOTES, 'UTF-8') ?>
                </td>
                <td><?= htmlspecialchars((string) ($t['motivo'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                <td><?php
                  $raw = (string) ($t['created_at'] ?? '');
                  $dt = $raw !== '' ? \DateTime::createFromFormat('Y-m-d H:i:s', $raw) : false;
                  echo htmlspecialchars($dt ? $dt->format('d/m/Y H:i') : ($raw ?: '-'), ENT_QUOTES, 'UTF-8');
                ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <small class="text-muted">Total: <?= count($trocas) ?> registro(s)</small>
    <?php endif; ?>
  </div>
</section>
