<section class="container py-4">
  <div class="bg-white border rounded-3 p-4 shadow-sm">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
      <h4 class="mb-0"><i class="bi bi-eye me-2"></i><?= htmlspecialchars((string) ($course['nome'] ?? 'Curso'), ENT_QUOTES, 'UTF-8') ?></h4>
      <div class="d-flex gap-2">
        <a class="btn btn-outline-secondary btn-sm" href="/admin/cursos"><i class="bi bi-arrow-left me-1"></i>Voltar para lista</a>
        <a class="btn btn-outline-primary btn-sm" href="/admin/cursos/editar?id=<?= (int) ($course['id'] ?? 0) ?>"><i class="bi bi-pencil-square me-1"></i>Editar</a>
      </div>
    </div>

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

    <table class="table table-bordered">
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
          <th class="bg-light">Tipo</th>
          <td><?= htmlspecialchars((string) ($course['tipo_nome'] ?? (($course['tipo_curso'] ?? 0) ? 'Tipo ' . $course['tipo_curso'] : '-')), ENT_QUOTES, 'UTF-8') ?></td>
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
  </div>
</section>
