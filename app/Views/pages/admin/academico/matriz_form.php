<?php
$matriz = $matriz ?? null;
$cursos = $cursos ?? [];
$idMatriz = (int) ($matriz['id'] ?? 0);
?>
<section class="container py-4">
  <div class="bg-white border rounded-3 p-4 shadow-sm">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
      <h4 class="mb-0"><i class="bi bi-diagram-3 me-2"></i><?= $idMatriz > 0 ? 'Editar' : 'Nova' ?> Matriz Curricular</h4>
      <a class="btn btn-outline-secondary btn-sm" href="/admin/academico/matrizes"><i class="bi bi-arrow-left me-1"></i>Voltar</a>
    </div>

    <form method="post" action="/admin/academico/matrizes/salvar" class="row g-3">
      <input type="hidden" name="id" value="<?= $idMatriz ?>">

      <div class="col-md-6">
        <label class="form-label">Curso <span class="text-danger">*</span></label>
        <select name="id_curso" class="form-select" required>
          <option value="">Selecione...</option>
          <?php foreach ($cursos as $curso): ?>
            <option value="<?= (int) ($curso['id'] ?? 0) ?>" <?= (int) ($matriz['id_curso'] ?? 0) === (int) ($curso['id'] ?? 0) ? 'selected' : '' ?>><?= htmlspecialchars((string) ($curso['nome'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-6">
        <label class="form-label">Nome da matriz <span class="text-danger">*</span></label>
        <input type="text" name="nome" class="form-control" maxlength="150" value="<?= htmlspecialchars((string) ($matriz['nome'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" required>
      </div>
      <div class="col-md-12">
        <label class="form-label">Descrição</label>
        <textarea name="descricao" class="form-control" rows="3"><?= htmlspecialchars((string) ($matriz['descricao'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>
      </div>
      <div class="col-md-4">
        <label class="form-label">Carga horária</label>
        <input type="number" name="carga_horaria" class="form-control" min="0" value="<?= (int) ($matriz['carga_horaria'] ?? 0) ?>">
      </div>
      <div class="col-md-4">
        <label class="form-label">Versão</label>
        <input type="text" name="versao" class="form-control" maxlength="20" value="<?= htmlspecialchars((string) ($matriz['versao'] ?? '1.0'), ENT_QUOTES, 'UTF-8') ?>">
      </div>
      <div class="col-md-4">
        <label class="form-label">Ativo</label>
        <select name="ativo" class="form-select">
          <option value="1" <?= (int) ($matriz['ativo'] ?? 1) === 1 ? 'selected' : '' ?>>Sim</option>
          <option value="0" <?= (int) ($matriz['ativo'] ?? 1) === 0 ? 'selected' : '' ?>>Não</option>
        </select>
      </div>

      <div class="col-12 d-flex gap-2">
        <button type="submit" class="btn btn-success"><i class="bi bi-check-lg me-1"></i>Salvar</button>
        <a class="btn btn-outline-secondary" href="/admin/academico/matrizes">Cancelar</a>
      </div>
    </form>
  </div>
</section>
