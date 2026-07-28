<section class="container py-4">
    <div class="bg-white border rounded-3 p-4 shadow-sm">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
            <h4 class="mb-0"><i class="bi bi-camera-reels me-2"></i>Vídeos - Turma</h4>
            <a class="btn btn-outline-secondary btn-sm" href="/admin/professores/turmas"><i class="bi bi-arrow-left me-1"></i>Voltar</a>
        </div>

        <p class="mb-3">
            <strong>Turma:</strong>
            <?= htmlspecialchars((string) ($turma['nome'] ?? '-'), ENT_QUOTES, 'UTF-8') ?>
            <?php if (!empty($turma['curso_nome'])): ?>
                (<?= htmlspecialchars((string) $turma['curso_nome'], ENT_QUOTES, 'UTF-8') ?>)
            <?php endif; ?>
        </p>

        <form method="post" action="/admin/professores/salvar-video">
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
                        <th class="text-center"><i class="bi bi-gear"></i></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($materiais ?? [])): ?>
                        <tr><td colspan="5" class="text-muted"><i class="bi bi-inbox me-1"></i>Nenhum vídeo cadastrado.</td></tr>
                    <?php endif; ?>
                    <?php foreach ($materiais ?? [] as $m): ?>
                        <tr>
                            <td>
                                <a href="#" class="text-decoration-none fw-bold" data-bs-toggle="modal" data-bs-target="#verVideoModal"
                                   data-titulo="<?= htmlspecialchars((string) ($m['titulo'] ?? '-'), ENT_QUOTES, 'UTF-8') ?>"
                                   data-link="<?= htmlspecialchars((string) ($m['link'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                                    #<?= (int) ($m['id'] ?? 0) ?>
                                </a>
                            </td>
                            <td><?= htmlspecialchars((string) ($m['titulo'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                            <td class="text-break"><?= htmlspecialchars((string) ($m['link'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars((string) ($m['created_at'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                            <td class="text-center">
                                <button type="button" class="btn btn-sm btn-outline-danger"
                                        onclick="confirmarExclusaoVideo(<?= (int) ($m['id'] ?? 0) ?>)">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

<?php require __DIR__ . '/ver_video.php'; ?>

<script>
function confirmarExclusaoVideo(id) {
    if (!confirm('Tem certeza que deseja excluir este vídeo?')) return;

    var formData = new FormData();
    formData.append('id', id);

    fetch('/admin/professores/deletar-video', {
        method: 'POST',
        body: formData
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.sucesso) {
            window.location.reload();
        } else {
            alert('Erro: ' + (data.erro || 'não foi possível excluir.'));
        }
    })
    .catch(function() {
        alert('Erro de rede ao tentar excluir o vídeo.');
    });
}

document.querySelectorAll('[data-bs-target="#verVideoModal"]').forEach(function(el) {
    el.addEventListener('click', function(e) {
        e.preventDefault();
        var titulo = this.getAttribute('data-titulo');
        var link = this.getAttribute('data-link');
        var embedSrc = '';

        if (link.indexOf('youtube.com/watch') !== -1 || link.indexOf('youtu.be') !== -1) {
            var vid = '';
            var match = link.match(/(?:youtube\.com\/watch\?v=|youtu\.be\/)([a-zA-Z0-9_-]+)/);
            if (match) vid = match[1];
            if (vid) embedSrc = 'https://www.youtube.com/embed/' + vid;
        } else if (link.indexOf('<iframe') !== -1) {
            var srcMatch = link.match(/src=["']([^"']+)["']/);
            if (srcMatch) embedSrc = srcMatch[1];
        } else if (link.match(/^https?:\/\//)) {
            embedSrc = link;
        }

        document.getElementById('videoTitulo').textContent = titulo;
        document.getElementById('videoIframe').src = embedSrc;
    });
});

document.getElementById('verVideoModal').addEventListener('hidden.bs.modal', function() {
    document.getElementById('videoIframe').src = '';
});
</script>
