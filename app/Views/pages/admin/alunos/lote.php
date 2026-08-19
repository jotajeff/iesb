<?php
$resultado = is_array($resultado ?? null) ? $resultado : null;
?>

<section class="container py-4">
  <div class="bg-white border rounded-3 p-4 shadow-sm">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
      <div>
        <div class="text-uppercase text-muted small fw-semibold mb-1">Cadastro de alunos</div>
        <h4 class="mb-0"><i class="bi bi-file-earmark-excel me-2"></i>Importação em Lote</h4>
      </div>
      <a class="btn btn-outline-secondary btn-sm" href="/admin/alunos">
        <i class="bi bi-arrow-left-short me-1"></i>Voltar
      </a>
    </div>

    <div class="alert alert-info d-flex align-items-center gap-2 py-2 mb-3">
      <i class="bi bi-info-circle-fill"></i>
      <span>A planilha deve conter as colunas <strong>nome</strong>, <strong>telefone</strong> e <strong>email</strong>. A senha será gerada automaticamente como o prefixo do email (antes do @) + "#" + ano atual, ex.: <code>jota@gmail.com</code> &rarr; <code>jota#<?= date('Y') ?></code>.</span>
    </div>

    <?php if ($resultado !== null): ?>
      <div class="alert alert-<?= ($resultado['erros'] ?? []) ? 'warning' : 'success' ?> py-2 mb-3">
        <div class="fw-semibold mb-1">
          Total: <strong><?= (int) ($resultado['total'] ?? 0) ?></strong> |
          Importados: <strong class="text-success"><?= (int) ($resultado['importados'] ?? 0) ?></strong> |
          Ignorados: <strong class="text-danger"><?= (int) ($resultado['ignorados'] ?? 0) ?></strong>
        </div>
        <?php if (!empty($resultado['erros'])): ?>
          <ul class="mb-0 small ps-3" style="max-height: 180px; overflow-y: auto;">
            <?php foreach ($resultado['erros'] as $erro): ?>
              <li><?= htmlspecialchars((string) $erro, ENT_QUOTES, 'UTF-8') ?></li>
            <?php endforeach; ?>
          </ul>
        <?php endif; ?>
      </div>
    <?php endif; ?>

    <form action="/admin/alunos/lote/importar" method="post" enctype="multipart/form-data">
      <div class="mb-3">
        <label for="planilha" class="form-label">Planilha (.xlsx ou .csv) <span class="text-danger">*</span></label>
        <input type="file" class="form-control" id="planilha" name="planilha" accept=".xlsx,.csv" required>
        <div class="form-text">Primeira linha = cabeçalho (nome, telefone, email). Emails duplicados são ignorados.</div>
      </div>
      <button type="submit" class="btn btn-primary"><i class="bi bi-upload me-1"></i>Importar</button>
    </form>
  </div>
</section>