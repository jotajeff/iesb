<section class="container py-4">
    <div class="bg-white border rounded-3 p-4 shadow-sm">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
            <h4 class="mb-0"><i class="bi bi-camera-reels me-2"></i>Materiais - Turma</h4>
            <a class="btn btn-outline-secondary btn-sm" href="/admin/professores/turmas"><i class="bi bi-arrow-left me-1"></i>Voltar</a>
        </div>

        <p class="mb-3">
            <strong>Turma:</strong>
            <?= htmlspecialchars((string) ($turma['nome'] ?? '-'), ENT_QUOTES, 'UTF-8') ?>
            <?php if (!empty($turma['curso_nome'])): ?>
                (<?= htmlspecialchars((string) $turma['curso_nome'], ENT_QUOTES, 'UTF-8') ?>)
            <?php endif; ?>
        </p>

        <form method="post" action="/admin/professores/salvar-material">
            <input type="hidden" name="id_fk" value="<?= (int) ($turma['id'] ?? 0) ?>">
            <input type="hidden" name="tipo" value="video">

            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label" for="titulo">Título</label>
                    <input class="form-control" type="text" name="titulo" id="titulo"
                           placeholder="Ex: Aula 1 - Introdução" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="link">Link / Iframe</label>
                    <input class="form-control" type="text" name="link" id="link"
                           placeholder="https://www.youtube.com/watch?v=... ou &lt;iframe src=..." required>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button class="btn btn-danger w-100" type="submit">
                        <i class="bi bi-camera-reels me-1"></i>Adicionar
                    </button>
                </div>
            </div>
        </form>

        <hr class="my-4">

        <h5 class="mb-3"><i class="bi bi-list me-1"></i>Vídeos cadastrados</h5>
        <div class="table-responsive">
            <table class="table table-striped table-sm align-middle">
                <thead>
                    <tr>
                        <th><i class="bi bi-hash"></i></th>
                        <th><i class="bi bi-fonts me-1"></i>Título</th>
                        <th><i class="bi bi-link-45deg me-1"></i>Link / Iframe</th>
                        <th><i class="bi bi-calendar me-1"></i>Cadastrado em</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($materiais ?? [])): ?>
                        <tr><td colspan="4" class="text-muted"><i class="bi bi-inbox me-1"></i>Nenhum vídeo cadastrado.</td></tr>
                    <?php endif; ?>
                    <?php foreach ($materiais ?? [] as $m): ?>
                        <tr>
                            <td><?= (int) ($m['id'] ?? 0) ?></td>
                            <td><?= htmlspecialchars((string) ($m['titulo'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                            <td class="text-break"><?= htmlspecialchars((string) ($m['link'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars((string) ($m['criado_em'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>
