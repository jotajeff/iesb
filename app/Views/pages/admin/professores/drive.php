<section class="container py-4">
    <div class="bg-white border rounded-3 p-4 shadow-sm">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
            <h4 class="mb-0"><i class="bi bi-google me-2"></i>Materiais - Turma</h4>
            <a class="btn btn-outline-secondary btn-sm" href="/admin/professores/turmas"><i class="bi bi-arrow-left me-1"></i>Voltar</a>
        </div>

        <p class="mb-3">
            <strong>Turma:</strong>
            <?= htmlspecialchars((string) ($turma['nome'] ?? '-'), ENT_QUOTES, 'UTF-8') ?>
            <?php if (!empty($turma['curso_nome'])): ?>
                (<?= htmlspecialchars((string) $turma['curso_nome'], ENT_QUOTES, 'UTF-8') ?>)
            <?php endif; ?>
        </p>

        <?php $storageConectado = (bool) ($storageConectado ?? false); ?>

        <?php if (!$storageConectado): ?>
            <div class="alert alert-warning border">
                <i class="bi bi-exclamation-triangle me-1"></i>
                Storage não conectado. Não é possível enviar materiais. Conecte em <a href="/admin/storage">Storage</a>.
            </div>
        <?php endif; ?>

        <form method="post" action="/admin/professores/salvar-drive" enctype="multipart/form-data">
            <input type="hidden" name="id_fk" value="<?= (int) ($turma['id'] ?? 0) ?>">
            <input type="hidden" name="tipo" value="drive">

            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label" for="titulo">Título</label>
                    <input class="form-control" type="text" name="titulo" id="titulo"
                           placeholder="Ex: Apostila - Módulo 1">
                    <div class="form-text">Opcional. Se vazio, usa o nome do arquivo.</div>
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="arquivo">Arquivo PDF</label>
                    <input class="form-control" type="file" name="arquivo" id="arquivo" accept="application/pdf,.pdf" required <?= $storageConectado ? '' : 'disabled' ?>>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button class="btn btn-primary w-100" type="submit" <?= $storageConectado ? '' : 'disabled' ?>>
                        <i class="bi bi-upload me-1"></i>Enviar
                    </button>
                </div>
            </div>
        </form>

        <hr class="my-4">

        <h5 class="mb-3"><i class="bi bi-list me-1"></i>Materiais da Turma</h5>
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
                        <tr><td colspan="4" class="text-muted"><i class="bi bi-inbox me-1"></i>Nenhum material cadastrado.</td></tr>
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

        var fileId = null;
        var fileMatch = link.match(/\/file\/d\/([a-zA-Z0-9_-]+)/);
        if (fileMatch) {
            fileId = fileMatch[1];
        } else {
            var docMatch = link.match(/\/document\/d\/([a-zA-Z0-9_-]+)/);
            if (docMatch) {
                fileId = docMatch[1];
            }
        }

        var embedSrc = link;
        if (fileId) {
            embedSrc = 'https://drive.google.com/file/d/' + fileId + '/preview';
        }

        document.getElementById('driveTitulo').textContent = titulo;

        var frame = document.getElementById('driveIframe');
        var fallback = document.getElementById('driveFallback');
        var download = document.getElementById('driveDownload');

        if (fileId) {
            download.href = 'https://drive.google.com/uc?export=download&id=' + fileId;
            download.classList.remove('d-none');
        } else {
            download.href = link;
            download.classList.remove('d-none');
        }

        frame.src = embedSrc;
        frame.classList.remove('d-none');
        fallback.classList.add('d-none');

        frame.onload = function() {
            try {
                if (frame.contentDocument && frame.contentDocument.body && !frame.contentDocument.body.innerHTML.trim()) {
                    fallback.classList.remove('d-none');
                }
            } catch (err) {
                // Cross-origin: iframe carregou. Mantem o preview.
                frame.classList.remove('d-none');
            }
        };
    });
});

document.getElementById('verDriveModal').addEventListener('hidden.bs.modal', function() {
    document.getElementById('driveIframe').src = '';
    document.getElementById('driveIframe').classList.add('d-none');
    document.getElementById('driveFallback').classList.add('d-none');
});
</script>
