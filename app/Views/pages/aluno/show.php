<section class="py-4" id="detalhes-curso" style="margin-top: 20px;">
  <div class="container">
    <div class="bg-white border rounded-3 p-4 shadow-sm" style="background: var(--bg-card); border-color: var(--border-color);">
      <div class="d-flex align-items-center justify-content-between mb-4" data-aos="fade-up">
        <h4 class="mb-0"><i class="bi bi-journal-bookmark me-2"></i><?= htmlspecialchars($matricula['curso_nome'] ?? '-', ENT_QUOTES, 'UTF-8') ?></h4>
        <a class="btn btn-outline-secondary btn-sm" href="/aluno/cursos"><i class="bi bi-arrow-left me-1"></i>Voltar</a>
      </div>

      <div class="accordion" id="accordionCurso">

        <div class="accordion-item">
          <h2 class="accordion-header">
            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseCurso" aria-expanded="true">
              <i class="bi bi-info-circle me-2"></i>Dados do Curso
            </button>
          </h2>
          <div id="collapseCurso" class="accordion-collapse collapse show" data-bs-parent="#accordionCurso">
            <div class="accordion-body">
              <div class="row g-3">
                <div class="col-md-6">
                  <p class="mb-1"><strong><i class="bi bi-people me-1"></i>Turma:</strong> <?= htmlspecialchars($matricula['turma_nome'] ?? '-', ENT_QUOTES, 'UTF-8') ?></p>
                  <p class="mb-1"><strong><i class="bi bi-diagram-3 me-1"></i>Matriz curricular:</strong> <?= htmlspecialchars($matricula['estrutura_nome'] ?? '-', ENT_QUOTES, 'UTF-8') ?></p>
                  <p class="mb-1"><strong><i class="bi bi-calendar me-1"></i>Início:</strong> <?= htmlspecialchars($matricula['data_inicio'] ?? '-', ENT_QUOTES, 'UTF-8') ?></p>
                  <p class="mb-1"><strong><i class="bi bi-calendar me-1"></i>Término:</strong> <?= htmlspecialchars($matricula['data_fim'] ?? '-', ENT_QUOTES, 'UTF-8') ?></p>
                </div>
                <div class="col-md-6">
                  <p class="mb-1"><strong><i class="bi bi-clock me-1"></i>Horário:</strong> <?= htmlspecialchars($matricula['horario'] ?? '-', ENT_QUOTES, 'UTF-8') ?></p>
                  <p class="mb-1"><strong><i class="bi bi-geo-alt me-1"></i>Local:</strong> <?= htmlspecialchars($matricula['local_curso'] ?? '-', ENT_QUOTES, 'UTF-8') ?></p>
                  <p class="mb-1">
                    <strong><i class="bi bi-toggle-on me-1"></i>Status:</strong>
                    <?php $status = (string) ($matricula['status'] ?? ''); ?>
                    <span class="badge bg-<?= in_array($status, ['active', 'matriculado']) ? 'success' : ($status === 'concluido' ? 'secondary' : ($status === 'cancelado' ? 'danger' : 'warning')) ?>">
                      <?= htmlspecialchars(match ($status) {
                        'active', 'matriculado' => 'Ativo',
                        'concluido' => 'Concluído',
                        'cancelado' => 'Cancelado',
                        default => ucfirst($status),
                      }, ENT_QUOTES, 'UTF-8') ?>
                    </span>
                  </p>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="accordion-item">
          <h2 class="accordion-header">
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseProfessor" aria-expanded="false">
              <i class="bi bi-person-badge me-2"></i>Professor<?= count($professores ?? []) > 1 ? 'es' : '' ?>
            </button>
          </h2>
          <div id="collapseProfessor" class="accordion-collapse collapse" data-bs-parent="#accordionCurso">
            <div class="accordion-body">
              <?php if (empty($professores ?? [])): ?>
                <p class="text-muted mb-0"><i class="bi bi-inbox me-1"></i>Nenhum professor vinculado.</p>
              <?php else: ?>
                <div class="row g-3">
                  <?php foreach ($professores as $prof): ?>
                    <?php
                      $profFoto = trim((string) ($prof['foto'] ?? ''));
                      $profFotoSrc = '';
                      if ($profFoto !== '') {
                        $profFotoSrc = (str_starts_with($profFoto, 'http') || str_starts_with($profFoto, '/'))
                          ? $profFoto
                          : '/' . $profFoto;
                      }
                    ?>
                    <div class="col-md-6">
                      <div class="d-flex align-items-center gap-3 p-3 border rounded-3">
                        <div class="flex-shrink-0">
                          <?php if ($profFotoSrc !== ''): ?>
                            <img src="<?= htmlspecialchars($profFotoSrc, ENT_QUOTES, 'UTF-8') ?>" alt="Foto de <?= htmlspecialchars((string) ($prof['nome'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" class="rounded-circle" width="64" height="64" style="object-fit: cover;">
                          <?php else: ?>
                            <div class="rounded-circle bg-secondary d-flex align-items-center justify-content-center text-white" style="width:64px;height:64px;font-size:1.5rem;">
                              <i class="bi bi-person"></i>
                            </div>
                          <?php endif; ?>
                        </div>
                        <div>
                          <h6 class="mb-1"><?= htmlspecialchars($prof['nome'] ?? '-', ENT_QUOTES, 'UTF-8') ?></h6>
                          <p class="mb-1 small text-muted">
                            <i class="bi bi-envelope me-1"></i><?= htmlspecialchars($prof['email'] ?? '-', ENT_QUOTES, 'UTF-8') ?><br>
                            <?php if (!empty($prof['telefone'])): ?>
                              <i class="bi bi-telephone me-1"></i><?= htmlspecialchars($prof['telefone'], ENT_QUOTES, 'UTF-8') ?>
                            <?php endif; ?>
                          </p>
                          <?php if (!empty($prof['curriculo_id'])): ?>
                            <a class="btn btn-sm btn-outline-primary mt-2" href="/aluno/professor?id=<?= (int) ($prof['id'] ?? 0) ?>">
                              <i class="bi bi-file-earmark-text me-1"></i>Ver currículo
                            </a>
                          <?php endif; ?>
                        </div>
                      </div>
                    </div>
                  <?php endforeach; ?>
                </div>
              <?php endif; ?>
            </div>
          </div>
        </div>

        <div class="accordion-item">
          <h2 class="accordion-header">
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseMaterial" aria-expanded="false">
              <i class="bi bi-files me-2"></i>Materiais
            </button>
          </h2>
          <div id="collapseMaterial" class="accordion-collapse collapse" data-bs-parent="#accordionCurso">
            <div class="accordion-body">
              <?php if (empty($materiais ?? [])): ?>
                <p class="text-muted mb-0"><i class="bi bi-inbox me-1"></i>Nenhum material disponível.</p>
              <?php else: ?>
                <div class="table-responsive">
                  <table class="table table-sm align-middle">
                    <thead>
                      <tr>
                        <th><i class="bi bi-hash"></i></th>
                        <th><i class="bi bi-fonts me-1"></i>Título</th>
                        <th><i class="bi bi-tag me-1"></i>Tipo</th>
                        <th><i class="bi bi-link-45deg me-1"></i>Link</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php foreach ($materiais as $m): ?>
                        <tr>
                          <td><?= (int) ($m['id'] ?? 0) ?></td>
                          <td><?= htmlspecialchars($m['titulo'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
                          <td>
                            <span class="badge <?= match ($m['tipo'] ?? '') { 'video' => 'bg-danger', 'drive' => 'bg-primary', default => 'bg-secondary' } ?>">
                              <?= htmlspecialchars(ucfirst($m['tipo'] ?? '-'), ENT_QUOTES, 'UTF-8') ?>
                            </span>
                          </td>
                          <td class="text-break" style="max-width:300px;">
                            <?php if (($m['tipo'] ?? '') === 'video'): ?>
                              <a href="/aluno/video?id=<?= (int) ($m['id'] ?? 0) ?>" class="btn btn-sm btn-outline-danger">
                                <i class="bi bi-camera-reels me-1"></i>Assistir
                              </a>
                            <?php elseif (($m['tipo'] ?? '') === 'drive'): ?>
                              <a href="/aluno/drive?id=<?= (int) ($m['id'] ?? 0) ?>" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-google me-1"></i>Visualizar
                              </a>
                            <?php else: ?>
                              <a href="<?= htmlspecialchars($m['link'] ?? '#', ENT_QUOTES, 'UTF-8') ?>" target="_blank" class="btn btn-sm btn-outline-secondary">
                                <i class="bi bi-box-arrow-up-right me-1"></i>Abrir
                              </a>
                            <?php endif; ?>
                          </td>
                        </tr>
                      <?php endforeach; ?>
                    </tbody>
                  </table>
                </div>
              <?php endif; ?>
            </div>
          </div>
        </div>

        <div class="accordion-item">
          <h2 class="accordion-header">
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseDisciplina" aria-expanded="false">
              <i class="bi bi-book me-2"></i>Disciplinas
            </button>
          </h2>
          <div id="collapseDisciplina" class="accordion-collapse collapse" data-bs-parent="#accordionCurso">
            <div class="accordion-body">
              <?php $modulosDisciplinas = $modulosDisciplinas ?? []; ?>
              <?php
                $totalHoras = 0;
                if (!empty($modulosDisciplinas)) {
                  foreach ($modulosDisciplinas as $grupo) {
                    foreach ($grupo['disciplinas'] ?? [] as $disciplina) {
                      $totalHoras += (int) ($disciplina['carga_horaria'] ?? 0);
                    }
                  }
                } else {
                  foreach ($disciplinas ?? [] as $disciplina) {
                    $totalHoras += (int) ($disciplina['carga_horaria'] ?? 0);
                  }
                }
              ?>
              <?php if (!empty($modulosDisciplinas)): ?>
                <?php foreach ($modulosDisciplinas as $grupo): ?>
                  <?php $modulo = $grupo['modulo'] ?? []; ?>
                  <?php $disciplinasModulo = $grupo['disciplinas'] ?? []; ?>
                  <div class="mb-4">
                    <h6 class="mb-2">
                      <span class="badge bg-secondary me-1">Módulo <?= (int) ($modulo['ordem'] ?? 0) ?></span>
                      <?= htmlspecialchars((string) ($modulo['nome'] ?? 'Módulo'), ENT_QUOTES, 'UTF-8') ?>
                      <?php if ((int) ($modulo['carga_horaria'] ?? 0) > 0): ?>
                        <span class="text-muted small fw-normal ms-1"><i class="bi bi-clock me-1"></i><?= (int) $modulo['carga_horaria'] ?>h</span>
                      <?php endif; ?>
                    </h6>
                    <?php if (empty($disciplinasModulo)): ?>
                      <p class="text-muted small mb-0"><i class="bi bi-inbox me-1"></i>Nenhuma disciplina neste módulo.</p>
                    <?php else: ?>
                      <div class="list-group">
                        <?php foreach ($disciplinasModulo as $i => $disciplina): ?>
                          <?php $professoresDisciplina = array_values(array_filter(array_map('trim', explode('||', (string) ($disciplina['professores_nomes'] ?? ''))))); ?>
                          <div class="list-group-item py-3 <?= $i % 2 === 0 ? '' : 'list-group-item-light' ?>" style="cursor: default;">
                            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                              <div>
                                <span class="badge bg-primary-subtle text-primary-emphasis me-2"><?= (int) ($disciplina['ordem'] ?? 0) ?></span>
                                <strong><?= htmlspecialchars($disciplina['nome'] ?? '-', ENT_QUOTES, 'UTF-8') ?></strong>
                                <?php if ((int) ($disciplina['carga_horaria'] ?? 0) > 0): ?>
                                  <span class="text-muted small ms-2">
                                    <i class="bi bi-clock me-1"></i><?= (int) $disciplina['carga_horaria'] ?>h
                                  </span>
                                <?php endif; ?>
                              </div>
                            </div>
                            <?php if (!empty($disciplina['ementa'])): ?>
                              <div class="mt-2 ps-4 border-start border-2 border-primary-subtle">
                                <p class="mb-0 small text-muted"><i class="bi bi-journal-text me-1"></i><?= nl2br(htmlspecialchars((string) $disciplina['ementa'], ENT_QUOTES, 'UTF-8')) ?></p>
                              </div>
                            <?php endif; ?>
                            <?php if (!empty($professoresDisciplina)): ?>
                              <div class="mt-2 ps-4">
                                <span class="small"><i class="bi bi-person-badge me-1"></i>Professor(es):</span>
                                <?php foreach ($professoresDisciplina as $professorNome): ?>
                                  <span class="badge bg-light text-dark border ms-1"><?= htmlspecialchars($professorNome, ENT_QUOTES, 'UTF-8') ?></span>
                                <?php endforeach; ?>
                              </div>
                            <?php endif; ?>
                          </div>
                        <?php endforeach; ?>
                      </div>
                    <?php endif; ?>
                  </div>
                <?php endforeach; ?>
              <?php elseif (empty($disciplinas ?? [])): ?>
                <p class="text-muted mb-0"><i class="bi bi-inbox me-1"></i>Nenhuma disciplina disponível.</p>
              <?php else: ?>
                <div class="list-group">
                  <?php foreach ($disciplinas as $i => $disciplina): ?>
                    <?php $professoresDisciplina = array_values(array_filter(array_map('trim', explode('||', (string) ($disciplina['professores_nomes'] ?? ''))))); ?>
                    <div class="list-group-item py-3 <?= $i % 2 === 0 ? '' : 'list-group-item-light' ?>" style="cursor: default;">
                      <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                        <div>
                          <span class="badge bg-primary-subtle text-primary-emphasis me-2"><?= (int) ($disciplina['ordem'] ?? 0) ?></span>
                          <strong><?= htmlspecialchars($disciplina['nome'] ?? '-', ENT_QUOTES, 'UTF-8') ?></strong>
                          <?php if ((int) ($disciplina['carga_horaria'] ?? 0) > 0): ?>
                            <span class="text-muted small ms-2">
                              <i class="bi bi-clock me-1"></i><?= (int) $disciplina['carga_horaria'] ?>h
                            </span>
                          <?php endif; ?>
                        </div>
                      </div>
                      <?php if (!empty($disciplina['ementa'])): ?>
                        <div class="mt-2 ps-4 border-start border-2 border-primary-subtle">
                          <p class="mb-0 small text-muted"><i class="bi bi-journal-text me-1"></i><?= nl2br(htmlspecialchars((string) $disciplina['ementa'], ENT_QUOTES, 'UTF-8')) ?></p>
                        </div>
                      <?php endif; ?>
                      <?php if (!empty($professoresDisciplina)): ?>
                        <div class="mt-2 ps-4">
                          <span class="small"><i class="bi bi-person-badge me-1"></i>Professor(es):</span>
                          <?php foreach ($professoresDisciplina as $professorNome): ?>
                            <span class="badge bg-light text-dark border ms-1"><?= htmlspecialchars($professorNome, ENT_QUOTES, 'UTF-8') ?></span>
                          <?php endforeach; ?>
                        </div>
                      <?php endif; ?>
                    </div>
                  <?php endforeach; ?>
                </div>
              <?php endif; ?>

              <?php if ($totalHoras > 0): ?>
                <div class="d-flex justify-content-end mt-3">
                  <div class="alert alert-info py-2 px-3 mb-0">
                    <strong><i class="bi bi-clock me-1"></i>Carga horária total do curso: <?= $totalHoras ?>h</strong>
                  </div>
                </div>
              <?php endif; ?>
            </div>
          </div>
        </div>

      </div>
    </div>
  </div>
</section>
