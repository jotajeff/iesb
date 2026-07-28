<section class="container py-4">
    <div class="bg-white border rounded-3 p-4 shadow-sm">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
            <h4 class="mb-0"><i class="bi bi-person-workspace me-2"></i><?= $funcao ? 'Editar Função Docente' : 'Nova Função Docente' ?></h4>
            <a class="btn btn-outline-secondary btn-sm" href="/admin/funcoes-docente"><i class="bi bi-arrow-left me-1"></i>Voltar</a>
        </div>

        <form method="post" action="/admin/funcoes-docente/update" class="row g-3">
            <?php if ($funcao): ?>
                <input type="hidden" name="id" value="<?= (int) ($funcao['id'] ?? 0) ?>">
            <?php endif; ?>

            <div class="col-md-6">
                <label class="form-label">Nome <span class="text-danger">*</span></label>
                <input type="text" name="nome" class="form-control" value="<?= htmlspecialchars((string) ($funcao['nome'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" required maxlength="50">
            </div>

            <div class="col-md-3">
                <label class="form-label">Ativo</label>
                <select name="ativo" class="form-select">
                    <option value="1" <?= (int) ($funcao['ativo'] ?? 1) == 1 ? 'selected' : '' ?>>Sim</option>
                    <option value="0" <?= (int) ($funcao['ativo'] ?? 1) == 0 ? 'selected' : '' ?>>Não</option>
                </select>
            </div>

            <div class="col-12">
                <label class="form-label">Descrição</label>
                <textarea name="descricao" class="form-control" rows="3" maxlength="255"><?= htmlspecialchars((string) ($funcao['descricao'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>
            </div>

            <div class="col-12">
                <button class="btn btn-success" type="submit"><i class="bi bi-check-lg me-1"></i>Salvar</button>
            </div>
        </form>
    </div>
</section>
