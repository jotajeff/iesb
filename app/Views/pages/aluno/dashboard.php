<?php
  $matriculaData = $matriculaDB ?? [];
  $cursosMatriculadosLista = $cursosMatriculados ?? [];
  $cursosMatriculadosIds = [];
  foreach ($cursosMatriculadosLista as $item) {
      $cursoId = (int) ($item['curso_id'] ?? 0);
      if ($cursoId > 0) {
          $cursosMatriculadosIds[$cursoId] = $cursoId;
      }
  }
  $totalMatricula = count($cursosMatriculadosIds);
  $statusCounts = [];
  foreach ($matriculaData as $item) {
      $s = $item['status'] ?? 'desconhecido';
      $statusCounts[$s] = ($statusCounts[$s] ?? 0) + 1;
  }
?>

<section class="py-4" id="home" style="margin-top: 20px;">
  <div class="container">
    <?php if (!empty($documentosPendentes)): ?>
      <?php
        $documentosPendentesLista = array_map(
            static fn (string $documento): string => htmlspecialchars($documento, ENT_QUOTES, 'UTF-8'),
            $documentosPendentes
        );
      ?>
      <div class="alert alert-warning d-flex align-items-start gap-3 mb-4" role="alert">
        <i class="bi bi-file-earmark-exclamation-fill fs-4 mt-1" aria-hidden="true"></i>
        <div>
          <div class="fw-semibold">Há documentos obrigatórios pendentes.</div>
          <div class="small mt-1">
            Envie os seguintes documentos: <?= implode(', ', $documentosPendentesLista) ?>.
          </div>
          <a href="/aluno/documentos" class="alert-link fw-semibold d-inline-block mt-2">
            <i class="bi bi-upload me-1" aria-hidden="true"></i>Enviar documentos
          </a>
        </div>
      </div>
    <?php endif; ?>

    <?php if (empty($temEndereco)): ?>
      <div class="alert alert-warning d-flex align-items-center gap-2 mb-4" role="alert">
        <i class="bi bi-exclamation-triangle-fill"></i>
        <div>
          Seus dados de endereço ainda não foram cadastrados.
          <a href="/aluno/endereco" class="alert-link fw-semibold">Clique aqui para cadastrar seu endereço</a>.
        </div>
      </div>
    <?php endif; ?>

    <?php if (!empty($avisoParcela)): ?>
      <div class="alert alert-warning d-flex align-items-start gap-3 mb-4" role="alert">
        <i class="bi bi-exclamation-triangle-fill fs-4 mt-1" aria-hidden="true"></i>
        <div>
          <div class="fw-semibold">
            <?php if ((int) $avisoParcelaDias === 1): ?>
              Falta 1 dia para o vencimento da sua parcela.
            <?php else: ?>
              Faltam <?= (int) $avisoParcelaDias ?> dias para o vencimento da sua parcela.
            <?php endif; ?>
          </div>
          <a href="/aluno/financeiro" class="alert-link fw-semibold d-inline-block mt-2">
            <i class="bi bi-cash-coin me-1"></i>Ver financeiro
          </a>
        </div>
      </div>
    <?php endif; ?>

    <?php if (!empty($chamadaAberta) && trim((string) ($chamadaAberta['presenca_atual'] ?? '')) === ''): ?>
      <?php
        $presencaAtual = (string) ($chamadaAberta['presenca_atual'] ?? '');
        $chData = (string) ($chamadaAberta['data_aula'] ?? '');
        $chDataFmt = $chData !== '' ? date_create($chData) : false;
        $chInicio = substr((string) ($chamadaAberta['hora_inicio'] ?? ''), 0, 5);
        $chFim = substr((string) ($chamadaAberta['hora_fim'] ?? ''), 0, 5);
        $chInicioFull = (string) ($chamadaAberta['hora_inicio'] ?? '');
        $chFimFull = (string) ($chamadaAberta['hora_fim'] ?? '');
        $dentroHorario = false;
        if ($chData !== '' && $chInicioFull !== '' && $chFimFull !== '') {
          $inicioTs = strtotime($chData . ' ' . $chInicioFull);
          $fimTs = strtotime($chData . ' ' . $chFimFull);
          $agoraTs = time();
          $dentroHorario = $inicioTs !== false && $fimTs !== false && $agoraTs >= $inicioTs && $agoraTs <= $fimTs;
        }
      ?>
      <div class="alert alert-primary d-flex align-items-start gap-3 mb-4" role="alert">
        <i class="bi bi-clipboard-check fs-4 mt-1" aria-hidden="true"></i>
        <div class="flex-grow-1">
          <div class="fw-semibold"><i class="bi bi-bell me-1"></i>Chamada em andamento</div>
          <div class="small mt-1">
            <strong>Disciplina:</strong> <?= htmlspecialchars((string) ($chamadaAberta['disciplina_nome'] ?? '-'), ENT_QUOTES, 'UTF-8') ?>
            &middot; <strong>Turma:</strong> <?= htmlspecialchars((string) ($chamadaAberta['turma_nome'] ?? '-'), ENT_QUOTES, 'UTF-8') ?>
            &middot; <strong>Horário:</strong> <?= htmlspecialchars(($chDataFmt ? $chDataFmt->format('d/m') : $chData) . ' ' . $chInicio . ' às ' . $chFim, ENT_QUOTES, 'UTF-8') ?>
          </div>
          <?php if (!$dentroHorario): ?>
            <div class="alert alert-warning py-2 px-3 mb-2 mt-2 small">
              <i class="bi bi-exclamation-triangle me-1"></i>Registro de presença permitido apenas entre <strong><?= htmlspecialchars($chInicio, ENT_QUOTES, 'UTF-8') ?></strong> e <strong><?= htmlspecialchars($chFim, ENT_QUOTES, 'UTF-8') ?></strong>.
            </div>
          <?php endif; ?>
          <div class="d-flex flex-wrap gap-2 mt-2">
            <form method="post" action="/aluno/chamada/presenca" class="d-inline">
              <input type="hidden" name="id_chamada" value="<?= (int) ($chamadaAberta['id'] ?? 0) ?>">
              <button type="submit" name="presenca" value="PRESENTE" class="btn btn-sm btn-success" <?= $dentroHorario ? '' : 'disabled' ?>><i class="bi bi-check-lg me-1"></i>Presente</button>
            </form>
            <form method="post" action="/aluno/chamada/presenca" class="d-inline">
              <input type="hidden" name="id_chamada" value="<?= (int) ($chamadaAberta['id'] ?? 0) ?>">
              <button type="submit" name="presenca" value="AUSENTE" class="btn btn-sm btn-outline-danger" <?= $dentroHorario ? '' : 'disabled' ?>><i class="bi bi-x-lg me-1"></i>Ausente</button>
            </form>
            <form method="post" action="/aluno/chamada/presenca" class="d-inline">
              <input type="hidden" name="id_chamada" value="<?= (int) ($chamadaAberta['id'] ?? 0) ?>">
              <button type="submit" name="presenca" value="JUSTIFICADA" class="btn btn-sm btn-outline-warning" <?= $dentroHorario ? '' : 'disabled' ?>><i class="bi bi-shield-check me-1"></i>Justificada</button>
            </form>
          </div>
        </div>
      </div>
    <?php endif; ?>

    <?php if (!empty($banners ?? [])): ?>
      <div class="mb-4" data-aos="fade-up">
        <?php foreach ($banners as $banner): ?>
          <?php
            $bannerImg = trim((string) ($banner['banner'] ?? ''));
            $bannerLink = trim((string) ($banner['link'] ?? ''));
            $bannerTexto = trim((string) ($banner['texto'] ?? ''));
          ?>
          <?php if ($bannerImg !== ''): ?>
            <div class="rounded-3 overflow-hidden shadow-sm border mb-3" style="border-color: var(--border-color); background: var(--bg-card);">
              <img src="/<?= htmlspecialchars($bannerImg, ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($bannerTexto !== '' ? $bannerTexto : 'Banner', ENT_QUOTES, 'UTF-8') ?>" class="img-fluid w-100" style="max-height: 400px; object-fit: cover; display: block;">
              <?php if ($bannerTexto !== ''): ?>
                <div class="p-3">
                  <a href="<?= htmlspecialchars($bannerLink !== '' ? $bannerLink : '#', ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener" class="fw-semibold text-decoration-none">
                    <?= htmlspecialchars($bannerTexto, ENT_QUOTES, 'UTF-8') ?>
                  </a>
                </div>
              <?php endif; ?>
            </div>
          <?php endif; ?>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <div class="row g-3 mb-4">
      <div class="col-md-3" data-aos="fade-up" data-aos-delay="0">
        <a href="/aluno/cursos" class="text-decoration-none">
          <div class="d-flex align-items-center gap-3 p-3 rounded-3 shadow-sm h-100" style="background: linear-gradient(135deg, #0d6efd, #0a58ca); color: #fff;">
            <div class="d-flex align-items-center justify-content-center rounded-circle" style="width: 54px; height: 54px; background: rgba(255,255,255,0.2); font-size: 1.5rem;">
              <i class="bi bi-journal-bookmark-fill"></i>
            </div>
            <div>
              <div class="fs-3 fw-bold"><?= $totalMatricula ?></div>
              <div class="small opacity-75">Cursos Matriculados</div>
            </div>
          </div>
        </a>
      </div>
      <div class="col-md-3" data-aos="fade-up" data-aos-delay="200">
        <div class="d-flex align-items-center gap-3 p-3 rounded-3 shadow-sm" style="background: linear-gradient(135deg, #ffc107, #e0a800); color: #212529;">
          <div class="d-flex align-items-center justify-content-center rounded-circle" style="width: 54px; height: 54px; background: rgba(0,0,0,0.1); font-size: 1.5rem;">
            <i class="bi bi-trophy-fill"></i>
          </div>
          <div>
            <div class="fs-3 fw-bold"><?= $statusCounts['concluido'] ?? 0 ?></div>
            <div class="small opacity-75">Concluídos</div>
          </div>
        </div>
      </div>
      <div class="col-md-3" data-aos="fade-up" data-aos-delay="300">
        <a href="/aluno/notificacoes" class="text-decoration-none">
          <div class="d-flex align-items-center gap-3 p-3 rounded-3 shadow-sm" style="background: linear-gradient(135deg, #6f42c1, #5530a3); color: #fff;">
            <div class="d-flex align-items-center justify-content-center rounded-circle" style="width: 54px; height: 54px; background: rgba(255,255,255,0.2); font-size: 1.5rem;">
              <i class="bi bi-bell-fill"></i>
            </div>
            <div>
              <div class="fs-3 fw-bold"><?= (int) ($notificacaoCount ?? 0) ?></div>
              <div class="small opacity-75">Notificações</div>
            </div>
          </div>
        </a>
      </div>
    </div>

    <div class="row g-4">
      <div class="col-12" data-aos="fade-up">
        <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 16px; padding: 2rem; box-shadow: var(--card-shadow);">
          <h3 class="mb-1">Amplie Seus Conhecimentos</h3>
          <p class="text-muted mb-4">Inscreva-se em um de Nossos Cursos e dê mais um passo rumo ao seu desenvolvimento profissional.</p>
          <?php if (empty($cursosDisponiveis)): ?>
            <div class="text-center text-muted py-4">
              <i class="bi bi-journal-bookmark" style="font-size: 2rem;"></i>
              <p class="mt-2 mb-0">Nenhum curso disponível no momento.</p>
            </div>
          <?php else: ?>
            <div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 g-4">
              <?php foreach ($cursosDisponiveis as $curso): ?>
                <?php
                  $cursoImage = trim((string) ($curso['imagem_card'] ?? ''));
                  $cursoSlug = rawurlencode((string) ($curso['slug'] ?? ''));
                  $cursoUrl = '/curso/' . $cursoSlug;
                  $cursoNome = htmlspecialchars((string) ($curso['nome'] ?? '-'), ENT_QUOTES, 'UTF-8');
                ?>
                <div class="col">
                  <a href="<?= htmlspecialchars($cursoUrl, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener" class="text-decoration-none" title="Detalhes do curso">
                    <div class="card curso-card h-100 border-0 shadow-sm">
                      <?php if ($cursoImage !== ''): ?>
                        <img src="/<?= htmlspecialchars($cursoImage, ENT_QUOTES, 'UTF-8') ?>" alt="Imagem do curso <?= $cursoNome ?>" class="card-img-top" style="height: 150px; object-fit: cover;">
                      <?php else: ?>
                        <div class="d-flex align-items-center justify-content-center bg-light" style="height: 150px;">
                          <i class="bi bi-image text-muted" style="font-size: 2rem;"></i>
                        </div>
                      <?php endif; ?>
                      <div class="card-body">
                        <h6 class="card-title mb-1"><?= $cursoNome ?></h6>
                        <?php if (!empty($curso['local_curso'])): ?>
                          <p class="card-text small text-muted mb-0"><?= htmlspecialchars($curso['local_curso'], ENT_QUOTES, 'UTF-8') ?></p>
                        <?php endif; ?>
                      </div>
                    </div>
                  </a>
                </div>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </div>
      </div>

      <div class="row g-4 mt-1">
        <div class="col-12" data-aos="fade-up">
          <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 16px; padding: 2rem; box-shadow: var(--card-shadow);">
            <h3 class="mb-3"><i class="bi bi-newspaper me-2"></i>Notícias</h3>
            <?php $noticiasLista = $noticias ?? []; ?>
            <?php if (empty($noticiasLista)): ?>
              <div class="text-center text-muted py-4">
                <i class="bi bi-newspaper" style="font-size: 2rem;"></i>
                <p class="mt-2 mb-0">Nenhuma notícia disponível no momento.</p>
              </div>
            <?php else: ?>
              <div class="row g-3">
                <?php foreach (array_slice($noticiasLista, 0, 6) as $n): ?>
                  <?php
                  $nTitulo = htmlspecialchars((string) ($n['titulo'] ?? '-'), ENT_QUOTES, 'UTF-8');
                  $nSlug = htmlspecialchars((string) ($n['slug'] ?? ''), ENT_QUOTES, 'UTF-8');
                  $nResumo = htmlspecialchars((string) ($n['resumo'] ?? ''), ENT_QUOTES, 'UTF-8');
                  $nImagem = trim((string) ($n['imagem_capa'] ?? ''));
                  $nCat = htmlspecialchars((string) ($n['categoria_nome'] ?? ''), ENT_QUOTES, 'UTF-8');
                  $nDt = \DateTime::createFromFormat('Y-m-d H:i:s', (string) ($n['data_publicacao'] ?? ''));
                  $nData = $nDt ? $nDt->format('d/m/Y') : '';
                  ?>
                  <div class="col-md-6 col-lg-4">
                    <a href="/aluno/noticia?slug=<?= rawurlencode($nSlug) ?>" class="text-decoration-none">
                      <div class="card noticia-card h-100 border-0 shadow-sm">
                        <?php if ($nImagem !== ''): ?>
                          <img src="/<?= htmlspecialchars($nImagem, ENT_QUOTES, 'UTF-8') ?>" class="card-img-top" alt="<?= $nTitulo ?>" style="height: 160px; object-fit: cover;">
                        <?php endif; ?>
                        <div class="card-body">
                          <?php if ($nCat !== ''): ?>
                            <span class="badge bg-warning text-dark mb-2"><?= $nCat ?></span>
                          <?php endif; ?>
                          <h6 class="card-title mb-1"><?= $nTitulo ?></h6>
                          <?php if ($nResumo !== ''): ?>
                            <p class="card-text small text-muted mb-2"><?= $nResumo ?></p>
                          <?php endif; ?>
                          <small class="text-muted"><?= $nData ?></small>
                        </div>
                      </div>
                    </a>
                  </div>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<div class="text-center small py-3" style="color: #6c757d !important;">
  <i class="bi bi-clock me-1"></i><?= htmlspecialchars(date('d/m/Y H:i:s'), ENT_QUOTES, 'UTF-8') ?>
</div>

<style>
  .noticia-card, .curso-card {
    transition: transform .2s ease, box-shadow .2s ease;
  }
  .noticia-card:hover, .curso-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 .5rem 1rem rgba(0, 0, 0, .15) !important;
  }
</style>
