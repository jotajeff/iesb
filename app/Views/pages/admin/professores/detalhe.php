<section class="container py-4">
  <div class="bg-white border rounded-3 p-4 shadow-sm">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
      <h4 class="mb-0"><i class="bi bi-person-circle me-2"></i>Detalhes do Professor</h4>
      <a class="btn btn-outline-secondary btn-sm" href="/admin/professores"><i class="bi bi-arrow-left me-1"></i>Voltar</a>
    </div>

    <div class="row">
      <div class="col-md-3 order-md-last mb-3 mb-md-0 text-center">
        <?php $foto = $imagens[0]['path'] ?? null; ?>
        <?php if ($foto): ?>
          <img src="/<?= htmlspecialchars($foto, ENT_QUOTES, 'UTF-8') ?>" class="img-fluid rounded-circle border" style="width:150px;height:150px;object-fit:cover;">
        <?php else: ?>
          <div class="d-inline-flex align-items-center justify-content-center bg-light rounded-circle border" style="width:150px;height:150px;">
            <i class="bi bi-person fs-1 text-muted"></i>
          </div>
        <?php endif; ?>
      </div>

      <div class="col-md-9">
        <dl class="row mb-0">
          <dt class="col-sm-3 text-muted">ID</dt>
          <dd class="col-sm-3"><?= (int) ($professor['id'] ?? 0) ?></dd>
          <dt class="col-sm-3 text-muted">Ativo</dt>
          <dd class="col-sm-3">
            <?php if ((int) ($professor['ativo'] ?? 1) === 1): ?>
              <span class="badge bg-success">Sim</span>
            <?php else: ?>
              <span class="badge bg-secondary">Não</span>
            <?php endif; ?>
          </dd>

          <dt class="col-sm-3 text-muted">Nome</dt>
          <dd class="col-sm-9"><?= htmlspecialchars((string) ($professor['nome'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></dd>

          <dt class="col-sm-3 text-muted">Email</dt>
          <dd class="col-sm-9"><?= htmlspecialchars((string) ($professor['email'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></dd>

          <dt class="col-sm-3 text-muted">Telefone</dt>
          <dd class="col-sm-9"><?= htmlspecialchars((string) ($professor['telefone'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></dd>
        </dl>
      </div>
    </div>

    <hr class="mt-3">
    <h5><i class="bi bi-geo-alt me-1"></i>Endereço</h5>
    <?php if ($endereco): ?>
      <dl class="row">
        <dt class="col-sm-2 text-muted">Logradouro</dt>
        <dd class="col-sm-10"><?= htmlspecialchars((string) ($endereco['logradouro'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></dd>
        <dt class="col-sm-2 text-muted">Número</dt>
        <dd class="col-sm-10"><?= htmlspecialchars((string) ($endereco['numero'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></dd>
        <dt class="col-sm-2 text-muted">Cidade</dt>
        <dd class="col-sm-10"><?= htmlspecialchars((string) ($endereco['cidade'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></dd>
        <dt class="col-sm-2 text-muted">CEP</dt>
        <dd class="col-sm-10"><?= htmlspecialchars((string) ($endereco['cep'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></dd>
        <dt class="col-sm-2 text-muted">UF</dt>
        <dd class="col-sm-10"><?= htmlspecialchars((string) ($endereco['uf'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></dd>
      </dl>
    <?php else: ?>
      <p class="text-muted">Nenhum endereço cadastrado.</p>
    <?php endif; ?>

    <hr>
    <h5><i class="bi bi-share me-1"></i>Redes Sociais</h5>
    <?php if (!empty($social)): ?>
      <ul>
        <?php foreach ($social as $rede): ?>
          <li><strong><?= htmlspecialchars((string) ($rede['rede'] ?? '-'), ENT_QUOTES, 'UTF-8') ?>:</strong> <a href="<?= htmlspecialchars((string) ($rede['link_perfil'] ?? '#'), ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener"><?= htmlspecialchars((string) ($rede['link_perfil'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></a></li>
        <?php endforeach; ?>
      </ul>
    <?php else: ?>
      <p class="text-muted">Nenhuma rede social vinculada.</p>
    <?php endif; ?>

    <hr>
    <h5><i class="bi bi-file-earmark-text me-1"></i>Resumo</h5>
    <?php if ($curriculo && trim((string) ($curriculo['resumo'] ?? '')) !== ''): ?>
      <p><?= nl2br(htmlspecialchars((string) ($curriculo['resumo'] ?? ''), ENT_QUOTES, 'UTF-8')) ?></p>
    <?php else: ?>
      <p class="text-muted">Nenhum resumo cadastrado.</p>
    <?php endif; ?>

    <hr>
    <h5><i class="bi bi-file-earmark-text me-1"></i>Currículo</h5>
    <?php if ($curriculo && trim((string) ($curriculo['conteudo'] ?? '')) !== ''): ?>
      <div class="p-3 border rounded bg-light"><?= ($curriculo['conteudo'] ?? '') ?></div>
    <?php else: ?>
      <p class="text-muted">Nenhum currículo cadastrado.</p>
    <?php endif; ?>

    <hr>
    <h5><i class="bi bi-camera me-1"></i>Fotos</h5>
    <?php if (!empty($imagens)): ?>
      <div class="row g-2">
        <?php foreach ($imagens as $img): ?>
          <div class="col-md-2">
            <img src="/<?= htmlspecialchars((string) ($img['path'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" class="img-fluid rounded border" alt="<?= htmlspecialchars((string) ($img['legenda'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" style="height:100px;object-fit:cover;">
          </div>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <p class="text-muted">Nenhuma foto cadastrada.</p>
    <?php endif; ?>

    <hr>
    <h5><i class="bi bi-link-45deg me-1"></i>Turmas Vinculadas</h5>
    <?php if (!empty($turmas)): ?>
      <div class="table-responsive">
        <table class="table table-sm table-striped align-middle">
          <thead>
            <tr>
              <th>#</th>
              <th>Turma</th>
              <th>Curso</th>
              <th>Início</th>
              <th>Fim</th>
              <th>Ativa</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($turmas as $t): ?>
              <tr>
                <td><?= (int) ($t['id'] ?? 0) ?></td>
                <td><?= htmlspecialchars((string) ($t['nome'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars((string) ($t['curso_nome'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars((string) ($t['data_inicio'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars((string) ($t['data_fim'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= (int) ($t['ativo'] ?? 0) ? '<span class="badge bg-success">Sim</span>' : '<span class="badge bg-secondary">Não</span>' ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php else: ?>
      <p class="text-muted">Nenhuma turma vinculada.</p>
    <?php endif; ?>
  </div>
</section>
