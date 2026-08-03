<?php
$tipo = $tipo ?? null;
$id = (int) ($tipo['id'] ?? 0);
$selecionados = $selecionados ?? [];
$editando = $tipo !== null;
?>
<section class="container py-4">
  <div class="bg-white border rounded-3 p-4 shadow-sm">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
      <h4 class="mb-0"><i class="bi bi-file-earmark-text me-2"></i><?= $editando ? 'Editar Tipo de Documento' : 'Novo Tipo de Documento' ?></h4>
      <a class="btn btn-outline-secondary btn-sm" href="/admin/tipos-documentos"><i class="bi bi-arrow-left me-1"></i>Voltar</a>
    </div>

    <?php if (!empty($flash ?? '')): ?>
      <div class="alert alert-info"><?= htmlspecialchars((string) $flash, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>

    <form method="post" action="/admin/tipos-documentos/<?= $editando ? 'atualizar' : 'salvar' ?>" class="row g-3">
      <?php if ($editando): ?>
        <input type="hidden" name="id" value="<?= $id ?>">
      <?php endif; ?>

      <div class="col-md-8">
        <label class="form-label">Descrição <span class="text-danger">*</span></label>
        <input type="text" name="descricao" class="form-control" value="<?= htmlspecialchars((string) ($tipo['descricao'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" maxlength="120" required>
      </div>

      <div class="col-md-2">
        <label class="form-label">Ordem</label>
        <input type="number" name="ordem" class="form-control" value="<?= (int) ($tipo['ordem'] ?? 0) ?>" min="0">
      </div>

      <div class="col-md-2">
        <label class="form-label">Ativo</label>
        <select name="ativo" class="form-select">
          <option value="1" <?= ((int) ($tipo['ativo'] ?? 1)) === 1 ? 'selected' : '' ?>>Sim</option>
          <option value="0" <?= ((int) ($tipo['ativo'] ?? 1)) === 0 ? 'selected' : '' ?>>Não</option>
        </select>
      </div>

      <div class="col-md-4">
        <label class="form-label">Obrigatório</label>
        <select name="obrigatorio" class="form-select">
          <option value="1" <?= ((int) ($tipo['obrigatorio'] ?? 0)) === 1 ? 'selected' : '' ?>>Sim</option>
          <option value="0" <?= ((int) ($tipo['obrigatorio'] ?? 0)) === 0 ? 'selected' : '' ?>>Não</option>
        </select>
      </div>

      <div class="col-12">
        <label class="form-label d-block">Grupo(s) que terão este tipo de documento</label>
        <?php if ($editando): ?>
          <select name="id_grupo" class="form-select" required>
            <option value="">Selecione um grupo</option>
            <?php foreach ($grupos as $g): ?>
              <?php $gid = (int) $g['id']; ?>
              <option value="<?= $gid ?>" <?= in_array($gid, $selecionados, true) ? 'selected' : '' ?>><?= htmlspecialchars((string) ($g['descricao'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></option>
            <?php endforeach; ?>
          </select>
        <?php else: ?>
          <?php if (empty($grupos)): ?>
            <p class="text-muted small">Nenhum grupo de documento cadastrado.</p>
          <?php else: ?>
            <div class="row g-2">
              <?php foreach ($grupos as $g): ?>
                <?php $gid = (int) $g['id']; ?>
                <div class="col-md-4">
                  <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="grupos[]" value="<?= $gid ?>" id="grupo_<?= $gid ?>" <?= in_array($gid, $selecionados, true) ? 'checked' : '' ?>>
                    <label class="form-check-label" for="grupo_<?= $gid ?>">
                      <?= htmlspecialchars((string) ($g['descricao'] ?? '-'), ENT_QUOTES, 'UTF-8') ?>
                    </label>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        <?php endif; ?>
      </div>

      <div class="col-12">
        <button class="btn btn-success" type="submit"><i class="bi bi-check-lg me-1"></i>Salvar</button>
      </div>
    </form>
  </div>
</section>
