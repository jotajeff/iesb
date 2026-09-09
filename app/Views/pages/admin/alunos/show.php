<?php
$alunoData = is_array($aluno ?? null) ? $aluno : null;
$cursosLista = is_array($cursos ?? null) ? $cursos : [];
$linkFinanceiroMigracao = trim((string) ($linkFinanceiroMigracao ?? ''));
$linksFinanceiro = is_array($linksFinanceiro ?? null) ? $linksFinanceiro : [];


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
              <div class="d-flex justify-content-end p-3 pb-0">
                <form method="post" action="/admin/alunos/liberar-documentos" onsubmit="return confirm('Liberar TODOS os documentos deste aluno para download público?');">
                  <input type="hidden" name="aluno_id" value="<?= (int) ($alunoData['id'] ?? 0) ?>">
                  <button type="submit" class="btn btn-outline-primary btn-sm"><i class="bi bi-unlock me-1"></i>Liberar todos para download público</button>
                </form>
              </div>
              <?php $documentos = is_array($documentos ?? null) ? $documentos : []; ?>
              <?php if (empty($documentos)): ?>
                <div class="alert alert-light border text-muted m-3">
                  <i class="bi bi-inbox me-1"></i>Nenhum documento definido para o grupo Alunos.
                </div>
              <?php else: ?>
                <div class="d-flex justify-content-between align-items-center p-3 pb-2 gap-2">
                  <button class="btn btn-outline-primary btn-sm" type="button" data-bs-toggle="collapse" data-bs-target="#formRegistrarDocumento" aria-expanded="false" aria-controls="formRegistrarDocumento">
                    <i class="bi bi-upload me-1"></i>Registrar documento recebido por e-mail
                  </button>
                </div>
                <div class="collapse p-3 pb-0" id="formRegistrarDocumento">
                  <div class="border rounded-3 p-3 bg-light">
                    <form method="post" action="/admin/alunos/upload-documento" enctype="multipart/form-data" id="formUploadDocumento">
                      <input type="hidden" name="aluno_id" value="<?= (int) ($alunoData['id'] ?? 0) ?>">
                      <div class="row g-2 align-items-end">
                        <div class="col-md-4">
                          <label class="form-label small fw-semibold mb-1">Tipo de documento</label>
                          <select name="id_tipo" class="form-select form-select-sm" required>
                            <option value="">Selecione...</option>
                            <?php foreach ($documentos as $docTipo): ?>
                              <?php if ((int) ($docTipo['tipo_id'] ?? 0) > 0): ?>
                                <option value="<?= (int) $docTipo['tipo_id'] ?>">
                                  <?= htmlspecialchars((string) ($docTipo['tipo_descricao'] ?? '-'), ENT_QUOTES, 'UTF-8') ?>
                                  <?= ((int) ($docTipo['obrigatorio'] ?? 0) === 1) ? ' (obrigatório)' : '' ?>
                                </option>
                              <?php endif; ?>
                            <?php endforeach; ?>
                          </select>
                        </div>
                        <div class="col-md-5">
                          <label class="form-label small fw-semibold mb-1">Arquivo</label>
                          <input type="file" name="arquivo" class="form-control form-control-sm" accept=".pdf,.png,.jpg,.jpeg" required>
                        </div>
                        <div class="col-md-3">
                          <button type="submit" class="btn btn-success btn-sm w-100"><i class="bi bi-upload me-1"></i>Registrar</button>
                        </div>
                      </div>
                      <div class="form-text small mt-2">
                        Registra o documento no Drive do aluno como <strong>Aprovado</strong>. Use para documentos recebidos por e-mail.
                      </div>
                    </form>
                  </div>
                </div>
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
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#financeiroAluno" aria-expanded="false" aria-controls="financeiroAluno">
              <i class="bi bi-cash-coin me-2"></i>Financeiro
            </button>
          </h2>
          <div id="financeiroAluno" class="accordion-collapse collapse" data-bs-parent="#alunoAccordion">
            <div class="accordion-body p-0">
              <?php $parcelasFin = is_array($parcelasFinanceiro ?? null) ? $parcelasFinanceiro : []; ?>
              <?php
                $totalParcelas = count($parcelasFin);
                $qtdPagas = 0;
                $valorTotal = 0.0;
                $valorPago = 0.0;
                $parcelaMigracao = null;
                foreach ($parcelasFin as $pf) {
                    $v = (float) ($pf['valor'] ?? 0);
                    $valorTotal += $v;
                    if (in_array((string) ($pf['status'] ?? ''), ['RECEBIDO', 'CONFIRMADO'], true)) {
                        $qtdPagas++;
                        $valorPago += $v;
                    }
                    if ($parcelaMigracao === null
                        && (int) ($pf['numero_parcela'] ?? 0) === 1
                        && in_array((string) ($pf['status'] ?? ''), ['RECEBIDO', 'CONFIRMADO'], true)
                        && (int) ($pf['id_matricula'] ?? 0) > 0
                        && count(array_filter($parcelasFin, static fn (array $outra): bool =>
                            (int) ($outra['id_matricula'] ?? 0) === (int) ($pf['id_matricula'] ?? 0)
                            && (int) ($outra['numero_parcela'] ?? 0) >= 2
                            && !in_array((string) ($outra['status'] ?? ''), ['RECEBIDO', 'CONFIRMADO'], true)
                        )) > 0
                    ) {
                        $parcelaMigracao = $pf;
                    }
                }
              ?>
              <?php if ($totalParcelas > 0): ?>
                <div class="d-flex flex-wrap gap-2 p-3 pb-0">
                  <span class="badge bg-light text-dark border"><?= $totalParcelas ?> parcela(s)</span>
                  <span class="badge bg-success"><?= $qtdPagas ?> paga(s)</span>
                  <span class="badge bg-warning text-dark"><?= $totalParcelas - $qtdPagas ?> pendente(s)</span>
                  <span class="badge bg-info text-dark">Total: R$ <?= number_format($valorTotal, 2, ',', '.') ?></span>
                  <span class="badge bg-success">Pago: R$ <?= number_format($valorPago, 2, ',', '.') ?></span>
                  <span class="badge bg-danger">Aberto: R$ <?= number_format($valorTotal - $valorPago, 2, ',', '.') ?></span>
                </div>
                <?php if (is_array($parcelaMigracao)): ?>
                  <div class="d-flex align-items-center justify-content-between gap-3 mx-3 mt-3 mb-2 p-3 border rounded-3 bg-light">
                    <div>
                      <div class="fw-semibold"><i class="bi bi-arrow-repeat me-1"></i>Alterar parcelas futuras para cartão recorrente</div>
                      <div class="small text-muted">Cancela as cobranças futuras no Asaas e envia ao aluno um novo link financeiro. A matrícula e a 1ª parcela paga serão preservadas.</div>
                    </div>
                    <button type="button" class="btn btn-primary btn-sm btn-abrir-migracao"
                            data-bs-toggle="modal" data-bs-target="#modalMigrarRecorrencia"
                            data-id-aluno="<?= (int) ($alunoData['id'] ?? 0) ?>"
                            data-id-matricula="<?= (int) ($parcelaMigracao['id_matricula'] ?? 0) ?>"
                            data-id-origem="<?= (int) ($parcelaMigracao['id'] ?? 0) ?>"
                            data-valor="<?= number_format((float) ($parcelaMigracao['valor'] ?? 0), 2, ',', '.') ?>">
                      <i class="bi bi-link-45deg me-1"></i>Gerar novo link
                    </button>
                  </div>
                <?php endif; ?>
              <?php endif; ?>
              <?php if (empty($parcelasFin)): ?>
                <div class="alert alert-light border text-muted m-3">
                  <i class="bi bi-inbox me-1"></i>Nenhuma parcela financeira encontrada para este aluno.
                </div>
              <?php else: ?>
                <div class="table-responsive">
                  <table class="table table-striped table-sm align-middle mb-0">
                    <thead>
                      <tr>
                        <th>Curso</th>
                        <th>Parcela</th>
                        <th>Vencimento</th>
                        <th>Valor</th>
                        <th>Situação</th>
                        <th>Pagamento</th>
                        <th>Ação</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php foreach ($parcelasFin as $pf): ?>
                        <?php
                          $pfNumero = (int) ($pf['numero_parcela'] ?? 0);
                          $pfTotal = (int) ($pf['total_parcelas'] ?? 0);
                          $pfStatus = (string) ($pf['status'] ?? 'PENDENTE');
                          $pfVenc = (string) ($pf['data_vencimento'] ?? '');
                          $pfValor = (float) ($pf['valor'] ?? 0);
                          $pfInvoice = (string) ($pf['invoice_url'] ?? '');
                          $pfPayment = (string) ($pf['asaas_payment'] ?? '');
                          $pfPodeMigrar = $pfNumero === 1
                            && in_array($pfStatus, ['RECEBIDO', 'CONFIRMADO'], true)
                            && (int) ($pf['id_matricula'] ?? 0) > 0
                            && count(array_filter($parcelasFin, static fn (array $outra): bool =>
                                (int) ($outra['id_matricula'] ?? 0) === (int) ($pf['id_matricula'] ?? 0)
                                && (int) ($outra['numero_parcela'] ?? 0) >= 2
                                && !in_array((string) ($outra['status'] ?? ''), ['RECEBIDO', 'CONFIRMADO'], true)
                            )) > 0;

                          $pfStatusLabel = match ($pfStatus) {
                              'RECEBIDO', 'CONFIRMADO' => 'Pago',
                              'CANCELADO' => 'Cancelado',
                              'ESTORNADO' => 'Estornado',
                              default => 'Pendente',
                          };
                          $pfStatusClass = match ($pfStatus) {
                              'RECEBIDO', 'CONFIRMADO' => 'success',
                              'CANCELADO', 'ESTORNADO' => 'danger',
                              default => 'warning',
                          };
                        ?>
                        <tr>
                          <td>
                            <div class="fw-semibold"><?= htmlspecialchars((string) ($pf['curso_nome'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></div>
                            <?php if (!empty($pf['turma_nome']) && (string) $pf['turma_nome'] !== '-'): ?>
                              <div class="text-muted small"><i class="bi bi-people me-1"></i><?= htmlspecialchars((string) $pf['turma_nome'], ENT_QUOTES, 'UTF-8') ?></div>
                            <?php endif; ?>
                          </td>
                          <td>
                            <?php if (!in_array($pfStatus, ['RECEBIDO', 'CONFIRMADO', 'CANCELADO', 'ESTORNADO'], true)): ?>
                              <button type="button" class="btn btn-sm btn-outline-success btn-lancar-pago"
                                      data-bs-toggle="modal" data-bs-target="#modalLancarParcelaPaga"
                                      data-id-parcela="<?= (int) ($pf['id'] ?? 0) ?>"
                                      data-valor="R$ <?= number_format($pfValor, 2, ',', '.') ?>"
                                      data-parcela="<?= $pfNumero > 0 ? $pfNumero . 'ª de ' . $pfTotal : 'Parcela' ?>">
                                <i class="bi bi-check-circle me-1"></i>Lançar como pago
                              </button>
                            <?php else: ?>
                              <span class="text-muted small">—</span>
                            <?php endif; ?>
                          </td>
                          <td>
                            <?php if ($pfNumero > 0): ?>
                              <span class="fw-semibold"><?= $pfNumero ?>ª</span>
                              <?php if ($pfTotal > 0): ?>
                                <span class="text-muted small"> de <?= $pfTotal ?></span>
                              <?php endif; ?>
                              <?php if ($pfNumero === 1): ?>
                                <span class="badge bg-light text-dark border ms-1">Entrada</span>
                              <?php endif; ?>
                            <?php else: ?>
                              -
                            <?php endif; ?>
                          </td>
                          <td class="text-nowrap">
                            <?= htmlspecialchars(
                              $pfVenc !== ''
                                ? (new \DateTime($pfVenc))->format('d/m/Y')
                                : '-',
                              ENT_QUOTES,
                              'UTF-8'
                            ) ?>
                          </td>
                          <td>R$ <?= number_format($pfValor, 2, ',', '.') ?></td>
                          <td>
                            <span class="badge bg-<?= $pfStatusClass ?>"><?= $pfStatusLabel ?></span>
                          </td>
                          <td>
                            <?php if ($pfInvoice !== ''): ?>
                              <a href="<?= htmlspecialchars($pfInvoice, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener noreferrer" title="Ver cobrança no Asaas">
                                <i class="bi bi-box-arrow-up-right me-1"></i>Cobrança
                              </a>
                            <?php elseif ($pfPayment !== ''): ?>
                              <span class="text-muted small" title="<?= htmlspecialchars($pfPayment, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars(substr($pfPayment, 0, 20) . (strlen($pfPayment) > 20 ? '…' : ''), ENT_QUOTES, 'UTF-8') ?></span>
                            <?php else: ?>
                              <span class="text-muted">-</span>
                            <?php endif; ?>
                            <?php if ($pfPodeMigrar): ?>
                              <button type="button" class="btn btn-sm btn-outline-primary mt-2 btn-abrir-migracao"
                                      data-bs-toggle="modal" data-bs-target="#modalMigrarRecorrencia"
                                      data-id-aluno="<?= (int) ($alunoData['id'] ?? 0) ?>"
                                      data-id-matricula="<?= (int) ($pf['id_matricula'] ?? 0) ?>"
                                      data-id-origem="<?= (int) ($pf['id'] ?? 0) ?>"
                                      data-valor="<?= number_format($pfValor, 2, ',', '.') ?>">
                                <i class="bi bi-arrow-repeat me-1"></i>Cartão recorrente
                              </button>
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
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#linksFinanceirosAluno" aria-expanded="false" aria-controls="linksFinanceirosAluno">
              <i class="bi bi-link-45deg me-2"></i>Links financeiros / acordos (<?= count($linksFinanceiro) ?>)
            </button>
          </h2>
          <div id="linksFinanceirosAluno" class="accordion-collapse collapse" data-bs-parent="#alunoAccordion">
            <div class="accordion-body p-0">
              <?php if ($linksFinanceiro === []): ?>
                <div class="alert alert-light border text-muted m-3 mb-0"><i class="bi bi-inbox me-1"></i>Sem registro/acordo</div>
              <?php else: ?>
                <div class="table-responsive">
                  <table class="table table-striped table-sm align-middle mb-0">
                    <thead><tr><th>Link</th><th>Valor</th><th>Gerado em</th><th>Último reenvio</th><th>Ação</th></tr></thead>
                    <tbody>
                      <?php foreach ($linksFinanceiro as $linkFinanceiro): ?>
                        <tr>
                          <td><a href="<?= htmlspecialchars((string) ($linkFinanceiro['url'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener noreferrer"><?= htmlspecialchars((string) ($linkFinanceiro['url'] ?? ''), ENT_QUOTES, 'UTF-8') ?></a></td>
                          <td>R$ <?= number_format((float) ($linkFinanceiro['valor_parcela'] ?? 0), 2, ',', '.') ?></td>
                          <td><?= htmlspecialchars((string) ($linkFinanceiro['created_at'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                          <td><?= htmlspecialchars((string) ($linkFinanceiro['ultimo_reenvio_em'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                          <td>
                            <form method="post" action="/admin/alunos/financeiro/reenviar-link" onsubmit="return confirm('Reenviar este link para o e-mail do aluno?');">
                              <input type="hidden" name="id_aluno" value="<?= (int) ($alunoData['id'] ?? 0) ?>">
                              <input type="hidden" name="id_link" value="<?= (int) ($linkFinanceiro['id'] ?? 0) ?>">
                              <button type="submit" class="btn btn-sm btn-outline-primary"><i class="bi bi-envelope me-1"></i>Reenviar</button>
                            </form>
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

<div class="modal fade" id="modalLancarParcelaPaga" tabindex="-1" aria-labelledby="modalLancarParcelaPagaLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form method="post" action="/admin/alunos/parcela/pagar" id="formLancarParcelaPaga">
        <input type="hidden" name="id_aluno" value="<?= (int) ($alunoData['id'] ?? 0) ?>">
        <input type="hidden" name="id_parcela" id="lancarParcelaId">
        <div class="modal-header bg-success-subtle">
          <h5 class="modal-title" id="modalLancarParcelaPagaLabel"><i class="bi bi-cash-coin me-2"></i>Lançar parcela como paga</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
        </div>
        <div class="modal-body">
          <p>Confirmar pagamento manual da <strong id="lancarParcelaNumero">parcela</strong> no valor de <strong id="lancarParcelaValor">-</strong>?</p>
          <p class="small text-muted">A cobrança do Asaas não será excluída. Apenas o status financeiro local será atualizado.</p>
          <label for="lancarParcelaSenha" class="form-label">Digite sua senha para confirmar</label>
          <input type="password" class="form-control" name="senha_confirmacao" id="lancarParcelaSenha" autocomplete="current-password" required>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-success"><i class="bi bi-check-lg me-1"></i>Confirmar pagamento</button>
        </div>
      </form>
    </div>
  </div>
</div>

<div class="modal fade" id="modalMigrarRecorrencia" tabindex="-1" aria-labelledby="modalMigrarRecorrenciaLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form method="post" action="/admin/alunos/financeiro/migrar-recorrencia" id="formMigrarRecorrencia">
        <input type="hidden" name="id_aluno" id="migracaoIdAluno">
        <input type="hidden" name="id_matricula" id="migracaoIdMatricula">
        <input type="hidden" name="id_parcela_origem" id="migracaoIdOrigem">
        <div class="modal-header bg-primary-subtle">
          <h5 class="modal-title" id="modalMigrarRecorrenciaLabel"><i class="bi bi-arrow-repeat me-2"></i>Gerar link de cartão recorrente</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
        </div>
        <div class="modal-body">
          <?php if ($linkFinanceiroMigracao !== ''): ?>
              <div class="alert alert-success mb-0">
              <div class="fw-semibold mb-2"><i class="bi bi-check-circle me-1"></i>Link gerado com sucesso</div>
              <div class="input-group">
                <input type="text" class="form-control" id="linkFinanceiroMigracao" value="<?= htmlspecialchars($linkFinanceiroMigracao, ENT_QUOTES, 'UTF-8') ?>" readonly>
                <button type="button" class="btn btn-outline-success" id="btnCopiarLinkMigracao"><i class="bi bi-copy me-1"></i>Copiar</button>
              </div>
              <div class="mt-2"><a href="<?= htmlspecialchars($linkFinanceiroMigracao, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener noreferrer"><?= htmlspecialchars($linkFinanceiroMigracao, ENT_QUOTES, 'UTF-8') ?></a></div>
              <div class="small mt-2">O link também foi enviado ao e-mail cadastrado do aluno.</div>
            </div>
          <?php else: ?>
            <p class="mb-3">As cobranças futuras serão canceladas no Asaas e substituídas por um link para o aluno escolher cartão recorrente. A matrícula e a primeira parcela paga serão preservadas.</p>
            <label for="migracaoValorParcela" class="form-label fw-semibold">Valor de cada parcela restante</label>
            <div class="input-group">
              <span class="input-group-text">R$</span>
              <input type="text" class="form-control" name="valor_parcela" id="migracaoValorParcela" inputmode="decimal" required>
            </div>
            <div class="form-text">Informe o valor acordado para cada parcela a partir da 2ª. Ele pode ser diferente da primeira parcela.</div>
            <div class="alert alert-warning small mt-3 mb-0"><i class="bi bi-exclamation-triangle me-1"></i>Esta operação cancela as cobranças futuras existentes no Asaas.</div>
          <?php endif; ?>
        </div>
        <div class="modal-footer">
          <?php if ($linkFinanceiroMigracao !== ''): ?>
            <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Concluir</button>
          <?php else: ?>
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
            <button type="submit" class="btn btn-primary"><i class="bi bi-link-45deg me-1"></i>Cancelar cobranças e gerar link</button>
          <?php endif; ?>
        </div>
      </form>
    </div>
  </div>
</div>

<div id="documentoLoader" class="d-none" style="position:fixed;inset:0;z-index:1100;background:rgba(0,0,0,.5);display:flex;align-items:center;justify-content:center;">
  <div class="d-flex flex-column align-items-center text-white px-3">
    <div class="spinner-border mb-3" style="width:3rem;height:3rem;" role="status">
      <span class="visually-hidden">Carregando...</span>
    </div>
    <p class="mb-0 fw-semibold">Enviando documento... Aguarde.</p>
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

  var uploadForm = document.getElementById('formUploadDocumento');
  if (uploadForm) {
    uploadForm.addEventListener('submit', function() {
      var overlay = document.getElementById('documentoLoader');
      if (overlay) overlay.classList.remove('d-none');
      var btn = uploadForm.querySelector('button[type="submit"]');
      if (btn) btn.disabled = true;
    });
  }

  document.querySelectorAll('.btn-lancar-pago').forEach(function (btn) {
    btn.addEventListener('click', function () {
      document.getElementById('lancarParcelaId').value = btn.getAttribute('data-id-parcela') || '';
      document.getElementById('lancarParcelaNumero').textContent = btn.getAttribute('data-parcela') || 'parcela';
      document.getElementById('lancarParcelaValor').textContent = btn.getAttribute('data-valor') || '-';
      document.getElementById('lancarParcelaSenha').value = '';
    });
  });

  document.querySelectorAll('.btn-abrir-migracao').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var aluno = document.getElementById('migracaoIdAluno');
      var matricula = document.getElementById('migracaoIdMatricula');
      var origem = document.getElementById('migracaoIdOrigem');
      var valor = document.getElementById('migracaoValorParcela');
      if (aluno) aluno.value = btn.getAttribute('data-id-aluno') || '';
      if (matricula) matricula.value = btn.getAttribute('data-id-matricula') || '';
      if (origem) origem.value = btn.getAttribute('data-id-origem') || '';
      if (valor) valor.value = btn.getAttribute('data-valor') || '';
    });
  });

  var btnCopiarLink = document.getElementById('btnCopiarLinkMigracao');
  if (btnCopiarLink) {
    btnCopiarLink.addEventListener('click', function () {
      var campo = document.getElementById('linkFinanceiroMigracao');
      if (!campo) return;
      navigator.clipboard.writeText(campo.value).then(function () {
        btnCopiarLink.innerHTML = '<i class="bi bi-check me-1"></i>Copiado';
      }).catch(function () {
        campo.select();
        document.execCommand('copy');
        btnCopiarLink.innerHTML = '<i class="bi bi-check me-1"></i>Copiado';
      });
    });
  }

  <?php if ($linkFinanceiroMigracao !== ''): ?>
    bootstrap.Modal.getOrCreateInstance(document.getElementById('modalMigrarRecorrencia')).show();
  <?php endif; ?>
</script>
