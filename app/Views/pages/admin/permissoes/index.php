<?php
$moduloEdicao = is_array($moduloEdicao ?? null) ? $moduloEdicao : [];
$funcaoEdicao = is_array($funcaoEdicao ?? null) ? $funcaoEdicao : [];
$funcaoSelecionada = is_array($funcaoSelecionada ?? null) ? $funcaoSelecionada : [];
$permissoes = is_array($permissoes ?? null) ? $permissoes : [];
?>
<section class="container py-4">
  <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <div>
      <h4 class="mb-1"><i class="bi bi-shield-check me-2"></i>Permissões</h4>
      <p class="text-muted mb-0">Cadastre módulos, funções e permissões de acesso. A aplicação ainda não aplica essas regras no login.</p>
    </div>
  </div>

  <div class="row g-4">
    <div class="col-lg-6">
      <div class="card border-0 shadow-sm h-100">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
          <strong><i class="bi bi-grid-3x3-gap me-2"></i><?= !empty($moduloEdicao) ? 'Editar módulo' : 'Novo módulo' ?></strong>
          <?php if (!empty($moduloEdicao)): ?><a href="/admin/permissoes" class="btn btn-sm btn-outline-secondary">Novo</a><?php endif; ?>
        </div>
        <div class="card-body">
          <form method="post" action="/admin/permissoes/modulo/salvar" class="row g-3">
            <input type="hidden" name="id" value="<?= (int) ($moduloEdicao['id'] ?? 0) ?>">
            <div class="col-md-7"><label class="form-label">Nome</label><input class="form-control" name="nome" required value="<?= htmlspecialchars((string) ($moduloEdicao['nome'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"></div>
            <div class="col-md-5"><label class="form-label">Rota</label><input class="form-control" name="rota" placeholder="/admin/exemplo" value="<?= htmlspecialchars((string) ($moduloEdicao['rota'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"></div>
            <div class="col-md-6"><label class="form-label">Ícone Bootstrap</label><input class="form-control" name="icone" placeholder="bi bi-grid" value="<?= htmlspecialchars((string) ($moduloEdicao['icone'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"></div>
            <div class="col-md-3"><label class="form-label">Ordem</label><input class="form-control" type="number" name="ordem" value="<?= (int) ($moduloEdicao['ordem'] ?? 0) ?>"></div>
            <div class="col-md-3"><label class="form-label">Ativo</label><select class="form-select" name="ativo"><option value="1" <?= (int) ($moduloEdicao['ativo'] ?? 1) === 1 ? 'selected' : '' ?>>Sim</option><option value="0" <?= (int) ($moduloEdicao['ativo'] ?? 1) === 0 ? 'selected' : '' ?>>Não</option></select></div>
            <div class="col-12"><button class="btn btn-success" type="submit"><i class="bi bi-check-lg me-1"></i>Salvar módulo</button></div>
          </form>
          <hr>
          <div class="table-responsive"><table class="table table-sm align-middle mb-0"><thead><tr><th>Nome</th><th>Rota</th><th>Status</th><th></th></tr></thead><tbody>
          <?php foreach (($modulos ?? []) as $modulo): ?><tr><td><?= htmlspecialchars((string) ($modulo['nome'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td><td class="small text-muted"><?= htmlspecialchars((string) ($modulo['rota'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td><td><?= (int) ($modulo['ativo'] ?? 0) === 1 ? '<span class="badge bg-success">Ativo</span>' : '<span class="badge bg-secondary">Inativo</span>' ?></td><td><a class="btn btn-sm btn-outline-secondary" href="/admin/permissoes?editar_modulo=<?= (int) $modulo['id'] ?>">Editar</a></td></tr><?php endforeach; ?>
          <?php if (empty($modulos)): ?><tr><td colspan="4" class="text-muted">Nenhum módulo cadastrado.</td></tr><?php endif; ?>
          </tbody></table></div>
        </div>
      </div>
    </div>

    <div class="col-lg-6">
      <div class="card border-0 shadow-sm h-100">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
          <strong><i class="bi bi-person-badge me-2"></i><?= !empty($funcaoEdicao) ? 'Editar função' : 'Nova função' ?></strong>
          <?php if (!empty($funcaoEdicao)): ?><a href="/admin/permissoes" class="btn btn-sm btn-outline-secondary">Nova</a><?php endif; ?>
        </div>
        <div class="card-body">
          <form method="post" action="/admin/permissoes/funcao/salvar" class="row g-3">
            <input type="hidden" name="id" value="<?= (int) ($funcaoEdicao['id'] ?? 0) ?>">
            <div class="col-md-7"><label class="form-label">Nome</label><input class="form-control" name="nome" required value="<?= htmlspecialchars((string) ($funcaoEdicao['nome'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"></div>
            <div class="col-md-5"><label class="form-label">Ativo</label><select class="form-select" name="ativo"><option value="1" <?= (int) ($funcaoEdicao['ativo'] ?? 1) === 1 ? 'selected' : '' ?>>Sim</option><option value="0" <?= (int) ($funcaoEdicao['ativo'] ?? 1) === 0 ? 'selected' : '' ?>>Não</option></select></div>
            <div class="col-12"><label class="form-label">Descrição</label><input class="form-control" name="descricao" value="<?= htmlspecialchars((string) ($funcaoEdicao['descricao'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"></div>
            <div class="col-12"><button class="btn btn-success" type="submit"><i class="bi bi-check-lg me-1"></i>Salvar função</button></div>
          </form>
          <hr>
          <div class="table-responsive"><table class="table table-sm align-middle mb-0"><thead><tr><th>Nome</th><th>Descrição</th><th>Status</th><th></th></tr></thead><tbody>
          <?php foreach (($funcoes ?? []) as $funcao): ?><tr><td><?= htmlspecialchars((string) ($funcao['nome'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td><td class="small text-muted"><?= htmlspecialchars((string) ($funcao['descricao'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td><td><?= (int) ($funcao['ativo'] ?? 0) === 1 ? '<span class="badge bg-success">Ativa</span>' : '<span class="badge bg-secondary">Inativa</span>' ?></td><td><a class="btn btn-sm btn-outline-secondary" href="/admin/permissoes?editar_funcao=<?= (int) $funcao['id'] ?>">Editar</a></td></tr><?php endforeach; ?>
          <?php if (empty($funcoes)): ?><tr><td colspan="4" class="text-muted">Nenhuma função cadastrada.</td></tr><?php endif; ?>
          </tbody></table></div>
        </div>
      </div>
    </div>
  </div>

  <div class="card border-0 shadow-sm mt-4">
    <div class="card-header bg-white"><strong><i class="bi bi-check2-square me-2"></i>Permissões por função</strong></div>
    <div class="card-body">
      <?php if (empty($funcoes)): ?><div class="alert alert-info mb-0">Cadastre uma função para lançar permissões.</div>
      <?php elseif (empty($modulos)): ?><div class="alert alert-info mb-0">Cadastre um módulo para lançar permissões.</div>
      <?php else: ?>
        <form method="get" action="/admin/permissoes" class="row g-2 align-items-end mb-3"><div class="col-md-5"><label class="form-label">Função</label><select class="form-select" name="funcao_id" onchange="this.form.submit()"><?php foreach ($funcoes as $funcao): ?><option value="<?= (int) $funcao['id'] ?>" <?= (int) ($funcaoSelecionada['id'] ?? 0) === (int) $funcao['id'] ? 'selected' : '' ?>><?= htmlspecialchars((string) $funcao['nome'], ENT_QUOTES, 'UTF-8') ?></option><?php endforeach; ?></select></div></form>
        <form method="post" action="/admin/permissoes/salvar">
          <input type="hidden" name="id_funcao" value="<?= (int) ($funcaoSelecionada['id'] ?? 0) ?>">
          <div class="table-responsive"><table class="table table-bordered table-sm align-middle"><thead><tr><th>Módulo</th><th class="text-center">Consultar</th><th class="text-center">Inserir</th><th class="text-center">Editar</th><th class="text-center">Excluir</th></tr></thead><tbody>
          <?php foreach ($modulos as $modulo): $mid = (int) $modulo['id']; $perm = $permissoes[$mid] ?? []; ?><tr><td><strong><?= htmlspecialchars((string) $modulo['nome'], ENT_QUOTES, 'UTF-8') ?></strong><div class="small text-muted"><?= htmlspecialchars((string) ($modulo['rota'] ?? ''), ENT_QUOTES, 'UTF-8') ?></div></td><?php foreach (['consultar', 'inserir', 'editar', 'excluir'] as $acao): ?><td class="text-center"><input class="form-check-input" type="checkbox" name="permissoes[<?= $mid ?>][<?= $acao ?>]" value="1" <?= !empty($perm[$acao]) ? 'checked' : '' ?>></td><?php endforeach; ?></tr><?php endforeach; ?>
          </tbody></table></div>
          <button class="btn btn-primary" type="submit"><i class="bi bi-save me-1"></i>Salvar permissões da função</button>
        </form>
      <?php endif; ?>
    </div>
  </div>
</section>
