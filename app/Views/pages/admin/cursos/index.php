<?php
  $currentOrder = strtolower((string) ($order ?? ($_GET['order'] ?? 'desc')));
  $currentOrder = $currentOrder === 'asc' ? 'asc' : 'desc';

  $currentAtivo = strtoupper(trim((string) ($_GET['ativo'] ?? '')));
  if ($currentAtivo !== 'S' && $currentAtivo !== 'N') {
      $currentAtivo = '';
  }

  $coursesView = is_array($courses ?? null) ? $courses : [];
  $niveisLista = array_filter(is_array($niveis ?? null) ? $niveis : [], static fn ($n) => is_array($n));
  $currentNivelId = (int) ($nivelSelecionado ?? ($_GET['nivel'] ?? 0));
  if ($currentNivelId < 0) {
      $currentNivelId = 0;
  }
  $nivelLabels = [];
  foreach ($niveisLista as $nivelItem) {
      $nivelId = (int) ($nivelItem['id'] ?? 0);
      if ($nivelId <= 0) {
          continue;
      }
      $nivelLabels[$nivelId] = (string) ($nivelItem['nome'] ?? ('Nível #' . $nivelId));
  }
  $currentNivelLabel = $currentNivelId > 0 && isset($nivelLabels[$currentNivelId])
      ? $nivelLabels[$currentNivelId]
      : 'Todos os níveis';

  if ($currentAtivo !== '') {
      $coursesView = array_values(array_filter($coursesView, static function ($course) use ($currentAtivo): bool {
          $ativoStatus = strtoupper(trim((string) ($course['ativo'] ?? 'N')));
          $isAtivo = ($ativoStatus === 'S' || $ativoStatus === '1' || $ativoStatus === 'Y');
          return $currentAtivo === 'S' ? $isAtivo : !$isAtivo;
      }));
  }

  usort($coursesView, static function ($a, $b) use ($currentOrder): int {
      $idA = (int) ($a['id'] ?? 0);
      $idB = (int) ($b['id'] ?? 0);

      if ($idA === $idB) {
          return 0;
      }

      if ($currentOrder === 'asc') {
          return $idA <=> $idB;
      }

      return $idB <=> $idA;
  });

  $buildUrl = static function (array $changes = []): string {
      $params = $_GET ?? [];

      foreach ($changes as $key => $value) {
          if ($value === null || $value === '') {
              unset($params[$key]);
              continue;
          }

          $params[$key] = $value;
      }

      $query = http_build_query($params);
      return '/admin/cursos' . ($query !== '' ? '?' . $query : '');
  };
?>

<section class="container py-4">
    <div class="bg-white border rounded-3 p-4 shadow-sm">
      <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <h4 class="mb-0"><i class="bi bi-journal-bookmark-fill me-2"></i>Cursos IESB</h4>
        <div class="d-flex flex-wrap align-items-center gap-2">
          <span class="text-muted small">Ordenar por ID</span>
           <div class="btn-group" role="group" aria-label="Ordenar cursos por ID">
             <a
                class="btn btn-outline-secondary btn-sm<?= $currentOrder === 'desc' ? ' active' : '' ?>"
                title="Mais novos primeiro (clique para ordenar decrescente)"
                href="<?= htmlspecialchars($buildUrl(['order' => 'desc']), ENT_QUOTES, 'UTF-8') ?>"
             >
               <i class="bi bi-arrow-down"></i>
             </a>
             <a
               class="btn btn-outline-secondary btn-sm<?= $currentOrder === 'asc' ? ' active' : '' ?>"
               title="Mais antigos primeiro (clique para ordenar crescente)"
               href="<?= htmlspecialchars($buildUrl(['order' => 'asc']), ENT_QUOTES, 'UTF-8') ?>"
             >
               <i class="bi bi-arrow-up"></i>
             </a>
           </div>

          <span class="text-muted small ms-2">Ativo</span>
          <div class="btn-group" role="group" aria-label="Filtrar cursos ativos">
            <a
              class="btn btn-outline-secondary btn-sm<?= $currentAtivo === 'S' ? ' active' : '' ?>"
              href="<?= htmlspecialchars($buildUrl(['ativo' => 'S']), ENT_QUOTES, 'UTF-8') ?>"
              title="Exibir somente cursos ativos"
            >
              Ativo S
            </a>
            <a
              class="btn btn-outline-secondary btn-sm<?= $currentAtivo === 'N' ? ' active' : '' ?>"
              href="<?= htmlspecialchars($buildUrl(['ativo' => 'N']), ENT_QUOTES, 'UTF-8') ?>"
              title="Exibir somente cursos inativos"
            >
              Ativo N
            </a>
          </div>

          <span class="text-muted small ms-2">Nível</span>
          <div class="dropdown">
            <button class="btn btn-outline-secondary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
              <?= htmlspecialchars($currentNivelLabel, ENT_QUOTES, 'UTF-8') ?>
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
              <li>
                <a class="dropdown-item<?= $currentNivelId === 0 ? ' active' : '' ?>" href="<?= htmlspecialchars($buildUrl(['nivel' => null]), ENT_QUOTES, 'UTF-8') ?>">
                  Todos os níveis
                </a>
              </li>
              <?php foreach ($niveisLista as $nivel): ?>
                <?php $nivelId = (int) ($nivel['id'] ?? 0); if ($nivelId <= 0) { continue; } ?>
                <li>
                  <a class="dropdown-item<?= $nivelId === $currentNivelId ? ' active' : '' ?>" href="<?= htmlspecialchars($buildUrl(['nivel' => $nivelId]), ENT_QUOTES, 'UTF-8') ?>">
                    <?= htmlspecialchars((string) ($nivel['nome'] ?? ('Nível #' . $nivelId)), ENT_QUOTES, 'UTF-8') ?>
                  </a>
                </li>
              <?php endforeach; ?>
            </ul>
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
            <th><i class="bi bi-diagram-3 me-1"></i>Segmento</th>
              <th>Modalidade</th>
             <th>Nível</th>
             <th><i class="bi bi-award-fill me-1"></i>Confirmado</th>
             <th><i class="bi bi-gear me-1"></i>Acoes</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($coursesView)): ?>
            <tr><td colspan="10" class="text-muted"><i class="bi bi-inbox me-1"></i>Nenhum curso encontrado.</td></tr>
          <?php endif; ?>

          <?php foreach ($coursesView as $course): ?>
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
              <td><?= htmlspecialchars((string) ($course['segmento_nome'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
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
