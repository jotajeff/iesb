<?php
  $turmasView = is_array($turmas ?? null) ? $turmas : [];
  $tiposView = is_array($tipos ?? null) ? $tipos : [];
  $storageConectado = (bool) ($storageConectado ?? false);
?>

<section class="container py-4">
  <div class="bg-white border rounded-3 p-4 shadow-sm">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
      <h4 class="mb-0"><i class="bi bi-file-earmark-plus me-2"></i>Novo Documento</h4>
      <a class="btn btn-outline-secondary btn-sm" href="/admin/documentos"><i class="bi bi-arrow-left me-1"></i>Voltar</a>
    </div>

    <?php if (!$storageConectado): ?>
      <div class="alert alert-warning">
        <i class="bi bi-exclamation-triangle me-1"></i>Storage não conectado. Conecte em
        <a href="/admin/storage" class="alert-link">/admin/storage</a> para enviar documentos.
      </div>
    <?php endif; ?>

    <?php if (empty($tiposView)): ?>
      <div class="alert alert-info">
        <i class="bi bi-info-circle me-1"></i>Nenhum tipo de documento ativo para o grupo Secretaria (id_grupo = 7).
      </div>
    <?php endif; ?>

    <form method="post" action="/admin/documentos/enviar" enctype="multipart/form-data" class="row g-3" id="formDocumento">
      <div class="col-md-6">
        <label class="form-label">Turma (filtro) <span class="text-danger">*</span></label>
        <select class="form-select" id="docTurma" required>
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
        <div class="form-text">Filtra os alunos matriculados na turma selecionada.</div>
      </div>

      <div class="col-md-6">
        <label class="form-label">Aluno <span class="text-danger">*</span></label>
        <select class="form-select" name="id_aluno" id="docAluno" required disabled>
          <option value="">Primeiro selecione a turma</option>
        </select>
      </div>

      <div class="col-md-6">
        <label class="form-label">Tipo de documento <span class="text-danger">*</span></label>
        <select class="form-select" name="id_tipo" required <?= empty($tiposView) ? 'disabled' : '' ?>>
          <option value="">Selecione o tipo</option>
          <?php foreach ($tiposView as $tipo): ?>
            <option value="<?= (int) ($tipo['id'] ?? 0) ?>">
              <?= htmlspecialchars((string) ($tipo['descricao'] ?? '-'), ENT_QUOTES, 'UTF-8') ?><?= (int) ($tipo['obrigatorio'] ?? 0) === 1 ? ' (obrigatório)' : '' ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="col-md-6">
        <label class="form-label">Arquivo <span class="text-danger">*</span></label>
        <input type="file" class="form-control" name="arquivo" accept="application/pdf,image/png,image/jpeg" required>
        <div class="form-text">PDF, PNG, JPG ou JPEG — até 20MB.</div>
      </div>

      <div class="col-12">
        <button class="btn btn-success" type="submit" <?= (!$storageConectado || empty($tiposView)) ? 'disabled' : '' ?>>
          <i class="bi bi-cloud-upload me-1"></i>Enviar documento
        </button>
      </div>
    </form>
  </div>
</section>

<div id="documentoLoader" style="display:none;position:fixed;inset:0;z-index:2000;background:rgba(0,0,0,.55);align-items:center;justify-content:center;">
  <div class="bg-white rounded-3 p-4 shadow" style="width:90%;max-width:420px;">
    <div class="d-flex align-items-center gap-2 mb-2">
      <span class="spinner-border spinner-border-sm text-primary"></span>
      <strong>Enviando documento...</strong>
    </div>
    <p class="text-muted small mb-2">Aguardando o envio para o Google Drive. Não feche esta página.</p>
    <div class="progress" style="height:18px;">
      <div id="documentoProgressBar" class="progress-bar progress-bar-striped progress-bar-animated" style="width:0%;">0%</div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
  var form = document.getElementById('formDocumento');
  var loader = document.getElementById('documentoLoader');
  var bar = document.getElementById('documentoProgressBar');
  if (!form || !loader || !bar) return;

  form.addEventListener('submit', function (event) {
    if (typeof form.checkValidity === 'function' && !form.checkValidity()) {
      return;
    }
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
      if (finalUrl.indexOf('/admin/documentos/novo') !== -1) {
        window.location.href = finalUrl;
      } else {
        window.location.href = '/admin/documentos';
      }
    });

    xhr.addEventListener('error', function () {
      loader.style.display = 'none';
      alert('Erro ao enviar o documento. Tente novamente.');
    });

    xhr.send(new FormData(form));
  });
});
</script>

<script>
document.addEventListener('DOMContentLoaded', function () {
  var selTurma = document.getElementById('docTurma');
  var selAluno = document.getElementById('docAluno');
  if (!selTurma || !selAluno) return;

  function limparAlunos(texto) {
    selAluno.innerHTML = '';
    var opt = document.createElement('option');
    opt.value = '';
    opt.textContent = texto;
    selAluno.appendChild(opt);
  }

  selTurma.addEventListener('change', function () {
    var idTurma = selTurma.value;
    selAluno.disabled = true;
    if (!idTurma) {
      limparAlunos('Primeiro selecione a turma');
      return;
    }

    limparAlunos('Carregando alunos...');
    fetch('/admin/documentos/ajax-alunos?id_turma=' + encodeURIComponent(idTurma))
      .then(function (r) { return r.json(); })
      .then(function (itens) {
        limparAlunos('Selecione o aluno');
        itens.forEach(function (a) {
          var opt = document.createElement('option');
          opt.value = a.id;
          opt.textContent = a.nome;
          selAluno.appendChild(opt);
        });
        selAluno.disabled = false;
      })
      .catch(function () {
        limparAlunos('Erro ao carregar alunos');
      });
  });
});
</script>