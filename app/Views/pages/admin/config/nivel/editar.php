<section class="container py-4">
    <div class="bg-white border rounded-3 p-4 shadow-sm">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
            <h4 class="mb-0"><i class="bi bi-pencil-square me-2"></i>Editar Nível</h4>
            <div class="d-flex flex-wrap align-items-center gap-2">
                <a class="btn btn-outline-secondary btn-sm" href="/admin/config/nivel"><i class="bi bi-arrow-left me-1"></i>Voltar</a>
            </div>
        </div>

        <form method="post" action="/admin/config/nivel/atualizar">
            <input type="hidden" name="id" value="<?= htmlspecialchars((string) ($nivel['id'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
            <div class="mb-3">
                <label for="nome" class="form-label">Nome</label>
                <input type="text" class="form-control" id="nome" name="nome" value="<?= htmlspecialchars((string) ($nivel['nome'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" readonly>
            </div>
            <div class="mb-3">
                <label for="ativo" class="form-label">Ativo (S/N)</label>
                <select class="form-select" id="ativo" name="ativo">
                    <option value="S" <?= ((string) ($nivel['ativo'] ?? '')) === 'S' ? 'selected' : '' ?>>S - Sim</option>
                    <option value="N" <?= ((string) ($nivel['ativo'] ?? '')) === 'N' ? 'selected' : '' ?>>N - Não</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary"><i class="bi bi-check-circle me-1"></i>Salvar</button>
        </form>
    </div>
</section>
