<?php
  $notificacoesView = is_array($notificacoes ?? null) ? $notificacoes : [];
?>

<section class="container py-4">
  <div class="bg-white border rounded-3 p-4 shadow-sm">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
      <h4 class="mb-0"><i class="bi bi-envelope-paper me-2"></i>Email Matrículas</h4>
      <span class="badge bg-primary"><?= count($notificacoesView) ?> registro(s)</span>
    </div>

    <p class="text-muted small">
      Registros de envio de e-mail de boas-vindas para matrículas realizadas.
    </p>

    <?php if (empty($notificacoesView)): ?>
      <p class="text-muted mb-0">Nenhuma notificação de matrícula encontrada.</p>
    <?php else: ?>
      <div class="table-responsive">
        <table class="table table-striped table-sm align-middle">
          <thead>
            <tr>
              <th><i class="bi bi-hash me-1"></i>ID</th>
              <th><i class="bi bi-person me-1"></i>Aluno</th>
              <th><i class="bi bi-book me-1"></i>Curso</th>
              <th><i class="bi bi-envelope me-1"></i>E-mail</th>
              <th><i class="bi bi-toggle-on me-1"></i>Status</th>
              <th><i class="bi bi-calendar3 me-1"></i>Data</th>
              <th><i class="bi bi-arrow-repeat me-1"></i>Ação</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($notificacoesView as $notificacao): ?>
              <?php
                $id = (int) ($notificacao['id'] ?? 0);
                $idAluno = (int) ($notificacao['id_aluno'] ?? 0);
                $enviado = (int) ($notificacao['status'] ?? 0) === 1;
                $rawDate = (string) ($notificacao['created_at'] ?? '');
                $date = $rawDate !== '' ? date_create($rawDate) : false;
              ?>
              <tr>
                <td><?= $id ?></td>
                <td>
                  <a class="text-decoration-none fw-medium" href="/admin/alunos/show?id=<?= $idAluno ?>">
                    <?= htmlspecialchars((string) ($notificacao['aluno_nome'] ?? '-'), ENT_QUOTES, 'UTF-8') ?>
                  </a>
                </td>
                <td><?= htmlspecialchars((string) ($notificacao['curso_nome'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars((string) ($notificacao['email'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                <td>
                  <?php if ($enviado): ?>
                    <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Enviado</span>
                  <?php else: ?>
                    <span class="badge bg-danger"><i class="bi bi-x-circle me-1"></i>Não enviado</span>
                  <?php endif; ?>
                </td>
                <td><?= htmlspecialchars($date ? $date->format('d/m/Y H:i') : ($rawDate ?: '-'), ENT_QUOTES, 'UTF-8') ?></td>
                <td>
                  <?php if (!$enviado): ?>
                    <form method="post" action="/admin/matriculas/notificacao/reenviar" class="d-inline" onsubmit="return confirm('Deseja reenviar o e-mail de matrícula para este aluno?');">
                      <input type="hidden" name="id" value="<?= $id ?>">
                      <button type="submit" class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-send me-1"></i>Reenviar e-mail
                      </button>
                    </form>
                  <?php else: ?>
                    <span class="text-muted small">—</span>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>
</section>
