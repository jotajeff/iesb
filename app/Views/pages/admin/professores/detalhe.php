<section class="container py-4">
  <div class="bg-white border rounded-3 p-4 shadow-sm">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
      <h4 class="mb-0"><i class="bi bi-person-circle me-2"></i>Detalhes do Professor</h4>
      <a class="btn btn-outline-secondary btn-sm" href="/admin/professores"><i class="bi bi-arrow-left me-1"></i>Voltar</a>
    </div>

    <nav class="nav nav-pills flex-wrap gap-1 mb-4 p-2 rounded-3" style="background:#f8f9fa;border:1px solid #dee2e6;">
      <a class="btn btn-sm btn-outline-primary" href="#secao-endereco"><i class="bi bi-geo-alt me-1"></i>Endereço</a>
      <a class="btn btn-sm btn-outline-primary" href="#secao-redes"><i class="bi bi-share me-1"></i>Redes Sociais</a>
      <a class="btn btn-sm btn-outline-primary" href="#secao-resumo"><i class="bi bi-card-text me-1"></i>Resumo</a>
      <a class="btn btn-sm btn-outline-primary" href="#secao-curriculo"><i class="bi bi-file-earmark-text me-1"></i>Currículo</a>
      <a class="btn btn-sm btn-outline-primary" href="#secao-fotos"><i class="bi bi-camera me-1"></i>Fotos</a>
      <a class="btn btn-sm btn-outline-primary" href="#secao-documentos"><i class="bi bi-folder2-open me-1"></i>Documentos</a>
      <a class="btn btn-sm btn-outline-primary" href="#secao-turmas"><i class="bi bi-link-45deg me-1"></i>Turmas</a>
    </nav>

    <div class="row">
      <div class="col-md-3 order-md-last mb-3 mb-md-0 text-center">
        <?php $foto = $imagens[0]['path'] ?? null; ?>
        <?php if ($foto): ?>
          <img src="/<?= htmlspecialchars($foto, ENT_QUOTES, 'UTF-8') ?>" class="img-fluid rounded-circle border" style="width:150px;height:150px;object-fit:cover;">
        <?php else: ?>
          <div class="d-inline-flex align-items-center justify-content-center bg-light rounded-circle border" style="width:150px;height:150px;">
            <i class="bi bi-person fs-1 text-muted"></i>
          </div>
        <?php endif; ?>
      </div>

      <div class="col-md-9">
        <dl class="row mb-0">
          <dt class="col-sm-3 text-muted">ID</dt>
          <dd class="col-sm-3"><?= (int) ($professor['id'] ?? 0) ?></dd>
          <dt class="col-sm-3 text-muted">Ativo</dt>
          <dd class="col-sm-3">
            <?php if ((int) ($professor['ativo'] ?? 1) === 1): ?>
              <span class="badge bg-success">Sim</span>
            <?php else: ?>
              <span class="badge bg-secondary">Não</span>
            <?php endif; ?>
          </dd>

          <dt class="col-sm-3 text-muted">Nome</dt>
          <dd class="col-sm-9"><?= htmlspecialchars((string) ($professor['nome'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></dd>

          <dt class="col-sm-3 text-muted">Email</dt>
          <dd class="col-sm-9"><?= htmlspecialchars((string) ($professor['email'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></dd>

          <dt class="col-sm-3 text-muted">Telefone</dt>
          <dd class="col-sm-9"><?= htmlspecialchars((string) ($professor['telefone'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></dd>
        </dl>
      </div>
    </div>

    <hr class="mt-3">
    <h5 id="secao-endereco" style="scroll-margin-top:80px;"><i class="bi bi-geo-alt me-1"></i>Endereço</h5>
    <?php if ($endereco): ?>
      <dl class="row">
        <dt class="col-sm-2 text-muted">Logradouro</dt>
        <dd class="col-sm-10"><?= htmlspecialchars((string) ($endereco['logradouro'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></dd>
        <dt class="col-sm-2 text-muted">Número</dt>
        <dd class="col-sm-10"><?= htmlspecialchars((string) ($endereco['numero'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></dd>
        <dt class="col-sm-2 text-muted">Cidade</dt>
        <dd class="col-sm-10"><?= htmlspecialchars((string) ($endereco['cidade'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></dd>
        <dt class="col-sm-2 text-muted">CEP</dt>
        <dd class="col-sm-10"><?= htmlspecialchars((string) ($endereco['cep'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></dd>
        <dt class="col-sm-2 text-muted">UF</dt>
        <dd class="col-sm-10"><?= htmlspecialchars((string) ($endereco['uf'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></dd>
      </dl>
    <?php else: ?>
      <p class="text-muted">Nenhum endereço cadastrado.</p>
    <?php endif; ?>

    <hr>
    <h5 id="secao-redes" style="scroll-margin-top:80px;"><i class="bi bi-share me-1"></i>Redes Sociais</h5>
    <?php if (!empty($social)): ?>
      <ul>
        <?php foreach ($social as $rede): ?>
          <li><strong><?= htmlspecialchars((string) ($rede['rede'] ?? '-'), ENT_QUOTES, 'UTF-8') ?>:</strong> <a href="<?= htmlspecialchars((string) ($rede['link_perfil'] ?? '#'), ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener"><?= htmlspecialchars((string) ($rede['link_perfil'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></a></li>
        <?php endforeach; ?>
      </ul>
    <?php else: ?>
      <p class="text-muted">Nenhuma rede social vinculada.</p>
    <?php endif; ?>

    <hr>
    <h5 id="secao-resumo" style="scroll-margin-top:80px;"><i class="bi bi-file-earmark-text me-1"></i>Resumo</h5>
    <?php if ($curriculo && trim((string) ($curriculo['resumo'] ?? '')) !== ''): ?>
      <p><?= nl2br(htmlspecialchars((string) ($curriculo['resumo'] ?? ''), ENT_QUOTES, 'UTF-8')) ?></p>
    <?php else: ?>
      <p class="text-muted">Nenhum resumo cadastrado.</p>
    <?php endif; ?>

    <hr>
    <h5 id="secao-curriculo" style="scroll-margin-top:80px;"><i class="bi bi-file-earmark-text me-1"></i>Currículo</h5>
    <?php if ($curriculo && trim((string) ($curriculo['conteudo'] ?? '')) !== ''): ?>
      <div class="p-3 border rounded bg-light"><?= ($curriculo['conteudo'] ?? '') ?></div>
    <?php else: ?>
      <p class="text-muted">Nenhum currículo cadastrado.</p>
    <?php endif; ?>

    <hr>
    <h5 id="secao-fotos" style="scroll-margin-top:80px;"><i class="bi bi-camera me-1"></i>Fotos</h5>
    <?php if (!empty($imagens)): ?>
      <div class="row g-2">
        <?php foreach ($imagens as $img): ?>
          <div class="col-md-2">
            <img src="/<?= htmlspecialchars((string) ($img['path'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" class="img-fluid rounded border" alt="<?= htmlspecialchars((string) ($img['legenda'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" style="height:100px;object-fit:cover;">
          </div>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <p class="text-muted">Nenhuma foto cadastrada.</p>
    <?php endif; ?>

    <hr>
    <h5 id="secao-documentos" style="scroll-margin-top:80px;"><i class="bi bi-folder2-open me-1"></i>Documentos</h5>
    <?php $documentos = is_array($documentos ?? null) ? $documentos : []; ?>
    <?php if (empty($documentos)): ?>
      <p class="text-muted">Nenhum documento definido para o grupo Professores.</p>
    <?php else: ?>
      <div class="table-responsive">
        <table class="table table-sm table-striped align-middle">
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
            <?php
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
            <?php foreach ($documentos as $doc): ?>
              <?php
              $docId = (int) ($doc['documento_id'] ?? 0);
              $status = (string) ($doc['status'] ?? 'nao_enviado');
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
                      onclick="visualizarDocumentoProf('<?= htmlspecialchars((string) ($doc['tipo_descricao'] ?? ''), ENT_QUOTES, 'UTF-8') ?>', '<?= htmlspecialchars((string) ($doc['file_id'] ?? ''), ENT_QUOTES, 'UTF-8') ?>')"
                      title="Visualizar">
                      <i class="bi bi-eye"></i>
                    </button>
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

    <hr>
    <h5 id="secao-turmas" style="scroll-margin-top:80px;"><i class="bi bi-link-45deg me-1"></i>Turmas Vinculadas</h5>
    <?php if (!empty($turmas)): ?>
      <div class="table-responsive">
        <table class="table table-sm table-striped align-middle">
          <thead>
            <tr>
              <th>#</th>
              <th>Turma</th>
              <th>Curso</th>
              <th>Início</th>
              <th>Fim</th>
              <th>Ativa</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($turmas as $t): ?>
              <tr>
                <td><?= (int) ($t['id'] ?? 0) ?></td>
                <td><?= htmlspecialchars((string) ($t['nome'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars((string) ($t['curso_nome'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars((string) ($t['data_inicio'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars((string) ($t['data_fim'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= (int) ($t['ativo'] ?? 0) ? '<span class="badge bg-success">Sim</span>' : '<span class="badge bg-secondary">Não</span>' ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php else: ?>
      <p class="text-muted">Nenhuma turma vinculada.</p>
    <?php endif; ?>
  </div>
</section>

<div class="modal fade" id="verDocumentoProfModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="verDocumentoProfTitulo"><i class="bi bi-eye me-2"></i>Visualizar documento</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
      </div>
      <div class="modal-body text-center">
        <div class="ratio ratio-4x3">
          <iframe id="verDocumentoProfFrame" src="" allowfullscreen></iframe>
        </div>
        <div id="verDocumentoProfFallback" class="d-none py-4">
          <i class="bi bi-file-earmark-pdf fs-1 text-muted"></i>
          <p class="mt-2 mb-0">Não foi possível incorporar a visualização.</p>
        </div>
      </div>
      <div class="modal-footer">
        <a id="verDocumentoProfDownload" href="#" target="_blank" rel="noopener" class="btn btn-outline-primary btn-sm">
          <i class="bi bi-download me-1"></i>Baixar PDF
        </a>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
      </div>
    </div>
  </div>
</div>

<script>
function visualizarDocumentoProf(titulo, fileId) {
  if (!fileId) {
    alert('Arquivo não encontrado no Drive.');
    return;
  }

  document.getElementById('verDocumentoProfTitulo').textContent = 'Visualizar documento: ' + titulo;

  var frame = document.getElementById('verDocumentoProfFrame');
  var fallback = document.getElementById('verDocumentoProfFallback');
  var download = document.getElementById('verDocumentoProfDownload');

  frame.src = 'https://drive.google.com/file/d/' + encodeURIComponent(fileId) + '/preview';
  frame.classList.remove('d-none');
  fallback.classList.add('d-none');
  download.href = 'https://drive.google.com/uc?export=download&id=' + encodeURIComponent(fileId);

  var modalEl = document.getElementById('verDocumentoProfModal');
  var modal = bootstrap.Modal.getOrCreateInstance(modalEl);
  modal.show();

  frame.onload = function() {
    try {
      if (frame.contentDocument && frame.contentDocument.body && !frame.contentDocument.body.innerHTML.trim()) {
        fallback.classList.remove('d-none');
      }
    } catch (err) {
      frame.classList.remove('d-none');
    }
  };
}

document.getElementById('verDocumentoProfModal').addEventListener('hidden.bs.modal', function() {
  document.getElementById('verDocumentoProfFrame').src = '';
});
</script>
