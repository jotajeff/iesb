<?php
  $tipoSelecionado = (string) ($tipo ?? '');
  $turmasView = is_array($turmas ?? null) ? $turmas : [];
?>

<section class="container py-4">
  <div class="bg-white border rounded-3 p-4 shadow-sm">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
      <h4 class="mb-0"><i class="bi bi-file-earmark-plus me-2"></i>Novo Material</h4>
      <a class="btn btn-outline-secondary btn-sm" href="/admin/material"><i class="bi bi-arrow-left me-1"></i>Voltar</a>
    </div>

    <?php if ($tipoSelecionado === ''): ?>
      <p class="text-muted mb-3">Escolha o tipo de material que deseja publicar:</p>
      <div class="row g-3">
        <div class="col-md-6">
          <a href="/admin/material/novo?tipo=video" class="text-decoration-none d-block h-100">
            <div class="card border shadow-sm h-100">
              <div class="card-body text-center">
                <i class="bi bi-camera-reels fs-1 text-danger"></i>
                <h5 class="mt-2 mb-1">Vídeo</h5>
                <p class="text-muted small mb-0">Publique um vídeo (link do YouTube ou iframe).</p>
              </div>
            </div>
          </a>
        </div>
        <div class="col-md-6">
          <a href="/admin/material/novo?tipo=pdf" class="text-decoration-none d-block h-100">
            <div class="card border shadow-sm h-100">
              <div class="card-body text-center">
                <i class="bi bi-file-earmark-pdf fs-1 text-primary"></i>
                <h5 class="mt-2 mb-1">PDF</h5>
                <p class="text-muted small mb-0">Envie um arquivo PDF (armazenado no Google Drive).</p>
              </div>
            </div>
          </a>
        </div>
      </div>
    <?php else: ?>
      <form method="post" action="/admin/material/salvar" enctype="multipart/form-data" class="row g-3" id="formMaterial">
        <input type="hidden" name="tipo" value="<?= htmlspecialchars($tipoSelecionado, ENT_QUOTES, 'UTF-8') ?>">

        <div class="col-12">
          <span class="badge <?= $tipoSelecionado === 'video' ? 'bg-danger' : 'bg-primary' ?>">
            <i class="bi <?= $tipoSelecionado === 'video' ? 'bi-camera-reels' : 'bi-file-earmark-pdf' ?> me-1"></i>
            <?= strtoupper($tipoSelecionado) ?>
          </span>
          <a class="btn btn-outline-secondary btn-sm ms-2" href="/admin/material/novo"><i class="bi bi-arrow-left-short me-1"></i>Trocar tipo</a>
        </div>

        <div class="col-md-6">
          <label class="form-label">Turma <span class="text-danger">*</span></label>
          <select class="form-select" name="id_fk" id="materialTurma" required>
            <option value="">Selecione a turma</option>
            <?php foreach ($turmasView as $turma): ?>
              <?php
                $label = (string) ($turma['turma_nome'] ?? '-');
                if (trim((string) ($turma['curso_nome'] ?? '')) !== '') {
                  $label .= ' — ' . (string) $turma['curso_nome'];
                }
              ?>
              <option value="<?= (int) ($turma['id'] ?? 0) ?>"><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="col-md-6">
          <label class="form-label">Disciplina</label>
          <select class="form-select" name="id_disciplina" id="materialDisciplina" disabled>
            <option value="0">Secretaria (material geral da turma)</option>
          </select>
          <div class="form-text">Selecione a disciplina ou deixe em "Secretaria" para material geral da turma.</div>
        </div>

        <div class="col-12">
          <div class="form-check">
            <input class="form-check-input" type="checkbox" name="extra" value="1" id="materialExtra">
            <label class="form-check-label" for="materialExtra">Material extra</label>
          </div>
        </div>

        <div class="col-md-6">
          <label class="form-label">Título <span class="text-danger">*</span></label>
          <input type="text" class="form-control" name="titulo" required maxlength="256" placeholder="Ex: Aula 1 - Introdução">
        </div>

        <?php if ($tipoSelecionado === 'video'): ?>
          <div class="col-12">
            <label class="form-label">Link do vídeo <span class="text-danger">*</span></label>
            <input type="text" class="form-control" name="link" required placeholder="https://www.youtube.com/watch?v=... ou &lt;iframe src=...">
          </div>
        <?php else: ?>
          <div class="col-12">
            <label class="form-label">Arquivo PDF <span class="text-danger">*</span></label>
            <input type="file" class="form-control" name="arquivo" accept="application/pdf" required>
            <div class="form-text">Apenas PDF, até 20MB. O arquivo será enviado ao Google Drive.</div>
          </div>
        <?php endif; ?>

        <div class="col-12">
          <button class="btn btn-success" type="submit"><i class="bi bi-check-lg me-1"></i>Publicar material</button>
        </div>
      </form>
    <?php endif; ?>
  </div>
</section>

<div id="materialLoader" style="display:none;position:fixed;inset:0;z-index:2000;background:rgba(0,0,0,.55);align-items:center;justify-content:center;">
  <div class="bg-white rounded-3 p-4 shadow" style="width:90%;max-width:420px;">
    <div class="d-flex align-items-center gap-2 mb-2">
      <span class="spinner-border spinner-border-sm text-primary"></span>
      <strong>Enviando material...</strong>
    </div>
    <p class="text-muted small mb-2">Aguardando o envio para o Google Drive. Não feche esta página.</p>
    <div class="progress" style="height:18px;">
      <div id="materialProgressBar" class="progress-bar progress-bar-striped progress-bar-animated" style="width:0%;">0%</div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
  var form = document.getElementById('formMaterial');
  if (!form) return;
  var loader = document.getElementById('materialLoader');
  var bar = document.getElementById('materialProgressBar');
  if (!loader || !bar) return;

  form.addEventListener('submit', function (event) {
    var tipoInput = form.querySelector('input[name="tipo"]');
    if (!tipoInput || tipoInput.value !== 'pdf') return;

    event.preventDefault();
    bar.style.width = '0%';
    bar.textContent = '0%';
    loader.style.display = 'flex';

    var xhr = new XMLHttpRequest();
    xhr.open('POST', form.action);

    xhr.upload.addEventListener('progress', function (e) {
      if (e.lengthComputable) {
        var pct = Math.min(95, Math.round((e.loaded / e.total) * 100));
        bar.style.width = pct + '%';
        bar.textContent = pct + '%';
      }
    });

    xhr.addEventListener('load', function () {
      bar.style.width = '100%';
      bar.textContent = '100%';
      var finalUrl = xhr.responseURL || '';
      if (finalUrl.indexOf('/admin/material/novo') !== -1) {
        window.location.href = finalUrl;
      } else {
        window.location.href = '/admin/material';
      }
    });

    xhr.addEventListener('error', function () {
      loader.style.display = 'none';
      alert('Erro ao enviar o arquivo. Tente novamente.');
    });

    xhr.send(new FormData(form));
  });
});
</script>

<script>
document.addEventListener('DOMContentLoaded', function () {
  var selTurma = document.getElementById('materialTurma');
  var selDisciplina = document.getElementById('materialDisciplina');
  if (!selTurma || !selDisciplina) return;

  function limparDisciplinas() {
    selDisciplina.innerHTML = '';
    var opt = document.createElement('option');
    opt.value = '0';
    opt.textContent = 'Secretaria (material geral da turma)';
    selDisciplina.appendChild(opt);
    selDisciplina.disabled = true;
  }

  function carregarDisciplinas(idTurma) {
    limparDisciplinas();
    if (!idTurma) return;
    fetch('/admin/material/ajax-disciplinas?id_turma=' + encodeURIComponent(idTurma))
      .then(function (r) { return r.json(); })
      .then(function (itens) {
        limparDisciplinas();
        itens.forEach(function (d) {
          var opt = document.createElement('option');
          opt.value = d.id;
          opt.textContent = d.disciplina_nome;
          selDisciplina.appendChild(opt);
        });
        selDisciplina.disabled = false;
      })
      .catch(function () {
        limparDisciplinas();
      });
  }

  selTurma.addEventListener('change', function () { carregarDisciplinas(selTurma.value); });
});
</script>