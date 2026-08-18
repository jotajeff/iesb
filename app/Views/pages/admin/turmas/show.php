<?php
  use App\Helpers\MaterialHelper;

  $turmaSelecionada = is_array($turma ?? null) ? $turma : null;
  $inscritosLista = is_array($inscritos ?? null) ? $inscritos : [];
  $totalInscritos = count($inscritosLista);
  $professoresLista = is_array($professores ?? null) ? $professores : [];
  $materiaisLista = is_array($materiais ?? null) ? $materiais : [];
  $modulosDaTurma = is_array($modulosDaTurma ?? null) ? $modulosDaTurma : [];
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
                    <th class="bg-light">Matriz curricular</th>
                    <td><?= htmlspecialchars((string) ($turmaSelecionada['estrutura_nome'] ?? 'Sem matriz definida'), ENT_QUOTES, 'UTF-8') ?></td>
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
                      <?php if (intval($turmaSelecionada['ativo'] ?? 0) === 1): ?>
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
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#modulosTurma" aria-expanded="false" aria-controls="modulosTurma">
              <i class="bi bi-grid-3x3-gap me-2"></i>Módulos da matriz (<?= count($modulosDaTurma) ?>)
            </button>
          </h2>
          <div id="modulosTurma" class="accordion-collapse collapse" data-bs-parent="#turmaAccordion">
            <div class="accordion-body p-0">
              <?php if (empty($modulosDaTurma)): ?>
                <div class="alert alert-light border text-muted m-3"><i class="bi bi-info-circle me-1"></i>Esta turma não possui matriz curricular vinculada ou a matriz ainda não possui módulos.</div>
              <?php else: ?>
                <div class="table-responsive">
                  <table class="table table-striped table-sm align-middle mb-0">
                    <thead><tr><th>Ordem</th><th>Módulo</th><th>Carga horária</th><th>Disciplinas</th><th>Status</th></tr></thead>
                    <tbody>
                    <?php foreach ($modulosDaTurma as $modulo): ?>
                      <tr>
                        <td><?= (int) ($modulo['ordem'] ?? 0) ?></td>
                        <td><?= htmlspecialchars((string) ($modulo['nome'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= (int) ($modulo['carga_horaria'] ?? 0) > 0 ? (int) $modulo['carga_horaria'] . 'h' : '-' ?></td>
                        <td><span class="badge bg-primary"><?= (int) ($modulo['total_disciplinas'] ?? 0) ?></span></td>
                        <td><?= (int) ($modulo['ativo'] ?? 0) === 1 ? '<span class="badge bg-success">Ativo</span>' : '<span class="badge bg-secondary">Inativo</span>' ?></td>
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
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#disciplinasTurma" aria-expanded="false" aria-controls="disciplinasTurma">
              <i class="bi bi-journal-text me-2"></i>Disciplinas (<?= count($disciplinasTurma ?? []) ?>)
            </button>
          </h2>
          <div id="disciplinasTurma" class="accordion-collapse collapse" data-bs-parent="#turmaAccordion">
            <div class="accordion-body p-0">
              <?php $disciplinasTurma = $disciplinasTurma ?? []; ?>
              <?php $disciplinasDoCurso = $disciplinasDoCurso ?? []; ?>
              <?php $professoresDaTurma = $professoresDaTurma ?? []; ?>
              <div class="p-3 border-bottom d-flex justify-content-end">
                <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalDisciplinaTurma" onclick="resetModalDisciplinaTurma()">
                  <i class="bi bi-plus-circle me-1"></i>Adicionar disciplina
                </button>
              </div>
              <?php if (empty($disciplinasTurma)): ?>
                <div class="alert alert-light border text-muted m-3">
                  <i class="bi bi-inbox me-1"></i>Nenhuma disciplina vinculada a esta turma.
                </div>
              <?php else: ?>
                <div class="table-responsive">
                  <table class="table table-striped table-sm align-middle mb-0">
                    <thead>
                      <tr>
                        <th>#</th>
                        <th>Disciplina</th>
                        <th>Professor</th>
                        <th>Início</th>
                        <th>Fim</th>
                        <th>Status</th>
                        <th>Ativo</th>
                        <th>Ações</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php foreach ($disciplinasTurma as $td): ?>
                        <?php
                          $tdAtivo = (int) ($td['ativo'] ?? 1) === 1;
                          $tdStatus = (string) ($td['status'] ?? 'PLANEJADA');
                          $statusLabel = match ($tdStatus) {
                            'PLANEJADA' => 'Planejada',
                            'EM_ANDAMENTO' => 'Em andamento',
                            'CONCLUIDA' => 'Concluída',
                            'CANCELADA' => 'Cancelada',
                            default => $tdStatus,
                          };
                        ?>
                        <tr>
                          <td><?= (int) ($td['id'] ?? 0) ?></td>
                          <td><?= htmlspecialchars((string) ($td['disciplina_nome'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                          <td><?= htmlspecialchars((string) ($td['professor_nome'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                          <td><?= !empty($td['data_inicio']) ? date('d/m/Y', strtotime((string) $td['data_inicio'])) : '-' ?></td>
                          <td><?= !empty($td['data_fim']) ? date('d/m/Y', strtotime((string) $td['data_fim'])) : '-' ?></td>
                          <td><span class="badge bg-info text-dark"><?= htmlspecialchars($statusLabel, ENT_QUOTES, 'UTF-8') ?></span></td>
                          <td>
                            <?php if ($tdAtivo): ?>
                              <span class="badge bg-success">Sim</span>
                            <?php else: ?>
                              <span class="badge bg-secondary">Não</span>
                            <?php endif; ?>
                          </td>
                          <td>
                            <div class="d-flex gap-1">
                              <button type="button" class="btn btn-sm btn-outline-secondary btn-editar-disciplina-turma"
                                      data-id="<?= (int) ($td['id'] ?? 0) ?>"
                                      data-disciplina="<?= (int) ($td['id_disciplina'] ?? 0) ?>"
                                      data-professor="<?= (int) ($td['id_usuario_professor'] ?? 0) ?>"
                                      data-inicio="<?= htmlspecialchars((string) ($td['data_inicio'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                                      data-fim="<?= htmlspecialchars((string) ($td['data_fim'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                                      data-status="<?= htmlspecialchars($tdStatus, ENT_QUOTES, 'UTF-8') ?>"
                                      data-ativo="<?= (int) ($td['ativo'] ?? 1) ?>"><i class="bi bi-pencil"></i></button>
                              <?php if ($tdAtivo): ?>
                                <button type="button" class="btn btn-sm btn-outline-danger btn-desativar-disciplina-turma" data-id="<?= (int) ($td['id'] ?? 0) ?>"><i class="bi bi-toggle-off"></i></button>
                              <?php endif; ?>
                            </div>
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
                        <?php if (!$isProfessor): ?>
                          <th>Ações</th>
                        <?php endif; ?>
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
                          <?php if (!$isProfessor): ?>
                            <td>
                              <button type="button" class="btn btn-sm btn-outline-primary btn-gerenciar-disciplinas"
                                      data-matricula="<?= (int) ($aluno['id_matricula'] ?? 0) ?>"
                                      data-aluno="<?= htmlspecialchars((string) ($aluno['nome'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                                      title="Gerenciar disciplinas da matrícula">
                                <i class="bi bi-journal-text me-1"></i>Disciplinas
                              </button>
                            </td>
                          <?php endif; ?>
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

<?php if (!$isProfessor): ?>
<div class="modal fade" id="modalDisciplinaTurma" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form id="formDisciplinaTurma">
        <input type="hidden" name="id" id="tdId" value="0">
        <input type="hidden" name="id_turma" value="<?= (int) ($turmaSelecionada['id'] ?? 0) ?>">
        <div class="modal-header">
          <h5 class="modal-title" id="modalDisciplinaTurmaTitulo">Adicionar disciplina</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Disciplina <span class="text-danger">*</span></label>
            <select name="id_disciplina" id="tdDisciplina" class="form-select" required>
              <option value="">Selecione...</option>
              <?php foreach ($disciplinasDoCurso as $d): ?>
                <option value="<?= (int) ($d['id'] ?? 0) ?>"><?= htmlspecialchars((string) ($d['nome'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></option>
              <?php endforeach; ?>
            </select>
            <div class="form-text">Somente disciplinas do curso desta turma.</div>
          </div>
          <div class="mb-3">
            <label class="form-label">Professor</label>
            <select name="id_usuario_professor" id="tdProfessor" class="form-select">
              <option value="">Sem professor</option>
              <?php foreach ($professoresDaTurma as $prof): ?>
                <option value="<?= (int) ($prof['id'] ?? 0) ?>"><?= htmlspecialchars((string) ($prof['nome'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></option>
              <?php endforeach; ?>
            </select>
            <div class="form-text">Apenas professores vinculados à turma.</div>
          </div>
          <div class="row g-3">
            <div class="col-6">
              <label class="form-label">Data início</label>
              <input type="date" name="data_inicio" id="tdInicio" class="form-control">
            </div>
            <div class="col-6">
              <label class="form-label">Data fim</label>
              <input type="date" name="data_fim" id="tdFim" class="form-control">
            </div>
            <div class="col-6">
              <label class="form-label">Status</label>
              <select name="status" id="tdStatus" class="form-select">
                <option value="PLANEJADA">Planejada</option>
                <option value="EM_ANDAMENTO">Em andamento</option>
                <option value="CONCLUIDA">Concluída</option>
                <option value="CANCELADA">Cancelada</option>
              </select>
            </div>
            <div class="col-6">
              <label class="form-label">Ativo</label>
              <select name="ativo" id="tdAtivo" class="form-select">
                <option value="1">Sim</option>
                <option value="0">Não</option>
              </select>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Salvar</button>
        </div>
      </form>
    </div>
  </div>
</div>

<div class="modal fade" id="modalDisciplinasMatricula" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <form id="formDisciplinasMatricula">
        <input type="hidden" name="id_matricula" id="mdMatriculaId" value="0">
        <input type="hidden" name="id_turma" value="<?= (int) ($turmaSelecionada['id'] ?? 0) ?>">
        <div class="modal-header">
          <h5 class="modal-title" id="modalDisciplinasMatriculaTitulo">Disciplinas da matrícula</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <p class="text-muted small mb-3">Aluno: <strong id="mdAlunoNome"></strong></p>
          <p class="text-muted small mb-3">Selecione as disciplinas desta turma para a matrícula.</p>
          <?php $disciplinasTurmaLista = $disciplinasTurma ?? []; ?>
          <?php if (empty($disciplinasTurmaLista)): ?>
            <div class="alert alert-warning">Esta turma ainda não possui disciplinas cadastradas. Cadastre disciplinas na seção "Disciplinas" primeiro.</div>
          <?php else: ?>
            <div class="list-group" id="mdDisciplinasList">
              <?php foreach ($disciplinasTurmaLista as $td): ?>
                <label class="list-group-item d-flex align-items-center gap-2">
                  <input type="checkbox" name="disciplinas[]" value="<?= (int) ($td['id'] ?? 0) ?>" class="form-check-input m-0 md-check">
                  <span class="flex-grow-1"><?= htmlspecialchars((string) ($td['disciplina_nome'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></span>
                  <span class="text-muted small"><?= htmlspecialchars((string) ($td['professor_nome'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></span>
                </label>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Salvar</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
var disciplinasMatriculadasPorMatricula = <?= json_encode($disciplinasMatriculadas ?? [], JSON_NUMERIC_CHECK) ?>;

function resetModalDisciplinaTurma() {
  document.getElementById('tdId').value = '0';
  document.getElementById('tdDisciplina').value = '';
  document.getElementById('tdProfessor').value = '';
  document.getElementById('tdInicio').value = '';
  document.getElementById('tdFim').value = '';
  document.getElementById('tdStatus').value = 'PLANEJADA';
  document.getElementById('tdAtivo').value = '1';
  document.getElementById('modalDisciplinaTurmaTitulo').textContent = 'Adicionar disciplina';
}

document.addEventListener('DOMContentLoaded', function () {
  var form = document.getElementById('formDisciplinaTurma');

  document.querySelectorAll('.btn-editar-disciplina-turma').forEach(function (btn) {
    btn.addEventListener('click', function () {
      document.getElementById('tdId').value = btn.dataset.id;
      document.getElementById('tdDisciplina').value = btn.dataset.disciplina;
      document.getElementById('tdProfessor').value = btn.dataset.professor || '';
      document.getElementById('tdInicio').value = btn.dataset.inicio || '';
      document.getElementById('tdFim').value = btn.dataset.fim || '';
      document.getElementById('tdStatus').value = btn.dataset.status || 'PLANEJADA';
      document.getElementById('tdAtivo').value = btn.dataset.ativo || '1';
      document.getElementById('modalDisciplinaTurmaTitulo').textContent = 'Editar disciplina';
      bootstrap.Modal.getOrCreateInstance(document.getElementById('modalDisciplinaTurma')).show();
    });
  });

  document.querySelectorAll('.btn-desativar-disciplina-turma').forEach(function (btn) {
    btn.addEventListener('click', function () {
      if (!confirm('Desativar esta disciplina da turma?')) return;
      var body = new URLSearchParams();
      body.set('id', this.dataset.id);
      fetch('/admin/turmas/disciplinas/desativar', { method: 'POST', body: body })
        .then(function (r) { return r.json(); })
        .then(function (data) { if (data.sucesso) location.reload(); else alert(data.erro || 'Erro.'); });
    });
  });

  if (form) {
    form.addEventListener('submit', function (e) {
      e.preventDefault();
      var data = new URLSearchParams(new FormData(form));
      fetch('/admin/turmas/disciplinas/salvar', { method: 'POST', body: data })
        .then(function (r) { return r.json(); })
        .then(function (res) {
          if (res.sucesso) { bootstrap.Modal.getInstance(document.getElementById('modalDisciplinaTurma')).hide(); location.reload(); }
          else alert(res.erro || 'Erro ao salvar disciplina.');
        })
        .catch(function () { alert('Erro ao salvar disciplina.'); });
    });
  }

  var formMd = document.getElementById('formDisciplinasMatricula');
  var modalMd = document.getElementById('modalDisciplinasMatricula');

  document.querySelectorAll('.btn-gerenciar-disciplinas').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var idMatricula = parseInt(btn.dataset.matricula || '0', 10);
      document.getElementById('mdMatriculaId').value = idMatricula;
      document.getElementById('mdAlunoNome').textContent = btn.dataset.aluno || '';
      var marcadas = disciplinasMatriculadasPorMatricula[idMatricula] || [];
      document.querySelectorAll('#mdDisciplinasList .md-check').forEach(function (cb) {
        cb.checked = marcadas.indexOf(parseInt(cb.value, 10)) !== -1;
      });
      bootstrap.Modal.getOrCreateInstance(modalMd).show();
    });
  });

  if (formMd) {
    formMd.addEventListener('submit', function (e) {
      e.preventDefault();
      var data = new URLSearchParams(new FormData(formMd));
      fetch('/admin/turmas/disciplinas-matricula/salvar', { method: 'POST', body: data })
        .then(function (r) { return r.json(); })
        .then(function (res) {
          if (res.sucesso) { bootstrap.Modal.getInstance(modalMd).hide(); location.reload(); }
          else alert(res.erro || 'Erro ao salvar disciplinas da matrícula.');
        })
        .catch(function () { alert('Erro ao salvar disciplinas da matrícula.'); });
    });
  }
});
</script>
<?php endif; ?>
