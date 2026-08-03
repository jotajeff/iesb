<section class="container py-4">
  <div class="bg-white border rounded-3 p-4 shadow-sm">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
      <h4 class="mb-0"><i class="bi bi-file-earmark-text me-2"></i>Tipos de Documentos</h4>
      <a class="btn btn-primary btn-sm" href="/admin/tipos-documentos/novo"><i class="bi bi-plus-circle me-1"></i>Novo tipo</a>
    </div>

    <?php if (!empty($flash ?? '')): ?>
      <div class="alert alert-info"><?= htmlspecialchars((string) $flash, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>

    <div class="table-responsive">
      <table class="table table-striped table-sm align-middle">
        <thead>
          <tr>
            <th>#</th>
            <th>Grupo</th>
            <th>Descrição</th>
            <th>Obrigatório</th>
            <th>Ordem</th>
            <th>Ativo</th>
            <th>Ações</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($tipos)): ?>
            <tr><td colspan="7" class="text-muted">Nenhum tipo de documento cadastrado.</td></tr>
          <?php else: ?>
            <?php foreach ($tipos as $t): ?>
              <?php $id = (int) ($t['id'] ?? 0); ?>
              <tr>
                <td><?= $id ?></td>
                <td><?= htmlspecialchars((string) ($t['grupo_nome'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars((string) ($t['descricao'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                <td>
                  <?php if ((int) ($t['obrigatorio'] ?? 0) === 1): ?>
                    <span class="badge bg-warning text-dark">Sim</span>
                  <?php else: ?>
                    <span class="badge bg-secondary">Não</span>
                  <?php endif; ?>
                </td>
                <td><?= (int) ($t['ordem'] ?? 0) ?></td>
                <td>
                  <?php if ((int) ($t['ativo'] ?? 0) === 1): ?>
                    <span class="badge bg-success">Sim</span>
                  <?php else: ?>
                    <span class="badge bg-secondary">Não</span>
                  <?php endif; ?>
                </td>
                <td>
                  <div class="d-flex gap-1">
                    <a class="btn btn-outline-secondary btn-sm" href="/admin/tipos-documentos/editar?id=<?= $id ?>"><i class="bi bi-pencil-square"></i></a>
                    <a class="btn btn-outline-danger btn-sm" href="/admin/tipos-documentos/excluir?id=<?= $id ?>" onclick="return confirm('Excluir este tipo de documento?');"><i class="bi bi-trash"></i></a>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</section>
