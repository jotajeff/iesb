<section class="container py-4">
  <div class="bg-white border rounded-3 p-4 shadow-sm">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
      <h4 class="mb-0"><i class="bi bi-pencil-square me-2"></i>Editar Usuário #<?= (int) ($usuario['id'] ?? 0) ?></h4>
      <a class="btn btn-outline-secondary btn-sm" href="/admin/usuarios"><i class="bi bi-arrow-left me-1"></i>Voltar para lista</a>
    </div>

    <form method="post" action="/admin/usuarios/atualizar" class="row g-3">
      <input type="hidden" name="id" value="<?= (int) ($usuario['id'] ?? 0) ?>">

      <div class="col-md-6">
        <label class="form-label">Nome</label>
        <input class="form-control" type="text" value="<?= htmlspecialchars((string) ($usuario['nome'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" readonly disabled>
      </div>
      <div class="col-md-6">
        <label class="form-label">Email</label>
        <input class="form-control" type="email" value="<?= htmlspecialchars((string) ($usuario['email'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" readonly disabled>
      </div>
      <div class="col-md-4">
        <label class="form-label">Nova senha</label>
        <input class="form-control" type="password" name="senha" placeholder="Deixe em branco para manter">
        <small class="text-muted">Preencha apenas se quiser alterar.</small>
      </div>
      <?php $tipoAtual = (string) ($usuario['tipo'] ?? 'operador'); ?>
      <div class="col-md-4">
        <label class="form-label">Tipo</label>
        <?php if (!empty($isAdmin)): ?>
          <select class="form-select" name="tipo">
            <option value="admin" <?= $tipoAtual === 'admin' ? 'selected' : '' ?>>Admin</option>
            <option value="operador" <?= $tipoAtual === 'operador' ? 'selected' : '' ?>>Operador</option>
            <option value="professor" <?= $tipoAtual === 'professor' ? 'selected' : '' ?>>Professor</option>
          </select>
        <?php else: ?>
          <?php
            $tipoLabel = match ($tipoAtual) {
              'admin' => 'Admin',
              'operador' => 'Operador',
              'professor' => 'Professor',
              default => ucfirst($tipoAtual ?: 'Operador'),
            };
          ?>
          <input type="text" class="form-control" value="<?= $tipoLabel ?>" readonly disabled>
          <input type="hidden" name="tipo" value="<?= htmlspecialchars($tipoAtual, ENT_QUOTES, 'UTF-8') ?>">
        <?php endif; ?>
      </div>
      <div class="col-md-4">
        <label class="form-label">Ativo</label>
        <?php if (!empty($isAdmin)): ?>
          <select class="form-select" name="ativo">
            <option value="1" <?= (int) ($usuario['ativo'] ?? 1) === 1 ? 'selected' : '' ?>>Sim</option>
            <option value="0" <?= (int) ($usuario['ativo'] ?? 1) === 0 ? 'selected' : '' ?>>Não</option>
          </select>
        <?php else: ?>
          <input type="text" class="form-control" value="<?= (int) ($usuario['ativo'] ?? 1) === 1 ? 'Sim' : 'Não' ?>" readonly disabled>
          <input type="hidden" name="ativo" value="<?= (int) ($usuario['ativo'] ?? 1) ?>">
        <?php endif; ?>
      </div>

      <div class="col-12">
        <button class="btn btn-success" type="submit"><i class="bi bi-check-lg me-1"></i>Atualizar Usuário</button>
        <?php if (empty($isAdmin)): ?>
          <small class="text-muted ms-2">Como operador, você pode alterar apenas sua senha.</small>
        <?php endif; ?>
      </div>
    </form>
  </div>
</section>
