<?php
$alunoData = is_array($aluno ?? null) ? $aluno : null;
$cursosLista = is_array($cursos ?? null) ? $cursos : [];


?>

<section class="container py-4">
  <div class="bg-white border rounded-3 p-4 shadow-sm">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
      <h4 class="mb-0"><i class="bi bi-eye me-2"></i><?= htmlspecialchars((string) ($alunoData['nome'] ?? 'Aluno'), ENT_QUOTES, 'UTF-8') ?></h4>
      <div class="d-flex gap-2">
        <a class="btn btn-secondary btn-sm" href="/admin/alunos"><i class="bi bi-arrow-left me-1"></i>Voltar para lista</a>
        <a class="btn btn-primary btn-sm" href="/admin/alunos/editar?id=<?= (int) ($alunoData['id'] ?? 0) ?>"><i class="bi bi-pencil-square me-1"></i>Editar</a>
        <a class="btn btn-success btn-sm" href="/admin/alunos/matricula?id=<?= (int) ($alunoData['id'] ?? 0) ?>"><i class="bi bi-journal-plus me-1"></i>Matricular</a>
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
                    <th class="bg-light" style="width: 180px;">Foto</th>
                    <td>
                      <?php if (!empty($alunoData['foto'])): ?>
                        <img src="/<?= htmlspecialchars($alunoData['foto'], ENT_QUOTES, 'UTF-8') ?>" alt="Foto do aluno" class="img-fluid rounded" style="max-height: 120px;">
                      <?php else: ?>
                        <i class="bi bi-camera fs-1 text-muted"></i>
                      <?php endif; ?>
                    </td>
                  </tr>
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
                      <?php if (intval($alunoData['ativo'] ?? 0) === 1): ?>
                        <span class="badge bg-primary">Sim</span>
                      <?php else: ?>
                        <span class="badge bg-secondary">Não</span>
                      <?php endif; ?>
                    </td>
                  </tr>
                  <tr>
                    <th class="bg-light">Senha</th>
                    <td>
                      <button type="button" class="btn btn-sm btn-warning"
                        onclick="restaurarSenha(<?= (int) ($alunoData['id'] ?? 0) ?>)">
                        <i class="bi bi-key me-1"></i>Restaurar Senha
                      </button>
                      <small class="text-muted ms-2">(senha será gerada automaticamente)</small>
                    </td>
                  </tr>
                  <tr>
                    <th class="bg-light">Criado em</th>
                    <td><?php
                        $rawCriado = (string) ($alunoData['created_at'] ?? '');
                        $dtCriado = $rawCriado !== '' ? \DateTime::createFromFormat('Y-m-d H:i:s', $rawCriado) : false;
                        echo htmlspecialchars($dtCriado ? $dtCriado->format('d/m/Y H:i') : ($rawCriado ?: '-'), ENT_QUOTES, 'UTF-8');
                        ?></td>
                  </tr>
                  <tr>
                    <th class="bg-light">Atualizado em</th>
                    <td><?php
                        $rawAtual = (string) ($alunoData['updated_at'] ?? '');
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
                        <th>Ações</th>
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
                          <td>
                            <a class="btn btn-info btn-sm" href="/admin/alunos/troca?id=<?= (int) ($alunoData['id'] ?? 0) ?>&matricula_id=<?= (int) ($curso['matricula_id'] ?? 0) ?>" title="Trocar de turma">
                              <i class="bi bi-arrow-left-right"></i>
                            </a>
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
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#documentosAluno" aria-expanded="false" aria-controls="documentosAluno">
              <i class="bi bi-folder2-open me-2"></i>Documentos
            </button>
          </h2>
          <div id="documentosAluno" class="accordion-collapse collapse" data-bs-parent="#alunoAccordion">
            <div class="accordion-body p-0">
              <?php $documentos = is_array($documentos ?? null) ? $documentos : []; ?>
              <?php if (empty($documentos)): ?>
                <div class="alert alert-light border text-muted m-3">
                  <i class="bi bi-inbox me-1"></i>Nenhum documento definido para o grupo Alunos.
                </div>
              <?php else: ?>
                <div class="table-responsive">
                  <table class="table table-striped table-sm align-middle mb-0">
                    <thead>
                      <tr>
                        <th>Documento</th>
                        <th>Obrigatório</th>
                        <th>Status</th>
                        <th>Arquivo</th>
                        <th>Enviado em</th>
                        <th>Ações</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php foreach ($documentos as $doc): ?>
                        <?php
                        $docId = (int) ($doc['documento_id'] ?? 0);
                        $status = (string) ($doc['status'] ?? 'nao_enviado');
                        $statusLabels = [
                          'nao_enviado' => 'Não enviado',
                          'enviado' => 'Enviado',
                          'em_analise' => 'Em análise',
                          'aprovado' => 'Aprovado',
                          'rejeitado' => 'Rejeitado',
                          'substituido' => 'Substituído',
                        ];
                        $statusBadges = [
                          'nao_enviado' => 'bg-secondary',
                          'enviado' => 'bg-info',
                          'em_analise' => 'bg-warning text-dark',
                          'aprovado' => 'bg-success',
                          'rejeitado' => 'bg-danger',
                          'substituido' => 'bg-dark',
                        ];
                        ?>
                        <tr>
                          <td><?= htmlspecialchars((string) ($doc['tipo_descricao'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                          <td>
                            <?php if ((int) ($doc['obrigatorio'] ?? 0) === 1): ?>
                              <span class="badge bg-danger">Obrigatório</span>
                            <?php else: ?>
                              <span class="badge bg-secondary">Opcional</span>
                            <?php endif; ?>
                          </td>
                          <td>
                            <span class="badge <?= $statusBadges[$status] ?? 'bg-secondary' ?>"><?= htmlspecialchars($statusLabels[$status] ?? ucfirst($status), ENT_QUOTES, 'UTF-8') ?></span>
                            <?php if ($status === 'rejeitado' && !empty($doc['observacao'])): ?>
                              <div class="small text-danger mt-1">
                                <i class="bi bi-chat-left-text me-1"></i><?= htmlspecialchars((string) $doc['observacao'], ENT_QUOTES, 'UTF-8') ?>
                              </div>
                            <?php endif; ?>
                          </td>
                          <td>
                            <?php if ($docId > 0): ?>
                              <i class="bi bi-file-earmark-pdf me-1"></i>
                              <?= htmlspecialchars((string) ($doc['nome_original'] ?? '-'), ENT_QUOTES, 'UTF-8') ?>
                              <?php if ((int) ($doc['versao'] ?? 1) > 1): ?>
                                <span class="badge bg-light text-dark border ms-1">v<?= (int) $doc['versao'] ?></span>
                              <?php endif; ?>
                            <?php else: ?>
                              <span class="text-muted">-</span>
                            <?php endif; ?>
                          </td>
                          <td class="text-nowrap">
                            <?= $docId > 0 ? htmlspecialchars((string) ($doc['created_at'] ?? '-'), ENT_QUOTES, 'UTF-8') : '<span class="text-muted">-</span>' ?>
                          </td>
                          <td>
                            <?php if ($docId > 0): ?>
                              <button type="button" class="btn btn-sm btn-outline-success"
                                onclick="visualizarDocumento(<?= (int) $docId ?>, '<?= htmlspecialchars((string) $doc['tipo_descricao'], ENT_QUOTES, 'UTF-8') ?>', '<?= htmlspecialchars((string) ($doc['file_id'] ?? ''), ENT_QUOTES, 'UTF-8') ?>')"
                                title="Visualizar">
                                <i class="bi bi-eye"></i>
                              </button>
                              <form method="post" action="/admin/alunos/compartilhar-documento" class="d-inline" onsubmit="return confirm('Liberar este documento para download público (qualquer pessoa com o link)?');">
                                <input type="hidden" name="id" value="<?= $docId ?>">
                                <input type="hidden" name="aluno_id" value="<?= (int) ($alunoData['id'] ?? 0) ?>">
                                <button type="submit" class="btn btn-sm btn-outline-primary" title="Liberar download público">
                                  <i class="bi bi-unlock"></i>
                                </button>
                              </form>
                            <?php else: ?>
                              <span class="text-muted">-</span>
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
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#logsAluno" aria-expanded="false" aria-controls="logsAluno">
              <i class="bi bi-clock-history me-2"></i>Logs (últimos 50)
            </button>
          </h2>
          <div id="logsAluno" class="accordion-collapse collapse" data-bs-parent="#alunoAccordion">
            <div class="accordion-body p-0">
              <?php if (empty($logsAluno ?? [])): ?>
                <div class="alert alert-light border text-muted m-3">
                  <i class="bi bi-inbox me-1"></i>Nenhum registro de log.
                </div>
              <?php else: ?>
                <div class="table-responsive">
                  <table class="table table-striped table-hover table-sm align-middle mb-0">
                    <thead>
                      <tr>
                        <th>#</th>
                        <th>Usuário</th>
                        <th>Ação</th>
                        <th>Entidade</th>
                        <th>Descrição</th>
                        <th>IP</th>
                        <th>Data</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php foreach ($logsAluno as $log): ?>
                        <?php
                        $raw = (string) ($log['created_at'] ?? '');
                        $dt = '-';
                        if ($raw !== '') {
                          try {
                            $dtObj = new \DateTime($raw);
                            $dt = $dtObj->format('d/m/Y H:i');
                          } catch (\Throwable $e) {
                            $dt = $raw;
                          }
                        }
                        ?>
                        <tr>
                          <td><?= (int) ($log['id'] ?? 0) ?></td>
                          <td><?= htmlspecialchars((string) ($log['aluno_nome'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                          <td><?= \App\Helpers\LogHelper::render((string) ($log['acao'] ?? '-')) ?></td>
                          <td><?= htmlspecialchars((string) ($log['entidade'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                          <td><?= htmlspecialchars((string) ($log['descricao'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                          <td>
                            <span title="<?= htmlspecialchars((string) ($log['ip'] ?? '-'), ENT_QUOTES, 'UTF-8') ?>">
                              <?php $loc = $log['location'] ?? []; ?>
                              <?= $loc['flag'] ?? '🏳️' ?>
                              <?= htmlspecialchars((string) ($loc['country'] ?? '-'), ENT_QUOTES, 'UTF-8') ?>
                              <?php if (!empty($loc['city']) && $loc['city'] !== '-'): ?>
                                / <?= htmlspecialchars($loc['city'], ENT_QUOTES, 'UTF-8') ?>
                              <?php endif; ?>
                            </span>
                          </td>
                          <td><?= htmlspecialchars($dt, ENT_QUOTES, 'UTF-8') ?></td>
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

<div class="modal fade" id="visualizarDocumentoModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="visualizarDocumentoModalTitle"><i class="bi bi-eye me-2"></i>Visualizar documento</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
      </div>
      <div class="modal-body p-2" style="background:#f8f9fa;">
        <iframe id="visualizarDocumentoFrame" src="" style="width:100%;aspect-ratio:4/3;border:0;border-radius:.375rem;" allowfullscreen></iframe>
        <div id="visualizarDocumentoFallback" class="text-center py-4 d-none">
          <i class="bi bi-file-earmark fs-1 text-muted"></i>
          <p class="mt-2 mb-0">Não foi possível incorporar este documento.</p>
        </div>
      </div>
      <div class="modal-footer">
        <a id="visualizarDocumentoAbrir" href="#" target="_blank" class="btn btn-outline-primary btn-sm"><i class="bi bi-box-arrow-up-right me-1"></i>Abrir no Drive</a>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
      </div>
    </div>
  </div>
</div>

<script>
  function visualizarDocumento(id, titulo, fileId) {
    if (!fileId) {
      alert('Arquivo não encontrado no Drive.');
      return;
    }

    document.getElementById('visualizarDocumentoModalTitle').textContent = 'Visualizar documento: ' + titulo;

    var embedSrc = 'https://drive.google.com/file/d/' + encodeURIComponent(fileId) + '/preview';
    var frame = document.getElementById('visualizarDocumentoFrame');
    var fallback = document.getElementById('visualizarDocumentoFallback');
    var abrir = document.getElementById('visualizarDocumentoAbrir');

    frame.src = embedSrc;
    frame.classList.remove('d-none');
    fallback.classList.add('d-none');
    abrir.href = 'https://drive.google.com/file/d/' + encodeURIComponent(fileId) + '/view';

    var modalEl = document.getElementById('visualizarDocumentoModal');
    var modal = bootstrap.Modal.getOrCreateInstance(modalEl);
    modal.show();

    frame.onload = function() {
      try {
        var hasAccess = frame.contentDocument && frame.contentDocument.body;
        if (!hasAccess) {
          fallback.classList.remove('d-none');
        }
      } catch (e) {
        // Cross-origin: iframe carregou, acesso indireto. Mantem o frame.
        frame.classList.remove('d-none');
      }
    };
  }

  document.getElementById('visualizarDocumentoModal').addEventListener('hidden.bs.modal', function() {
    document.getElementById('visualizarDocumentoFrame').src = '';
  });

  function restaurarSenha(id) {
    if (!confirm('Tem certeza que deseja restaurar a senha deste aluno?')) return;

    var formData = new FormData();
    formData.append('id', id);

    fetch('/admin/alunos/restaurar-senha', {
        method: 'POST',
        body: formData
      })
      .then(function(r) {
        return r.json();
      })
      .then(function(data) {
        if (data.sucesso) {
          alert('Senha restaurada com sucesso!\nNova senha: ' + data.senha);
        } else {
          alert('Erro: ' + (data.erro || 'não foi possível restaurar a senha.'));
        }
      })
      .catch(function() {
        alert('Erro de rede ao tentar restaurar a senha.');
      });
  }
</script>