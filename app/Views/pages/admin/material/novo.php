<?php
  $tipoSelecionado = (string) ($tipo ?? '');
  $turmasView = is_array($turmas ?? null) ? $turmas : [];
  $badgesTipo = [
    'video' => ['cor' => 'bg-danger', 'icone' => 'bi-camera-reels'],
    'pdf' => ['cor' => 'bg-primary', 'icone' => 'bi-file-earmark-pdf'],
    'excel' => ['cor' => 'bg-success', 'icone' => 'bi-file-earmark-excel'],
    'doc' => ['cor' => 'bg-info text-dark', 'icone' => 'bi-file-earmark-word'],
    'link' => ['cor' => 'bg-success', 'icone' => 'bi-link-45deg'],
  ];
  $badgeAtual = $badgesTipo[$tipoSelecionado] ?? $badgesTipo['pdf'];
  $arquivosTipo = [
    'pdf' => [
      'label' => 'Arquivo PDF',
      'accept' => 'application/pdf',
      'ajuda' => 'Apenas PDF, até 20MB. O arquivo será enviado ao Google Drive.',
    ],
    'excel' => [
      'label' => 'Planilha Excel',
      'accept' => '.xls,.xlsx,.csv,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,text/csv',
      'ajuda' => 'Formatos XLS, XLSX ou CSV, até 20MB. O arquivo será enviado ao Google Drive.',
    ],
    'doc' => [
      'label' => 'Documento Word',
      'accept' => '.doc,.docx,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document',
      'ajuda' => 'Formatos DOC ou DOCX, até 20MB. O arquivo será enviado ao Google Drive.',
    ],
  ];
  $arquivoAtual = $arquivosTipo[$tipoSelecionado] ?? null;
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
        <div class="col-md-6 col-lg-4">
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
        <div class="col-md-6 col-lg-4">
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
        <div class="col-md-6 col-lg-4">
          <a href="/admin/material/novo?tipo=excel" class="text-decoration-none d-block h-100">
            <div class="card border shadow-sm h-100">
              <div class="card-body text-center">
                <i class="bi bi-file-earmark-excel fs-1 text-success"></i>
                <h5 class="mt-2 mb-1">Excel</h5>
                <p class="text-muted small mb-0">Envie uma planilha XLS, XLSX ou CSV (armazenada no Google Drive).</p>
              </div>
            </div>
          </a>
        </div>
        <div class="col-md-6 col-lg-4">
          <a href="/admin/material/novo?tipo=doc" class="text-decoration-none d-block h-100">
            <div class="card border shadow-sm h-100">
              <div class="card-body text-center">
                <i class="bi bi-file-earmark-word fs-1 text-info"></i>
                <h5 class="mt-2 mb-1">DOC</h5>
                <p class="text-muted small mb-0">Envie um documento Word DOC ou DOCX (armazenado no Google Drive).</p>
              </div>
            </div>
          </a>
        </div>
        <div class="col-md-6 col-lg-4">
          <a href="/admin/material/novo?tipo=link" class="text-decoration-none d-block h-100">
            <div class="card border shadow-sm h-100">
              <div class="card-body text-center">
                <i class="bi bi-link-45deg fs-1 text-success"></i>
                <h5 class="mt-2 mb-1">Link</h5>
                <p class="text-muted small mb-0">Publique um link externo (site, Google Drive, formulário...).</p>
              </div>
            </div>
          </a>
        </div>
      </div>
    <?php else: ?>
      <form method="post" action="/admin/material/salvar" enctype="multipart/form-data" class="row g-3" id="formMaterial">
        <input type="hidden" name="tipo" value="<?= htmlspecialchars($tipoSelecionado, ENT_QUOTES, 'UTF-8') ?>">

        <div class="col-12">
          <span class="badge <?= $badgeAtual['cor'] ?>">
            <i class="bi <?= $badgeAtual['icone'] ?> me-1"></i>
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
        <?php elseif ($tipoSelecionado === 'link'): ?>
          <div class="col-12">
            <label class="form-label">URL do link <span class="text-danger">*</span></label>
            <input type="text" class="form-control" name="link" required maxlength="1000" placeholder="https://exemplo.com/...">
            <div class="form-text">Informe a URL completa (começando com http:// ou https://). O link será aberto em nova aba pelo aluno.</div>
          </div>
        <?php elseif ($arquivoAtual !== null): ?>
          <div class="col-12">
            <label class="form-label"><?= htmlspecialchars($arquivoAtual['label'], ENT_QUOTES, 'UTF-8') ?> <span class="text-danger">*</span></label>
            <input type="file" class="form-control" name="arquivo" accept="<?= htmlspecialchars($arquivoAtual['accept'], ENT_QUOTES, 'UTF-8') ?>" required>
            <div class="form-text"><?= htmlspecialchars($arquivoAtual['ajuda'], ENT_QUOTES, 'UTF-8') ?></div>
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
    if (!tipoInput || ['pdf', 'excel', 'doc'].indexOf(tipoInput.value) === -1) return;

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