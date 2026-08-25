<?php
  $matriculasView = is_array($matriculas ?? null) ? $matriculas : [];
  $emailStatus = is_array($emailStatus ?? null) ? $emailStatus : [];
  $cursoAtual = null;
?>

<section class="container py-4">
  <div class="bg-white border rounded-3 p-4 shadow-sm">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
      <h4 class="mb-0"><i class="bi bi-card-checklist me-2"></i>Matrículas ativas</h4>
      <span class="badge bg-primary"><?= count($matriculasView) ?> matrícula(s)</span>
    </div>

    <?php if (empty($matriculasView)): ?>
      <p class="text-muted mb-0">Nenhuma matrícula ativa encontrada.</p>
    <?php else: ?>
      <?php foreach ($matriculasView as $matricula): ?>
        <?php $cursoNome = (string) ($matricula['curso_nome'] ?? 'Curso não informado'); ?>
        <?php if ($cursoAtual !== $cursoNome): ?>
          <?php if ($cursoAtual !== null): ?>
            </tbody>
            </table>
          </div>
          <div class="my-4"></div>
          <?php endif; ?>

          <?php $cursoAtual = $cursoNome; ?>
          <div class="border rounded-3 overflow-hidden mb-4">
            <div class="bg-light border-bottom px-3 py-2 fw-semibold" style="color: #b8860b;">
              <i class="bi bi-book me-2"></i><?= htmlspecialchars($cursoNome, ENT_QUOTES, 'UTF-8') ?>
            </div>
            <div class="table-responsive">
              <table class="table table-striped table-sm align-middle mb-0">
                <thead>
                  <tr>
                    <th>Aluno</th>
                    <th>Turma</th>
                    <th>Data de matrícula</th>
                    <th><i class="bi bi-envelope me-1"></i>Email</th>
                  </tr>
                </thead>
                <tbody>
        <?php endif; ?>
                <tr>
                  <td>
                    <a class="text-decoration-none fw-medium me-2" href="/admin/alunos/show?id=<?= (int) ($matricula['id_aluno'] ?? 0) ?>">
                      #<?= (int) ($matricula['id_aluno'] ?? 0) ?>
                    </a>
                    <?= htmlspecialchars((string) ($matricula['aluno_nome'] ?? '-'), ENT_QUOTES, 'UTF-8') ?>
                  </td>
                  <td><?= htmlspecialchars((string) ($matricula['turma_nome'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                  <td><?php
                    $rawDate = (string) ($matricula['data_matricula'] ?? '');
                    $date = $rawDate !== '' ? date_create($rawDate) : false;
                    echo htmlspecialchars($date ? $date->format('d/m/Y') : ($rawDate ?: '-'), ENT_QUOTES, 'UTF-8');
                  ?></td>
                  <td>
                    <?php
                      $chaveEmail = ((int) ($matricula['id_aluno'] ?? 0)) . ':' . ((int) ($matricula['id_curso'] ?? 0));
                      $emailEnviado = isset($emailStatus[$chaveEmail]) && $emailStatus[$chaveEmail];
                    ?>
                    <?php if ($emailEnviado): ?>
                      <span class="badge bg-success"><i class="bi bi-envelope-check me-1"></i>Enviado</span>
                    <?php else: ?>
                      <span class="badge bg-danger"><i class="bi bi-envelope-x me-1"></i>Não enviado</span>
                    <?php endif; ?>
                  </td>
                </tr>
      <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
    <?php endif; ?>
  </div>
</section>
