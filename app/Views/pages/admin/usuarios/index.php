<section class="container py-4">
  <div class="bg-white border rounded-3 p-4 shadow-sm">
    <?php
      $currentRole = (string) ($authUser['role'] ?? '');
      $currentUserId = (int) ($authUser['id'] ?? 0);
      $isAdmin = $currentRole === 'admin';
      $canCreateUser = $isAdmin;
      $funcoes = $funcoes ?? [];
    ?>
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
      <h4 class="mb-0"><i class="bi bi-people me-2"></i>Usuários IESB</h4>
      <div class="d-flex flex-wrap align-items-center gap-2">
        <?php if ($canCreateUser): ?>
          <a class="btn btn-primary btn-sm" href="/admin/usuarios/novo"><i class="bi bi-plus-circle me-1"></i>Novo usuário</a>
        <?php endif; ?>
      </div>
    </div>

    <div class="border rounded-3 bg-light p-3 mb-3">
      <div class="row g-2 align-items-end">
        <div class="col-12 col-md-5">
          <label class="form-label small text-muted mb-1" for="filtro-busca"><i class="bi bi-search me-1"></i>Buscar</label>
          <input type="search" class="form-control form-control-sm" id="filtro-busca" placeholder="Nome ou email">
        </div>
        <div class="col-12 col-md-4">
          <label class="form-label small text-muted mb-1" for="filtro-funcao"><i class="bi bi-person-badge me-1"></i>Função</label>
          <select class="form-select form-select-sm" id="filtro-funcao">
            <option value="">Todas as funções</option>
            <?php foreach ($funcoes as $funcao): ?>
              <option value="<?= (int) ($funcao['id'] ?? 0) ?>"><?= htmlspecialchars((string) ($funcao['nome'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-6 col-md-2">
          <label class="form-label small text-muted mb-1" for="filtro-ativo"><i class="bi bi-toggle-on me-1"></i>Ativo</label>
          <select class="form-select form-select-sm" id="filtro-ativo">
            <option value="">Todos</option>
            <option value="1">Sim</option>
            <option value="0">Não</option>
          </select>
        </div>
        <div class="col-12 col-md-1 d-grid">
          <button type="button" class="btn btn-outline-secondary btn-sm" id="filtro-limpar" title="Limpar filtros"><i class="bi bi-x-lg"></i></button>
        </div>
      </div>
    </div>

    <div class="table-responsive">
      <table class="table table-striped table-sm align-middle" id="tabela-usuarios">
        <thead>
          <tr>
            <th><i class="bi bi-hash"></i></th>
            <th><i class="bi bi-person me-1"></i>Nome</th>
            <th><i class="bi bi-envelope me-1"></i>Email</th>
            <th><i class="bi bi-person-badge me-1"></i>Funções</th>
            <th><i class="bi bi-toggle-on me-1"></i>Ativo</th>
            <th><i class="bi bi-gear me-1"></i>Ações</th>
          </tr>
        </thead>
        <tbody>
          <?php
          $filtered = $isAdmin
              ? ($usuarios ?? [])
              : array_filter($usuarios ?? [], fn($u) => (int) ($u['id'] ?? 0) === $currentUserId);

          usort($filtered, fn($a, $b) =>
              strcasecmp((string) ($a['nome'] ?? ''), (string) ($b['nome'] ?? ''))
          );
          ?>

          <tr id="usuarios-vazio" <?= empty($filtered) ? '' : 'style="display:none;"' ?>>
            <td colspan="6" class="text-muted"><i class="bi bi-inbox me-1"></i>Nenhum usuário encontrado.</td>
          </tr>

          <?php foreach ($filtered as $user): ?>
            <?php
              $userId = (int) ($user['id'] ?? 0);
              $ativo = (int) ($user['ativo'] ?? 1);
              $funcoesUsuario = $user['funcoes'] ?? [];
              $idsFuncoes = array_map(static fn($f) => (int) ($f['id'] ?? 0), $funcoesUsuario);
            ?>
            <tr class="usuario-row"
                data-nome="<?= htmlspecialchars((string) ($user['nome'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                data-email="<?= htmlspecialchars((string) ($user['email'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                data-ativo="<?= $ativo === 1 ? '1' : '0' ?>"
                data-funcoes="<?= htmlspecialchars(implode(',', $idsFuncoes), ENT_QUOTES, 'UTF-8') ?>">
              <td><?= $userId ?></td>
              <td class="d-flex align-items-center gap-2">
                <?php $fotoPath = $fotos[$userId] ?? null; ?>
                <?php if ($fotoPath): ?>
                  <img src="/<?= htmlspecialchars($fotoPath, ENT_QUOTES, 'UTF-8') ?>" alt="" class="rounded-circle" style="width:32px;height:32px;object-fit:cover;">
                <?php endif; ?>
                <?= htmlspecialchars((string) ($user['nome'] ?? '-'), ENT_QUOTES, 'UTF-8') ?>
              </td>
              <td><?= htmlspecialchars((string) ($user['email'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
              <td>
                <?php if (!empty($funcoesUsuario)): ?>
                  <div class="d-flex flex-wrap gap-1">
                    <?php foreach ($funcoesUsuario as $funcaoUsuario): ?>
                      <span class="badge bg-light text-dark border"><?= htmlspecialchars((string) ($funcaoUsuario['nome'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></span>
                    <?php endforeach; ?>
                  </div>
                <?php else: ?>
                  <span class="text-muted small">—</span>
                <?php endif; ?>
              </td>
              <td>
                <?php if ($ativo === 1): ?>
                  <span class="badge bg-success">Sim</span>
                <?php else: ?>
                  <span class="badge bg-secondary">Não</span>
                <?php endif; ?>
              </td>
              <td>
                <div class="d-flex gap-1">
                  <?php if ($isAdmin || $userId === $currentUserId): ?>
                    <a class="btn btn-outline-secondary btn-sm" href="/admin/usuarios/editar?id=<?= $userId ?>">
                      <i class="bi bi-pencil-square me-1"></i>Editar
                    </a>
                  <?php endif; ?>
                  <a class="btn btn-outline-primary btn-sm" href="/admin/usuarios/fotos?id=<?= $userId ?>" title="Fotos do usuário">
                    <i class="bi bi-camera"></i>
                  </a>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
        <tfoot>
          <tr class="table-secondary fw-semibold">
            <td colspan="6" class="text-end" id="total-usuarios">Total: <?= count($filtered) ?> usuário(s)</td>
          </tr>
        </tfoot>
      </table>
    </div>
  </div>
</section>

<script>
(function () {
  const busca = document.getElementById('filtro-busca');
  const funcao = document.getElementById('filtro-funcao');
  const ativo = document.getElementById('filtro-ativo');
  const vazio = document.getElementById('usuarios-vazio');
  const total = document.getElementById('total-usuarios');
  const limpar = document.getElementById('filtro-limpar');

  if (!busca || !funcao || !ativo) return;

  function aplicar() {
    const termo = busca.value.trim().toLowerCase();
    const funcaoSelecionada = funcao.value;
    const ativoSelecionado = ativo.value;
    let visiveis = 0;

    document.querySelectorAll('tr.usuario-row').forEach(function (linha) {
      const nome = (linha.dataset.nome || '').toLowerCase();
      const email = (linha.dataset.email || '').toLowerCase();
      const funcoes = (linha.dataset.funcoes || '').split(',').filter(Boolean);

      const okBusca = termo === '' || nome.includes(termo) || email.includes(termo);
      const okFuncao = funcaoSelecionada === '' || funcoes.includes(funcaoSelecionada);
      const okAtivo = ativoSelecionado === '' || linha.dataset.ativo === ativoSelecionado;
      const exibir = okBusca && okFuncao && okAtivo;

      linha.style.display = exibir ? '' : 'none';
      if (exibir) visiveis++;
    });

    if (vazio) vazio.style.display = visiveis === 0 ? '' : 'none';
    if (total) total.textContent = 'Total: ' + visiveis + ' usuário(s)';
  }

  [busca, funcao, ativo].forEach(function (elemento) {
    elemento.addEventListener('input', aplicar);
    elemento.addEventListener('change', aplicar);
  });

  if (limpar) {
    limpar.addEventListener('click', function () {
      busca.value = '';
      funcao.value = '';
      ativo.value = '';
      aplicar();
    });
  }

  aplicar();
})();
</script>
