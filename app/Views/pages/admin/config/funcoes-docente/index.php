<section class="container py-4">
    <div class="bg-white border rounded-3 p-4 shadow-sm">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
            <h4 class="mb-0"><i class="bi bi-person-workspace me-2"></i>Funções Docente</h4>
            <div class="d-flex flex-wrap align-items-center gap-2">
                <?php if (($authUser['role'] ?? '') === 'admin'): ?>
                    <a class="btn btn-primary btn-sm" href="/admin/funcoes-docente/edit"><i class="bi bi-plus-circle me-1"></i>Nova função</a>
                <?php endif; ?>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-striped table-sm align-middle">
                <thead>
                    <tr>
                        <th><i class="bi bi-hash"></i></th>
                        <th><i class="bi bi-card-text me-1"></i>Nome</th>
                        <th><i class="bi bi-chat-left-text me-1"></i>Descrição</th>
                        <th><i class="bi bi-toggle-on me-1"></i>Ativo</th>
                        <th><i class="bi bi-gear me-1"></i>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $isAdmin = (($authUser['role'] ?? '') === 'admin');
                    $filtered = $isAdmin ? ($funcoes ?? []) : [];
                    ?>

                    <?php if (empty($filtered)): ?>
                        <tr><td colspan="5" class="text-muted"><i class="bi bi-inbox me-1"></i>Nenhuma função encontrada.</td></tr>
                    <?php endif; ?>

                    <?php foreach ($filtered as $funcao): ?>
                        <?php $id = (int) ($funcao['id'] ?? 0); ?>
                        <tr>
                            <td><?= $id ?></td>
                            <td><?= htmlspecialchars((string) ($funcao['nome'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars((string) ($funcao['descricao'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                            <td>
                                <?php $ativo = (int) ($funcao['ativo'] ?? 1); ?>
                                <?php if ($ativo == 1): ?>
                                    <span class="badge bg-success">Sim</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Não</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($isAdmin): ?>
                                    <a class="btn btn-outline-secondary btn-sm me-1" href="/admin/funcoes-docente/edit?id=<?= $id ?>">
                                        <i class="bi bi-pencil-square me-1"></i>Editar
                                    </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>
