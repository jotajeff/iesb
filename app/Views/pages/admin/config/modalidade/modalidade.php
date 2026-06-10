<section class="container py-4">
    <div class="bg-white border rounded-3 p-4 shadow-sm">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
            <h4 class="mb-0"><i class="bi bi-geo-alt me-2"></i>Modalidades</h4>
        </div>

        <div class="table-responsive">
            <table class="table table-striped table-sm align-middle">
                <thead>
                    <tr>
                        <th><i class="bi bi-hash"></i></th>
                        <th><i class="bi bi-card-text me-1"></i>Nome</th>
                        <th><i class="bi bi-toggle-on me-1"></i>Ativo</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($modalidades)): ?>
                        <tr><td colspan="3" class="text-muted"><i class="bi bi-inbox me-1"></i>Nenhuma modalidade encontrada.</td></tr>
                    <?php endif; ?>

                    <?php foreach ($modalidades as $modalidade): ?>
                        <?php $id = (int) ($modalidade['id'] ?? 0); ?>
                        <tr>
                            <td><?= $id ?></td>
                            <td><?= htmlspecialchars((string) ($modalidade['nome'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars((string) ($modalidade['ativo'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>
