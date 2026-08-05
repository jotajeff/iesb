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
        <?php $driveArquivos = is_array($driveArquivos ?? null) ? $driveArquivos : []; ?>

        <?php if (!$storageConectado): ?>
            <div class="alert alert-warning border">
                <i class="bi bi-exclamation-triangle me-1"></i>
                Storage não conectado. Não é possível buscar materiais do Google Drive. Conecte em <a href="/admin/storage">Storage</a>.
            </div>
        <?php endif; ?>

        <form method="post" action="/admin/professores/salvar-drive">
            <input type="hidden" name="id_fk" value="<?= (int) ($turma['id'] ?? 0) ?>">
            <input type="hidden" name="tipo" value="drive">

            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label" for="titulo">Título</label>
                    <input class="form-control" type="text" name="titulo" id="titulo"
                           placeholder="Ex: Apostila - Módulo 1" required readonly>
                    <div class="form-text">Preenchido automaticamente ao buscar o material.</div>
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="link">Buscar Material (Google Drive)</label>
                    <input class="form-control" type="text" id="buscaMaterial"
                           placeholder="Digite o nome do arquivo para buscar..." <?= $storageConectado ? '' : 'disabled' ?>>
                    <input class="form-control mt-2" type="hidden" name="link" id="link" required>
                    <div class="form-text">Digite para filtrar os arquivos do seu Drive e selecione um.</div>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button class="btn btn-primary w-100" type="submit" <?= $storageConectado ? '' : 'disabled' ?>>
                        <i class="bi bi-link-45deg me-1"></i>Adicionar
                    </button>
                </div>
            </div>

            <?php if ($storageConectado && !empty($driveArquivos)): ?>
                <div class="mt-3 border rounded-3 p-2" style="max-height: 260px; overflow-y: auto;">
                    <?php foreach ($driveArquivos as $arquivo): ?>
                        <?php
                        $fileId = (string) ($arquivo['file_id'] ?? '');
                        $nomeArquivo = (string) ($arquivo['name'] ?? '-');
                        $linkArquivo = (string) ($arquivo['link'] ?? ('https://drive.google.com/file/d/' . $fileId . '/view'));
                        ?>
                        <button type="button" class="btn btn-outline-secondary btn-sm d-block text-start w-100 mb-1 material-option"
                                data-nome="<?= htmlspecialchars($nomeArquivo, ENT_QUOTES, 'UTF-8') ?>"
                                data-link="<?= htmlspecialchars($linkArquivo, ENT_QUOTES, 'UTF-8') ?>">
                            <i class="bi bi-file-earmark me-1"></i><?= htmlspecialchars($nomeArquivo, ENT_QUOTES, 'UTF-8') ?>
                            <span class="text-muted small ms-1"><?= $arquivo['size'] ? '(' . number_format((int) $arquivo['size'], 0, ',', '.') . ' bytes)' : '' ?></span>
                        </button>
                    <?php endforeach; ?>
                </div>
            <?php elseif ($storageConectado && empty($driveArquivos)): ?>
                <div class="alert alert-light border text-muted mt-3 mb-0">
                    <i class="bi bi-inbox me-1"></i>Nenhum arquivo encontrado no seu Google Drive. Envie arquivos para sua pasta e tente novamente.
                </div>
            <?php endif; ?>
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

<?php if ($storageConectado): ?>
document.querySelectorAll('.material-option').forEach(function(btn) {
    btn.addEventListener('click', function() {
        document.getElementById('titulo').value = this.getAttribute('data-nome');
        document.getElementById('link').value = this.getAttribute('data-link');
        btn.classList.add('btn-primary');
        btn.classList.remove('btn-outline-secondary');
        document.querySelectorAll('.material-option').forEach(function(other) {
            if (other !== btn) {
                other.classList.add('btn-outline-secondary');
                other.classList.remove('btn-primary');
            }
        });
    });
});

document.getElementById('buscaMaterial').addEventListener('input', function() {
    var termo = this.value.toLowerCase();
    document.querySelectorAll('.material-option').forEach(function(btn) {
        var nome = btn.getAttribute('data-nome').toLowerCase();
        btn.style.display = nome.indexOf(termo) !== -1 ? '' : 'none';
    });
});
<?php endif; ?>
</script>
