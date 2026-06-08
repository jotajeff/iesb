<?php
  $alunoData = is_array($aluno ?? null) ? $aluno : null;
  $cursosLista = is_array($cursos ?? null) ? $cursos : [];
?>

<section class="container py-4">
  <div class="bg-white border rounded-3 p-4 shadow-sm">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
      <h4 class="mb-0"><i class="bi bi-eye me-2"></i><?= htmlspecialchars((string) ($alunoData['nome'] ?? 'Aluno'), ENT_QUOTES, 'UTF-8') ?></h4>
      <div class="d-flex gap-2">
        <a class="btn btn-outline-secondary btn-sm" href="/admin/alunos"><i class="bi bi-arrow-left me-1"></i>Voltar para lista</a>
        <a class="btn btn-outline-primary btn-sm" href="/admin/alunos/editar?id=<?= (int) ($alunoData['id'] ?? 0) ?>"><i class="bi bi-pencil-square me-1"></i>Editar</a>
        <a class="btn btn-outline-success btn-sm" href="/admin/alunos/matricula?id=<?= (int) ($alunoData['id'] ?? 0) ?>"><i class="bi bi-journal-plus me-1"></i>Matricular</a>
      </div>
    </div>

    <?php if (!$alunoData): ?>
      <div class="alert alert-warning mb-0">
        <i class="bi bi-exclamation-triangle me-2"></i>Aluno não encontrado.
      </div>
    <?php else: ?>
      <div class="accordion" id="alunoAccordion">
        <div class="accordion-item">
          <h2 class="accordion-header">
            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#dadosPessoais" aria-expanded="true" aria-controls="dadosPessoais">
              <i class="bi bi-person me-2"></i>Dados Pessoais
            </button>
          </h2>
          <div id="dadosPessoais" class="accordion-collapse collapse show" data-bs-parent="#alunoAccordion">
            <div class="accordion-body p-0">
              <table class="table table-bordered mb-0">
                <tbody>
                  <tr>
                    <th class="bg-light" style="width: 180px;">ID</th>
                    <td><?= (int) ($alunoData['id'] ?? 0) ?></td>
                  </tr>
                  <tr>
                    <th class="bg-light">Nome</th>
                    <td><?= htmlspecialchars((string) ($alunoData['nome'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                  </tr>
                  <tr>
                    <th class="bg-light">CPF</th>
                    <td><?= htmlspecialchars((string) ($alunoData['cpf'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                  </tr>
                  <tr>
                    <th class="bg-light">Nascimento</th>
                    <td><?php
                      $rawDate = (string) ($alunoData['data_nascimento'] ?? '');
                      $dt = $rawDate !== '' ? \DateTime::createFromFormat('Y-m-d', $rawDate) : false;
                      echo htmlspecialchars($dt ? $dt->format('d/m/Y') : ($rawDate ?: '-'), ENT_QUOTES, 'UTF-8');
                    ?></td>
                  </tr>
                  <tr>
                    <th class="bg-light">Telefone</th>
                    <td><?= htmlspecialchars((string) ($alunoData['telefone'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                  </tr>
                  <tr>
                    <th class="bg-light">Email</th>
                    <td><?= htmlspecialchars((string) ($alunoData['email'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                  </tr>
                  <tr>
                    <th class="bg-light">Ativo</th>
                    <td>
                      <?php if (strtoupper(trim((string) ($alunoData['ativo'] ?? 'N'))) === 'S'): ?>
                        <span class="badge bg-primary">Sim</span>
                      <?php else: ?>
                        <span class="badge bg-secondary">Não</span>
                      <?php endif; ?>
                    </td>
                  </tr>
                  <tr>
                    <th class="bg-light">Criado em</th>
                    <td><?php
                      $rawCriado = (string) ($alunoData['criado_em'] ?? '');
                      $dtCriado = $rawCriado !== '' ? \DateTime::createFromFormat('Y-m-d H:i:s', $rawCriado) : false;
                      echo htmlspecialchars($dtCriado ? $dtCriado->format('d/m/Y H:i') : ($rawCriado ?: '-'), ENT_QUOTES, 'UTF-8');
                    ?></td>
                  </tr>
                  <tr>
                    <th class="bg-light">Atualizado em</th>
                    <td><?php
                      $rawAtual = (string) ($alunoData['atualizado_em'] ?? '');
                      $dtAtual = $rawAtual !== '' ? \DateTime::createFromFormat('Y-m-d H:i:s', $rawAtual) : false;
                      echo htmlspecialchars($dtAtual ? $dtAtual->format('d/m/Y H:i') : ($rawAtual ?: '-'), ENT_QUOTES, 'UTF-8');
                    ?></td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <div class="accordion-item">
          <h2 class="accordion-header">
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#cursosInscritos" aria-expanded="false" aria-controls="cursosInscritos">
              <i class="bi bi-book me-2"></i>Cursos Inscritos (<?= count($cursosLista) ?>)
            </button>
          </h2>
          <div id="cursosInscritos" class="accordion-collapse collapse" data-bs-parent="#alunoAccordion">
            <div class="accordion-body p-0">
              <?php if (empty($cursosLista)): ?>
                <div class="alert alert-light border text-muted m-3">
                  <i class="bi bi-inbox me-1"></i>Nenhum curso encontrado.
                </div>
              <?php else: ?>
                <div class="table-responsive">
                  <table class="table table-striped table-sm align-middle mb-0">
                    <thead>
                      <tr>
                        <th>Curso</th>
                        <th>Turma</th>
                        <th>Status</th>
                        <th>Data Matrícula</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php foreach ($cursosLista as $curso): ?>
                        <tr>
                          <td><?= htmlspecialchars((string) ($curso['curso_nome'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                          <td><?= htmlspecialchars((string) ($curso['turma_nome'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                          <td>
                            <?php
                              $statusClass = match ($curso['status'] ?? '') {
                                'ativo' => 'bg-success',
                                'concluido' => 'bg-primary',
                                'cancelado' => 'bg-danger',
                                'inadimplente' => 'bg-warning text-dark',
                                'inscrito' => 'bg-info',
                                default => 'bg-secondary',
                              };
                            ?>
                            <span class="badge <?= $statusClass ?>"><?= htmlspecialchars((string) ($curso['status'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></span>
                          </td>
                          <td><?php
                            $raw = (string) ($curso['data_matricula'] ?? '');
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
      </div>
    <?php endif; ?>
  </div>
</section>
