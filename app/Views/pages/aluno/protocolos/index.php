<?php
  $protocolosView = is_array($protocolos ?? null) ? $protocolos : [];

  $statusInfo = static function (string $status): array {
    return match ($status) {
      'ABERTO' => ['bg-info text-dark', 'Aberto'],
      'EM_ATENDIMENTO' => ['bg-warning text-dark', 'Em atendimento'],
      'RESPONDIDO' => ['bg-success', 'Respondido'],
      'ENCERRADO' => ['bg-secondary', 'Encerrado'],
      default => ['bg-light text-dark border', $status ?: '-'],
    };
  };
  $prioridadeInfo = static function (string $p): array {
    return match ($p) {
      'ALTA' => ['bg-warning text-dark', 'Alta'],
      'URGENTE' => ['bg-danger', 'Urgente'],
      default => ['bg-light text-dark border', 'Normal'],
    };
  };
?>

<section class="py-4" style="margin-top: 20px;">
  <div class="container">
    <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 16px; padding: 2rem; box-shadow: var(--card-shadow);">
      <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <h4 class="mb-0"><i class="bi bi-chat-left-text me-2"></i>Protocolos</h4>
        <a class="btn btn-primary btn-sm" href="/aluno/protocolos/novo"><i class="bi bi-plus-circle me-1"></i>Novo Protocolo</a>
      </div>

      <?php if (empty($protocolosView)): ?>
        <p class="text-muted mb-0"><i class="bi bi-inbox me-1"></i>Você ainda não possui protocolos.</p>
      <?php else: ?>
        <div class="table-responsive">
          <table class="table table-striped table-hover align-middle mb-0">
            <thead>
              <tr>
                <th>Protocolo</th>
                <th>Assunto</th>
                <th>Curso</th>
                <th>Status</th>
                <th>Prioridade</th>
                <th>Atualizado</th>
                <th></th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($protocolosView as $p): ?>
                <?php
                  $id = (int) ($p['id'] ?? 0);
                  [$stClasse, $stLabel] = $statusInfo((string) ($p['status'] ?? ''));
                  [$prClasse, $prLabel] = $prioridadeInfo((string) ($p['prioridade'] ?? ''));
                  $upd = (string) ($p['updated_at'] ?? '');
                  $updDt = $upd !== '' ? date_create($upd) : false;
                ?>
                <tr>
                  <td class="fw-semibold">#<?= str_pad((string) $id, 6, '0', STR_PAD_LEFT) ?></td>
                  <td><?= htmlspecialchars((string) ($p['assunto'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                  <td><?= htmlspecialchars((string) ($p['curso_nome'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                  <td><span class="badge <?= $stClasse ?>"><?= htmlspecialchars($stLabel, ENT_QUOTES, 'UTF-8') ?></span></td>
                  <td><span class="badge <?= $prClasse ?>"><?= htmlspecialchars($prLabel, ENT_QUOTES, 'UTF-8') ?></span></td>
                  <td><?= htmlspecialchars($updDt ? $updDt->format('d/m/Y H:i') : ($upd ?: '-'), ENT_QUOTES, 'UTF-8') ?></td>
                  <td>
                    <a class="btn btn-sm btn-outline-primary" href="/aluno/protocolos/show?id=<?= $id ?>">
                      <i class="bi bi-eye me-1"></i>Abrir
                    </a>
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