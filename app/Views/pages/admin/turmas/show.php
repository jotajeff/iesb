<?php
  use App\Helpers\MaterialHelper;

  $turmaSelecionada = is_array($turma ?? null) ? $turma : null;
  $inscritosLista = is_array($inscritos ?? null) ? $inscritos : [];
  $totalInscritos = count($inscritosLista);
  $professoresLista = is_array($professores ?? null) ? $professores : [];
  $materiaisLista = is_array($materiais ?? null) ? $materiais : [];
  $currentUserRole = (string) ($authUser['role'] ?? $authUser['tipo'] ?? '');
  $isProfessor = $currentUserRole === 'professor';
?>

<section class="container py-4">
  <div class="bg-white border rounded-3 p-4 shadow-sm">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
      <h4 class="mb-0"><i class="bi bi-eye me-2"></i><?= htmlspecialchars((string) ($turmaSelecionada['nome'] ?? 'Turma'), ENT_QUOTES, 'UTF-8') ?></h4>
      <div class="d-flex gap-2">
        <a class="btn btn-outline-secondary btn-sm" href="<?= $isProfessor ? '/admin/professores/turmas' : '/admin/turmas' ?>"><i class="bi bi-arrow-left me-1"></i>Voltar para lista</a>
        <?php if (!$isProfessor): ?>
        <a class="btn btn-outline-primary btn-sm" href="/admin/turmas/editar?id=<?= (int) ($turmaSelecionada['id'] ?? 0) ?>"><i class="bi bi-pencil-square me-1"></i>Editar</a>
        <?php endif; ?>
      </div>
    </div>

    <?php if (!$turmaSelecionada): ?>
      <div class="alert alert-warning mb-0">
        <i class="bi bi-exclamation-triangle me-2"></i>Turma não encontrada.
      </div>
    <?php else: ?>
      <div class="accordion" id="turmaAccordion">
        <div class="accordion-item">
          <h2 class="accordion-header">
            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#dadosTurma" aria-expanded="true" aria-controls="dadosTurma">
              <i class="bi bi-info-circle me-2"></i>Dados da Turma
            </button>
          </h2>
          <div id="dadosTurma" class="accordion-collapse collapse show" data-bs-parent="#turmaAccordion">
            <div class="accordion-body p-0">
              <table class="table table-bordered mb-0">
                <tbody>
                  <tr>
                    <th class="bg-light" style="width: 180px;">ID</th>
                    <td><?= (int) ($turmaSelecionada['id'] ?? 0) ?></td>
                  </tr>
                  <tr>
                    <th class="bg-light">Nome</th>
                    <td><?= htmlspecialchars((string) ($turmaSelecionada['nome'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                  </tr>
                  <tr>
                    <th class="bg-light">Curso</th>
                    <td><?= htmlspecialchars((string) ($turmaSelecionada['curso_nome'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                  </tr>
                  <tr>
                    <th class="bg-light">Nível</th>
                    <td><?= htmlspecialchars((string) ($turmaSelecionada['nivel_nome'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                  </tr>
                  <tr>
                    <th class="bg-light">Data de Início</th>
                    <td><?php
                      $rawDate = (string) ($turmaSelecionada['data_inicio'] ?? '');
                      $dt = $rawDate !== '' ? \DateTime::createFromFormat('Y-m-d', $rawDate) : false;
                      echo htmlspecialchars($dt ? $dt->format('d/m/Y') : ($rawDate ?: '-'), ENT_QUOTES, 'UTF-8');
                    ?></td>
                  </tr>
                  <tr>
                    <th class="bg-light">Ativa</th>
                    <td>
                      <?php if (strtoupper(trim((string) ($turmaSelecionada['ativa'] ?? 'N'))) === 'S'): ?>
                        <span class="badge bg-primary">Sim</span>
                      <?php else: ?>
                        <span class="badge bg-secondary">Não</span>
                      <?php endif; ?>
                    </td>
                  </tr>
                  <tr>
                    <th class="bg-light">Total Inscritos</th>
                    <td><span class="badge bg-info"><?= $totalInscritos ?></span></td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <div class="accordion-item">
          <h2 class="accordion-header">
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#professoresTurma" aria-expanded="false" aria-controls="professoresTurma">
              <i class="bi bi-person-badge me-2"></i>Professores vinculados (<?= count($professoresLista) ?>)
            </button>
          </h2>
          <div id="professoresTurma" class="accordion-collapse collapse" data-bs-parent="#turmaAccordion">
            <div class="accordion-body p-0">
              <?php if (empty($professoresLista)): ?>
                <div class="alert alert-light border text-muted m-3">
                  <i class="bi bi-inbox me-1"></i>Nenhum professor vinculado a esta turma.
                </div>
              <?php else: ?>
                <div class="table-responsive">
                  <table class="table table-striped table-sm align-middle mb-0">
                    <thead>
                      <tr>
                        <th><i class="bi bi-hash"></i></th>
                        <th>Nome</th>
                        <th>Email</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php foreach ($professoresLista as $prof): ?>
                        <tr>
                          <td><?= (int) ($prof['id'] ?? 0) ?></td>
                          <td><?= htmlspecialchars((string) ($prof['nome'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                          <td><?= htmlspecialchars((string) ($prof['email'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
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
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#alunosTurma" aria-expanded="false" aria-controls="alunosTurma">
              <i class="bi bi-people me-2"></i>Alunos inscritos (<?= $totalInscritos ?>)
            </button>
          </h2>
          <div id="alunosTurma" class="accordion-collapse collapse" data-bs-parent="#turmaAccordion">
            <div class="accordion-body p-0">
              <?php if (empty($inscritosLista)): ?>
                <div class="alert alert-light border text-muted m-3">
                  <i class="bi bi-inbox me-1"></i>Nenhum aluno matriculado nesta turma.
                </div>
              <?php else: ?>
                <div class="table-responsive">
                  <table class="table table-striped table-sm align-middle mb-0">
                    <thead>
                      <tr>
                        <th><i class="bi bi-hash"></i></th>
                        <th>Nome</th>
                        <th>CPF</th>
                        <th>Email</th>
                        <th>Telefone</th>
                        <th>Status</th>
                        <th>Data Matrícula</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php foreach ($inscritosLista as $aluno): ?>
                        <tr>
                          <td><?= (int) ($aluno['id'] ?? 0) ?></td>
                          <td><?= htmlspecialchars((string) ($aluno['nome'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                          <td><?= htmlspecialchars((string) ($aluno['cpf'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                          <td><?= htmlspecialchars((string) ($aluno['email'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                          <td><?= htmlspecialchars((string) ($aluno['telefone'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                          <td>
                            <?php
                              $statusClass = match ($aluno['status'] ?? '') {
                                'ativo' => 'bg-success',
                                'concluido' => 'bg-primary',
                                'cancelado' => 'bg-danger',
                                'inadimplente' => 'bg-warning text-dark',
                                'inscrito' => 'bg-info',
                                default => 'bg-secondary',
                              };
                            ?>
                            <span class="badge <?= $statusClass ?>"><?= htmlspecialchars((string) ($aluno['status'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></span>
                          </td>
                          <td><?php
                            $raw = (string) ($aluno['data_matricula'] ?? '');
                            $dtm = $raw !== '' ? \DateTime::createFromFormat('Y-m-d H:i:s', $raw) ?: \DateTime::createFromFormat('Y-m-d', $raw) : false;
                            echo htmlspecialchars($dtm ? $dtm->format('d/m/Y H:i') : ($raw ?: '-'), ENT_QUOTES, 'UTF-8');
                          ?></td>
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
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#materiaisTurma" aria-expanded="false" aria-controls="materiaisTurma">
              <i class="bi bi-file-earmark-text me-2"></i>Materiais (<?= count($materiaisLista) ?>)
            </button>
          </h2>
          <div id="materiaisTurma" class="accordion-collapse collapse" data-bs-parent="#turmaAccordion">
            <div class="accordion-body p-0">
              <?php if (empty($materiaisLista)): ?>
                <div class="alert alert-light border text-muted m-3">
                  <i class="bi bi-inbox me-1"></i>Nenhum material vinculado a esta turma.
                </div>
              <?php else: ?>
                <div class="table-responsive">
                  <table class="table table-striped table-sm align-middle mb-0">
                    <thead>
                      <tr>
                        <th><i class="bi bi-hash"></i></th>
                        <th>Tipo</th>
                        <th>Título</th>
                        <th>Link</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php foreach ($materiaisLista as $mat): ?>
                        <tr>
                          <td><?= (int) ($mat['id'] ?? 0) ?></td>
                          <td>
                            <?php $tipoMaterial = (string) ($mat['tipo'] ?? ''); ?>
                            <span class="badge <?= match ($tipoMaterial) { 'video' => 'bg-danger', 'drive' => 'bg-primary', default => 'bg-secondary' } ?>">
                              <i class="bi <?= MaterialHelper::icon($tipoMaterial) ?> me-1"></i>
                              <?= htmlspecialchars($tipoMaterial ?: '-', ENT_QUOTES, 'UTF-8') ?>
                            </span>
                          </td>
                          <td><?= htmlspecialchars((string) ($mat['titulo'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                          <td>
                            <?php $link = (string) ($mat['link'] ?? ''); ?>
                            <?php if ($link !== ''): ?>
                              <?php if ($tipoMaterial === 'video'): ?>
                                <a class="btn btn-sm btn-outline-danger" href="/admin/turmas/ver-video?id=<?= (int) ($mat['id'] ?? 0) ?>">
                                  <i class="bi bi-camera-reels me-1"></i>Assistir
                                </a>
                              <?php elseif ($tipoMaterial === 'drive'): ?>
                                <a class="btn btn-sm btn-outline-primary" href="/admin/turmas/ver-drive?id=<?= (int) ($mat['id'] ?? 0) ?>">
                                  <i class="bi bi-google me-1"></i>Visualizar
                                </a>
                              <?php else: ?>
                                <a href="<?= htmlspecialchars($link, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener noreferrer">
                                  <i class="bi bi-box-arrow-up-right me-1"></i>Abrir
                                </a>
                              <?php endif; ?>
                            <?php else: ?>
                              -
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
      </div>
    <?php endif; ?>
  </div>
</section>
