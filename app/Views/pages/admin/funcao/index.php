<section class="container py-4">
  <div class="bg-white border rounded-3 p-4 shadow-sm">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
      <div>
        <h4 class="mb-1"><i class="bi bi-gear-fill me-2"></i>Função</h4>
        <p class="text-muted mb-0">Selecione um usuário para definir as funções dele.</p>
      </div>
    </div>

    <div class="table-responsive">
      <table class="table table-striped table-sm align-middle">
        <thead>
          <tr>
            <th><i class="bi bi-hash"></i></th>
            <th><i class="bi bi-person me-1"></i>Nome</th>
            <th><i class="bi bi-envelope me-1"></i>Email</th>
            <th><i class="bi bi-person-workspace me-1"></i>Funções</th>
            <th><i class="bi bi-toggle-on me-1"></i>Ativo</th>
            <th><i class="bi bi-gear me-1"></i>Ações</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($usuarios)): ?>
            <tr><td colspan="6" class="text-muted"><i class="bi bi-inbox me-1"></i>Nenhum usuário encontrado.</td></tr>
          <?php endif; ?>

          <?php foreach (($usuarios ?? []) as $usuario): ?>
            <?php
            $id = (int) ($usuario['id'] ?? 0);
            $funcoes = is_array($usuario['funcoes'] ?? null) ? $usuario['funcoes'] : [];
            ?>
            <tr>
              <td><?= $id ?></td>
              <td><?= htmlspecialchars((string) ($usuario['nome'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
              <td><?= htmlspecialchars((string) ($usuario['email'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
              <td>
                <?php if (empty($funcoes)): ?>
                  <span class="text-muted small">Nenhuma função</span>
                <?php else: ?>
                  <div class="d-flex flex-wrap gap-1">
                    <?php foreach ($funcoes as $funcao): ?>
                      <span class="badge bg-secondary"><?= htmlspecialchars((string) ($funcao['nome'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></span>
                    <?php endforeach; ?>
                  </div>
                <?php endif; ?>
              </td>
              <td>
                <?php if ((int) ($usuario['ativo'] ?? 0) === 1): ?>
                  <span class="badge bg-success">Sim</span>
                <?php else: ?>
                  <span class="badge bg-secondary">Não</span>
                <?php endif; ?>
              </td>
              <td>
                <a class="btn btn-outline-secondary btn-sm" href="/admin/funcao/usuario?id=<?= $id ?>">
                  <i class="bi bi-gear-fill me-1"></i>Definir função
                </a>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</section>
