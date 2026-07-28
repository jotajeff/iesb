<section class="container py-4">
    <div class="bg-white border rounded-3 p-4 shadow-sm">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
            <h4 class="mb-0"><i class="bi bi-pencil-square me-2"></i>Editar Modalidade</h4>
            <div class="d-flex flex-wrap align-items-center gap-2">
                <a class="btn btn-outline-secondary btn-sm" href="/admin/config/modalidade"><i class="bi bi-arrow-left me-1"></i>Voltar</a>
            </div>
        </div>

        <form method="post" action="/admin/config/modalidade/atualizar">
            <input type="hidden" name="id" value="<?= htmlspecialchars((string) ($modalidade['id'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
            <div class="mb-3">
                <label for="nome" class="form-label">Nome</label>
                <input type="text" class="form-control" id="nome" name="nome" value="<?= htmlspecialchars((string) ($modalidade['nome'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" readonly>
            </div>
            <div class="mb-3">
                <label for="ativo" class="form-label">Ativo (S/N)</label>
                <select class="form-select" id="ativo" name="ativo">
                    <option value="1" <?= (int) ($modalidade['ativo'] ?? 0) == 1 ? 'selected' : '' ?>>1 - Sim</option>
                    <option value="0" <?= (int) ($modalidade['ativo'] ?? 0) == 0 ? 'selected' : '' ?>>0 - Não</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary"><i class="bi bi-check-circle me-1"></i>Salvar</button>
        </form>
    </div>
</section>
