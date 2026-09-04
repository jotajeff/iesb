<?php
  $bannersView = is_array($banners ?? null) ? $banners : [];
?>

<section class="container py-4">
  <div class="bg-white border rounded-3 p-4 shadow-sm">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
      <h4 class="mb-0"><i class="bi bi-images me-2"></i>Banner Aluno</h4>
      <div class="d-flex flex-wrap align-items-center gap-2">
        <a class="btn btn-primary btn-sm" href="/admin/config/banner-aluno/novo"><i class="bi bi-plus-circle me-1"></i>Novo banner</a>
      </div>
    </div>

    <?php if (empty($bannersView)): ?>
      <p class="text-muted mb-0">Nenhum banner cadastrado.</p>
    <?php else: ?>
      <div class="table-responsive">
        <table class="table table-striped table-hover table-sm align-middle">
          <thead>
            <tr>
              <th><i class="bi bi-hash"></i></th>
              <th>Banner</th>
              <th>Texto</th>
              <th>Link</th>
              <th>Curso</th>
              <th>Ativo</th>
              <th><i class="bi bi-gear me-1"></i>Ações</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($bannersView as $banner): ?>
              <?php
                $bannerImg = trim((string) ($banner['banner'] ?? ''));
                $bannerAtivo = (int) ($banner['ativo'] ?? 1) === 1;
              ?>
              <tr>
                <td><?= (int) ($banner['id'] ?? 0) ?></td>
                <td>
                  <?php if ($bannerImg !== ''): ?>
                    <img src="/<?= htmlspecialchars($bannerImg, ENT_QUOTES, 'UTF-8') ?>" alt="Banner" style="width: 120px; height: 56px; object-fit: cover;" class="rounded border">
                  <?php else: ?>
                    <span class="text-muted">-</span>
                  <?php endif; ?>
                </td>
                <td><?= htmlspecialchars((string) ($banner['texto'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                <td class="text-break" style="max-width: 240px;"><a href="<?= htmlspecialchars((string) ($banner['link'] ?? '#'), ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener"><?= htmlspecialchars((string) ($banner['link'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></a></td>
                <td><?= htmlspecialchars((string) ($banner['curso_nome'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                <td>
                  <?php if ($bannerAtivo): ?>
                    <span class="badge bg-success">Sim</span>
                  <?php else: ?>
                    <span class="badge bg-secondary">Não</span>
                  <?php endif; ?>
                </td>
                <td>
                  <a class="btn btn-outline-secondary btn-sm" href="/admin/config/banner-aluno/editar?id=<?= (int) ($banner['id'] ?? 0) ?>">
                    <i class="bi bi-pencil-square"></i>
                  </a>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>
</section>
