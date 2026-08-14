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
      <a class="nav-link" href="#publico-alvo-curso"><i class="bi bi-people me-1"></i>Público-alvo</a>
      <a class="nav-link" href="#disciplinas-curso"><i class="bi bi-book me-1"></i>Disciplinas</a>
      <a class="nav-link" href="#corpo-docente-curso"><i class="bi bi-people me-1"></i>Corpo Docente</a>
      <a class="nav-link" href="#galeria-curso"><i class="bi bi-images me-1"></i>Galeria</a>
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
      $ativoLabel = (int) ($course['ativo'] ?? 1);
      $ativoTexto = $ativoLabel == 0 ? 'Não' : 'Sim';
      $exibirHomeLabel = (int) ($course['exibir_home'] ?? 0);
      $confirmadoLabel = (int) ($course['confirmado'] ?? 0);
      $confirmadoTexto = $confirmadoLabel == 1 ? 'Sim' : 'Não';
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
            <?php if ($exibirHomeLabel == 1): ?>
              <span class="badge bg-success">Sim</span>
            <?php else: ?>
              <span class="badge bg-danger">Não</span>
            <?php endif; ?>
          </td>
        </tr>
        <tr>
          <th class="bg-light">Confirmado</th>
          <td>
            <?php if ($confirmadoLabel == 1): ?>
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

    <div class="d-flex align-items-center justify-content-between mb-3" id="publico-alvo-curso">
      <h5 class="mb-0"><i class="bi bi-people me-2"></i>Público-alvo</h5>
      <a class="btn btn-sm btn-outline-primary" href="/admin/cursos/editar?id=<?= (int) ($course['id'] ?? 0) ?>"><i class="bi bi-pencil-square me-1"></i>Editar</a>
    </div>
    <?php $publicoAlvo = (string) ($course['publico_alvo'] ?? ''); ?>
    <?php if (trim($publicoAlvo) !== ''): ?>
      <div class="border rounded p-3 bg-light">
        <?= nl2br(htmlspecialchars($publicoAlvo, ENT_QUOTES, 'UTF-8')) ?>
      </div>
    <?php else: ?>
      <p class="text-muted mb-0"><i class="bi bi-info-circle me-1"></i>Nenhum público-alvo definido.</p>
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
              <th>Desconto</th>
              <th>Ativo</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($pagamentos as $p): ?>
              <?php
                $descontoPerc = (float) ($p['desconto_percentual'] ?? 0);
                $descontoLimite = (string) ($p['desconto_data_limite'] ?? '');
                $temDesconto = $descontoPerc > 0 && $descontoLimite !== '';
              ?>
              <tr>
                <td><?= htmlspecialchars((string) ($p['descricao'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                <td><span class="badge bg-secondary"><?= htmlspecialchars((string) ($p['tipo'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></span></td>
                <td><?= (int) ($p['parcelas'] ?? 1) ?>x</td>
                <td>R$ <?= number_format((float) ($p['valor'] ?? 0), 2, ',', '.') ?></td>
                <td>
                  <?php if ($temDesconto): ?>
                    <span class="badge bg-danger"><?= number_format($descontoPerc, 2, ',', '.') ?>%</span>
                    <span class="text-muted small d-block">até <?= date('d/m/Y', strtotime($descontoLimite)) ?></span>
                  <?php else: ?>
                    <span class="text-muted">—</span>
                  <?php endif; ?>
                </td>
                <td>
                  <span class="badge <?= (int) ($p['ativo'] ?? 1) == 1 ? 'bg-success' : 'bg-secondary' ?>">
                    <?= (int) ($p['ativo'] ?? 1) == 1 ? 'Sim' : 'Não' ?>
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

    <hr class="my-4">

    <div class="d-flex align-items-center justify-content-between mb-3" id="disciplinas-curso">
      <h5 class="mb-0"><i class="bi bi-book me-2"></i>Disciplinas do Curso</h5>
      <a class="btn btn-sm btn-outline-primary" href="/admin/cursos/disciplinas?id_curso=<?= (int) ($course['id'] ?? 0) ?>"><i class="bi bi-plus-circle me-1"></i>Adicionar disciplina</a>
    </div>
    <?php $disciplinas = $disciplinas ?? []; ?>
    <?php if (!empty($disciplinas)): ?>
      <div class="table-responsive">
        <table class="table table-sm table-bordered align-middle">
          <thead class="table-light">
            <tr>
              <th>ID</th>
              <th>Nome</th>
              <th>Carga horária</th>
              <th>Ordem</th>
              <th>Ativo</th>
              <th>Ações</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($disciplinas as $d): ?>
              <tr>
                <td><?= (int) ($d['id'] ?? 0) ?></td>
                <td><?= htmlspecialchars((string) ($d['nome'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= (int) ($d['carga_horaria'] ?? 0) ?>h</td>
                <td><?= (int) ($d['ordem'] ?? 0) ?></td>
                <td>
                  <span class="badge <?= (int) ($d['ativo'] ?? 1) == 1 ? 'bg-success' : 'bg-secondary' ?>">
                    <?= (int) ($d['ativo'] ?? 1) == 1 ? 'Sim' : 'Não' ?>
                  </span>
                </td>
                <td>
                  <a class="btn btn-outline-secondary btn-sm" href="/admin/cursos/disciplinas?id_curso=<?= (int) ($course['id'] ?? 0) ?>&id=<?= (int) ($d['id'] ?? 0) ?>" title="Editar disciplina">
                    <i class="bi bi-pencil-square"></i>
                  </a>
                  <?php $temEmenta = (int) ($d['tem_ementa'] ?? 0) > 0; ?>
                  <a class="btn btn-sm <?= $temEmenta ? 'btn-outline-success' : 'btn-outline-warning' ?>" href="/admin/cursos/ementa?id_disciplina=<?= (int) ($d['id'] ?? 0) ?>" title="Ementa">
                    <i class="bi bi-file-text"></i>
                  </a>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php else: ?>
      <div class="text-muted">
        <i class="bi bi-info-circle me-1"></i>Nenhuma disciplina cadastrada.
        <a class="btn btn-sm btn-outline-success ms-2" href="/admin/cursos/disciplinas?id_curso=<?= (int) ($course['id'] ?? 0) ?>"><i class="bi bi-plus-circle me-1"></i>Adicionar disciplinas</a>
      </div>
    <?php endif; ?>

    <hr class="my-4">

    <div class="d-flex align-items-center justify-content-between mb-3" id="corpo-docente-curso">
      <h5 class="mb-0"><i class="bi bi-people me-2"></i>Corpo Docente</h5>
      <a class="btn btn-sm btn-outline-primary" href="/admin/cursos/corpo-docente?id_curso=<?= (int) ($course['id'] ?? 0) ?>"><i class="bi bi-plus-circle me-1"></i>Vincular docente</a>
    </div>
    <?php $corpoDocente = $corpoDocente ?? []; ?>
    <?php if (!empty($corpoDocente)): ?>
      <div class="table-responsive">
        <table class="table table-sm table-bordered align-middle">
          <thead class="table-light">
            <tr>
              <th>Professor</th>
              <th>Função</th>
              <th>Ações</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($corpoDocente as $doc): ?>
              <tr>
                <td><?= htmlspecialchars((string) ($doc['usuario_nome'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars((string) ($doc['funcao_nome'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                <td>
                  <button type="button" class="btn btn-outline-danger btn-sm" onclick="removerDocente(<?= (int) ($doc['id'] ?? 0) ?>, <?= (int) ($course['id'] ?? 0) ?>)" title="Remover vínculo">
                    <i class="bi bi-person-x"></i>
                  </button>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php else: ?>
      <div class="text-muted">
        <i class="bi bi-info-circle me-1"></i>Nenhum docente vinculado.
        <a class="btn btn-sm btn-outline-success ms-2" href="/admin/cursos/corpo-docente?id_curso=<?= (int) ($course['id'] ?? 0) ?>"><i class="bi bi-plus-circle me-1"></i>Vincular docente</a>
      </div>
    <?php endif; ?>

    <hr class="my-4">

    <div class="d-flex align-items-center justify-content-between mb-3" id="galeria-curso">
      <h5 class="mb-0"><i class="bi bi-images me-2"></i>Galeria de Imagens</h5>
      <a class="btn btn-sm btn-outline-primary" href="/admin/cursos/galeria?id=<?= (int) ($course['id'] ?? 0) ?>"><i class="bi bi-plus-circle me-1"></i>Gerenciar imagens</a>
    </div>
    <?php $galeriaImagens = $galeriaImagens ?? []; ?>
    <?php if (!empty($galeriaImagens)): ?>
      <div class="row g-2">
        <?php foreach ($galeriaImagens as $img): ?>
          <div class="col-md-2">
            <img src="/<?= htmlspecialchars((string) ($img['path'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" class="img-fluid rounded border" alt="<?= htmlspecialchars((string) ($img['legenda'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" style="height:100px;object-fit:cover;">
          </div>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <div class="text-muted">
        <i class="bi bi-info-circle me-1"></i>Nenhuma imagem na galeria.
        <a class="btn btn-sm btn-outline-success ms-2" href="/admin/cursos/galeria?id=<?= (int) ($course['id'] ?? 0) ?>"><i class="bi bi-cloud-arrow-up me-1"></i>Adicionar imagens</a>
      </div>
    <?php endif; ?>
  </div>
</section>

<script>
function removerDocente(id, cursoId) {
  if (!confirm('Remover este vínculo docente?')) return;
  const formData = new FormData();
  formData.append('id', id);
  fetch('/admin/cursos/remover-corpo-docente', { method: 'POST', body: formData })
    .then(r => r.json())
    .then(data => {
      if (data.sucesso) {
        location.reload();
      } else {
        alert('Erro: ' + (data.erro || 'Erro desconhecido'));
      }
    })
    .catch(() => alert('Erro ao remover vínculo.'));
}
</script>
