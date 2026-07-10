<section class="container py-4">
  <div class="bg-white border rounded-3 p-4 shadow-sm">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
      <h4 class="mb-0"><i class="bi bi-eye me-2"></i><?= htmlspecialchars((string) ($course['nome'] ?? 'Curso'), ENT_QUOTES, 'UTF-8') ?></h4>
      <div class="d-flex gap-2">
        <a class="btn btn-outline-secondary btn-sm" href="/admin/cursos"><i class="bi bi-arrow-left me-1"></i>Voltar para lista</a>
        <a class="btn btn-outline-primary btn-sm" href="/admin/cursos/editar?id=<?= (int) ($course['id'] ?? 0) ?>"><i class="bi bi-pencil-square me-1"></i>Editar</a>
      </div>
    </div>

    <nav class="nav nav-pills nav-fill gap-2 mb-4 p-2 bg-light rounded small">
      <a class="nav-link" href="#dados-curso"><i class="bi bi-info-circle me-1"></i>Dados do curso</a>
      <a class="nav-link" href="#detalhe-curso"><i class="bi bi-journal-text me-1"></i>Detalhes</a>
      <a class="nav-link" href="#pagamento-curso"><i class="bi bi-currency-dollar me-1"></i>Plano de pagamento</a>
    </nav>

    <?php
      $img = (string) ($course['imagem_card'] ?? '');
      $cursoCalendarioRaw = (string) ($course['curso_calendario'] ?? '');
      $cursoCalendarioExibicao = '-';
      if ($cursoCalendarioRaw !== '') {
        $dtCalendario = \DateTime::createFromFormat('Y-m-d', $cursoCalendarioRaw);
        if ($dtCalendario instanceof \DateTime) {
          $cursoCalendarioExibicao = $dtCalendario->format('d/m/Y');
        } else {
          $cursoCalendarioExibicao = $cursoCalendarioRaw;
        }
      }
      $createdAtRaw = (string) ($course['created_at'] ?? '');
      $createdAtExibicao = '-';
      if ($createdAtRaw !== '') {
        $dtCreated = \DateTime::createFromFormat('Y-m-d H:i:s', $createdAtRaw) ?: \DateTime::createFromFormat('Y-m-d', $createdAtRaw);
        if ($dtCreated instanceof \DateTime) {
          $createdAtExibicao = $dtCreated->format('d/m/Y H:i:s');
        } else {
          $createdAtExibicao = $createdAtRaw;
        }
      }
      $ativoLabel = strtoupper(trim((string) ($course['ativo'] ?? 'S')));
      $ativoTexto = $ativoLabel === 'N' ? 'Não' : 'Sim';
      $exibirHomeLabel = strtoupper(trim((string) ($course['exibir_home'] ?? 'N')));
      $confirmadoLabel = strtoupper(trim((string) ($course['confirmado'] ?? 'N')));
      $confirmadoTexto = $confirmadoLabel === 'S' ? 'Sim' : 'Não';
    ?>
    <?php if ($img !== ''): ?>
      <div class="text-center mb-4">
        <img class="img-fluid border rounded shadow-sm" src="/<?= htmlspecialchars($img, ENT_QUOTES, 'UTF-8') ?>" alt="Card do curso" style="max-height: 300px;">
      </div>
    <?php else: ?>
      <div class="text-center mb-4 p-5 bg-light rounded border">
        <i class="bi bi-exclamation-triangle-fill text-warning" style="font-size: 4rem;"></i>
        <p class="text-muted mt-2 mb-0">Nenhuma imagem de card cadastrada.</p>
        <a class="btn btn-sm btn-outline-warning mt-2" href="/admin/cursos/upload?id=<?= (int) ($course['id'] ?? 0) ?>"><i class="bi bi-upload me-1"></i>Enviar imagem</a>
      </div>
    <?php endif; ?>

    <table class="table table-bordered" id="dados-curso">
      <tbody>
        <tr>
          <th class="bg-light" style="width: 180px;">ID</th>
          <td><?= (int) ($course['id'] ?? 0) ?></td>
        </tr>
        <tr>
          <th class="bg-light">Nome</th>
          <td><?= htmlspecialchars((string) ($course['nome'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
        </tr>
        <tr>
          <th class="bg-light">Slug</th>
          <td><code><?= htmlspecialchars((string) ($course['slug'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></code></td>
        </tr>
        <tr>
          <th class="bg-light">Data do curso</th>
          <td><?= htmlspecialchars((string) ($course['data_curso'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
        </tr>
        <tr>
          <th class="bg-light">Segmento</th>
          <td><?= htmlspecialchars((string) ($course['segmento_nome'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
        </tr>
        <tr>
          <th class="bg-light">Carga Horária</th>
          <td><?= (int) ($course['carga_horaria'] ?? 0) ?>h</td>
        </tr>
        <tr>
          <th class="bg-light">Calendário</th>
          <td><?= htmlspecialchars($cursoCalendarioExibicao, ENT_QUOTES, 'UTF-8') ?></td>
        </tr>
        <tr>
          <th class="bg-light">Horario</th>
          <td><?= htmlspecialchars((string) ($course['horario'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
        </tr>
        <tr>
          <th class="bg-light">Local</th>
          <td><?= htmlspecialchars((string) ($course['local_curso'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
        </tr>
        <tr>
          <th class="bg-light">Link de ingresso</th>
          <td>
            <?php $link = (string) ($course['link_ingresso'] ?? ''); ?>
            <?php if ($link !== ''): ?>
              <a href="<?= htmlspecialchars($link, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener noreferrer">
                <i class="bi bi-box-arrow-up-right me-1"></i><?= htmlspecialchars($link, ENT_QUOTES, 'UTF-8') ?>
              </a>
            <?php else: ?>
              <span class="text-muted">-</span>
            <?php endif; ?>
          </td>
        </tr>
        <tr>
          <th class="bg-light">Ativo</th>
          <td><?= htmlspecialchars($ativoTexto, ENT_QUOTES, 'UTF-8') ?></td>
        </tr>
        <tr>
          <th class="bg-light">Exibir na home</th>
          <td>
            <?php if ($exibirHomeLabel === 'S'): ?>
              <span class="badge bg-success">Sim</span>
            <?php else: ?>
              <span class="badge bg-danger">Não</span>
            <?php endif; ?>
          </td>
        </tr>
        <tr>
          <th class="bg-light">Confirmado</th>
          <td>
            <?php if ($confirmadoLabel === 'S'): ?>
              <span class="badge bg-success">Confirmado</span>
            <?php else: ?>
              <span class="badge bg-secondary">Não confirmado</span>
            <?php endif; ?>
          </td>
        </tr>
        <tr>
          <th class="bg-light">Imagem do card</th>
          <td>
            <?php if ($img !== ''): ?>
              <code>/<?= htmlspecialchars($img, ENT_QUOTES, 'UTF-8') ?></code>
            <?php else: ?>
              <span class="text-muted">Nenhuma</span>
            <?php endif; ?>
          </td>
        </tr>
        <tr>
          <th class="bg-light">Criado em</th>
          <td><?= htmlspecialchars($createdAtExibicao, ENT_QUOTES, 'UTF-8') ?></td>
        </tr>
      </tbody>
    </table>

    <hr class="my-4">

    <div class="d-flex align-items-center justify-content-between mb-3" id="detalhe-curso">
      <h5 class="mb-0"><i class="bi bi-journal-text me-2"></i>Detalhe do Curso</h5>
      <a class="btn btn-sm btn-outline-primary" href="/admin/cursos/detalhes?id=<?= (int) ($course['id'] ?? 0) ?>">
        <i class="bi bi-pencil-square me-1"></i><?= $detalhe ? 'Editar' : 'Adicionar' ?>
      </a>
    </div>
    <?php if ($detalhe && trim((string) ($detalhe['detalhe'] ?? '')) !== ''): ?>
      <div class="border rounded p-3 bg-light">
        <?= $detalhe['detalhe'] ?>
      </div>
    <?php else: ?>
      <p class="text-muted mb-0"><i class="bi bi-info-circle me-1"></i>Nenhum detalhe cadastrado para este curso.</p>
    <?php endif; ?>

    <hr class="my-4">

    <div class="d-flex align-items-center justify-content-between mb-3" id="pagamento-curso">
      <h5 class="mb-0"><i class="bi bi-currency-dollar me-2"></i>Formas de pagamento</h5>
      <a class="btn btn-sm btn-outline-primary" href="/admin/cursos/definir-valor?id=<?= (int) ($course['id'] ?? 0) ?>"><i class="bi bi-pencil-square me-1"></i>Editar</a>
    </div>
    <?php $pagamentos = $pagamentos ?? []; ?>
    <?php if (!empty($pagamentos)): ?>
      <div class="table-responsive">
        <table class="table table-sm table-bordered align-middle">
          <thead class="table-light">
            <tr>
              <th>Descrição</th>
              <th>Tipo</th>
              <th>Parcelas</th>
              <th>Valor</th>
              <th>Ativo</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($pagamentos as $p): ?>
              <tr>
                <td><?= htmlspecialchars((string) ($p['descricao'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                <td><span class="badge bg-secondary"><?= htmlspecialchars((string) ($p['tipo'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></span></td>
                <td><?= (int) ($p['parcelas'] ?? 1) ?>x</td>
                <td>R$ <?= number_format((float) ($p['valor'] ?? 0), 2, ',', '.') ?></td>
                <td>
                  <span class="badge <?= ((string) ($p['ativo'] ?? 'S') === 'S') ? 'bg-success' : 'bg-secondary' ?>">
                    <?= ((string) ($p['ativo'] ?? 'S') === 'S') ? 'Sim' : 'Não' ?>
                  </span>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php else: ?>
      <div class="text-muted">
        <i class="bi bi-info-circle me-1"></i>Sem plano de pagamento lançado.
        <a class="btn btn-sm btn-outline-success ms-2" href="/admin/cursos/definir-valor?id=<?= (int) ($course['id'] ?? 0) ?>"><i class="bi bi-currency-dollar me-1"></i>Definir plano de pagamento</a>
      </div>
    <?php endif; ?>
  </div>
</section>
