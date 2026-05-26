<section class="container py-4">
    <div class="bg-white border rounded-3 p-4 shadow-sm">
      <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <h4 class="mb-0"><i class="bi bi-journal-bookmark-fill me-2"></i>Cursos IESB</h4>
        <div class="d-flex flex-wrap align-items-center gap-2">
          <?php $currentOrder = ($order ?? 'desc'); ?>
          <span class="text-muted small">Ordenar por ID</span>
           <div class="btn-group" role="group" aria-label="Ordenar cursos por ID">
             <button
               type="button"
               class="btn btn-outline-secondary btn-sm<?= $currentOrder === 'desc' ? ' active' : '' ?>"
               title="Mais novos primeiro (clique para ordenar decrescente)"
                onclick="var url=window.location.href; if(url.indexOf('order=')>-1){url=url.replace(/order=[^&]*/,'order=desc');}else if(url.indexOf('?')>-1){url+='&order=desc';}else{url+='?order=desc';} window.location.href=url;"
             >
               <i class="bi bi-arrow-down"></i>
             </button>
             <button
               type="button"
               class="btn btn-outline-secondary btn-sm<?= $currentOrder === 'asc' ? ' active' : '' ?>"
               title="Mais antigos primeiro (clique para ordenar crescente)"
                onclick="var url=window.location.href; if(url.indexOf('order=')>-1){url=url.replace(/order=[^&]*/,'order=asc');}else if(url.indexOf('?')>-1){url+='&order=asc';}else{url+='?order=asc';} window.location.href=url;"
             >
               <i class="bi bi-arrow-up"></i>
             </button>
           </div>
          <a class="btn btn-primary btn-sm" href="/admin/cursos/novo"><i class="bi bi-plus-circle me-1"></i>Novo curso</a>
        </div>
      </div>

    <div class="table-responsive">
      <table class="table table-striped table-sm align-middle">
        <thead>
          <tr>
            <th><i class="bi bi-hash"></i></th>
            <th><i class="bi bi-card-image me-1"></i>Card</th>
            <th><i class="bi bi-journal-text me-1"></i>Nome</th>
            <th><i class="bi bi-toggle-on me-1"></i>Ativo</th>
            <th><i class="bi bi-calendar-event me-1"></i>Data</th>
            <th><i class="bi bi-geo-alt me-1"></i>Local</th>
              <th>Modalidade</th>
             <th>Nível</th>
             <th><i class="bi bi-award-fill me-1"></i>Confirmado</th>
             <th><i class="bi bi-gear me-1"></i>Acoes</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($courses)): ?>
            <tr><td colspan="10" class="text-muted"><i class="bi bi-inbox me-1"></i>Nenhum curso encontrado.</td></tr>
          <?php endif; ?>

          <?php foreach (($courses ?? []) as $course): ?>
            <tr>
              <td><a class="text-decoration-none fw-medium" href="/admin/cursos/show?id=<?= (int) ($course['id'] ?? 0) ?>"><i class="bi bi-box-arrow-up-right me-1"></i><?= (int) ($course['id'] ?? 0) ?></a></td>
              <td class="text-center">
                <?php $img = (string) ($course['imagem_card'] ?? ''); ?>
                <a href="/admin/cursos/upload?id=<?= (int) ($course['id'] ?? 0) ?>" class="text-decoration-none" title="<?= $img !== '' ? 'Imagem cadastrada' : 'Sem imagem' ?>">
                  <?php if ($img !== ''): ?>
                    <i class="bi bi-check-circle-fill text-success fs-5"></i>
                  <?php else: ?>
                    <i class="bi bi-exclamation-triangle-fill text-warning fs-5"></i>
                  <?php endif; ?>
                </a>
              </td>
              <td><?= htmlspecialchars((string) ($course['nome'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
              <?php $ativoStatus = strtoupper(trim((string) ($course['ativo'] ?? 'N'))); ?>
              <td>
                <?php if ($ativoStatus === 'S' || $ativoStatus === '1' || $ativoStatus === 'Y'): ?>
                  <span class="badge bg-primary">Sim</span>
                <?php else: ?>
                  <span class="badge bg-secondary">Não</span>
                <?php endif; ?>
              </td>
              <?php $cursoCalendarioRaw = (string) ($course['curso_calendario'] ?? ''); ?>
              <?php $needsAlert = $cursoCalendarioRaw === '0000-00-00'; ?>
              <td<?= $needsAlert ? ' class="text-danger fw-semibold"' : '' ?>>
                <?= htmlspecialchars((string) ($course['data_curso'] ?? '-'), ENT_QUOTES, 'UTF-8') ?>
              </td>
              <td><?= htmlspecialchars((string) ($course['local_curso'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars((string) ($course['modalidade_nome'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars((string) ($course['nivel_nome'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                <td>
                  <?php if (strtoupper(trim((string) ($course['confirmado'] ?? 'N'))) === 'S'): ?>
                    <span class="badge bg-success">Sim</span>
                  <?php else: ?>
                    <span class="badge bg-secondary">Não</span>
                  <?php endif; ?>
                </td>
              <td>
                <a class="btn btn-outline-secondary btn-sm" href="/admin/cursos/editar?id=<?= (int) ($course['id'] ?? 0) ?>">
                  <i class="bi bi-pencil-square me-1"></i>Editar
                </a>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</section>
