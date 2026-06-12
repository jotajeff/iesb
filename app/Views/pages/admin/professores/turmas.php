<section class="container py-4">
    <div class="bg-white border rounded-3 p-4 shadow-sm">
      <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <h4 class="mb-0"><i class="bi bi-people me-2"></i>Minhas Turmas</h4>
      </div>

    <div class="table-responsive">
      <table class="table table-striped table-sm align-middle">
        <thead>
          <tr>
            <th><i class="bi bi-hash"></i></th>
            <th><i class="bi bi-journal-bookmark-fill me-1"></i>Curso</th>
            <th><i class="bi bi-people me-1"></i>Turma</th>
            <th><i class="bi bi-calendar me-1"></i>Início</th>
            <th><i class="bi bi-calendar me-1"></i>Fim</th>
            <th><i class="bi bi-person-badge me-1"></i>Inscritos</th>
            <th><i class="bi bi-toggle-on me-1"></i>Ativa</th>
            <th><i class="bi bi-gear me-1"></i>Ações</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($turmas ?? [])): ?>
            <tr><td colspan="8" class="text-muted"><i class="bi bi-inbox me-1"></i>Nenhuma turma vinculada.</td></tr>
          <?php endif; ?>

          <?php foreach ($turmas ?? [] as $t): ?>
            <?php $tid = (int) ($t['id'] ?? 0); ?>
            <tr>
              <td><?= $tid ?></td>
              <td><?= htmlspecialchars((string) ($t['curso_nome'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
              <td><?= htmlspecialchars((string) ($t['nome'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
              <td><?= htmlspecialchars((string) ($t['data_inicio'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
              <td><?= htmlspecialchars((string) ($t['data_fim'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
              <td>
                <span class="badge bg-primary"><?= (int) ($t['total_inscritos'] ?? 0) ?></span>
              </td>
              <td>
                <?php $ativa = (string) ($t['ativa'] ?? 'S'); ?>
                <?php if ($ativa === 'S'): ?>
                  <span class="badge bg-success">Sim</span>
                <?php else: ?>
                  <span class="badge bg-secondary">Não</span>
                <?php endif; ?>
              </td>
              <td>
                <div class="d-flex gap-1">
                  <a class="btn btn-outline-info btn-sm" href="/admin/turmas/show?id=<?= $tid ?>" title="Visualizar inscritos">
                    <i class="bi bi-eye"></i><span class="ms-1">Inscritos</span>
                  </a>
                  <a class="btn btn-outline-danger btn-sm" href="/admin/professores/videos?turma_id=<?= $tid ?>" title="Gerenciar vídeos">
                    <i class="bi bi-camera-reels"></i>
                  </a>
                  <a class="btn btn-outline-success btn-sm" href="/admin/professores/pdf?turma_id=<?= $tid ?>" title="Enviar apostila PDF">
                    <i class="bi bi-filetype-pdf"></i>
                  </a>
                  <a class="btn btn-outline-primary btn-sm" href="/admin/professores/drive?turma_id=<?= $tid ?>" title="Google Drive">
                    <i class="bi bi-google"></i>
                  </a>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</section>
