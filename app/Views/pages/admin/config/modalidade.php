<section class="container py-4">
    <div class="bg-white border rounded-3 p-4 shadow-sm">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
            <h4 class="mb-0"><i class="bi bi-geo-alt me-2"></i>Modalidades</h4>
            <div class="d-flex flex-wrap align-items-center gap-2">
                <?php if (($authUser['role'] ?? '') === 'admin'): ?>
                    <a class="btn btn-primary btn-sm" href="/admin/config/modalidade/novo"><i class="bi bi-plus-circle me-1"></i>Nova modalidade</a>
                <?php endif; ?>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-striped table-sm align-middle">
                <thead>
                    <tr>
                        <th><i class="bi bi-hash"></i></th>
                        <th><i class="bi bi-card-text me-1"></i>Nome</th>
                        <th><i class="bi bi-toggle-on me-1"></i>Ativo</th>
                        <th><i class="bi bi-gear me-1"></i>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $isAdmin = (($authUser['role'] ?? '') === 'admin');
                    $filtered = $isAdmin ? ($modalidades ?? []) : array_filter($modalidades ?? [], fn($m) => false);
                    ?>

                    <?php if (empty($filtered)): ?>
                        <tr><td colspan="4" class="text-muted"><i class="bi bi-inbox me-1"></i>Nenhuma modalidade encontrada.</td></tr>
                    <?php endif; ?>

                    <?php foreach ($filtered as $modalidade): ?>
                        <?php $id = (int) ($modalidade['id'] ?? 0); ?>
                        <tr>
                            <td><?= $id ?></td>
                            <td><?= htmlspecialchars((string) ($modalidade['nome'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                            <td>
                                <?php $ativo = (int) ($modalidade['ativo'] ?? 0); ?>
                                <?php if ($ativo === 1): ?>
                                    <span class="badge bg-success">Sim</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Não</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($isAdmin): ?>
                                    <a class="btn btn-outline-secondary btn-sm me-1" href="/admin/config/modalidade/editar?id=<?= $id ?>">
                                        <i class="bi bi-pencil-square me-1"></i>Editar
                                    </a>
                                    <form method="post" action="/admin/config/modalidade/excluir" class="d-inline"
                                          onsubmit="return confirm('Tem certeza que deseja excluir esta modalidade?');">
                                        <input type="hidden" name="id" value="<?= $id ?>">
                                        <button type="submit" class="btn btn-outline-danger btn-sm">
                                            <i class="bi bi-trash me-1"></i>Excluir
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>
