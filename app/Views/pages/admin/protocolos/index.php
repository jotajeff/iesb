<?php
  $protocolosView = is_array($protocolos ?? null) ? $protocolos : [];
  $contagem = is_array($contagem ?? null) ? $contagem : [];
  $cursosView = is_array($cursos ?? null) ? $cursos : [];
  $filtros = is_array($filtros ?? null) ? $filtros : [];
  $statusAtual = (string) ($filtros['status'] ?? '');
  $totalTodos = (int) ($contagem['ABERTO'] ?? 0) + (int) ($contagem['EM_ATENDIMENTO'] ?? 0) + (int) ($contagem['RESPONDIDO'] ?? 0) + (int) ($contagem['ENCERRADO'] ?? 0);

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

<section class="container py-4">
  <div class="bg-white border rounded-3 p-4 shadow-sm">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
      <h4 class="mb-0"><i class="bi bi-chat-left-text me-2"></i>Protocolos</h4>
    </div>

    <div class="d-flex flex-wrap gap-2 mb-3">
      <a class="btn btn-sm <?= $statusAtual === '' ? 'btn-primary' : 'btn-outline-secondary' ?>" href="/admin/protocolos">
        Todos <span class="badge bg-light text-dark ms-1"><?= $totalTodos ?></span>
      </a>
      <a class="btn btn-sm <?= $statusAtual === 'ABERTO' ? 'btn-primary' : 'btn-outline-secondary' ?>" href="/admin/protocolos?status=ABERTO">
        Abertos <span class="badge bg-info text-dark ms-1"><?= (int) ($contagem['ABERTO'] ?? 0) ?></span>
      </a>
      <a class="btn btn-sm <?= $statusAtual === 'EM_ATENDIMENTO' ? 'btn-primary' : 'btn-outline-secondary' ?>" href="/admin/protocolos?status=EM_ATENDIMENTO">
        Em atendimento <span class="badge bg-warning text-dark ms-1"><?= (int) ($contagem['EM_ATENDIMENTO'] ?? 0) ?></span>
      </a>
      <a class="btn btn-sm <?= $statusAtual === 'RESPONDIDO' ? 'btn-primary' : 'btn-outline-secondary' ?>" href="/admin/protocolos?status=RESPONDIDO">
        Respondidos <span class="badge bg-success ms-1"><?= (int) ($contagem['RESPONDIDO'] ?? 0) ?></span>
      </a>
      <a class="btn btn-sm <?= $statusAtual === 'ENCERRADO' ? 'btn-primary' : 'btn-outline-secondary' ?>" href="/admin/protocolos?status=ENCERRADO">
        Encerrados <span class="badge bg-secondary ms-1"><?= (int) ($contagem['ENCERRADO'] ?? 0) ?></span>
      </a>
    </div>

    <form method="get" action="/admin/protocolos" class="row g-2 align-items-end mb-4">
      <div class="col-md-2">
        <label class="form-label small mb-1">Status</label>
        <select class="form-select form-select-sm" name="status">
          <option value="">Todos</option>
          <?php foreach (['ABERTO' => 'Aberto', 'EM_ATENDIMENTO' => 'Em atendimento', 'RESPONDIDO' => 'Respondido', 'ENCERRADO' => 'Encerrado'] as $val => $lbl): ?>
            <option value="<?= $val ?>" <?= $statusAtual === $val ? 'selected' : '' ?>><?= $lbl ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-2">
        <label class="form-label small mb-1">Prioridade</label>
        <select class="form-select form-select-sm" name="prioridade">
          <option value="">Todas</option>
          <?php foreach (['NORMAL' => 'Normal', 'ALTA' => 'Alta', 'URGENTE' => 'Urgente'] as $val => $lbl): ?>
            <option value="<?= $val ?>" <?= (string) ($filtros['prioridade'] ?? '') === $val ? 'selected' : '' ?>><?= $lbl ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-2">
        <label class="form-label small mb-1">Aluno</label>
        <input type="text" class="form-control form-control-sm" name="aluno" value="<?= htmlspecialchars((string) ($filtros['aluno'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" placeholder="Nome do aluno">
      </div>
      <div class="col-md-2">
        <label class="form-label small mb-1">Curso</label>
        <select class="form-select form-select-sm" name="id_curso">
          <option value="">Todos</option>
          <?php foreach ($cursosView as $curso): ?>
            <option value="<?= (int) ($curso['id'] ?? 0) ?>" <?= (int) ($filtros['id_curso'] ?? 0) === (int) ($curso['id'] ?? 0) ? 'selected' : '' ?>>
              <?= htmlspecialchars((string) ($curso['nome'] ?? '-'), ENT_QUOTES, 'UTF-8') ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-2">
        <label class="form-label small mb-1">De</label>
        <input type="date" class="form-control form-control-sm" name="inicio" value="<?= htmlspecialchars((string) ($filtros['inicio'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
      </div>
      <div class="col-md-2">
        <label class="form-label small mb-1">Até</label>
        <input type="date" class="form-control form-control-sm" name="fim" value="<?= htmlspecialchars((string) ($filtros['fim'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
      </div>
      <div class="col-12 d-flex gap-2">
        <button class="btn btn-primary btn-sm" type="submit"><i class="bi bi-funnel me-1"></i>Filtrar</button>
        <a class="btn btn-outline-secondary btn-sm" href="/admin/protocolos">Limpar</a>
      </div>
    </form>

    <?php if (empty($protocolosView)): ?>
      <p class="text-muted mb-0">Nenhum protocolo encontrado.</p>
    <?php else: ?>
      <div class="table-responsive">
        <table class="table table-striped table-hover table-sm align-middle">
          <thead>
            <tr>
              <th>Protocolo</th>
              <th>Aluno</th>
              <th>Curso</th>
              <th>Assunto</th>
              <th>Status</th>
              <th>Prioridade</th>
              <th>Responsável</th>
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
                <td><?= htmlspecialchars((string) ($p['aluno_nome'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars((string) ($p['curso_nome'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars((string) ($p['assunto'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                <td><span class="badge <?= $stClasse ?>"><?= htmlspecialchars($stLabel, ENT_QUOTES, 'UTF-8') ?></span></td>
                <td><span class="badge <?= $prClasse ?>"><?= htmlspecialchars($prLabel, ENT_QUOTES, 'UTF-8') ?></span></td>
                <td><?= htmlspecialchars((string) ($p['responsavel_nome'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars($updDt ? $updDt->format('d/m/Y H:i') : ($upd ?: '-'), ENT_QUOTES, 'UTF-8') ?></td>
                <td>
                  <a class="btn btn-sm btn-outline-primary" href="/admin/protocolos/show?id=<?= $id ?>"><i class="bi bi-box-arrow-up-right"></i></a>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>
</section>