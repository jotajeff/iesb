<?php $id = (int) ($instituicao['id'] ?? 0); ?>
<section class="container py-4">
  <div class="bg-white border rounded-3 p-4 shadow-sm">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
      <h4 class="mb-0">
        <i class="bi bi-pencil-square me-2"></i><?= $id > 0 ? 'Editar Instituição #' . $id : 'Nova Instituição' ?>
      </h4>
      <a class="btn btn-outline-secondary btn-sm" href="/admin/config/cliente"><i class="bi bi-arrow-left me-1"></i>Voltar para lista</a>
    </div>

    <form method="post" action="/admin/config/cliente/atualizar" class="row g-3">
      <input type="hidden" name="id" value="<?= $id ?>">

      <div class="col-md-6">
        <label class="form-label">Razão Social</label>
        <input class="form-control" type="text" name="razao_social" required value="<?= htmlspecialchars((string) ($instituicao['razao_social'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
      </div>

      <div class="col-md-6">
        <label class="form-label">Nome Fantasia</label>
        <input class="form-control" type="text" name="nome_fantasia" value="<?= htmlspecialchars((string) ($instituicao['nome_fantasia'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
      </div>

      <div class="col-md-6">
        <label class="form-label">Domínio</label>
        <input class="form-control" type="text" name="dominio" value="<?= htmlspecialchars((string) ($instituicao['dominio'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
      </div>

      <div class="col-md-4">
        <label class="form-label">Documento (CNPJ/CPF)</label>
        <input class="form-control" type="text" name="documento" required value="<?= htmlspecialchars((string) ($instituicao['documento'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
      </div>

      <div class="col-md-4">
        <label class="form-label">Inscrição Estadual</label>
        <input class="form-control" type="text" name="inscricao_estadual" value="<?= htmlspecialchars((string) ($instituicao['inscricao_estadual'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
      </div>

      <div class="col-md-4">
        <label class="form-label">Telefone</label>
        <input class="form-control" type="text" name="telefone" value="<?= htmlspecialchars((string) ($instituicao['telefone'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
      </div>

      <div class="col-md-6">
        <label class="form-label">Email</label>
        <input class="form-control" type="email" name="email" required value="<?= htmlspecialchars((string) ($instituicao['email'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
      </div>

      <div class="col-md-6">
        <label class="form-label">Responsável</label>
        <input class="form-control" type="text" name="responsavel_nome" value="<?= htmlspecialchars((string) ($instituicao['responsavel_nome'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
      </div>

      <div class="col-md-4">
        <label class="form-label">Tipo de Cliente</label>
        <?php $tipoCliente = (string) ($instituicao['tipo_cliente'] ?? 'PJ'); ?>
        <select class="form-select" name="tipo_cliente">
          <option value="PJ" <?= $tipoCliente === 'PJ' ? 'selected' : '' ?>>Pessoa Jurídica</option>
          <option value="PF" <?= $tipoCliente === 'PF' ? 'selected' : '' ?>>Pessoa Física</option>
        </select>
      </div>

      <div class="col-md-4">
        <label class="form-label">Status</label>
        <?php $status = (string) ($instituicao['status'] ?? 'Ativo'); ?>
        <select class="form-select" name="status">
          <option value="Ativo" <?= $status === 'Ativo' ? 'selected' : '' ?>>Ativo</option>
          <option value="Inativo" <?= $status === 'Inativo' ? 'selected' : '' ?>>Inativo</option>
        </select>
      </div>

      <div class="col-md-4">
        <label class="form-label">Senha (SMTP)</label>
        <input class="form-control" type="password" name="senha" value="<?= htmlspecialchars((string) ($instituicao['senha'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" <?= $id > 0 ? 'placeholder="Deixe em branco para manter a atual"' : 'required' ?>>
      </div>

      <div class="col-12">
        <button class="btn btn-success" type="submit">
          <i class="bi bi-check-lg me-1"></i><?= $id > 0 ? 'Atualizar Instituição' : 'Criar Instituição' ?>
        </button>
      </div>
    </form>
  </div>
</section>
