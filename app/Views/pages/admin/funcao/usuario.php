<?php
$usuario = is_array($usuario ?? null) ? $usuario : [];
$funcoes = is_array($funcoes ?? null) ? $funcoes : [];
$funcoesSelecionadas = array_map('intval', (array) ($funcoesSelecionadas ?? []));
$idUsuario = (int) ($usuario['id'] ?? 0);
?>
<section class="container py-4">
  <div class="bg-white border rounded-3 p-4 shadow-sm">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
      <div>
        <h4 class="mb-1"><i class="bi bi-gear-fill me-2"></i>Função do Usuário</h4>
        <p class="text-muted mb-0">Selecione as funções de <?= htmlspecialchars((string) ($usuario['nome'] ?? '-'), ENT_QUOTES, 'UTF-8') ?>.</p>
      </div>
      <a class="btn btn-outline-secondary btn-sm" href="/admin/funcao"><i class="bi bi-arrow-left me-1"></i>Voltar para lista</a>
    </div>

    <div class="row g-3 mb-4">
      <div class="col-md-4">
        <label class="form-label text-muted mb-1">Nome</label>
        <div class="fw-semibold"><?= htmlspecialchars((string) ($usuario['nome'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></div>
      </div>
      <div class="col-md-4">
        <label class="form-label text-muted mb-1">Email</label>
        <div><?= htmlspecialchars((string) ($usuario['email'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></div>
      </div>
      <div class="col-md-2">
        <label class="form-label text-muted mb-1">Tipo</label>
        <div><?= htmlspecialchars(ucfirst((string) ($usuario['tipo'] ?? '-')), ENT_QUOTES, 'UTF-8') ?></div>
      </div>
      <div class="col-md-2">
        <label class="form-label text-muted mb-1">Ativo</label>
        <div><?= (int) ($usuario['ativo'] ?? 0) === 1 ? '<span class="badge bg-success">Sim</span>' : '<span class="badge bg-secondary">Não</span>' ?></div>
      </div>
    </div>

    <form method="post" action="/admin/funcao/salvar">
      <input type="hidden" name="id" value="<?= $idUsuario ?>">

      <?php if (empty($funcoes)): ?>
        <div class="alert alert-info mb-0"><i class="bi bi-info-circle me-1"></i>Nenhuma função ativa cadastrada.</div>
      <?php else: ?>
        <div class="row g-2">
          <?php foreach ($funcoes as $funcao): ?>
            <?php
            $idFuncao = (int) ($funcao['id'] ?? 0);
            $selecionada = in_array($idFuncao, $funcoesSelecionadas, true);
            ?>
            <div class="col-md-6">
              <div class="form-check border rounded-3 p-3 ps-5">
                <input class="form-check-input" type="checkbox" name="funcoes[]" id="funcao-<?= $idFuncao ?>" value="<?= $idFuncao ?>" <?= $selecionada ? 'checked' : '' ?>>
                <label class="form-check-label" for="funcao-<?= $idFuncao ?>">
                  <span class="fw-semibold d-block"><?= htmlspecialchars((string) ($funcao['nome'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></span>
                  <?php if (trim((string) ($funcao['descricao'] ?? '')) !== ''): ?>
                    <span class="text-muted small"><?= htmlspecialchars((string) $funcao['descricao'], ENT_QUOTES, 'UTF-8') ?></span>
                  <?php endif; ?>
                </label>
              </div>
            </div>
          <?php endforeach; ?>
        </div>

        <div class="mt-4">
          <button class="btn btn-primary" type="submit"><i class="bi bi-check-lg me-1"></i>Salvar funções</button>
        </div>
      <?php endif; ?>
    </form>
  </div>
</section>
