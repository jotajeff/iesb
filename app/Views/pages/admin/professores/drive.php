<section class="container py-4">
    <div class="bg-white border rounded-3 p-4 shadow-sm">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
            <h4 class="mb-0"><i class="bi bi-google me-2"></i>Google Drive - Turma</h4>
            <a class="btn btn-outline-secondary btn-sm" href="/admin/professores/turmas"><i class="bi bi-arrow-left me-1"></i>Voltar</a>
        </div>

        <p class="mb-3">
            <strong>Turma:</strong>
            <?= htmlspecialchars((string) ($turma['nome'] ?? '-'), ENT_QUOTES, 'UTF-8') ?>
            <?php if (!empty($turma['curso_nome'])): ?>
                (<?= htmlspecialchars((string) $turma['curso_nome'], ENT_QUOTES, 'UTF-8') ?>)
            <?php endif; ?>
        </p>

        <form method="post" action="/admin/professores/salvar-drive">
            <input type="hidden" name="id_fk" value="<?= (int) ($turma['id'] ?? 0) ?>">
            <input type="hidden" name="tipo" value="drive">

            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label" for="titulo">Título</label>
                    <input class="form-control" type="text" name="titulo" id="titulo"
                           placeholder="Ex: Apostila - Módulo 1" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="link">Link do Google Drive</label>
                    <input class="form-control" type="text" name="link" id="link"
                           placeholder="https://drive.google.com/..." required>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button class="btn btn-primary w-100" type="submit">
                        <i class="bi bi-google me-1"></i>Adicionar
                    </button>
                </div>
            </div>
        </form>

        <hr class="my-4">

        <h5 class="mb-3"><i class="bi bi-list me-1"></i>Arquivos do Drive</h5>
        <div class="table-responsive">
            <table class="table table-striped table-sm align-middle">
                <thead>
                    <tr>
                        <th><i class="bi bi-hash"></i></th>
                        <th><i class="bi bi-fonts me-1"></i>Título</th>
                        <th><i class="bi bi-link-45deg me-1"></i>Link</th>
                        <th><i class="bi bi-calendar me-1"></i>Cadastrado em</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($materiais ?? [])): ?>
                        <tr><td colspan="4" class="text-muted"><i class="bi bi-inbox me-1"></i>Nenhum arquivo cadastrado.</td></tr>
                    <?php endif; ?>
                    <?php foreach ($materiais ?? [] as $m): ?>
                        <tr>
                            <td>
                                <a href="#" class="text-decoration-none fw-bold" data-bs-toggle="modal" data-bs-target="#verDriveModal"
                                   data-titulo="<?= htmlspecialchars((string) ($m['titulo'] ?? '-'), ENT_QUOTES, 'UTF-8') ?>"
                                   data-link="<?= htmlspecialchars((string) ($m['link'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                                    #<?= (int) ($m['id'] ?? 0) ?>
                                </a>
                            </td>
                            <td><?= htmlspecialchars((string) ($m['titulo'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                            <td class="text-break">
                                <a href="<?= htmlspecialchars((string) ($m['link'] ?? '#'), ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener">
                                    <?= htmlspecialchars((string) ($m['link'] ?? '-'), ENT_QUOTES, 'UTF-8') ?>
                                </a>
                            </td>
                            <td><?= htmlspecialchars((string) ($m['created_at'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

<?php require __DIR__ . '/ver_drive.php'; ?>

<script>
document.querySelectorAll('[data-bs-target="#verDriveModal"]').forEach(function(el) {
    el.addEventListener('click', function(e) {
        e.preventDefault();
        var titulo = this.getAttribute('data-titulo');
        var link = this.getAttribute('data-link');

        var embedSrc = link;

        var fileMatch = link.match(/\/file\/d\/([a-zA-Z0-9_-]+)/);
        if (fileMatch) {
            embedSrc = 'https://drive.google.com/file/d/' + fileMatch[1] + '/preview';
        } else {
            var docMatch = link.match(/\/document\/d\/([a-zA-Z0-9_-]+)/);
            if (docMatch) {
                embedSrc = 'https://docs.google.com/document/d/' + docMatch[1] + '/preview';
            }
        }

        document.getElementById('driveTitulo').textContent = titulo;
        document.getElementById('driveIframe').src = embedSrc;
    });
});

document.getElementById('verDriveModal').addEventListener('hidden.bs.modal', function() {
    document.getElementById('driveIframe').src = '';
});
</script>
