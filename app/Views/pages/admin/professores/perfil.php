<section class="container py-4">
  <div class="bg-white border rounded-3 p-4 shadow-sm">
    <h4 class="mb-1"><i class="bi bi-person-circle me-2"></i>Meu Perfil</h4>
    <p class="text-muted mb-4">Dados pessoais, endereço e redes sociais vinculadas.</p>

    <div class="accordion" id="perfilAccordion">
      <div class="accordion-item">
        <h2 class="accordion-header">
          <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#dadosPessoais" aria-expanded="true">
            <i class="bi bi-person-vcard me-2"></i>Dados Pessoais
          </button>
        </h2>
        <div id="dadosPessoais" class="accordion-collapse collapse show" data-bs-parent="#perfilAccordion">
          <div class="accordion-body">
            <dl class="row mb-3">
              <dt class="col-sm-3 text-muted">Nome</dt>
              <dd class="col-sm-9"><?= htmlspecialchars((string) ($usuario['nome'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></dd>
              <dt class="col-sm-3 text-muted">Email</dt>
              <dd class="col-sm-9"><?= htmlspecialchars((string) ($usuario['email'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></dd>
              <dt class="col-sm-3 text-muted">Telefone</dt>
              <dd class="col-sm-9"><?= htmlspecialchars((string) ($usuario['telefone'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></dd>
            </dl>
            <a class="btn btn-primary btn-sm" href="/admin/professores/editar?id=<?= (int) ($usuario['id'] ?? 0) ?>"><i class="bi bi-pencil me-1"></i>Editar dados pessoais</a>
          </div>
        </div>
      </div>

      <div class="accordion-item">
        <h2 class="accordion-header">
          <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#endereco" aria-expanded="false">
            <i class="bi bi-geo-alt me-2"></i>Endereço
          </button>
        </h2>
        <div id="endereco" class="accordion-collapse collapse" data-bs-parent="#perfilAccordion">
          <div class="accordion-body">
            <?php if ($endereco): ?>
              <dl class="row mb-3">
                <dt class="col-sm-3 text-muted">Logradouro</dt>
                <dd class="col-sm-9"><?= htmlspecialchars((string) ($endereco['logradouro'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></dd>
                <dt class="col-sm-3 text-muted">Número</dt>
                <dd class="col-sm-9"><?= htmlspecialchars((string) ($endereco['numero'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></dd>
                <dt class="col-sm-3 text-muted">Cidade</dt>
                <dd class="col-sm-9"><?= htmlspecialchars((string) ($endereco['cidade'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></dd>
                <dt class="col-sm-3 text-muted">CEP</dt>
                <dd class="col-sm-9"><?= htmlspecialchars((string) ($endereco['cep'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></dd>
                <dt class="col-sm-3 text-muted">UF</dt>
                <dd class="col-sm-9"><?= htmlspecialchars((string) ($endereco['uf'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></dd>
              </dl>
            <?php else: ?>
              <p class="text-muted mb-3">Nenhum endereço cadastrado.</p>
            <?php endif; ?>
            <a class="btn btn-primary btn-sm" href="/admin/professores/endereco?id=<?= (int) ($usuario['id'] ?? 0) ?>"><i class="bi bi-geo-alt me-1"></i><?= $endereco ? 'Editar endereço' : 'Cadastrar endereço' ?></a>
          </div>
        </div>
      </div>

      <div class="accordion-item">
        <h2 class="accordion-header">
          <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#redesSociais" aria-expanded="false">
            <i class="bi bi-share me-2"></i>Redes Sociais
          </button>
        </h2>
        <div id="redesSociais" class="accordion-collapse collapse" data-bs-parent="#perfilAccordion">
          <div class="accordion-body">
            <?php if (!empty($social)): ?>
              <?php foreach ($social as $rede): ?>
                <div class="mb-2">
                  <strong><?= htmlspecialchars((string) ($rede['rede'] ?? $rede['tipo'] ?? '-'), ENT_QUOTES, 'UTF-8') ?>:</strong>
                  <a href="<?= htmlspecialchars((string) ($rede['link_perfil'] ?? '#'), ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener">
                    <?= htmlspecialchars((string) ($rede['link_perfil'] ?? '-'), ENT_QUOTES, 'UTF-8') ?>
                  </a>
                </div>
              <?php endforeach; ?>
            <?php else: ?>
              <p class="text-muted mb-0">Nenhuma rede social vinculada.</p>
            <?php endif; ?>
            <div class="mt-3">
              <a class="btn btn-primary btn-sm" href="/admin/professores/social"><i class="bi bi-pencil me-1"></i>Gerenciar redes sociais</a>
            </div>
          </div>
        </div>
      </div>

      <div class="accordion-item">
        <h2 class="accordion-header">
          <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#curriculo" aria-expanded="false">
            <i class="bi bi-file-earmark-text me-2"></i>Currículo
          </button>
        </h2>
        <div id="curriculo" class="accordion-collapse collapse" data-bs-parent="#perfilAccordion">
          <div class="accordion-body">
            <?php if ($curriculo): ?>
              <?php $resumoCurriculo = (string) ($curriculo['resumo'] ?? ''); ?>
              <?php if ($resumoCurriculo !== ''): ?>
                <div class="mb-3"><strong>Resumo:</strong>
                  <p class="mb-1"><?= nl2br(htmlspecialchars($resumoCurriculo, ENT_QUOTES, 'UTF-8')) ?></p>
                </div>
              <?php endif; ?>
              <div class="mb-3 p-3 border rounded bg-light"><?= ($curriculo['conteudo'] ?? '') ?></div>
              <a class="btn btn-primary btn-sm" href="/admin/professores/curriculo"><i class="bi bi-pencil me-1"></i>Editar currículo</a>
            <?php else: ?>
              <p class="text-muted mb-3"><i class="bi bi-exclamation-circle text-warning me-1"></i>Sem currículo vinculado.</p>
              <a class="btn btn-primary btn-sm" href="/admin/professores/curriculo"><i class="bi bi-plus-circle me-1"></i>Adicionar currículo</a>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
